<?php

// db conn
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

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
                    echo "<tr><th>Name</th><th>Year</th><th>Tutor Group</th><th>Class</th><th>Username</th><th>Password</th><th>Stamps</th></tr>";
                    // while loop iterates until there are no more lines left
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        $name = $data[0];
                        $year = $data[1];
                        $tutor = $data[2];
                        $class = $data[3];
                        $username = $data[4];
                        $password = $data[5];
                        $stamps = $data[6]; // Stamps not at 0 because it's mid year
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                        if (is_int(intval($year)) && is_int(intval($stamps))) {
                            $year = (int)$year;
                            $stamps = (int)$stamps;

                            // Outputs current record from CSV
                            echo "<tr><td>$name</td><td>$year</td><td>$tutor</td><td>$class</td><td>$username</td><td>$password</td><td>$stamps</td></tr>";
                            // Adds them to students table
                            $stmt = prepare("INSERT INTO students (fullname, school_year, tutor, class, username, hashed_password, stamps) VALUES (?,?,?,?,?,?);");
                            $stmt->bind_param("sissssi", $name, $year, $tutor, $class, $username, $hashed_password, $stamps);
                            $stmt->execute();
                            $stmt->close();
                            // Adds them to roles table
                            $stmt = prepare("INSERT INTO roles (username, user_role) VALUES (?, ?);");
                            $stmt->bind_param("ss", $username, "student");
                            $stmt->execute();
                            $stmt->close();
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
