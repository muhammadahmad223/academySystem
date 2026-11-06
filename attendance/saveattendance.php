<?php
include '../config/db_config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}
$attendance_date = $_POST['attendance_date'] ?? '';
$class_name = $_POST['class_name'] ?? '';
$section = $_POST['section'] ?? '';
$statuses = $_POST['status'] ?? [];
if (empty($attendance_date) || empty($class_name) || empty($statuses)) {
    header("Location: index.php?error=1");
    exit;
}
foreach ($statuses as $student_id => $status) {
    $student_id = (int)$student_id;
    if ($status !== 'Present' && $status !== 'Absent') {
        continue;
    }
    $check_sql = "SELECT id FROM attendance WHERE student_id = $student_id AND attendance_date = '$attendance_date'";
    $check_result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_result) > 0) {
        $row = mysqli_fetch_assoc($check_result);
        $attendance_id = $row['id'];
        $update_sql = "UPDATE attendance SET status = '$status', updated_at = NOW()
                       WHERE id = $attendance_id";
        mysqli_query($conn, $update_sql);

    } else {
        $insert_sql = "INSERT INTO attendance (student_id, attendance_date, status, created_at, updated_at)
                       VALUES ($student_id, '$attendance_date', '$status', NOW(), NOW())";
        mysqli_query($conn, $insert_sql);
    }
}
header("Location: index.php?attendance=success&class=" . urlencode($class_name) . "&section=" . urlencode($section) . "&date=" . urlencode($attendance_date));
exit;
?>





