<?php

include '../config/db_config.php';
require_once '../vendor/autoload.php';
$classes = ['Play Group', 'Nursery', 'Prep', 'One', 'Two', 'Three','Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten','Eleven', 'Twelve'];
$student_id = (int)($_GET['student_id'] ?? 0);
$selected_class = $_GET['class'] ?? '';
$selected_section = $_GET['section'] ?? '';
$selected_status = $_GET['status'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$student_search = $_GET['student_search'] ?? '';
// Attendance records
$sql = "SELECT attendance.attendance_date, attendance.status, students.sformNo, students.student_name, students.stu_class, students.stu_section
        FROM attendance INNER JOIN students ON attendance.student_id = students.id WHERE students.deleted_at = 0";
        if ($student_id > 0) {
    $sql .= " AND students.id = $student_id";
             }
if (!empty($selected_class)) {
    $class = mysqli_real_escape_string($conn, $selected_class);
    $sql .= " AND students.stu_class = '$class'";
}
if (!empty($selected_section)) {
    $section = mysqli_real_escape_string($conn, $selected_section);
    $sql .= " AND students.stu_section = '$section'";
}
if (!empty($selected_status)) {
    $status = mysqli_real_escape_string($conn, $selected_status);
    $sql .= " AND attendance.status = '$status'";
}

if (!empty($from_date)) {
    $from = mysqli_real_escape_string($conn, $from_date);
    $sql .= " AND attendance.attendance_date >= '$from'";
}

if (!empty($to_date)) {
    $to = mysqli_real_escape_string($conn, $to_date);
    $sql .= " AND attendance.attendance_date <= '$to'";
}

if (!empty($student_search)) {
    $search = mysqli_real_escape_string($conn, $student_search);

    $sql .= " AND (
        students.student_name LIKE '%$search%'
        OR students.sformNo LIKE '%$search%'
    )";
}

$sql .= " ORDER BY attendance.attendance_date DESC, students.student_name ASC";
$result = mysqli_query($conn, $sql);
$attendance_records = [];
$student_name = '';
$student_form_no = '';
$student_class = '';
$student_section = '';

if ($student_id > 0) {
    $student_sql = "SELECT sformNo, student_name, stu_class, stu_section
                    FROM students WHERE id = $student_id
                    AND deleted_at = 0";
    $student_result = mysqli_query($conn, $student_sql);
    if ($student_result && mysqli_num_rows($student_result) > 0) {
        $student = mysqli_fetch_assoc($student_result);
        $student_name = $student['student_name'];
        $student_form_no = $student['sformNo'];
        $student_class = $student['stu_class'];
        $student_section = $student['stu_section'];
    }
}
while ($row = mysqli_fetch_assoc($result)) {
    $attendance_records[] = $row;
}
// Summary
$total_records = count($attendance_records);
$present_records = 0;
$absent_records = 0;
foreach ($attendance_records as $record) {

    if ($record['status'] === 'Present') {
        $present_records++;
    }

    if ($record['status'] === 'Absent') {
        $absent_records++;
    }
}

$attendance_percentage = $total_records > 0
    ? round(($present_records / $total_records) * 100, 2)
    : 0;


    $html = '
<style>
    body {
        font-family: sans-serif;
        font-size: 10px;
    }

    h1 {
        text-align: center;
        font-size: 18px;
        margin-bottom: 5px;
    }

    .subtitle {
        text-align: center;
        font-size: 11px;
        margin-bottom: 15px;
    }

    .info {
        width: 100%;
        margin-bottom: 15px;
    }

    .info td {
        padding: 4px;
    }

    .summary {
        width: 100%;
        margin-bottom: 15px;
    }

    .summary td {
        border: 1px solid #ddd;
        padding: 7px;
        text-align: center;
    }

    table.records {
        width: 100%;
        border-collapse: collapse;
    }

    table.records th,
    table.records td {
        border: 1px solid #ccc;
        padding: 6px;
    }

    table.records th {
        font-weight: bold;
    }

    .present {
        font-weight: bold;
    }

    .absent {
        font-weight: bold;
    }
