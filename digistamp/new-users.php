<?php

// db conn
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

$type = ''; // type meaning staff or student
$rowCount = 1; // initialize row count

// start PHP session to access $_SESSION
session_start();

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
        "Classes" => ["Curie", "Herschel", "Lyell", "Laplace", "Hubble"]
    ],
    "Year 13" => [
        "Tutors" => ["Thomson", "Born", "Crick", "Fermi", "Liebig"],
        "Classes" => ["Eddington", "Harvey", "Malpighi", "Huygens", "Gauss"]
]];
if (isset($_SESSION['role']) && $_SESSION['role'] == "admin") {
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file, "r");
            if ($handle !== FALSE) {
                $firstRow = fgetcsv($handle); // gets first row and removes it from array
                $firstRowLower = array_map('strtolower', $firstRow);
                $firstRowLowerTrim = array_map('trim', $firstRowLower);
                $firstRowCols = count($firstRow);
                if ($firstRowCols == 6) {
                    $forenameIndex = array_search("forename", $firstRowLowerTrim);
                    $surnameIndex = array_search("surname", $firstRowLowerTrim);
                    $yearIndex = array_search("year", $firstRowLowerTrim);
                    $tutorIndex = array_search("tutor", $firstRowLowerTrim);
                    $classIndex = array_search("class", $firstRowLowerTrim);
                    $passwordIndex = array_search("password", $firstRowLowerTrim);
                    if ($forenameIndex === false || $surnameIndex === false || $yearIndex === false || $tutorIndex === false || $classIndex === false || $passwordIndex === false) {
                        // At least one of the column headers can't be found
                        echo "You need the 6 columns: forename, surname, year, tutor, class and password<br>";
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
                    if ($forenameIndex === false || $surnameIndex === false || $passwordIndex === false || $roleIndex === false) {
                        // At least one of the column headers can't be found
                        echo "You need the 4 columns: forename, surname, password and role<br>";
                        echo "No entries added";
                    }
                    else {
                        // All of the column headers are found
                        $type = 'staff';
                    }
                }
                else {
                    echo "For students, you need the 6 columns: forename, surname, year, tutor, class and password<br>";
                    echo "For staff, you need the 4 columns: forename, surname, password and role<br>";
                    echo "No entries added";
                }
                if ($type !== '') {
                    if ($type === "student") {
                        // student logic
                        echo "<h2>Uploaded Student Records:</h2>";
                        echo "<table border='1'>";
                        echo "<tr><th>Surname</th><th>Forename</th><th>Username</th><th>Year</th><th>Tutor</th><th>Class</th><th>Password</th></tr>";
                        // while loop iterates until there are no more lines left
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $rowCount++;
                            $error = '';
                            // define bools for verification conditions
                            $validColumn = FALSE;
                            $validYear = FALSE;
                            $validClass = FALSE;
                            $validTutor = FALSE;
                            $validNameLength = FALSE;
                            $validNameFormat = FALSE;

                            // Check whether there are 6 columns
                            $data = array_map('trim', $data);
                            $columnCount = count($data); // gets column count
                            if ($columnCount === 6) {
                                // dump data
                                $forename = $data[$forenameIndex];
                                $surname = $data[$surnameIndex];
                                $year = $data[$yearIndex];
                                $tutor = $data[$tutorIndex];
                                $class = $data[$classIndex];
                                $password = $data[$passwordIndex];
                                $stamps = 0;
                                $role = "student";

                                // replaces all spaces/tabs/newlines with 1 space
                                preg_replace("/\s+/", " ", $forename);
                                preg_replace("/\s+/", " ", $surname);
                                // removes all spaces from password, tutor and class then hash password
                                preg_replace("/\s+/", "", $tutor);
                                preg_replace("/\s+/", "", $class);
                                preg_replace("/\s+/", "", $password);
                                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                                // Check if year is integer
                                if (is_int(intval($year))) {
                                    // check if year is between 10 and 13
                                    if ($year >= 10 && $year <= 13) {
                                        $validYear = TRUE;
                                    }
                                }

                                // checks form and tutor if year is int
                                if ($validYear) {
                                    $textYear = "Year " . $year;
                                    if (isset($yearInfo[$textYear])) {
                                        foreach ($yearInfo[$textYear]['Classes'] as $arrayClass) {
                                            if ($class === ucfirst(strtolower($arrayClass))) { // Changes case to lowercase then capitalizes first letter
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
                                }

                                // checks whether name aside from hyphens, apostrophes and spaces is valid (2-25 chars)
                                $cleanedSurname = preg_replace("/[^a-zA-Z]+/", "", $surname); // Removes apostrophes, hyphens and whitespaces for username formatting
                                $cleanedForename = preg_replace("/[^a-zA-Z]+/", "", $forename);

                                $forenameLastChar = substr($forename, -1);
                                $surnameLastChar = substr($surname, -1);
                                if (preg_match("/^[a-zA-Z' -]+$/", $surname) && preg_match("/^[A-Za-z' -]+$/", $forename) && preg_match("/^[A-Z]/", $surname[0]) && preg_match("/^[a-z]/", $surnameLastChar) && preg_match("/^[a-z]/", $forenameLastChar) && preg_match("/^[A-Z]/", $forename[0])) {
                                    $validNameFormat = TRUE;
                                }
                                if (strlen($cleanedSurname) >= 2 && strlen($cleanedSurname) <= 25 && strlen($cleanedForename) >= 2 && strlen($cleanedForename) <= 25) {
                                    $validNameLength = TRUE;
                                }
                                if ($validClass && $validNameFormat && $validNameLength && $validTutor && $validYear) {
                                    // logic for adding a new staff member
                                    // create username
                                    $shortenedSurname = '';
                                    $initial = $cleanedForename[0];
                                    // surname limited to 10 chars max for the username to make typing username faster
                                    if (strlen($cleanedSurname) > 10) {
                                        for ($i = 0; $i < 10; $i++) {
                                            $shortenedSurname .= $cleanedSurname[$i];
                                        }
                                    }
                                    else {
                                        $shortenedSurname = $cleanedSurname;
                                    }
                                    // makes username prefix i.e. the 19 in 19surname.initial (11-6=5, 24-5=19)
                                    $currentYear = date('y'); // 2 digit year number - i.e. for 2024, you get 24
                                    $howLongAgo = (int)$year - 6; // to see how long ago they joined secondary. 6 because 6 years in primary
                                    $prefix = $currentYear - $howLongAgo; // calculates the year they joined secondary.

                                    $username = $prefix . $shortenedSurname . $initial;
                                    
                                    // start connection
                                    $conn = new mysqli($host, $srvuser, $srvpass, $db);
                                    if ($conn->connect_error) {
                                        die("Connection failed ". $conn->connect_error);
                                    }
                                    // see whether username is already in db
                                    $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?;");
                                    $stmt->bind_param("s", $username);
                                    $stmt->execute();
                                    if ($stmt->get_result()->num_rows > 0) {
                                        // username is already in db
                                        // keep incrementing last digit until a unique username is found i.e. 19doe.j1, 19doe.j2 ...
                                        $index = 1;
                                        do {
                                            $updatedUsername = $username . $index;
                                            $stmt->bind_param("s", $updatedUsername);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            if ($result->num_rows === 0) {
                                                // Unique username found, exit the loop
                                                $username = $updatedUsername;
                                                $stmt->close();
                                                break;
                                            }
                                            $index++;
                                        } while (true);
                                    }
                                    // echo "<tr><th>Surname</th><th>Forename</th><th>Username</th><th>Year</th><th>Tutor</th><th>Class</th><th>Password</th></tr>";
                                    echo "<tr><td>$surname</td><td>$forename</td><td>$username</td><td>$year</td><td>$tutor</td><td>$class</td><td>$password</td></tr>";
                                    // Adds student to students table
                                    $stmt = $conn->prepare("INSERT INTO students (surname, forename, school_year, tutor, class, username, password_hash, stamps) VALUES (?,?,?,?,?,?,?,?);");
                                    $stmt->bind_param("ssissssi", $surname, $forename, $year, $tutor, $class, $username, $hashed_password, $stamps);
                                    $stmt->execute();
                                    $stmt->close();
                                    // Adds student to roles table
                                    $stmt = $conn->prepare("INSERT INTO roles (username, user_role) VALUES (?,?);");
                                    $stmt->bind_param("ss", $username, $role);
                                    $stmt->execute();
                                    $stmt->close();
                                }
                                if ($validYear) {
                                    if (!$validTutor) {
                                        $error .= "Tutor must be in tutor list. ";
                                    }
                                    if (!$validClass) {
                                        $error .= "Class must be in class list. ";
                                    }
                                }
                                else {
                                    $error .= "Year needs to be the school year (10-13). ";
                                }
                                if (!$validNameFormat) {
                                    $error .= "Name needs to be in a proper name format. ";
                                }
                                if (!$validNameLength) {
                                    $error .= "Name must have at least 2 letters and be 25 characters max. ";
                                }
                                if (!($validYear && $validNameFormat && $validNameLength && $validTutor && $validClass)) {
                                    $errorMessage[] = "Failed to add row $rowCount because: " . $error . "<hr>";
                                }
                            }
                            else {
                                $errorMessage[] = "Failed to add row $rowCount because there need to be 4 columns.<br>";
                            }
                        }
                        echo '</table><hr>';
                        if (isset($errorMessage)) {
                            foreach($errorMessage as $item) {
                                echo $item;
                            }
                        }
                    }
                    elseif ($type === "staff") {
                        // staff logic
                        echo "<h2>Uploaded Staff Records:</h2>";
                        echo "<table border='1'>";
                        echo "<tr><th>Full Name</th><th>Username</th><th>Password</th><th>Role</th></tr>";
                        // while loop iterates until there are no more lines left
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $rowCount++;
                            $error = '';
                            // define bools for verification conditions
                            $validNameLength = FALSE;
                            $validNameFormat = FALSE;
                            $validRole = FALSE;

                            $data = array_map('trim', $data);
                            $columnCount = count($data);
                            if ($columnCount === 4) {
                                // dump data
                                $forename = $data[$forenameIndex];
                                $surname = $data[$surnameIndex];
                                $password = $data[$passwordIndex];
                                $role = $data[$roleIndex];
                                
                                // replace spaces/tabs/newlines with 1 space
                                preg_replace("/\s+/", " ", $forename);
                                preg_replace("/\s+/", " ", $surname);
                                // remove spaces from password and roles and hash pass
                                preg_replace("/\s+/", "", $password);
                                preg_replace("/\s+/", "", $role);
                                strtolower($role);
                                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                                if ($role === "admin" || $role === "teacher") {
                                    $validRole = TRUE;
                                }
                                $cleanedSurname = preg_replace("/['\s-]+/", "", $surname); // Removes apostrophes, hyphens and whitespaces for username formatting
                                $cleanedForename = preg_replace("/['\s-]+/", "", $forename);

                                if (strlen($cleanedForename) >= 2 && strlen($cleanedSurname) >= 2 && strlen($forename) <= 25 && strlen($surname) <= 25) {
                                    $validNameLength = TRUE;
                                }
                                $forenameLastChar = substr($forename, -1);
                                $surnameLastChar = substr($surname, -1);
                                if (preg_match("/^[a-zA-Z' -]+$/", $surname) && preg_match("/^[A-Za-z' -]+$/", $forename) && preg_match("/^[A-Z]/", $surname[0]) && preg_match("/^[a-z]/", $surnameLastChar) && preg_match("/^[a-z]/", $forenameLastChar) && preg_match("/^[A-Z]/", $forename[0])) {
                                    $validNameFormat = TRUE;
                                }
                                if ($validNameFormat && $validRole && $validNameLength) {
                                        // Limiting $shortenedForename & $shortenedSurname to 10 chars for short username.
                                        $shortenedForename = $shortenedSurname = '';
                                        if (strlen($cleanedForename) > 10) {
                                            for ($i = 0; $i < 10; $i++) {
                                                $shortenedForename .= $cleanedForename[$i];
                                            }
                                        }
                                        else {
                                            $shortenedForename = $cleanedForename;
                                        }
                                        if (strlen($cleanedSurname) > 10) {
                                            for ($i = 0; $i < 10; $i++) {
                                                $shortenedSurname .= $cleanedSurname[$i];
                                            }
                                        }
                                        else {
                                            $shortenedForename = $cleanedForename;
                                        }
                                        // Making username and full name
                                        $username = $shortenedForename . "." . $cleanedSurname;
                                        $fullname = $forename . " " . $surname;

                                        // Start connection
                                        $conn = new mysqli($host, $srvuser, $srvpass, $db);
                                        if ($conn->connect_error) {
                                            die("Connection failed " . $conn->connect_error);
                                        }
                                        // Checking to see if username exists, if it does, it will get incremented at the end, i.e john.doe1, john.doe2 ...
                                        $stmt = $conn->prepare("SELECT * FROM staff WHERE username = ?;");
                                        $stmt->bind_param("s", $username);
                                        $stmt->execute();
                                        if ($stmt->get_result()->num_rows > 0) {
                                            $index = 1;
                                            do {
                                                $updatedUsername = $username . $index;
                                                $stmt->bind_param("s", $updatedUsername);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                if ($result->num_rows === 0) {
                                                    // Unique username found, exit the loop
                                                    $username = $updatedUsername;
                                                    $stmt->close();
                                                    break;
                                                }
                                                $index++;
                                            } while (true);
                                        }
                                        // Adds staff member into staff table
                                        echo "<tr><th>$fullname</th><th>$username</th><th>$password</th><th>$role</th></tr>";
                                        $stmt = $conn->prepare("INSERT INTO staff (fullname, username, password_hash) VALUES (?,?,?);");
                                        $stmt->bind_param("sss", $fullname, $username, $hashed_password);
                                        $stmt->execute();
                                        $stmt->close();
                                        // Adds staff member into roles table
                                        $stmt = $conn->prepare("INSERT INTO roles (username, user_role) VALUES (?,?);");
                                        $stmt->bind_param("ss", $username, $role);
                                        $stmt->execute();
                                        $stmt->close();
                                    }
                            
                                if (!$validRole) {
                                    $error .= "Role needs to be teacher or admin. "; // If you add vendor, include it here
                                } 
                                if (!$validNameFormat) {
                                    $error .= "Name needs to be in a proper name format. ";
                                }
                                if (!$validNameLength) {
                                    $error .= "Name must have at least 2 letters and be 25 characters max. ";
                                }
                                if (!($validRole && $validNameFormat && $validNameLength)) {
                                    $errorMessage[] = "Failed to add row $rowCount because: " . $error . "<br>";
                                }
                            }
                            else {
                                $errorMessage[] = "Failed to add row $rowCount because there need to be 4 columns.<br>";
                            }
                        }
                        echo '</table><hr>';
                        if (isset($errorMessage)) {
                            foreach($errorMessage as $item) {
                                echo $item;
                            }
                        }
                    }
                }
		    }
	    }
    }
}
?>