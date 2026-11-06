<?php
include '../config/db_config.php';
    $teacher_id = $_POST['teacher_id'];
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
    $teacher_image_path = "";
    $sql1 = "SELECT teacher_img FROM teachers WHERE id = {$teacher_id}";
    $result1 = mysqli_query($conn, $sql1);
    $row = mysqli_fetch_assoc($result1);
    $teacher_image_path = $row['teacher_img'];
        if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] == 0) {
            $fileName = $_FILES['teacher_photo']['name'];
            $tmpName = $_FILES['teacher_photo']['tmp_name'];
            $teacher_image_path = $fileName;
            move_uploaded_file(
                $tmpName,
                "../uploads/teachers/" . $fileName
            );
        }
$sql = "UPDATE teachers SET
        teacher_img = '$teacher_image_path',
        teacher_name = '$t_name',
        father_name = '$tf_name',
        teacher_dob = '$t_dob',
        teacher_cnic = '$t_cnic',
        teacher_gender = '$t_gender',
        teacher_contact = '$t_contact',
        qualification = '$t_qualification',
        experience = '$t_experience',
        joining_date = '$t_joindate',
        basic_salary = '$t_salary',
        teacher_address = '$t_address'    
        WHERE id = '$teacher_id'";
$result = mysqli_query($conn, $sql) or die("Update Query Unsuccessful");
// 4. Redirect after successful update
header("Location: index.php?update=success");
exit();
mysqli_close($conn);
?>