</style>

<h1> Unique Science Academy</h1>

<div class="subtitle">
    Attendance History Report
</div>

<table class="info">';

if ($student_id > 0) {

    $html .= '
    <tr>
        <td><strong>Student:</strong> ' . htmlspecialchars($student_name) . '</td>
        <td><strong>Form No:</strong> ' . htmlspecialchars($student_form_no) . '</td>
    </tr>
    <tr>
        <td><strong>Class:</strong> ' . htmlspecialchars($student_class) . '</td>
        <td><strong>Section:</strong> ' . htmlspecialchars($student_section ?: '-') . '</td>
    </tr>
    <tr>
        <td><strong>From:</strong> ' . htmlspecialchars($from_date ?: 'All Dates') . '</td>
        <td><strong>To:</strong> ' . htmlspecialchars($to_date ?: 'All Dates') . '</td>
    </tr>
    <tr>
        <td><strong>Status:</strong> ' . htmlspecialchars($selected_status ?: 'All Status') . '</td>
        <td></td>
    </tr>';

} else {

    $html .= '
    <tr>
        <td><strong>Class:</strong> ' . htmlspecialchars($selected_class ?: 'All Classes') . '</td>
        <td><strong>Section:</strong> ' . htmlspecialchars($selected_section ?: 'All Sections') . '</td>
    </tr>

    <tr>
        <td><strong>From:</strong> ' . htmlspecialchars($from_date ?: 'All Dates') . '</td>
        <td><strong>To:</strong> ' . htmlspecialchars($to_date ?: 'All Dates') . '</td>
    </tr>

    <tr>
        <td><strong>Student:</strong> ' . htmlspecialchars($student_search ?: 'All Students') . '</td>
        <td><strong>Status:</strong> ' . htmlspecialchars($selected_status ?: 'All Status') . '</td>
    </tr>';
}

$html .= '
</table>

<table class="summary">
    <tr>
        <td>
            <strong>Total Records</strong><br>
            ' . $total_records . '
        </td>

        <td>
            <strong>Present</strong><br>
            ' . $present_records . '
        </td>

        <td>
            <strong>Absent</strong><br>
            ' . $absent_records . '
        </td>

        <td>
            <strong>Attendance Rate</strong><br>
            ' . $attendance_percentage . '%
        </td>
    </tr>
</table>

<table class="records">
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Form No</th>
            <th>Student</th>
            <th>Class</th>
            <th>Section</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
';

$count = 1;

foreach ($attendance_records as $record) {

    $status_class = $record['status'] === 'Present'
        ? 'present'
        : 'absent';

    $html .= '
        <tr>
            <td>' . $count++ . '</td>
            <td>' . date('d M Y', strtotime($record['attendance_date'])) . '</td>
            <td>' . htmlspecialchars($record['sformNo']) . '</td>
            <td>' . htmlspecialchars($record['student_name']) . '</td>
            <td>' . htmlspecialchars($record['stu_class']) . '</td>
            <td>' . htmlspecialchars($record['stu_section'] ?: '-') . '</td>
            <td class="' . $status_class . '">' . htmlspecialchars($record['status']) . '</td>
        </tr>
    ';
}

if (empty($attendance_records)) {
    $html .= '
        <tr>
            <td colspan="7" style="text-align:center;">
                No attendance records found.
            </td>
        </tr>
    ';
}

$html .= '
    </tbody>
</table>
';


$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
    'orientation' => 'L',
    'margin_top' => 12,
    'margin_bottom' => 12,
    'margin_left' => 10,
    'margin_right' => 10
]);

$mpdf->SetTitle('Attendance History Report');
$mpdf->WriteHTML($html);
$mpdf->Output('attendance-history.pdf', 'I');