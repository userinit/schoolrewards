<?php
if (isset($_SESSION['isTeacher']) && $_SESSION['isTeacher'] === TRUE) {
    
}
else {
    header('HTTP/1.1 401 Unauthorized');
}
?>