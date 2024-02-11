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
$type = ''; // type meaning staff or student
$validName = FALSE;

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
                $firstRow = fgetcsv($csvFile); // gets first row
                $firstRowLower = array_map('strtolower', $firstRow);
                $firstRowLowerTrim = array_map('trim', $firstRow);
                $firstRowCols = count($firstRow);
                if ($firstRowCols == 6) { // 8 if you allow to manually add stamp count
                    //$type = 'student';
                    $forenameIndex = array_search("forename", $firstRowLowerTrim);
                    $surnameIndex = array_search("surname", $firstRowLowerTrim);
                    $yearIndex = array_search("year", $firstRowLowerTrim);
                    $tutorIndex = array_search("tutor", $firstRowLowerTrim);
                    $classIndex = array_search("class", $firstRowLowerTrim);
                    $passwordIndex = array_search("password", $firstRowLowerTrim);
                    if (!($forenameIndex && $surnameIndex && $yearIndex && $tutorIndex && $classIndex && $passwordIndex)) {
                        // At least one of the column headers can't be found
                        echo "You need the 6 columns: forename, surname, year, tutor, class and password";
                        echo "No entries added";
                    }
                    else {
                        // All of the column headers are found
                        $type = 'student';
                    }
                }
                elseif ($firstRowCols == 4) {
                    $forenameIndex = array_search("forename", $firstRowLowerTrim);
                    $surnameIndex = array_search("surname", $firstRowLowerTrim);
                    $passwordIndex = array_search("password", $firstRowLowerTrim);
                    $roleIndex = array_search("role", $firstRowLowerTrim);
                    if (!($forenameIndex && $surnameIndex && $passwordIndex && $roleIndex)) {
                        // At least one of the column headers can't be found
                        echo "You need the 4 columns: forename, surname, password and role";
                        echo "No entries added";
                    }
                    else {
                        // All of the column headers are found
                        $type = 'staff';
                    }
                }
                else {
                    echo "For students, you need the 6 columns: forename, surname, year, tutor, class and password";
                    echo "For staff, you need the 4 columns: forename, surname, password and role";
                    echo "No entries added";
                }

                if ($type !== '') {
                    $conn = new mysqli($host, $srvuser, $srvpass, $db);
                    if ($conn->connect_error) {
                        die("Connection error: " . $conn->connect_error);
                    }
                    // removes first row which will be column headers
                    fgetcsv($handle);
                    
                    if ($type === "student") {
                        // student logic
                        echo "<h2>Uploaded Student Records:</h2>";
                        echo "<table border='1'>";
                        echo "<tr><th>Forename</th><th>Surname</th><th>Username</th><th>Year</th><th>Tutor</th><th>Class</th><th>Password</th></tr>";
                        // while loop iterates until there are no more lines left
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $columnCount = count($data); // gets column count
                            if ($columnCount === 7) {
                                
                            }
                            $forename = ucwords(strtolower($data[$forenameIndex]));
                            $surname = ucwords(strtolower($data[$surnameIndex]));
                            $year = $data[$yearIndex];
                            $tutor = $data[$tutorIndex];
                            $class = $data[$classIndex];
                            $password = $data[$passwordIndex];

                            // Stamp score could be mid-year but it will be 0 until further notice to prevent selective stamp addition.
                            $stamps = 0;
                            // $stamps = $data[6]; // Stamps not at 0 because it's mid year

                            if (!preg_match('/\s/', $password)) {
                                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                                $noWhitespaces = TRUE;
                            }
                            if (is_int(intval($year)) && preg_match("/^[a-zA-Z' -]+$/", $surname) && preg_match("/^[a-zA-Z' -]+$/", $forename) && preg_match("/^[a-zA-Z]/", $surname[0]) && preg_match("/^[a-zA-Z]/", $forename[0])) {
                                $year = (int)$year;
                                $textYear = "Year " . $year;
                                if (isset($yearInfo[$textYear])) {
                                    foreach ($yearInfo[$textYear]['Classes'] as $arrayClass) {
                                        if ($class === ucfirst(strtolower($arrayClass))) { // Changes case to lowercase then capitalizes first
                                            $validClass = TRUE;
                                            break;
                                        }
                                    }
                                    foreach ($yearInfo[$textYear]['Tutors'] as $arrayTutor) {
                                        if ($tutor === ucfirst(strtolower($arrayTutor))) { 
                                            $validTutor = TRUE;
                                            break;
                                        }
                                    }
                                }
                                $cleanedSurname = preg_replace("/['\s-]+/", "", $surname); // Replaces `'`, `-` and whitespaces for username formatting
                                $cleanedForename = preg_replace("/['\s-]+/", "", $forename);
                                if (strlen($cleanedForename) >= 2 && strlen($cleanedSurname) >= 2) {
                                    $validName = TRUE;
                                }
                                if ($validClass && $validTutor && $noWhitespaces && $validName) {
                                    // makes username prefix i.e. the 19 in 19surname.initial (11-6=5, 24-5=19)
                                    $currentYear = date('y'); // 2 digit year number - i.e. for 2024, you get 24
                                    $howLongAgo = (int)$year - 6; // to see how long ago they joined secondary. 6 because 6 years in primary
                                    $prefix = $currentYear - $howLongAgo; // calculates the year they joined secondary.

                                    $username = $prefix . $cleanedSurname . $cleanedForename[0];
                                
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
                            $stmt->execute();
                            $stmt->close();
                        }
                        $conn->close();    
                    }
                }
            }
        }
    }
}

    

