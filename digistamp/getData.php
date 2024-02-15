<?php

// db creds
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";


if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $conn = new mysqli($host, $srvuser, $srvpass, $db);
        $stmt = "SELECT * FROM students WHERE username = ?;";
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $results = $result->fetch_assoc();
            $conn->close();
            $stmt->close();

            // not encoded
            $class = $results['class'];
            $year = $results['school_year'];
            $stamps = $results['stamps'];
            $tutor = $results['tutor'];
            $name = $results['fullname'];

            // sanitization
            // checks if it is integer to not break program
            if (is_int(intval($year)) && is_int(intval($stamps))) {
                $year = filter_var($year, FILTER_SANITIZE_NUMBER_INT);
                $stamps = filter_var($stamps, FILTER_SANITIZE_NUMBER_INT);
                $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                $tutor = htmlspecialchars($tutor, ENT_QUOTES, 'UTF-8');
                $class = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
                // encode then AJAX
                $user_info = array(
                    'username' => $username,
                    'class' => $class,
                    'year' => $year,
                    'stamps' => $stamps,
                    'tutor' => $tutor,
                    'name' => $name
                );

                $studentInfo = json_encode($user_info);
                header('Content-Type: application/json');
                echo $jsonData;
            }
            else {
                http_response_code(400);
            }
        }
    }
    else {
        http_response_code(401);
        echo "User not logged in";
    }
}

?>