<?php
// start session and edit ini file to let session be 24 hours.
ini_set('session.cookie_lifetime', 86400); // 60*60*24=86400s
session_start();

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
        echo json_encode(array('invalid' => 'Failed to decode JSON data.'));
    }
    else {
        $username = $postData['username'];
        // removes case sensitivity
        $username = strtolower($username);
        $password = $postData['password'];

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
                $stmt->bind_param("s", $username);
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
                    else {
                        $response = json_encode(array('invalid' => "Error: Undefined role. Speak to system admin."));
                        header("Content-Type: application/json");
                        echo $response;
                    }
                    if (!password_verify($password, $dbpass)) {
                        $response = json_encode(array('invalid' => 'Invalid username or password.'));
                        header("Content-Type: application/json");
                        echo $response;
                    }
                    else {
                        // CSRF token removed for simplicity - may be readded soon
                        // Start session with their username
                        $_SESSION['username'] = $username;
                        if ($role === "admin") {
                            header("Content-Type: text/html");
                            header("Location: http://localhost/digistamp/admin.html");
                            exit();
                        }
                        elseif ($role === "teacher") {
                            header("Location: http://localhost/digistamp/teacher.html");
                            exit();
                        }
                        else {
                            header("Location: http://localhost/digistamp/dashboard.html");
                            exit();
                        }
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