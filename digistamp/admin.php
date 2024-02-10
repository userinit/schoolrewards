<?php?>

/*
if(isset($_SESSION['role']) && $_SESSION['role'] === "admin") {
    /*if ($_SERVER['REQUEST_METHOD'] === "POST") {
        if (isset($_FILES['csv_file'] && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK)) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, "r");

        }
        else {
            // Handle upload errors
            echo 'Error uploading file: ' . $_FILES['file']['error'];
        }
    }
    else {
        header("Location: http://localhost/digistamp/admin.html");
    }
}
else {
    header("Location: http://localhost/digistamp/login.html");
}
?>

