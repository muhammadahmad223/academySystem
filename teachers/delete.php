<?php
include '../config/db_config.php';
    if (isset($_GET['id'])) {
        $teacher_id = $_GET['id'];
        $sql = "UPDATE teachers SET deleted_at = 1 WHERE id = $teacher_id";
        $result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
        if ($result) {
            mysqli_close($conn);
            header("Location: index.php?delete=success");
            exit();
        }
        mysqli_close($conn);
    }
