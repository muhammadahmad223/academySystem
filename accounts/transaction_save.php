<?php
include '../config/db_config.php';

$account_id = $_POST['account_id'];
$transaction_type = $_POST['transaction_type'];
$amount = $_POST['amount'];
$reference = $_POST['reference'];
$remarks = $_POST['remarks'];
$transaction_date = $_POST['transaction_date'];
$is_reconcile = $_POST['is_reconcile'];

$account = mysqli_query($conn, "SELECT name FROM accounts WHERE id='$account_id' AND deleted_at IS NULL");
$account_data = mysqli_fetch_assoc($account);
$account_name = $account_data['name'];

$sql = "INSERT INTO transactions(account_id, account_name, transaction_type, amount, reference, remarks, transaction_date, is_reconcile)
        VALUES ('$account_id', '$account_name', '$transaction_type', '$amount', '$reference', '$remarks', '$transaction_date', '$is_reconcile')";

$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");

header("Location: index.php?transaction=success");
mysqli_close($conn);