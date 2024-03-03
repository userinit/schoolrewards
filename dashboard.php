<?php

// db creds
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

$auditPath = "assets/audit.csv"; // path to audit log

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

// Sorting algorithm for student format with split surnames and forenames
function sortingAlgorithm($a, $b) {
    // Compare surnames
    $surnameComparison = strcmp($a['surname'],  $b['surname']);
    
    // If surnames are the same, compare forenames
    if ($surnameComparison == 0) {
        $forenameComparison = strcmp($a['forename'], $b['forename']);
        return $forenameComparison;
    }
    return $surnameComparison;
}

// Sorting algorithm for staff format with full names
function sortStaff($a, $b) {
    $comparison = strcmp($a['fullname'], $b['fullname']);
    return $comparison;
}

function logEntry($name, $username, $action, $auditPath) {
    // {logID}, {date}, {time}, {name}, {username}, {action}
    if (file_exists($auditPath)) {
        // logs.csv exists - use it
        $rows = file($auditPath);
        $lastRow = array_pop($rows);
        $data = str_getcsv($lastRow);
        $lastLogID = preg_replace("/[^0-9]/", "", $data[0]);
        // If $lastLogID isn't empty then increment, otherwise set it to 0
        $newLogID = ($lastLogID !== "") ? ++$lastLogID : 0;
    }
    else {
        touch($auditPath); // create empty file
        $newLogID = 0;
    }
    $timezone = date_default_timezone_get();
    $timestamp = time();
    $gmtOffset = date('Z', $timestamp); // Calculates GMT offset
    $gmtOffsetHours = $gmtOffset / 3600;
    if ($gmtOffset == 1) {
        date_default_timezone_set('GMT+1');
    }
    $date = gmdate("d/m/Y", $timestamp); // date in DD/MM/YYYY
    $time = gmdate("H:i:s", $timestamp); // 24h time in HH:MM:SS (according to timezone)

    $handle = fopen($auditPath, "a");
    $data = array($newLogID, $date, $time, $name, $username, $action);
    fputcsv($handle, $data);
    fclose($handle);
}

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
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        if (isset($_GET['item']) && $_GET['item'] === "profile") {
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
        if ($_SESSION['role'] === "admin") {
            if (isset($_GET['students'])) {
                $conn = new mysqli($host, $srvuser, $srvpass, $db);
                if ($conn->connect_error) {
                    die("Error: " . $conn->connect_error);
                }
                $result = $conn->query("SELECT * FROM students;");
                if ($result->num_rows > 0) {
                    // success
                    // extract student data with iteration
                    // Sorts array
                    while ($row = $result->fetch_assoc()) {
                        $username = $row['username'];
                        $class = $row['class'];
                        $tutor = $row['tutor'];
                        $year = $row['school_year'];
                        $forename = $row['forename'];
                        $surname = $row['surname'];
                        $student[] = [
                            "surname" => $surname,
                            "forename" => $forename,
                            "username" => $username,
                            "class" => $class,
                            "tutor" => $tutor,
                            "year" => $year
                        ];
                    }
                    usort($student, 'sortingAlgorithm');
                    header("Content-Type: application/json");
                    if ($student) {
                        echo json_encode($student);
                    }
                    else {
                        $failure = "Failed to load data ):";
                        echo json_encode(array("failure" => $failure));
                    }
                }
                else {
                    header("Content-Type: application/json");
                    $failure = "No students found in database.";
                    echo json_encode(array("failure" => $failure));
                }
            }
            elseif (isset($_GET['staff'])) {
                $conn = new mysqli($host, $srvuser, $srvpass, $db);
                if ($conn->connect_error) {
                    die("Error: " . $conn->connect_error);
                }
                $result = $conn->query("SELECT * FROM staff");
                if ($result->num_rows > 0) {
                    $staff = [];
                    $i = 0;
                    while ($row = $result->fetch_assoc()) {
                        $username = $row['username'];
                        $fullname = $row['fullname'];
                        // search for roles in roles table
                        $stmt = $conn->prepare("SELECT user_role FROM roles WHERE username = ?");
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $roleResult = $stmt->get_result();
                        if ($roleResult->num_rows > 0) {
                            $role = $roleResult->fetch_assoc()['user_role'];
                        }
                        else {
                            $role = '';
                        }
                        $staff[] = [
                            'fullname' => $fullname,
                            'username' => $username,
                            'role' => $role
                        ];
                    }
                    usort($staff, 'sortStaff');
                    header("Content-Type: application/json");
                    if ($staff) {
                        echo json_encode($staff);
                    }
                    else {
                        echo json_encode(array("failure" => "User list empty..."));
                    }
                }
            }
        }
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

                $stmt = $conn->prepare("SELECT fullname FROM staff WHERE username = ?;");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $results = $result->fetch_assoc();
                    $name = $results['fullname'];
                }
                else {
                    $name = '';
                }
                $action = "Moved years up";
                logEntry($name, $username, $action, $auditPath);

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
            elseif (isset($_FILES['csvFile'])) {
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
                            // Prepare for logging
                            $stmt = $conn->prepare("SELECT fullname FROM staff WHERE username = ?;");
                            $admUser = $_SESSION['username'];
                            $stmt->bind_param("s", $admUser);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $results = $result->fetch_assoc();
                                $name = $results['fullname'];
                            }
                            if ($startClass) {
                                $action = "Changed $fullname's class from $startClass to $endClass";
                            }
                            else {
                                $action = "Tried to change $fullname's class to $endClass";
                            }
                            logEntry($name, $admUser, $action, $auditPath);
                        }
                    }
                }
            }
            elseif (array_key_exists("delUser", $decoded) && $decoded['delUser'] === TRUE) {
                $conn = new mysqli($host, $srvuser, $srvpass, $db);
                if ($conn->connect_error) {
                    die("Error: " . $conn->connect_error);
                }
                $username = $decoded['username'];
                $role = $decoded['role'];
                if ($role === "staff" || $role === "students") {
                    if ($role === "staff") {
                        $stmt = $conn->prepare("SELECT * FROM staff WHERE username = ?;");
                    }
                    else {
                        $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
                    }
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $results = $result->fetch_assoc();
                        if ($role === "staff") {
                            $fullname = $results['fullname'];
                            $stmt = $conn->prepare("DELETE FROM staff WHERE username = ?;");
                        }
                        else {
                            $fullname = $results['forename'] . " " . $results['surname'];
                            $stmt = $conn->prepare("DELETE FROM students WHERE username = ?;");
                        }
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        header("Content-Type: application/json");
                        echo json_encode(array("success" => "Successfully deleted user $fullname"));
                    }
                    else {
                        header("Content-Type: application/json");
                        echo json_encode(array("failure" => "User not found..."));
                    }
                    if ($fullname) {
                        $action = "Deleted user $fullname";
                    }
                    else {
                        $action = "Attempted to delete $username";
                    }
                    $admUser = $_SESSION['username'];
                    $stmt = $conn->prepare("SELECT * FROM staff WHERE username = ?");
                    $stmt->bind_param("s", $admUser);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows) {
                        $results = $result->fetch_assoc();
                        $name = $results['fullname'];
                    }
                    else {
                        $name = '';
                    }
                    logEntry($name, $admUser, $action, $auditPath);
                }
            }
        }
    }
}
?>