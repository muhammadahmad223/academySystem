<?php
include '../config/db_config.php';

$id = $_POST['id'];
$name = $_POST['name'];
$phoneno = $_POST['phoneno'];
$address = $_POST['address'];
$status = $_POST['status'];
$sql = "UPDATE accounts SET
        name='$name',
        phoneno='$phoneno',
        address='$address',
        status='$status'
        WHERE id='$id' AND deleted_at IS NULL";

$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
header("Location: index.php?update=success");
mysqli_close($conn);