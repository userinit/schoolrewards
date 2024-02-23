<?php

// Max stamps addable in one sitting -- Edit this
$maxStamps = 10000;

// Assigning db creds and variables
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

$validClass = FALSE;
// classes hardcoded for simplicity
// classes don't reflect real school classes (just for PoC)
$yearInfo = [
    "Year 10" => [
        "Tutors" => ["Newton", "Einstein", "Bohr", "Darwin", "Pasteur"],
        "Classes" => ["Freud", "Galilei", "Lavoisier", "Kepler", "Copernicus"]
    ],
    "Year 11" => [
        "Tutors" => ["Faraday", "Maxwell", "Bernard", "Boas", "Heisenberg"],
        "Classes" => ["Pauling", "Virchow", "Schrodinger", "Rutherford", "Dirac"]
    ],
    "Year 12" => [
        "Tutors" => ["Vesalius", "Brahe", "Buffon", "Boltzmann", "Planck"],
        "Classes" => ["Curie", "Herschel", "Lyell", "Laplace", "Hubble"]
    ],
    "Year 13" => [
        "Tutors" => ["Thomson", "Born", "Crick", "Fermi", "Liebig"],
        "Classes" => ["Eddington", "Harvey", "Malpighi", "Huygens", "Gauss"]
    ]];

session_start();
if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        if(isset($_GET['year']) && isset($_GET['type']) && !isset($_GET['class'])) {
            $year = $_GET['year'];
            $type = ucfirst(strtolower($_GET['type'])); // type being tutor/class
            $validYears = [10, 11, 12, 13];

            // Checks whether years are valid, types are valid. This prevents query tampering
            if (in_array($year, $validYears) && ($type === 'Tutors' || $type === 'Classes')) {
                // Properly formats the year and type for extraction
                $textYear = "Year " . $year;
                $classList = $yearInfo[$textYear][$type];
                $associativeArray = array('classList' => $classList);
                $encodedErray = json_encode($associativeArray);
                header("Content-Type: application/json");
                echo $encodedErray;
            }
        }

        elseif (isset($_GET['year']) && isset($_GET['class']) && isset($_GET['type'])) {
            $validClass = FALSE;
            // Retrieves value from GET
            $class = ucfirst(strtolower($_GET['class'])); // class being class/tutor name
            $year = $_GET['year']; // type being whether it's a tutor or class
            $type = ucfirst(strtolower($_GET['type']));
            $validYears = [10, 11, 12, 13]; 
            $textYear = "Year " . $year;

            // Check if the provided year is valid and the type is either "Tutors" or "Classes"
            if (in_array($year, $validYears) && ($type === "Tutors" || $type === "Classes")) {
                // Check if the year number and tutor/class exists in $yearInfo
                if (isset($yearInfo[$textYear][$type])) {
                    // Checks if the specified class or tutor exists in the array
                    if (in_array($class, $yearInfo[$textYear][$type])) {
                        $validClass = TRUE;
                    }
                }
            }
            if ($validClass) {
                $conn = new mysqli($host, $srvuser, $srvpass, $db);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                else {
                    // looks for the student info of that tutor/class
                    if ($type === "Tutors") {
                        $stmt = $conn->prepare("SELECT * FROM students WHERE tutor = ?;");
                    }
                    else {
                        $stmt = $conn->prepare("SELECT * FROM students WHERE class = ?;");
                    }
                    $stmt->bind_param("s", $class);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        // Iterate over the rows making an array entry for each instance
                        while ($row = $result->fetch_assoc()) {
                            $userInfo[] = [
                                'surname' => $row['surname'],
                                'forename' => $row['forename'],
                                'username' => $row['username'],
                                'stamps' => $row['stamps']
                            ];
                        }
                        // Sorts usernames alphabetically
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
                        // Sorts array
                        usort($userInfo, 'sortingAlgorithm');

                        // Changes array from associative to non-associative
                        $nonAssociative = array_map(function($item) {
                            return [$item['surname'], $item['forename'], $item['username'], $item['stamps']];
                        }, $userInfo);
                        $jsonArr = json_encode($nonAssociative);
                        header("Content-Type: application/json");
                        echo $jsonArr;
                    }
                    else {
                        $failure = json_encode(array("failure" => "No students found in class."));
                        header("Content-Type: application/json");
                        echo $failure;
                    }
                }
            }
        }
        elseif (isset($_GET['username']) && isset($_GET['stamps'])) {
            $username = $_GET['username'];
            $stampIncrease = $_GET['stamps'];
            $username = preg_replace("/[^a-zA-Z0-9]/" , "", $username);
            $conn = new mysqli($host, $srvuser, $srvpass, $db);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
        // Receives JSON
        $jsonData = file_get_contents('php://input');
        // Decodes into associative array
        $associativeArray = json_decode($jsonData, true);
        // Checks whether the username and stamp keys exist. If they do, they get extracted.
        if (isset($associativeArray['username']) && isset($associativeArray['stamps'])) {
            $username = $associativeArray['username'];
            $username = strtolower($username);
            $stampIncrease = $associativeArray['stamps'];
            $username = preg_replace("/[^a-z0-9.]/" , "", $username); // removes everything that's not a-z, 0-9 or .
            $conn = new mysqli($host, $srvuser, $srvpass, $db);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $resultSet = $stmt->get_result();
            if (!($resultSet->num_rows > 0)) {
                $failure = json_encode(array("failure" => "Failed to find username."));
                header("Content-Type: application/json");
                echo $failure;
            }
            // Runs code if a) $stampIncrease = int. b) $stampIncrease is over 0. c) $stampIncrease is not bigger than $maxStamps 
            elseif (is_int(intval($stampIncrease)) && $stampIncrease > 0 && !($stampIncrease > $maxStamps)) {
                if (!($stampIncrease > $maxStamps) && is_int(intval($stampIncrease))) {
                    // Allows for singular/plural text
                    if ($stampIncrease == 1) {
                        $stampText = "stamp";
                    }
                    else {
                        $stampText = "stamps";
                    }
                    $result = $resultSet->fetch_assoc();
                    $currentStamps = $result['stamps'];
                    // Fetching name from results for response
                    $forename = $result['forename'];
                    $surname = $result['surname'];
                    $fullname = $forename . " " . $surname;
                    $newStamps = $currentStamps + $stampIncrease;
                    $stmt = $conn->prepare("UPDATE students SET stamps = ? WHERE username = ?;");
                    $stmt->bind_param("is", $newStamps, $username);
                    $stmt->execute();
                    if (!($stmt->affected_rows > 0)) {
                        $failure = json_encode(array("failure" => "Failed to add stamps."));
                        header("Content-Type: application/json");
                        echo $failure;
                    }
                    else {
                        $success = json_encode(array("success" => "Success: Added $stampIncrease $stampText for $forename $surname."));
                        header("Content-Type: application/json");
                        echo $success;
                    }
                }
                
            }
            else {
                $failure = json_encode(array("failure" => "Stamps need to be an integer between 1-$maxStamps"));
                header("Content-Type: application/json");
                echo $failure;
            }
        }
    }
    else {
        http_response_code(405);
    }
}
else {
    http_response_code(403);
    header("Location: 403.html");
}
?>