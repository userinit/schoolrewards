<?php

// db conn
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

// at start everything is invalid
$validClass = FALSE;
$validTutor = FALSE;
$noWhitespaces = FALSE;

// assigning classes and tutors - update as needed
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

if (isset($_SESSION['role']) && $_SESSION['role'] === "admin") {
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file, "r");
            if ($handle !== FALSE) {
                $headers = fgetcsv($csvFile);
                $columnCount = count($headers);

                $conn = new mysqli($host, $srvuser, $srvpass, $db);
                if ($conn->connect_error) {
                    die("Connection error: " . $conn->connect_error);
                }
                // removes first row which will be column headers
                fgetcsv($handle);
                
                if ($columnCount > 4) {
                    // student logic
                    echo "<h2>Uploaded Student Records:</h2>";
                    echo "<table border='1'>";
                    echo "<tr><th>Surname</th><th>Forename</th><th>Year</th><th>Tutor</th><th>Class</th><th>Password</th></tr>";//<th>Stamps</th></tr>";
                    // while loop iterates until there are no more lines left
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        $surname = trim($data[0]);
                        $forename = trim($data[1]);
                        $year = trim($data[2]);
                        $tutor = trim($data[3]);
                        $class = trim($data[4]);
                        $password = trim($data[5]);
                        // Stamp score could be mid-year but it will be 0 until further notice.
                        $stamps = 0;
                        // $stamps = $data[6]; // Stamps not at 0 because it's mid year

                        if (!preg_match('/\s/', $password)) {
                            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                            $noWhitespaces = TRUE;
                        }
                        if (is_int(intval($year)) && is_int(intval($stamps)) && preg_match("/^[a-zA-Z' -]+$/", $surname) && preg_match("/^[a-zA-Z' -]+$/", $forename && preg_match("/^[a-zA-Z]/", $surname) && preg_match("/^[a-zA-Z]/", $forename))) {
                            $year = (int)$year;
                            $textYear = "Year " . $year;
                            if (isset($yearInfo[$textYear])) {
                                foreach ($yearInfo[$textYear]['Classes'] as $arrayClass) {
                                    if ($class === $arrayClass) {
                                        $validClass = TRUE;
                                        break;
                                    }
                                }
                                foreach ($yearInfo[$textYear]['Tutors'] as $arrayTutor) {
                                    if ($tutor === $arrayTutor) {
                                        $validTutor = TRUE;
                                        break;
                                    }
                                }
                            }
                            if ($validClass && $validTutor && $noWhitespaces) {
                                // makes username prefix i.e. the 19 in 19surname.initial (11-6=5, 24-5=19)
                                $currentYear = date('y'); // 2 digit year number - i.e. for 2024, you get 24
                                $howLongAgo = (int)$year - 6; // to see how long ago they joined secondary. 6 because 6 years in primary
                                $prefix = $currentYear - $howLongAgo; // calculates the year they joined secondary.

                                // For loop to remove 
                                $username = $prefix . $surname . $forename[0];
                                //$stamps = (int)$stamps;

                                // Outputs current record from CSV
                                echo "<tr><td>$surname</td><td>$forename</td><td>$username</td> <td>$year</td><td>$tutor</td><td>$class</td><td>$username</td><td>$password</td></tr>";
                                // Adds them to students table
                                $stmt = prepare("INSERT INTO students (forename, surname, school_year, tutor, class, username, hashed_password, stamps) VALUES (?,?,?,?,?,?,?,?);");
                                $stmt->bind_param("ssissssi", $forename, $surname, $year, $tutor, $class, $username, $hashed_password, $stamps);
                                $stmt->execute();
                                $stmt->close();
                                // Adds them to roles table
                                $stmt = prepare("INSERT INTO roles (username, user_role) VALUES (?, ?);");
                                $stmt->bind_param("ss", $username, "student");
                                $stmt->execute();
                                $stmt->close();
                            }
                        }
                    }
                
                    $conn->close();
                    
                }
                
                else {
                    // staff logic
                    echo "<h2>Uploaded Staff Records:</h2>";
                    echo "<table border='1'>";
                    echo "<tr><th>Name</th><th>username</th><th>Password</th><th>Role</th></tr>";

                    // while loop iterates until there are no more lines left
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        $name = $data[0];
                        $username = $data[1];
                        $password = $data[2];
                        if (strtolower($data[3]) === "admin" || strtolower($data[3] === "teacher")) {
                            $role = $data[3];
                        }
                        else {
                            $role = "teacher";
                        }
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                        // Outputs current record from CSV
                        echo "<tr><td>$name</td><td>$username</td><td>$password</td><td>$role</td></tr>";
                        // Adds staff member to staff table
                        $stmt = prepare("INSERT INTO staff (fullname, username, password_hash) VALUES (?, ?, ?);");
                        $stmt->bind_param("sss", $name, $username, $hashed_password);
                        $stmt->execute();
                        $stmt->close();
                        // Adds staff member to roles table
                        $stmt = prepare("INSERT INTO roles (username, user_role) VALUES (?,?);");
                        $stmt->bind_param("ss", $username, $role);
                    }
                    $conn->close();    
                }
            }
        }
    }
}
