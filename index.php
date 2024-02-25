<?php

// Get URL desired segments and the method
$method = $_SERVER['REQUEST_METHOD'];
$url = $_SERVER['REQUEST_URI'];
$url = trim($url, '/');
$segments = explode('/', $url);
if (!empty($segments && count($segments) > 1)) {
    $remainingSegments = array_slice($segments, 1);
}
else {
    $remainingSegments = '';
}

session_start();

switch($remainingSegments[0]) {
    case 'home':
    case '':
        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] === "teacher") {
                header("Location: http://localhost/digistamp/teacher.html");
                exit();
            }
            elseif ($_SESSION['role'] === "admin") {
                header("Location: http://localhost/digistamp/admin.html");
                exit();
            }
            else {
                header("Location: http://localhost/digistamp/dashboard.html");
            }
            break;
        }
        else {
            header("Location: http://localhost/digistamp/login.html");
        }

    case 'login':
        header("Location: http://localhost/digistamp/login.html");
        exit();
        break;
        
        // If they are teacher, take them to teacher panel
    case 'teacher':
        if (isset($_SESSION['role']) && $_SESSION['role'] === "teacher") {
            header("http://localhost/digistamp/teacher.html");
            exit();
        }
        elseif (isset($_SESSION['role']) && $_SESSION['role'] !== "teacher") {
            header("Location: http://localhost/digistamp/403.html");
            exit();
        }
        else {
            header("Location: http://localhost/digistamp/login.html");
        }
        break;
        // If they are admin, take them to admin panel
    case 'admin':
        if (isset($_SESSION['role']) && $_SESSION['role'] === "admin") {
            header("Location: http://localhost/digistamp/new-users.html");
            exit();
        }
        elseif (isset($_SESSION['role']) && $_SESSION['role'] !== "admin") {
            header("Location: http://localhost/digistamp/403.html");
        }
        else {
            header("Location: http://localhost/digistamp/login.html");
            exit();
        }
        break;

        // If they are student, take them to student panel
    case 'student':
        if (isset($SESSION['role']) && $_SESSION['role'] === "student") {
            header("Location: http://localhost/digistamp/dashboard.html");
            exit();
        }
        elseif (isset($_SESSION['role']) && $_SESSION['role'] !== "student") {
            header("Location: http://localhost/digistamp/403.html");
        }
        else {
            header("Location: http://localhost/digistamp/login.html");
            exit();
        }
        break;
}

?>