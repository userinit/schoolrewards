<?php

// define database info
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Decode JSON then take the username and password
    $postData = json_decode(file_get_contents('php://input'), TRUE);
    if ($postData == null) {
        header("Content-Type: application/json");
        echo json_encode(array('error' => 'Failed to decode JSON data.'));
    }
    else {
        $username = $postData['username'];
        $password = $postData['password'];

        // Non-AJAX method below
        //$username = htmlspecialchars($_POST["username"], ENT_QUOTES, 'UTF-8');
        //$password = $_POST["password"];

        // Starts connection and ends if failed
        $conn = new mysqli($host, $srvuser, $srvpass, $db);
        if ($conn->connect_error) {
            die("Connection erorr: " . $conn->connect_error);
        }
        else {
            // Prepared statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT user_role FROM roles WHERE username = ?;");
            $stmt->bind_param("s",$username);
            $result = $stmt->execute();
            if (!$result) {
                // Error handling
            }
            $result_set = $stmt->get_result();
            $num_rows = $result_set->num_rows;
            if (!($num_rows > 0)) {
                $response = json_encode(array('invalid' => 'Invalid username or password.'));
                header("Content-Type: application/json");
                echo $response;
            }
            else {
                // Statements depending on whether person is staff or not (staff being teacher/admin)
                $row = $result_set->fetch_assoc();
                $role = $row['user_role'];
                if ($role === "admin" || $role === "teacher") {
                    $stmt = $conn->prepare("SELECT * FROM staff WHERE username = ?;");
                }
                else {
                    $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
                }
                $stmt->bind_param("s",$username);
                $result = $stmt->execute();
                if (!$result) {
                    // error handling
                }
                $result_set = $stmt->get_result();
                $num_rows = $result_set->num_rows;
                if (!($num_rows > 0)) {
                    $response = json_encode(array('invalid' => 'Invalid username or password.'));
                    header("Content-Type: application/json");
                    echo $response;
                }
                else {
                    $row = $result_set->fetch_assoc();
                    $dbuser = $row['username'];
                    $dbpass = $row['password_hash'];
                    if ($role === "admin" || $role === "teacher" || $role === "student") {
                        $_SESSION['role'] = $role;
                    }
                    if (!password_verify($password, $dbpass)) {
                        $response = json_encode(array('invalid' => 'Invalid username or password.'));
                        header("Content-Type: application/json");
                        echo $response;
                    }
                    else {
                        // Start session with their username
                        $_SESSION['username'] = $username;

                        // DEFINE FUNCTION VALUES

                        // NOTE: THESE TOKENS ARE BY NO MEANS ADEQUATE FOR CSRF PROTECTION!!!!!
                        // IF YOU WANT TO DO CSRF PROTECTION EITHER SEND TOKEN TO FRONT END OR USE JWT!!!
                        ini_set('session.cookie_lifetime', 3600);
                        session_start();

                        function generate_csrf_token() {
                            $token = bin2hex(random_bytes(32));
                            $expirationTime = time() + 3600; // 24 hour token (3600=24x60x60)
                            $_SESSION['csrf_token'] = $token;
                            $_SESSION['token_expiration'] = $expirationTime;
                            return['token' => $token, 'expiration' => $expirationTime];
                        }

                        function store_csrf_token($host, $srvuser, $srvpass, $db, $username, $token, $expirationTime) {
                            $conn = new mysqli($host, $srvuser, $srvpass, $db);
                            if ($conn->connect_error) {
                                die("Connection error: " . $conn->connect_error);
                            }
                            else {
                                $stmt = $conn->prepare("INSERT INTO tokens (username, token, expiration_time) VALUES (?, ?, ?);");
                                $stmt->bind_param("ssi", $username, $token, $expirationTime);
                                $stmt->execute();
                                $stmt->close();
                                $conn->close();
                            }
                        }

                        // Adds CSRF token to db
                        function set_csrf_token($host, $srvuser, $srvpass, $db, $username) {
                            $conn = new mysqli($host, $srvuser, $srvpass, $db);
                            if ($conn->connect_error) {
                                die("Connection error: " . $conn->connect_error);
                            }
                            else {
                                $stmt = $conn->prepare("SELECT * FROM tokens WHERE username = ?;");
                                $stmt->bind_param("s", $username);
                                $result = $stmt->execute();
                                if (!$result) {
                                    // error handling
                                }

                                $result_set = $stmt->get_result();
                                $num_rows = $result_set->num_rows;
                                if (!($num_rows > 0)) {
                                    //$row = $result_set->fetch_assoc();
                                    //$expirationTime = $row['expiration_time'];
                                    $stmt->close();
                                    $conn->close();
                                    $tokenInfo = generate_csrf_token();
                                    $token = $tokenInfo['token'];
                                    $expirationTime = $tokenInfo['expiration'];
                                    store_csrf_token($host, $srvuser, $srvpass, $db, $username, $token, $expirationTime);
                                }
                                else {
                                    $tokenInfo = generate_csrf_token();
                                    $token = $tokenInfo['token'];
                                    $expirationTime = $tokenInfo['expiration'];
                                    $stmt = $conn->prepare("UPDATE tokens SET token = ?, expiration_time = ? WHERE username = ?;");
                                    $stmt->bind_param("sis", $token, $expirationTime, $username);
                                    $stmt->execute();
                                    $stmt->close();
                                    $conn->close();
                                }
                            }
                        }

                        // TOKEN LOGIC
                        // Token deprecated - just use $_SESSION['username']
                        set_csrf_token($host, $srvuser, $srvpass, $db, $username);
                        
                        // redirect here
                        //http_response_code(302);
                        header("Location: http://localhost/digistamp/dashboard");
                    }
                }
            }
        }
    }
}
else {
    http_response_code(405);
}



?>