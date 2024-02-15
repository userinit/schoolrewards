<?php

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
        "Classes" => ["Curie", "Herschel", "Lyell", "Lapalace", "Hubble"]
    ],
    "Year 13" => [
        "Tutors" => ["Thomson", "Born", "Crick", "Fermi", "Liebig"],
        "Classes" => ["Eddington", "Harvey", "Malpighi", "Huygens", "Gauss"]
    ]];

if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
            if(isset($_GET['year']) && isset($_GET['type'])) {
                $year = $_GET['year'];
                $type = $_GET['type'];
                $validYears = [10, 11, 12, 13];

                if (in_array($year, $validYears) && ($type === 'tutor' || $type === 'class')) {
                    // After this, we can be sure nobody has manipulated the year and type
                    $textYear = "Year " . $year;
                    $type = ucfirst(strtolower($type)) . "s";
                    $classList = $yearInfo[$textYear][$type];
                    $associativeArray = array('classList', $classList);
                    $encodedErray = json_encode($associativeArray);
                    echo $encodedErray;

                }
            elseif (isset($_GET['class']) && isset($_GET['type'])) {
                $class = $_GET['class'];
                // Iterate over "Year 10,11,12,13"
                foreach ($yearInfo as $year => $categoryInfo) {
                    // Iterate over "Tutors/classes" array
                    foreach ($categories as $classOrTutor => $individualClasses) {
                        if (in_array($class, $individualClasses)) {
                            $validClass = TRUE;
                            $classCategory = $classOrTutor;
                            break 2;
                        }
                    }
                }
                if ($validClass) {
                    $conn = new mysqli($host, $srvuser, $srvpass, $db);
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    else {
                        if ($classCategory === "Tutors") {
                            $stmt = $conn->prepare("SELECT * FROM students WHERE tutor = ?;");
                        }
                        else {
                            $stmt = $conn->prepare("SELECT * FROM students WHERE class = ?;");
                        }
                        $stmt->bind_param("s", $class);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $surnames = [];
                            $forenames = [];
                            $usernames = [];
                            $stamps = [];
                            $tutors = [];
                            $classes = [];
                            // Iterate over the rows making an array entry for each instance
                            while ($row = $result->fetch_assoc()) {
                                $surnames[] = $row['surname'];
                                $forenames[] = $row['forename'];
                                $usernames[] = $row['username'];
                                $stamps[] = $row['stamps'];
                            }

                            // Iterate over every array to get associative arrays
                            for ($i = 0; $i < count($usernames); $i++) {
                                $userInfo = [
                                    'surname' => $surnames[i],
                                    'forename' => $forenames[i],
                                    'username' => $usernames[i],
                                    'stamps' => $stamps[i]
                                ];
                                $combinedArray[$surnames[$i]] = $userInfo;
                            }
                            // Sorts usernames alphabetically
                            function sortingAlgorithm($a, $b) {
                                // Compare surnames
                                $surnameComparison = strcmp($a['surname'],  $b['surname']);
                                
                                // If surnames are the same, compare forenames
                                if ($surnameComparison == 0) {
                                    $forenameComparison = stcmp($a['forename'], $b['forename']);
                                    return $forenameComparison;
                                }
                                return $surnameComparison;
                            }
                            // Sorts array
                            usort($userInfo, 'sortingAlgorithm');

                            // Changes array from associative to non-associative
                            $nonAssociative = [];
                            foreach ($userInfo as $items) {
                            $nonAssociative[] = [$items['surname'], $items['forename'], $items['stamps'], $items['tutor'], $items['class']];
                            }
                            $jsonArr = json_encode($nonAssociative);
                            echo $jsonArr;
                        }
                        else {
                            // error handling
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
                else {
                    $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if (!($result->num_rows > 0)) {
                        $failure = json_encode(array("failure" => "Failed to find username."));
                        echo $failure;
                    }
                    elseif (is_int(intval($stampIncrease))) {
                        $resultSet = $result->fetch_assoc();
                        $currentStamps = $resultSet['stamps'];
                        // Fetching name from results for response
                        $forename = $resultSet['forename'];
                        $surname = $resultSet['surname'];
                        $fullname = $forename . " " . $surname;
                        $newStamps = $currentStamps + $stampIncrease;
                        $stmt = $conn->prepare("UPDATE students SET stamps = ? WHERE username = ?;");
                        $stmt->bind_param("is", $newStamps, $username);
                        $stmt->execute();
                        if (!($stmt->affected_rows > 0)) {
                            $failure = json_encode(array("failure" => "Failed to add stamps."));
                            echo $failure;
                        }
                        else {
                            $success = json_encode(array("success" => "Success: Added " . $stampIncrease . " stamps for " . $surname));
                            echo $success;
                        }
                    }
                }
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