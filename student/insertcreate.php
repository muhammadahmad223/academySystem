<?php
    include '../config/db_config.php';
    $stu_fNo = 'STU-' . $_POST['student_form_no'];
    $adm_date = strtotime($_POST['admission_date']);
    $adm_year = $_POST['academic_year'];
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
    $stu_image_path = "";
    if (
        isset($_FILES['student_photo']) &&
        $_FILES['student_photo']['error'] == 0
    ) {
        $fileName = $_FILES['student_photo']['name'];
        $tmpName = $_FILES['student_photo']['tmp_name'];
        $uploadsFolder = "../uploads/students/";
        $imagePath = $uploadsFolder . $fileName;
        move_uploaded_file(
            $tmpName,
            $imagePath
        );
        $stu_image_path = $fileName;
    }
    $sql = "INSERT INTO students(sformNo, admission_date, admission_year, student_img, student_name, stu_dob, stu_gender, stu_bform, stu_religion,	father_name, father_contact, stu_city, stu_address, stu_class, stu_section,	 monthly_fee, admission_fee) 
                    VALUES ('$stu_fNo', '$adm_date', '$adm_year', '$stu_image_path','$stu_name','$stu_dob','$stu_gender','$stu_bform','$stu_religion','$fname','$father_cont','$stu_city','$stu_address','$stu_class','$stu_section','$mon_fee','$adm_fee')";
    $result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
    $student_id = mysqli_insert_id($conn);

    foreach ($_POST['subjects'] as $subject_id) {
        $sql= "INSERT INTO student_subjects(student_id,subject_id) VALUES ('$student_id', '$subject_id')";
        $result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
    }
    header("Location: create.php?save=success");
    mysqli_close($conn);
    