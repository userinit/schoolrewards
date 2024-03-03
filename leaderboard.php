<?php

// db creds
$host = "localhost";
$srvuser = "root";
$srvpass = "";
$db = "schoolrewardsdb";

// leaderboard.php added to reduce wait times with dashboard.php
$leaderboardMax = 25; // Edit this to change how many users are displayed on leaderboard

session_start();

if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === "student") {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $username = $_SESSION['username'];
        $leaderboardArr = ['school', 'year', 'tutor'];
        if (isset($_GET['leaderboard']) && in_array($_GET['leaderboard'], $leaderboardArr)) {
            $leaderboardType = $_GET['leaderboard'];
            $lbArr = [];
            $conn = new mysqli($host, $srvuser, $srvpass, $db);
            if ($conn->connect_error) {
                die("Error: " . $conn->connect_error);
            }
            if ($leaderboardType === "tutor") {
                // Get tutor group
                $sql =
                "WITH RankedUsers AS (
                    SELECT *, ROW_NUMBER() OVER (ORDER BY stamps DESC) AS user_rank,
                    CASE WHEN username = ? THEN 1 ELSE 0 END AS selected
                    FROM students WHERE tutor = (SELECT tutor FROM students WHERE username = ?)
                )
                SELECT forename,surname,stamps,selected
                FROM RankedUsers WHERE user_rank <= ?  OR username = ?
                ORDER BY user_rank;";
            }
            elseif ($leaderboardType === "year") {
                $sql = 
                "WITH RankedUsers AS (
                    SELECT *, ROW_NUMBER() OVER (ORDER BY stamps DESC) AS user_rank,
                    CASE WHEN username = ? THEN 1 ELSE 0 END AS selected
                    FROM students WHERE school_year = (SELECT school_year FROM students WHERE username = ?)
                )
                SELECT forename,surname,stamps,tutor,selected
                FROM RankedUsers WHERE user_rank <= ? OR username = ?
                ORDER BY user_rank;";
            }
            elseif ($leaderboardType === "school") {
                $sql = 
                "WITH RankedUsers AS (
                    SELECT *, ROW_NUMBER() OVER (ORDER BY stamps DESC) AS user_rank,
                    CASE WHEN username = ? THEN 1 ELSE 0 END AS selected
                    FROM students
                )
                SELECT forename,surname,stamps,tutor,school_year,selected
                FROM RankedUsers WHERE user_rank <= ? OR username = ?
                ORDER BY user_rank;";
            }
            $stmt = $conn->prepare($sql);
            if ($leaderboardType !== "school") {
                $stmt->bind_param("ssis", $username, $username, $leaderboardMax, $username);
            }
            else {
                $stmt->bind_param("sis", $username, $leaderboardMax, $username);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $i = 1;
                if ($leaderboardType === "tutor") {
                    while ($row = $result->fetch_assoc()) {
                        $forename = $row['forename'];
                        $surnameInitial = $row['surname'][0];
                        $name = "$forename $surnameInitial";
                        $stamps = $row['stamps'];
                        $selected = $row['selected'];
                        $rank = "#" . $i;
                        $lbArr[] = [$rank, $name, $stamps, $selected];
                        $i++;
                    }
                    $keys = ["rank", "name", "stamps", "selected"];
                }
                elseif ($leaderboardType === "year") {
                    while ($row = $result->fetch_assoc()) {
                        $forename = $row['forename'];
                        $surnameInitial = $row['surname'][0];
                        $name = "$forename $surnameInitial";
                        $stamps = $row['stamps'];
                        $tutor = $row['tutor'];
                        $selected = $row['selected'];
                        $rank = "#" . $i;
                        $lbArr[] = [$rank, $name, $stamps, $tutor, $selected];
                        $i++;
                    }
                    $keys = ["rank", "name", "stamps", "tutor", "selected"];
                }
                else {
                    while ($row = $result->fetch_assoc()) {
                        $forename = $row['forename'];
                        $surnameInitial = $row['surname'][0];
                        $name = "$forename $surnameInitial";
                        $stamps = $row['stamps'];
                        $tutor = $row['tutor'];
                        $year = $row['school_year'];
                        $selected = $row['selected'];
                        $rank = "#" . $i;
                        $lbArr[] = [$rank, $name, $stamps, $tutor, $year, $selected];
                        $i++;
                    }
                    $keys = ["rank", "name", "stamps", "tutor", "year", "selected"];
                }
                $stampIndex = 1;
                $associative = [];
                foreach($lbArr as $row) {
                    $assocRow = array_combine($keys, $row);
                    $associative[] = $assocRow;
                }
                header("Content-Type: application/json");
                if ($associative) {
                    echo json_encode(array("success" => $associative));
                }
                else {
                    echo json_encode(array("failure" => "Failed to load database."));
                }
            }
            else {
                echo json_encode(array("failure" => "Failed to find username info."));
            }
        }
    }
}
?>