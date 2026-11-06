<?php
include '../config/db_config.php';
$tform_no = 'TEC-' . $_POST['teacher_id'];
$t_name = $_POST['teacher_name'];
$tf_name = $_POST['father_name'];
$t_dob = date('Y-m-d', strtotime($_POST['date_of_birth']));
$t_cnic = $_POST['teacher_cnic'];
$t_gender = $_POST['gender'];
$t_contact = $_POST['contact_no'];
$t_qualification = $_POST['qualification'];
$t_experience = $_POST['experience'];
$t_joindate = date('Y-m-d', strtotime($_POST['joining_date']));
$t_salary = $_POST['basic_salary'];
$t_address = $_POST['address'];
$tech_image_path = "";

if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] == 0) {
    $fileName = $_FILES['teacher_photo']['name'];
    $tmpName = $_FILES['teacher_photo']['tmp_name'];
    $uploadsFolder = "../uploads/teachers/";
    $imagePath = $uploadsFolder . $fileName;
    move_uploaded_file($tmpName, $imagePath);
    $tech_image_path = $fileName;
}

$sql = "INSERT INTO teachers(tform_no, teacher_img, teacher_name, father_name, teacher_dob, teacher_cnic, teacher_gender, teacher_contact, qualification, experience, joining_date, basic_salary, teacher_address) 
VALUES ('$tform_no', '$tech_image_path', '$t_name', '$tf_name', '$t_dob', '$t_cnic', '$t_gender', '$t_contact', '$t_qualification', '$t_experience', '$t_joindate', '$t_salary', '$t_address')";
$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");

$teacher_id = mysqli_insert_id($conn);

$account_sql = "INSERT INTO accounts(teacher_id, name, phoneno, address, account_type, status) 
VALUES ('$teacher_id', '$t_name', '$t_contact', '$t_address', 'Teacher', 'Active')";
mysqli_query($conn, $account_sql) or die("Account Query Unsuccessful");

header("Location: create.php?save=success");
mysqli_close($conn);