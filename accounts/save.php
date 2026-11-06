<?php
include '../config/db_config.php';

$name = $_POST['name'];
$phoneno = $_POST['phoneno'];
$address = $_POST['address'];
$account_type = $_POST['account_type'];
$status = $_POST['status'];

$sql = "INSERT INTO accounts(name, phoneno, address, account_type, status)
        VALUES ('$name', '$phoneno', '$address', '$account_type', '$status')";

$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");

header("Location: index.php?save=success");
mysqli_close($conn);