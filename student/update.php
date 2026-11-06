<?php

include '../config/db_config.php';

$stu_id = $_POST['student_id'];
$stu_fNo = $_POST['student_form_no'];
$adm_date = strtotime($_POST['admission_date']);
$adm_year = $_POST['academic_year'];
$stu_image_path = $_FILES['student_img'];
$sql1 = "SELECT student_img FROM students WHERE id = {$stu_id}";

$result1 = mysqli_query($conn, $sql1);

$row = mysqli_fetch_assoc($result1);

$stu_image_path = $row['student_img'];

if (isset($_FILES['student_img']) && $_FILES['student_img']['error'] == 0) {



    $fileName = $_FILES['student_img']['name'];
    $tmpName = $_FILES['student_img']['tmp_name'];

    $stu_image_path = $fileName;

    move_uploaded_file(
        $tmpName,
        "../uploads/students/" . $fileName
    );
}
$stu_name = $_POST['student_name'];
$stu_dob = strtotime($_POST['date_of_birth']);
$stu_gender = $_POST['gender'];
$stu_bform = $_POST['sbform'];
$stu_religion = $_POST['religion'];
$fname = $_POST['father_name'];
$father_cont = $_POST['fcontact'];
$stu_city = $_POST['city'];
$stu_address = $_POST['address'];
$stu_class = $_POST['student_class'];
$stu_section = $_POST['section'];
$mon_fee = $_POST['monthly_fee'];
$adm_fee = $_POST['admission_fee'];


$sql = "UPDATE students SET

    sformNo = '{$stu_fNo}',
    admission_date = '{$adm_date}',
    admission_year = '{$adm_year}',
student_img = '{$stu_image_path}',
    student_name = '{$stu_name}',
    stu_dob = '{$stu_dob}',
    stu_gender = '{$stu_gender}',
    stu_bform = '{$stu_bform}',
    stu_religion = '{$stu_religion}',

    father_name = '{$fname}',
    father_contact = '{$father_cont}',

    stu_city = '{$stu_city}',
    stu_address = '{$stu_address}',

    stu_class = '{$stu_class}',
   
    stu_section = '{$stu_section}',

    monthly_fee = '{$mon_fee}',
    admission_fee = '{$adm_fee}'

    WHERE id = '{$stu_id}'";


$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
$sql_subjects = "DELETE FROM student_subjects WHERE student_id = {$stu_id}";
mysqli_query($conn, $sql_subjects) or die("Query Unsuccessful");
foreach ($_POST['subjects'] as $subject_id) {
 $sql= "INSERT INTO student_subjects(student_id,subject_id)
 VALUES ('$stu_id', '$subject_id')";
 $result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
}
header("Location: edit.php?id={$stu_id}&save=success");

exit;
