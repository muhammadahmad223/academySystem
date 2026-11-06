<?php

include '../config/db_config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$student_id = (int)$_GET['id'];

$sql = "SELECT *
        FROM students
        WHERE id = $student_id
        AND deleted_at = 0
        LIMIT 1";

$result = mysqli_query($conn,$sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Student record not found.");
}

$student = mysqli_fetch_assoc($result);

/* Student subjects */
$subjects = [];

$subject_sql = "SELECT subjects.subject_name
                FROM student_subjects
                INNER JOIN subjects
                    ON subjects.id = student_subjects.subject_id
                WHERE student_subjects.student_id = $student_id
                ORDER BY subjects.subject_name ASC";

$subject_result = mysqli_query($conn,$subject_sql);

if ($subject_result) {
    while ($subject_row = mysqli_fetch_assoc($subject_result)) {
        $subjects[] = $subject_row['subject_name'];
    }
}

$subjects_text = !empty($subjects)
    ? implode(', ',$subjects)
    : 'N/A';

$admission_date = !empty($student['admission_date'])
    ? date('d M Y',(int)$student['admission_date'])
    : 'N/A';

$dob = !empty($student['stu_dob'])
    ? date('d M Y',(int)$student['stu_dob'])
    : 'N/A';

$form_no = $student['sformNo'];

$photo = !empty($student['student_img'])
    ? '../uploads/students/' . $student['student_img']
    : '../dist/assets/img/defaultimage.jpg';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form - <?php echo htmlspecialchars($student['student_name']); ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e9ecef;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            font-size: 13px;
        }

        .print-button {
            text-align: center;
            padding: 15px;
        }

        .print-button button {
            border: 0;
            background: #0d6efd;
            color: #fff;
            padding: 9px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 12mm;
            background: #fff;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .logo {
            width: 105px;
            height: 70px;
            object-fit: contain;
        }

        .academy-info {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }

        .academy-name {
            font-size: 25px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .tagline {
            font-size: 10px;
            color: #666;
            letter-spacing: 1px;
        }

        .form-title {
            font-size: 19px;
            font-weight: 700;
            margin-top: 8px;
            letter-spacing: 1px;
        }

        .form-meta {
            width: 125px;
            font-size: 12px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #aaa;
            padding: 4px 0;
        }

        .section-title {
            background: #f1f3f5;
            border: 1px solid #bbb;
            padding: 6px 9px;
            font-weight: 700;
            font-size: 13px;
            margin-top: 10px;
            margin-bottom: 7px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-top: 1px solid #aaa;
            border-left: 1px solid #aaa;
        }

        .field {
            display: flex;
            min-height: 30px;
            border-right: 1px solid #aaa;
            border-bottom: 1px solid #aaa;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .label {
            width: 38%;
            padding: 7px 8px;
            font-weight: 600;
            background: #fafafa;
        }

        .value {
            flex: 1;
            padding: 7px 8px;
        }

        .student-photo {
            width: 95px;
            height: 110px;
            object-fit: cover;
            border: 1px solid #888;
            padding: 2px;
        }

        .student-photo-cell {
            width: 115px;
            text-align: center;
            padding: 6px;
            border-right: 1px solid #aaa;
            border-bottom: 1px solid #aaa;
        }

        .academic-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-top: 1px solid #aaa;
            border-left: 1px solid #aaa;
        }

        .academic-field {
            border-right: 1px solid #aaa;
            border-bottom: 1px solid #aaa;
            padding: 7px 8px;
        }

        .academic-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .academic-value {
            font-weight: 600;
        }

        .rules {
            border: 1px solid #aaa;
            padding: 9px 12px;
            line-height: 1.5;
        }

        .rules ol {
            margin: 4px 0 0 18px;
            padding: 0;
        }

        .declaration {
            border: 1px solid #aaa;
            padding: 9px 12px;
            margin-top: 9px;
            line-height: 1.5;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 25px;
            margin-top: 35px;
        }

        .signature {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .office-note {
            margin-top: 15px;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: #fff;
            }

            .print-button {
                display: none;
            }

            .page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 12mm;
            }
        }
    </style>
</head>

<body>

<div class="print-button">
    <button onclick="window.print()">
        Print Admission Form
    </button>
</div>

