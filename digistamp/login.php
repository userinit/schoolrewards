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
                        // token part removed for simplicity
                        ini_set('session.cookie_lifetime', 3600);
                        session_start();

                        // Start session with their username
                        $_SESSION['username'] = $username;

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