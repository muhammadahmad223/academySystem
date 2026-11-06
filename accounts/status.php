<?php
include '../config/db_config.php';

$id = $_GET['id'];
$status = $_GET['status'];

if ($status != 'Active' && $status != 'Inactive') {
    die("Invalid status");
}

$sql = "UPDATE accounts SET status='$status' WHERE id='$id' AND deleted_at IS NULL";

$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");

header("Location: index.php?status=success");
mysqli_close($conn);