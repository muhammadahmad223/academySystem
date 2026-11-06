<?php

include '../config/db_config.php';
if (isset($_GET['id'])) {
    $stu_id = $_GET['id'];
    $sql = "UPDATE students SET deleted_at = 1 WHERE id = $stu_id";
    $result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
    if ($result) {
        header("Location: index.php?delete=success");
exit;
    }
    mysqli_close($conn);
}
?>