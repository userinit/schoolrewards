<?php

// db conn
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

$type = ''; // type meaning staff or student
$error = ''; // validation check errors 
$invalidUser = ''; // name the user whose data inputted is invalid
$errorsPresent = FALSE;

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
        "Classes" => ["Curie", "Herschel", "Lyell", "Lapalace", "Hubble"]
    ],
    "Year 13" => [
        "Tutors" => ["Thomson", "Born", "Crick", "Fermi", "Liebig"],
        "Classes" => ["Eddington", "Harvey", "Malpighi", "Huygens", "Gauss"]
    ]];
header("role: " . $_SESSION['role']);
header("username: " . $_SESSION['username']);
if (isset($_SESSION['role']) && $_SESSION['role'] == "admin") {
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file, "r");
            if ($handle !== FALSE) {
                $firstRow = fgetcsv($handle); // gets first row and removes it from array
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
                    if (!($forenameIndex && $surnameIndex && $passwordIndex && $roleIndex)) {
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
                    $conn = new mysqli($host, $srvuser, $srvpass, $db);
                    if ($conn->connect_error) {
                        die("Connection error: " . $conn->connect_error);
                    }
                    if ($type === "student") {
                        // student logic
                        echo "<h2>Uploaded Student Records:</h2>";
                        echo "<table border='1'>";
                        echo "<tr><th>Surname</th><th>Forename</th><th>Username</th><th>Year</th><th>Tutor</th><th>Class</th><th>Password</th></tr>";
                        // while loop iterates until there are no more lines left
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $validColumn = FALSE;
                            $validClass = FALSE;
                            $validTutor = FALSE;
                            $validYear = FALSE;
                            $validNameLength = FALSE;
                            $validNameFormat = FALSE;
                            
                            $data = array_map('trim', $data);
                            $columnCount = count($data); // gets column count
                            if ($columnCount === 6) {
                                $validColumn = TRUE;
                            }
                            else {
                                $error .= "Row needs to have 6 columns. ";
                            }
                            $forename = $data[$forenameIndex];
                            $surname = $data[$surnameIndex];
                            $year = $data[$yearIndex];
                            $tutor = $data[$tutorIndex];
                            $class = $data[$classIndex];
                            $password = $data[$passwordIndex];

                            // Stamp score could be mid-year but it will be 0 until further notice to prevent selective stamp addition.
                            $stamps = 0;

                            $password = preg_replace('/\s+/', '', $password); // removes whitespaces from password

                            $surnameLastChar = $surname[strlen($surname) - 1];
                            $forenameLastChar = $forename[strlen($forename) - 1];

                            // checks if year is int, if surname and forname only has characters a-z,A-Z,',- and space. Also checks if first letters of 
                            // forename & surname are A-Z, a-z for end of forename & surname
                            if (is_int(intval($year)) && preg_match("/^[a-zA-Z' -]+$/", $surname) && preg_match("/^[A-Z' -]+$/", $forename) && preg_match("/^[A-Z]/", $surname[0]) && preg_match("/^[a-z]/", $surnameLastChar) && preg_match("/^[a-z]/", $forenameLastChar) && preg_match("/^[A-Z]/", $forename[0])) {
                                $validNameFormat = TRUE;
                                $validYear = TRUE;
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
                                if (strlen($cleanedForename) >= 2 && strlen($cleanedSurname) >= 2 && strlen($forename) <= 25 && strlen($surname) <= 25) {
                                    $validNameLength = TRUE;
                                }
                                if ($validClass && $validTutor && $validNameLength) {
                                    // makes username prefix i.e. the 19 in 19surname.initial (11-6=5, 24-5=19)
                                    $currentYear = date('y'); // 2 digit year number - i.e. for 2024, you get 24
                                    $howLongAgo = (int)$year - 6; // to see how long ago they joined secondary. 6 because 6 years in primary
                                    $prefix = $currentYear - $howLongAgo; // calculates the year they joined secondary.

                                    if (strlen($cleanedSurname > 10)) {
                                        $shortenedSurname = ''; // surname part of username limited to 10 chars
                                        for ($i = 0; $i < 10; $i++) {
                                            $shortenedSurname .= $cleanedSurname[$i];
                                        }
                                    }
                                    else {
                                        $shortenedSurname = $cleanedSurname;
                                    }
                                    
                                    $username = $prefix . $shortenedSurname . "." . $cleanedForename[0];
                                    // Forename and surname will be inputted into SQL separately
                                    //$fullname = $forename . " " . $surname;
                                    // Checks students table to see if they are there
                                    $stmt = prepare("SELECT * FROM students WHERE username = ?;");
                                    $stmt->bind_param("s", $username);
                                    $stmt->execute();
                                    
                                    // if statement is in case that there are two or more people in the same year with the same username
                                    if ($stmt->get_result()->num_rows > 0) {
                                        // increments name until a username is not taken
                                        $index = 1;
                                        while (TRUE) {
                                            $updatedUsername = $username . $index;
                                            $stmt = prepare("SELECT * FROM students WHERE username = ?;");
                                            $stmt->bind_param("s", $updatedUsername);
                                            $stmt->execute();
                                            if ($stmt->get_result()->num_rows > 0) {
                                                $username = $updatedUsername;
                                                $stmt->close();
                                                break;
                                            }
                                            else {
                                                $index++;
                                            }
                                        }

                                    }
                                    
                                    // Outputs current record from CSV
                                    echo "<tr><td>$surname</td><td>$forename</td><td>$username</td><td>$year</td><td>$tutor</td><td>$class</td><td>$password</td></tr>";
                                    $stmt->close();
                                    // Adds them to students table
                                    $stmt = prepare("INSERT INTO students (surname, forename school_year, tutor, class, username, hashed_password, stamps) VALUES (?,?,?,?,?,?,?,?);");
                                    $stmt->bind_param("ssissssi", $surname, $forename, $year, $tutor, $class, $username, $hashed_password, $stamps);
                                    $stmt->execute();
                                    $stmt->close();
                                    // Adds them to roles table
                                    $stmt = prepare("INSERT INTO roles (username, user_role) VALUES (?, ?);");
                                    $stmt->bind_param("ss", $username, "student");
                                    $stmt->execute();
                                    $stmt->close();
                                }
                                else {
                                    if (!$validClass) {
                                        $error .= "Unknown class. ";
                                    }
                                    if (!$validTutor) {
                                        $error .= "Unknown tutor. ";
                                    }
                                    else {
                                        $error .= "Forename and surname must have at least two alphabetical characters. ";
                                    }

                                }
                            }
                            if (!($validClass && $validColumn && $validTutor && $validNameLength && $validNameFormat && $validYear)) {
                                // this is inside loop so array is needed...
                                $invalidUser .= "Unable to add: " . $forename . " " . $surname . ".<br>Reason(s): ";
                                // array made for what errors correspond to what user.
                                $invalidUserArray[] = $invalidUser;
                                $errorArray[] = $error;
                                $errorsPresent = TRUE;
                            }
                        }
                    }
                    if ($errorsPresent) {
                        $combinedArray = array_combine($invalidUserArray, $errorArray);
                        foreach ($combinedArray as $anInvalidUser => $anError) {
                            echo $anInvalidUser . $anError . '<hr>';
                        }
                        echo "Note: some errors may be false positives due to logic flow.<br>";
						echo "However, check CSV and make sure all the information is valid.<br>";
                    }
                    else {
                        // staff logic
                        echo "<h2>Uploaded Staff Records:</h2>";
                        echo "<table border='1'>";
                        echo "<tr><th>Full Name</th><th>Username</th><th>Password</th><th>Role</th></tr>"; // outputs what data it will input into SQL

                        // while loop iterates until there are no more lines left
                        while (($data = fgetcsv($handle)) !== FALSE) {
							$error = '';
                            $validColumn = FALSE;
                            $validRole = FALSE;
                            $validNameLength = FALSE;
                            $validNameFormat = FALSE;
                            $data = array_map('trim', $data);
                            $columnCount = count($data); // gets column count
                            if ($columnCount === 4) {
                                $validColumn = TRUE;
                            }

                            $forename = $data[$forenameIndex];
                            $surname = $data[$surnameIndex];
                            $password = $data[$passwordIndex];
                            $role = strtolower($data[$roleIndex]);
                            
                            if ($role === "admin" || $role === "teacher") {
                                $validRole = TRUE;
                            }
                            preg_replace('/\s+/', '', $password); // removes whitespaces from password
                            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                            $surnameLastChar = $surname[strlen($surname) - 1];
                            $forenameLastChar = $forename[strlen($forename) - 1];

                            if (preg_match("/^[a-zA-Z' -]+$/", $surname) && preg_match("/^[A-Z' -]+$/", $forename) && preg_match("/^[A-Z]/", $surname[0]) && preg_match("/^[a-z]/", $forenameLastChar) && preg_match("/^[a-z]/", $forenameLastChar) && preg_match("/^[a-zA-Z]/", $forename[0] && $validNameLength && $validColumn && $validRole)) {
                                $validNameFormat = TRUE;
                                $cleanedSurname = preg_replace("/['\s-]+/", "", $surname); // Replaces `'`, `-` and whitespaces for username formatting
                                $cleanedForename = preg_replace("/['\s-]+/", "", $forename);
                                if (strlen($cleanedForename) >= 2 && strlen($cleanedSurname) >= 2 && strlen($forename) <= 25 && strlen($surname) <= 25) {
                                    $validNameLength = TRUE;
                                }
                                if ($validNameLength) {
                                    if (strlen($cleanedSurname > 10)) {
                                        $shortenedSurname = ''; // surname part of username limited to 10 chars
                                        for ($i = 0; $i < 10; $i++) {
                                            $shortenedSurname .= $cleanedSurname[$i];
                                        }
                                    }
                                    else {
                                        $shortenedSurname = $cleanedSurname;
                                    }
                                    if (strlen($cleanedForename > 10)) {
                                        $shortenedForename = ''; // surname part of username limited to 10 chars
                                        for ($i = 0; $i < 10; $i++) {
                                            $shortenedForename .= $cleanedForename[$i];
                                        }
                                    }
                                    else {
                                        $shortenedForename = $cleanedForename;
                                    }
                                    $username = $shortenedForename . "." . $shortenedSurname;
                                    $fullname = $forename . " " . $surname;
                                    // Outputs current record from CSV
                                    // Makes sure username doesn't exist
                                    $stmt = prepare("SELECT * FROM staff WHERE username = ?;");
                                    $stmt->bind_param("s", $username);
                                    $stmt->execute();
                                    if ($stmt->get_results()->num_rows > 0) {
                                        $index = 1;
                                        while (TRUE) {
                                            $updatedUsername = $username . $index;
                                            $stmt = prepare("SELECT * FROM staff WHERE username = ?;");
                                            $stmt->bind_param("s", $updatedUsername);
                                            $stmt->execute();
                                            if ($stmt->get_result()->num_rows > 0) {
                                                $username = $updatedUsername;
                                                $stmt->close();
                                                break;
                                            }
                                            else {
                                                $index++;
                                            }
                                        }
                                    }
                                    echo "<tr><td>$fullname</td><td>$username</td><td>$password</td><td>$role</td></tr>"; // outputs what data it will input into SQL
                                    // Adds staff member to staff table
                                    $stmt = prepare("INSERT INTO staff (fullname, username, password_hash) VALUES (?, ?, ?);");
                                    $stmt->bind_param("sss", $fullname, $username, $hashed_password);
                                    $stmt->execute();
                                    $stmt->close();
                                    // Adds staff member to roles table
                                    $stmt = prepare("INSERT INTO roles (username, user_role) VALUES (?,?);");
                                    $stmt->bind_param("ss", $username, $role);
                                    $stmt->execute();
                                    $stmt->close();
                                }
                            }
                            if (!$validNameLength) {
                                $error .= "Forename and surname needs to have between 2 and 25 alphabetical characters. ";
                            }
                            if (!$validColumn) {
                                $error .= "Column needs to have 4 columns. ";
                            }
                            if (!$validRole) {
                                // If you add vendor role, edit the following text
                                $error .= "User must have the role admin or teacher. ";
                            }
							if (!$validNameFormat) {
								$error .= "Name must be in the correct format. ";
							}
                            if (!($validNameLength && $validColumn && $validRole && $validNameFormat)) {
                                $invalidUser .= "Unable to add: " . $forename . " " . $surname . ".<br>Reason(s): ";
								$errorsPresent = TRUE; 
								// arrays for after loop ends
								$invalidUserArray[] = $invalidUser;
								$errorArray[] = $error;
                            }
                        }
						if ($errorsPresent) {
							$combinedArray = array_combine($invalidUserArray, $errorArray);
							foreach ($combinedArray as $anInvalidUser => $anError) {
								echo $anInvalidUser . $anError . '<hr>';
							}
							echo "Note: some errors may be false positives due to logic flow.<br>";
							echo "However, check CSV and make sure all the information is valid.<br>";
                    	}
					}
                }
				if ($conn instanceof mysqli) {
					$conn->close();
				}
            }
        }
    }
}

elseif (isset($_SESSION['username'])) {
    header("Location: http://localhost/digistamp/403.html");
}
else {
    http_response_code(401);
    header("Location: http://localhost/digistamp/login.html");
}
?>