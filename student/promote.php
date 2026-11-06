<?php
    include '../config/db_config.php';
    if(!isset($_POST['student_ids']) ||
        empty($_POST['student_ids']) ||
        !isset($_POST['promote_to_class']) ||
        empty($_POST['promote_to_class'])
    ){
        header("Location: index.php");
        exit;
    }
$student_ids = $_POST['student_ids'];
$promote_to_class = mysqli_real_escape_string($conn, $_POST['promote_to_class']);
$ids = [];
foreach ($student_ids as $student_id) {
    $student_id = (int)$student_id;
    if ($student_id > 0) {
        $ids[] = $student_id;
    }
}
if (empty($ids)) {
    header("Location: index.php");
    exit;
}
$id_list = implode(',', $ids);
$sql = "UPDATE students SET stu_class = '$promote_to_class' WHERE id IN ($id_list) AND deleted_at = 0";
$result = mysqli_query($conn, $sql);
if ($result) {
    header("Location: index.php?promotion=success");
    exit;
} else {
    header("Location: index.php?promotion=error");
    exit;
}
mysqli_close($conn);
