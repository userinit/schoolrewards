<?php

// db creds
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

// Class array for updating classes
$yearInfo = [
    "10" => ["Freud", "Galilei", "Lavoisier", "Kepler", "Copernicus"],
    "11" => ["Pauling", "Virchow", "Schrodinger", "Rutherford", "Dirac"],
    "12" => ["Curie", "Herschel", "Lyell", "Laplace", "Hubble"],
    "13" => ["Eddington", "Harvey", "Malpighi", "Huygens", "Gauss"]
];

// For password reset
$minChars = 10; // Edit this depending on password requirements
$maxChars = 30; // Max chars to stop overflows

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
                echo json_encode(array("failure" => "Not found in database ):"));
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
}
elseif (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === "teacher" || $_SESSION['role'] === "admin") {
    if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['item']) && $_GET['item'] === "profile") {
        $username = $_SESSION['username'];
        $conn = new mysqli($host, $srvuser, $srvpass, $db);
        if ($conn->connect_error) {
            die("Error: " . $conn->connect_error);
        }
        $stmt = $conn->prepare("SELECT fullname FROM staff WHERE username = ?;");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $results = $result->fetch_assoc();
            $fullname = $results['fullname'];
            $response = json_encode(array(
                'username' => $username,
                'fullname' => $fullname
            ));
            header("Content-Type: application/json");
            echo $response;
        }
        else {
            header("Content-Type: application/json");
            echo json_encode(array("failure" => "Not found in database ):"));
        }
        $stmt->close();
        $conn->close();
        http_response_code(200);
    }
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $roleArray = ['student', 'teacher', 'admin'];
    if (isset($_SESSION['username']) && isset($_SESSION['role']) && in_array($_SESSION['role'], $roleArray)) {
        $username = $_SESSION['username'];
        $jsonData = file_get_contents('php://input');
        // decodes request
        $decoded = json_decode($jsonData, true);
        if (array_key_exists('logout', $decoded) && $decoded['logout'] === TRUE) {
            header("Location: http://localhost/digistamp/login.html");
            $_SESSION = [];
            session_destroy();
        }
        elseif (array_key_exists("old", $decoded) && array_key_exists("new", $decoded) && array_key_exists("confirm", $decoded)) {
            // defines bools for validation
            $hasUpper = FALSE;
            $hasLower = FALSE;
            $hasNums = FALSE;
            $hasSyms = FALSE;
            $goodLength = FALSE;
            
            $oldPass = $decoded['old'];
            $newPass = $decoded['new'];
            $confPass = $decoded['confirm'];
            $conn = new mysqli($host, $srvuser, $srvpass, $db);
            if ($conn->connect_error) {
                die("Error: " . $conn->connect_error);
            }
            if ($_SESSION['role'] === "teacher" || $_SESSION['role'] === "admin") {
                $stmt = $conn->prepare("SELECT * FROM staff WHERE username = ?;");
            }
            else {
                $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // User exists
                $results = $result->fetch_assoc();
                $hash = $results['password_hash'];
                if (password_verify($oldPass, $hash)) {
                    // Old password matches, check whether passwords are the same then change them
                    if ($newPass === $confPass) {
                        // Make sure they are not trying to change to the same password they already have
                        if ($oldPass !== $newPass) {
                            // Check nums
                            if (preg_match('/[0-9]/', $newPass)) {
                                $hasNums = TRUE;
                            }
                            if (preg_match('/[a-z]/', $newPass)) {
                                $hasLower = TRUE;
                            }
                            if (preg_match('/[A-Z]/', $newPass)) {
                                $hasUpper = TRUE;
                            }
                            if (preg_match("/[!£$%^&*()_+|~=`{}\[\]:\";'<>?,.\/-]/", $newPass)) {
                                $hasSyms = TRUE;
                            }
                            if (strlen($newPass) >= $minChars && strlen($newPass) <= $maxChars) {
                                $goodLength = TRUE;                                
                            }
                            if ($hasNums && $hasLower && $hasUpper && $hasSyms && $goodLength) {
                                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                                if ($_SESSION['role'] === "teacher" || $_SESSION['role'] === "admin") {
                                    $stmt = $conn->prepare("UPDATE staff SET password_hash = ? WHERE username = ?;");
                                }
                                else {
                                    $stmt = $conn->prepare("UPDATE students SET password_hash = ? WHERE username = ?;");
                                }
                                $stmt->bind_param("ss", $newHash, $username);
                                $stmt->execute();
                                if ($stmt->affected_rows > 0) {
                                    $success = json_encode(array("success" => "Successfully changed password"));
                                    header("Content-Type: application/json");
                                    echo $success;
                                }
                                else {
                                    $failure = json_encode(array("failure" => "Password change failed"));
                                    header("Content-Type: application/json");
                                    echo $failure;
                                }
                            }
                            else {
                                $response = '';
                                if (!$goodLength) {
                                    $response .= "Password needs to be between $minChars and $maxChars"; 
                                }
                                if (!($hasLower && $hasUpper && $hasNums && $hasSyms)) {
                                    if (!$goodLength) {
                                        $response .= "<br>";
                                    }
                                    $response .= "Password needs to have lowercase, uppercase, numbers and symbols";
                                }
                                $failure = json_encode(array("failure" => $response));
                                header("Content-Type: application/json");
                                echo $failure;
                            }
                        }
                        else {
                            $failure = json_encode(array("failure" => "Password must be different to current password"));
                            header("Content-Type: application/json");
                            echo $failure;
                        }
                    }
                    else {
                        $failure = json_encode(array("failure" => "Passwords need to match"));
                        header("Content-Type: application/json");
                        echo $failure;
                    }
                }
                else {
                    // Old password doesn't match, give response
                    $failure = json_encode(array("failure" => "Invalid password"));
                    header("Content-Type: application/json");
                    echo $failure;
                }
            }
            else {
                // User doesn't exist
                $failure = json_encode(array("failure" => "User doesn't exist"));
                header("Content-Type: application/json");
                echo $failure;
            }
            if (isset($conn)) {
                $conn->close();
                $stmt->close();
            }
        }
        elseif (isset($_SESSION['role']) && $_SESSION['role'] === "admin") {
            if (array_key_exists("moveYearUp", $decoded) && $decoded['moveYearUp'] === TRUE) {
                $conn = new mysqli($host, $srvuser, $srvpass, $db);
                if ($conn->connect_error) {
                    die("Error: " . $conn->connect_error);
                }
                $result1 = $conn->query("DELETE * FROM students WHERE school_year = 11 OR school_year = 13;");
                $result2 = $conn->query("UPDATE students SET school_year = 11 WHERE school_year = 10;");
                $result3 = $conn->query("UPDATE students SET school_year = 13 WHERE school_year = 12;");
    
                if ($result1 && $result2 && $result3) {
                    $json = json_encode(array("Removed year 11 and 13. Moved year 10->11. Moved year 12->13"));
                    header("Content-Type: application/json");
                    echo $json;
                    // All options have been processed, send response   
                }
                else {
                    $failMsg = '';
                    if (!$result1) {
                        $failMsg .= "Failed to remove year 11 and 13. ";
                    }
                    if (!$result2) {
                        $failMsg .= "Failed to move year 10->11. ";
                    }
                    if (!$result3) {
                        $failMsg .= "Failed to move year 12->13. ";
                    }
                    $json = json_encode(array("failure" => $failMsg));
                    header("Content-Type: application/json");
                    echo $json;
                }
            }
            if (isset($_FILES['csvFile'])) {
                $csvFile = $_FILES['csvFile']['tmp_name'];
                $handle = fopen($csvFile, "r");
                if ($handle !== FALSE) {
                    $firstRow = fgetcsv($handle);
                    $firstRow = array_map('strtolower', $firstRow);
                    $firstRow = array_map('trim', $firstRow);
                    $firstRowCols = count($firstRow);
                    if ($firstRowCols === 2) {
                        $usernameIndex = array_search('username');
                        $classIndex = array_search('class');
                        if ($usernameIndex === FALSE || $classIndex === FALSE) {
                            // handle
                        }
                        else {
                            $f = 0; // $f is failure index for key-value pairs
                            $s = 0; // $s is success index for key-value pairs
                            while (($data = fgetcsv($handle)) !== FALSE) {
                                $username = $data[$usernameIndex];
                                $class = $data[$classIndex];
                                $conn = new mysqli($host, $srvuser, $srvpass, $db);
                                if ($conn->connect_error) {
                                    die("Error: " . $conn->connect_error);
                                }
                                $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
                                $stmt->bind_param("s", $username);
                                $stmt->execute();
                                if (!($stmt->get_result()->num_rows > 0 )) {
                                    $failure[] = array("Failure$f" => [
                                        "success" => "No",
                                        "username" => $username,
                                        "fullname" => "",
                                        "startClass" => "",
                                        "endClass" => $class,
                                        "errors" => "Invalid username"
                                    ]);
                                    $f++;
                                }
                                else {
                                    $results = $stmt->get_result()->fetch_assoc();
                                    $year = $results['school_year'];
                                    $startClass = $results['class'];
                                    $forename = $results['forename'];
                                    $surname = $results['surname'];
                                    $fullname = "$forename $surname";
                                    $validClass = array_search($class, $yearInfo[$year]);
                                    if (!($validClass === TRUE)) {
                                        $failure[] = array("Failure$f" => [
                                            "success" => "No",
                                            "username" => $username,
                                            "fullname" => $fullname,
                                            "startClass" => $startClass,
                                            "endClass" => $class,
                                            "errors" => "Invalid new class"
                                        ]);
                                        $f++;
                                    }
                                    else {
                                        // valid class - continue SQL logic
                                        $stmt = $conn->prepare("UPDATE students SET class = ? WHERE username = ?;");
                                        $stmt->bind_param("ss", $class, $username);
                                        $stmt->execute();
                                        if ($stmt->affected_rows > 0) {
                                            $success[] = array("Success$f" => [
                                                "success" => "Yes",
                                                "username" => $username,
                                                "fullname" => $fullname,
                                                "startClass" => $startClass,
                                                "endClass" => $class,
                                                "errors" => "N/A"
                                            ]);
                                            $s++;
                                        }
                                    }
                                }
                            }
                            $jsonArr = json_encode(array($success, $failure));
                            header("Content-Type: application/json");
                            echo $jsonArr;
                        }
                    }
                }
            }
        }
    }
}

?>