<div class="page">

    <div class="header">

        <img
            src="../dist/assets/img/uniquelogo2.png"
            class="logo"
            alt="Unique Science Academy">

        <div class="academy-info">
            <div class="academy-name">
                Unique Science Academy
            </div>

            <div class="tagline">
                WHERE KNOWLEDGE IGNITES POTENTIAL
            </div>

            <div class="form-title">
                ADMISSION FORM
            </div>
        </div>

        <div class="form-meta">

            <div class="meta-row">
                <strong>Form No</strong>
                <span><?php echo htmlspecialchars($form_no); ?></span>
            </div>

            <div class="meta-row">
                <strong>Date</strong>
                <span><?php echo $admission_date; ?></span>
            </div>

        </div>

    </div>


    <div class="section-title">
        STUDENT INFORMATION
    </div>

    <div style="display:grid;grid-template-columns:1fr 115px;border-top:1px solid #aaa;border-left:1px solid #aaa;">

        <div class="info-grid" style="border-top:0;border-left:0;">

            <div class="field">
                <div class="label">Student Name</div>
                <div class="value"><?php echo htmlspecialchars($student['student_name']); ?></div>
            </div>

            <div class="field">
                <div class="label">Date of Birth</div>
                <div class="value"><?php echo $dob; ?></div>
            </div>

            <div class="field">
                <div class="label">Gender</div>
                <div class="value"><?php echo htmlspecialchars($student['stu_gender']); ?></div>
            </div>

            <div class="field">
                <div class="label">B-Form / CNIC</div>
                <div class="value"><?php echo htmlspecialchars($student['stu_bform']); ?></div>
            </div>

            <div class="field">
                <div class="label">Religion</div>
                <div class="value"><?php echo htmlspecialchars($student['stu_religion']); ?></div>
            </div>

            <div class="field">
                <div class="label">Academic Year</div>
                <div class="value"><?php echo htmlspecialchars($student['admission_year']); ?></div>
            </div>

        </div>

        <div class="student-photo-cell">
            <img
                src="<?php echo htmlspecialchars($photo); ?>"
                class="student-photo"
                alt="Student Photo">
        </div>

    </div>


    <div class="section-title">
        FATHER / FAMILY INFORMATION
    </div>

    <div class="info-grid">

        <div class="field">
            <div class="label">Father Name</div>
            <div class="value"><?php echo htmlspecialchars($student['father_name']); ?></div>
        </div>

        <div class="field">
            <div class="label">Father Contact</div>
            <div class="value"><?php echo htmlspecialchars($student['father_contact']); ?></div>
        </div>

        <div class="field full">
    <div class="label" style="width:18%;">Address</div>
    <div class="value"><?php echo htmlspecialchars($student['stu_address']); ?></div>
</div>

    </div>


    <div class="section-title">
        ACADEMIC & FEE INFORMATION
    </div>

    <div class="academic-grid">

        <div class="academic-field">
            <div class="academic-label">CLASS</div>
            <div class="academic-value"><?php echo htmlspecialchars($student['stu_class']); ?></div>
        </div>

        <div class="academic-field">
            <div class="academic-label">SECTION</div>
            <div class="academic-value"><?php echo htmlspecialchars($student['stu_section']); ?></div>
        </div>

        <div class="academic-field">
            <div class="academic-label">MONTHLY FEE</div>
            <div class="academic-value">
                Rs. <?php echo number_format((float)$student['monthly_fee'],2); ?>
            </div>
        </div>

        <div class="academic-field">
            <div class="academic-label">ADMISSION FEE</div>
            <div class="academic-value">
                Rs. <?php echo number_format((float)$student['admission_fee'],2); ?>
            </div>
        </div>

        <div class="academic-field" style="grid-column:span 2;">
            <div class="academic-label">SUBJECTS</div>
            <div class="academic-value">
                <?php echo htmlspecialchars($subjects_text); ?>
            </div>
        </div>

    </div>


   <div class="section-title">
    ACADEMY RULES & DECLARATION
</div>

<div class="rules">

    <ol>
        <li>Student must maintain regular attendance and punctuality.</li>
        <li>Student must follow all academy rules and instructions.</li>
        <li>Respectful and appropriate behaviour is required from every student.</li>
        <li>Repeated misconduct or violation of academy rules may result in disciplinary action.</li>
        <li>In case of serious or repeated disciplinary violations, the academy may terminate the student's admission.</li>
    </ol>

    <div style="margin-top:8px;">
        
        I hereby declare that the information provided in this admission form
        is correct and complete. I agree to follow the rules and regulations
        of Unique Science Academy and accept the academy's disciplinary policies.
    </div>

</div>


    <div class="signatures">

        <div class="signature">
            Parent / Guardian Signature
        </div>

        <div class="signature">
            Student Signature
        </div>

        <div class="signature">
            Office Signature
        </div>

    </div>

    <div class="office-note">
        For Office Use — Unique Science Academy
    </div>

</div>

</body>
</html>