<?php

if (!isset($_SESSION['username']) || time() > $_SESSION['token_expiration']) {
    session_destroy();
    http_response_code(302);
    header("Location: http://localhost/digistamp/login.html");
}
else {
    if ($_SESSION['role'] === "admin") {
        header("Location: http://localhost/digistamp/admin.html");
    }
    elseif ($_SESSION['role'] === "teacher") {
        header("Location: http://localhost/digistamp/teacher.php");
    }
    else {
        header("Location: http://localhost/digistamp/student.php");

    }
}

?>