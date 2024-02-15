<?php

if (!isset($_SESSION['username'])) {
    session_destroy();
    http_response_code(302);
    header("Location: http://localhost/digistamp/login.html");
}
else {
    if ($_SESSION['role'] === "admin") {
        header("Location: http://localhost/digistamp/new-users.html");
    }
    elseif ($_SESSION['role'] === "teacher") {
        header("Location: http://localhost/digistamp/panel.html");
    }
    else {
        header("Location: http://localhost/digistamp/dashboard.html");
    }
}

?>