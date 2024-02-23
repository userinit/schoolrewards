<?php

// db creds
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

session_start();

if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === "student") {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $username = $_SESSION['username'];
        if (isset($_GET['item']) && $_GET['item'] === "stamps") {
            $conn = new mysqli($host, $srvuser, $srvpass, $db);
            if ($conn->connect_error) {
                die("Error: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("SELECT stamps FROM students WHERE username = ?;");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $results = $result->fetch_assoc();
                $stamps = $results['stamps'];
                header("Content-Type: application/json");
                echo json_encode(array("stamps" => $stamps));
            }
            else {
                header("Content-Type: application/json");
                echo json_encode(array("failure" => "Failed to load stamps ):"));
            }
            $stmt->close();
            $conn->close();
        }
        elseif (isset($_GET['item']) && $_GET['item'] === "profile") {
            $conn = new mysqli($host, $srvuser, $srvpass, $db);
            if ($conn->connect_error) {
                die("Error: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $results = $result->fetch_assoc();
                // not encoded
                $class = $results['class'];
                $year = $results['school_year'];
                $tutor = $results['tutor'];
                $forename = $results['forename'];
                $surname = $results['surname'];
                $fullname = "$forename $surname";
            }
            if (is_int(intval($year))) {
                // sanitization
                $fullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
                $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                $tutor = htmlspecialchars($tutor, ENT_QUOTES, 'UTF-8');
                $class = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
                // encode then AJAX
                $user_info = array(
                    'username' => $username,
                    'class' => $class,
                    'year' => $year,
                    'tutor' => $tutor,
                    'name' => $fullname
                );
                $studentInfo = json_encode($user_info);
                header('Content-Type: application/json');
                echo $studentInfo;
            }
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
        $username = $_SESSION['username'];
        $jsonData = file_get_contents('php://input');
        // decodes request
        $decoded = json_decode($jsonData, true);
        if (array_key_exists('logout', $decoded) && $decoded['logout'] === TRUE) {
            header("Location: http://localhost/digistamp/login.html");
            $_SESSION = [];
            session_destroy();
        }
    }
}
// Add elseif for teachers then admins

?>