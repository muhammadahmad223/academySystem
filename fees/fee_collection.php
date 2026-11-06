<?php

include '../config/db_config.php';

$selected_month = isset($_GET['month']) && $_GET['month'] !== ''
    ? (int)$_GET['month']
    : 0;
$current_month = (int)date('n');
$current_year = (int)date('Y');
$cutoff_month = $selected_month > 0 ? $selected_month : $current_month;
if ($cutoff_month > $current_month) {
    $cutoff_month = $current_month;
}
$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';
$class_filter = isset($_GET['class'])
    ? trim($_GET['class'])
    : '';
$status_filter = isset($_GET['status'])
    ? trim($_GET['status'])
    : '';
$search = mysqli_real_escape_string($conn,$search);
$class_filter = mysqli_real_escape_string($conn,$class_filter);
$total_students = 0;
$paid_students = 0;
$partial_students = 0;
$unpaid_students = 0;

/* Create missing monthly payables up to selected month */
$students_sql = "SELECT id,monthly_fee,admission_date
                 FROM students WHERE deleted_at = 0";
$students_result = mysqli_query($conn,$students_sql);
while ($student_row = mysqli_fetch_assoc($students_result)) {
    $student_id = (int)$student_row['id'];
    $monthly_fee = (float)$student_row['monthly_fee'];
    $admission_date = (int)$student_row['admission_date'];
    $admission_month = (int)date('n',$admission_date);
    $admission_year = (int)date('Y',$admission_date);
    /* Ignore students admitted after selected month */
    if ($admission_year > $current_year ||
        ($admission_year == $current_year && $admission_month > $cutoff_month)) {
        continue;
    }
    $start_number = ($admission_year * 12) + $admission_month;
    $cutoff_number = ($current_year * 12) + $cutoff_month;
    for ($number = $start_number; $number <= $cutoff_number; $number++) {
        $year = (int)floor(($number - 1) / 12);
        $month = (($number - 1) % 12) + 1;
        $insert_sql = "INSERT IGNORE INTO fee_payables (student_id,fee_month,fee_year,amount,discount_amount,payable_amount,status)
            VALUES ($student_id,$month,$year,$monthly_fee,0.00,$monthly_fee,'Unpaid')";
        mysqli_query($conn,$insert_sql);
    }
}

/* Main query */
$sql = "SELECT students.id, students.sformNo, students.student_name, students.stu_class, students.monthly_fee, students.admission_date,
            COALESCE(SUM(fee_payables.amount),0) AS fee_amount,
            COALESCE(SUM(fee_payables.discount_amount),0) AS discount_amount,
            COALESCE(SUM(fee_payables.payable_amount),0) AS payable_amount,
            COALESCE(SUM(fp.paid_amount),0) AS paid_amount
        FROM students LEFT JOIN fee_payables
            ON fee_payables.student_id = students.id
            AND fee_payables.deleted_at IS NULL
            AND (
                fee_payables.fee_year < $current_year
                OR (
                    fee_payables.fee_year = $current_year
                    AND fee_payables.fee_month <= $cutoff_month
                )
            )

        LEFT JOIN (
            SELECT
                fee_payable_id,
                SUM(amount) AS paid_amount
            FROM fee_payments
            WHERE deleted_at IS NULL
            GROUP BY fee_payable_id
        ) AS fp
            ON fp.fee_payable_id = fee_payables.id

        WHERE students.deleted_at = 0

        AND (
            YEAR(FROM_UNIXTIME(students.admission_date)) < $current_year
            OR (
                YEAR(FROM_UNIXTIME(students.admission_date)) = $current_year
                AND MONTH(FROM_UNIXTIME(students.admission_date)) <= $cutoff_month
            )
        )";

if (!empty($search)) {
    $sql .= " AND (
        students.student_name LIKE '%$search%'
        OR students.sformNo LIKE '%$search%'
        OR students.father_name LIKE '%$search%'
        OR students.father_contact LIKE '%$search%'
    )";
}
if (!empty($class_filter)) {
    $sql .= " AND students.stu_class = '$class_filter'";
}
$sql .= " GROUP BY
            students.id,
            students.sformNo,
            students.student_name,
            students.stu_class,
            students.monthly_fee,
            students.admission_date

          ORDER BY students.id DESC";

$result = mysqli_query($conn,$sql)
    or die("Query Unsuccessful");

/* Calculate summary counts */
while ($row = mysqli_fetch_assoc($result)) {
    $payable_amount = (float)$row['payable_amount'];
    $paid_amount = (float)$row['paid_amount'];
    $remaining = $payable_amount - $paid_amount;
    if ($remaining < 0) {
        $remaining = 0;
    }
    if ($payable_amount <= 0 || $paid_amount >= $payable_amount) {
        $status = 'Paid';
    } elseif ($paid_amount > 0) {
        $status = 'Partial';
    } else {
        $status = 'Unpaid';
    }
    if (!empty($status_filter) && $status != $status_filter) {
        continue;
    }
    $total_students++;
    if ($status == 'Paid') {
        $paid_students++;
    } elseif ($status == 'Partial') {
        $partial_students++;
    } else {
        $unpaid_students++;
    }
}

/* Reset result pointer */
mysqli_data_seek($result,0);

?>



<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE v4 | Dashboard</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
        (() => {
            'use strict';
            const root = document.documentElement;

            // Applications with their own theming opt out of AdminLTE's color mode
            // entirely, here as well as in the bundle.
            if (root.getAttribute('data-lte-color-mode') === 'off') {
                return;
            }

            const STORAGE_KEY = 'lte-theme';
            let stored = null;
            try {
                stored = localStorage.getItem(STORAGE_KEY);
            } catch {
                // localStorage may be unavailable (private mode, sandboxed iframe).
            }
            // Mirror the precedence in color-mode.ts: the visitor's stored choice
            // wins, then a theme this page declared itself, then the OS preference.
            const authored = root.getAttribute('data-bs-theme');
            let resolved = 'light';
            if (stored === 'dark' || stored === 'light') {
                resolved = stored;
            } else if (authored === 'dark' || authored === 'light') {
                resolved = authored;
            } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
                resolved = 'dark';
            }
            root.setAttribute('data-bs-theme', resolved);
            root.style.colorScheme = resolved;
            // Flag values computed here, so the bundle does not mistake them for a
            // theme the page declared and stop following the OS preference.
            if (resolved !== authored) {
                root.setAttribute('data-lte-theme-resolved', '');
            }
        })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE v4 | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
        name="description"
        content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind." />
    <meta
        name="keywords"
        content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel" />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../dist/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="../dist/css/custom.css">
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
        crossorigin="anonymous"
        media="print"
        onload="this.media = 'all'" />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <!-- apexcharts -->
<style>
.bulk-student-list{
    max-height:220px;
    overflow-y:auto;
    border:1px solid #dee2e6;
    border-radius:6px;
    padding:4px;
}

.bulk-student-list::-webkit-scrollbar{
    width:5px;
}

.bulk-student-list::-webkit-scrollbar-thumb{
    background:#adb5bd;
    border-radius:10px;
}
</style>

</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <?php include '../layout/sidebar.php'; ?>
        <div class="layout-page">
            <?php include '../layout/header.php'; ?>

            <div class="content-wrapper">

                <div class="container-fluid">

                    <main class="app-main py-4">


                        <div class="container-fluid">

                            <div class="row">

                                 <div class="col-sm-6">
                                    <h1 class="mb-0 fs-3"> Fee Collection </h1>
                                    <p> Collect Fee From Students <p>
                                </div>
                                 <div class="col-sm-6">
                                    <nav aria-label="breadcrumb">
                                       <ol class="breadcrumb float-sm-end">
                                           <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                                           <li class="breadcrumb-item"><a href="../student/index.php">Students</a></li>
                                           <li class="breadcrumb-item active" aria-current="page">Fees</li>
                                        </ol>
                                   </nav>
                                </div>
                                   
                            </div>

                        </div>

                   


                    <div class="toast-container position-fixed top-0 end-0 p-3">

                        <div
                            id="paymentToast"
                            class="toast align-items-center text-bg-success border-0"
                            role="alert"
                            aria-live="assertive"
                            aria-atomic="true">

                            <div class="d-flex">

                                <div class="toast-body">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <span id="paymentToastMessage">
                                        Fee collected successfully!
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    class="btn-close btn-close-white me-2 m-auto"
                                    data-bs-dismiss="toast">
                                </button>

                            </div>

                        </div>

                    </div>


                    <div class="card">

                        <div class="card-body">

                            <div class="row mb-4">

                                <form method="GET" class="row g-3 mb-4">

                                    <!-- Search -->
                                    <div class="col-md-3">
                                        <label class="form-label">Search Student</label>
                                        <input
                                            type="text"
                                            name="search"
                                            class="form-control"
                                            placeholder="Name or Form No Father Name or Contact"
                                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    </div>

                                    <!-- Class -->
                                    <div class="col-md-3">
                                        <label class="form-label">Class</label>

                                        <select name="class" class="form-select">
                                            <option value="">All Classes</option>

                                            <?php
                                            $classes = [
                                                'Play Group',
                                                'Nursery',
                                                'Prep',
                                                'One',
                                                'Two',
                                                'Three',
                                                'Four',
                                                'Five',
                                                'Six',
                                                'Seven',
                                                'Eight',
                                                'Nine',
                                                'Ten',
                                                'Eleven',
                                                'Twelve'
                                            ];

                                            foreach ($classes as $class) {
                                            ?>

                                                <option
                                                    value="<?php echo $class; ?>"
                                                    <?php echo (($_GET['class'] ?? '') == $class) ? 'selected' : ''; ?>>
                                                    <?php echo $class; ?>
                                                </option>

                                            <?php } ?>

                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>

                                        <select name="status" class="form-select">

                                            <option value="">All</option>

                                            <option value="Unpaid"
                                                <?php echo (($_GET['status'] ?? '') == 'Unpaid') ? 'selected' : ''; ?>>
                                                Unpaid
                                            </option>

                                            <option value="Partial"
                                                <?php echo (($_GET['status'] ?? '') == 'Partial') ? 'selected' : ''; ?>>
                                                Partial
                                            </option>

                                            <option value="Paid"
                                                <?php echo (($_GET['status'] ?? '') == 'Paid') ? 'selected' : ''; ?>>
                                                Paid
                                            </option>

                                        </select>
                                    </div>

                                    <!-- Month -->
                                    <div class="col-md-2">
                                        <label class="form-label">Fee Month</label>
                                      <select name="month" class="form-select">
                              <option value="" <?php echo ($selected_month == 0) ? 'selected' : ''; ?>>
                                                                 All Months
                                                                       </option>

                                                          <?php
                                                            $months = [
                                                                          1 => 'January',
                                                                          2 => 'February',
                                                                          3 => 'March',
                                                                          4 => 'April',
                                                                          5 => 'May',
                                                                          6 => 'June',
                                                                          7 => 'July',
                                                                          8 => 'August',
                                                                          9 => 'September',
                                                                          10 => 'October',
                                                                          11 => 'November',
                                                                          12 => 'December'
                                                                      ];

                                                    foreach ($months as $month_number => $month_name) {
                                                    ?>

                                                  <option
                                                      value="<?php echo $month_number; ?>"
                                                      <?php echo ($selected_month == $month_number) ? 'selected' : ''; ?>>
                                                      <?php echo $month_name; ?>
                                                  </option>
                                          
                                              <?php } ?>

                                          </select>
                                    </div>

                                    <!-- Search Button -->
                                    <div class="col-md-2 d-flex align-items-end gap-2">

                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search me-1"></i>
                                            Search
                                        </button>

                                        <a href="fee_collection.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Reset
                                        </a>

                                    </div>

                                    <!-- Year -->
                                    <input
                                        type="hidden"
                                        name="year"
                                        value="<?php echo $current_year; ?>">

                                </form>

                            </div>



                            <div class="row g-3 mb-4">

                                <!-- Total Students -->
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">
                                                Total Students
                                            </div>

                                            <h4 class="mb-0">
                                                <?php echo $total_students; ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>


                                <!-- Paid -->
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">
                                                Paid
                                            </div>

                                            <h4 class="mb-0 text-success" id="paidStudents">
                                                <?php echo $paid_students; ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>


                                <!-- Partial -->
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">
                                                Partial
                                            </div>

                                            <h4 class="mb-0 text-warning" id="partialStudents">
                                                <?php echo $partial_students; ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>


                                <!-- Unpaid -->
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">
                                                Unpaid
                                            </div>

                                            <h4 class="mb-0 text-danger" id="unpaidStudents">
                                                <?php echo $unpaid_students; ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>

                            </div>



                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div>
                                    <button
                                        type="button"
                                        class="btn btn-success"
                                        id="collectSelectedBtn"
                                        disabled>
                                        <i class="bi bi-cash-stack me-1"></i>
                                        Collect Selected
                                    </button>
                                </div>

                                <div class="text-muted">
                                    Selected:
                                    <strong id="selectedCount">0</strong>
                                </div>

                            </div>


                            <div class="table-responsive">

                                <table class="table">

                                    <thead>

                                        <tr>

                                            <th>
                                                <input type="checkbox" id="selectAll">
                                            </th>
                                            <th>#</th>
                                            <th>Form No</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Fee</th>
                                            <th>Paid</th>
                                            <th>Remaining</th>
                                            <th>Status</th>
                                            <th>Action</th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php

                                        $count = 1;
                                       while ($row = mysqli_fetch_assoc($result)) {
                                           $payable_amount = (float)$row['payable_amount'];
                                           $paid_amount = (float)$row['paid_amount'];
                                           $remaining = $payable_amount - $paid_amount;
                                        if ($remaining < 0) {
                                                  $remaining = 0;
                                                }
                                      
                                    if ($payable_amount <= 0 || $paid_amount >= $payable_amount) {
                                           $status = 'Paid';
                                      } elseif ($paid_amount > 0) {
                                        $status = 'Partial';
                                               } else {
                                            $status = 'Unpaid';
                                             }
                                     if (!empty($status_filter) && $status != $status_filter) {
                                            continue;
                                            }
                                        ?>

                                            <tr>



                                                <td>
                                                    <input
                                                         type="checkbox"
                                                         class="student-checkbox"
                                                         name="student_ids[]"
                                                         value="<?php echo $row['id']; ?>"
                                                         data-remaining="<?php echo $remaining; ?>"
                                                         <?php echo ($remaining <= 0) ? 'disabled' : ''; ?>>
                                                </td>

                                                <td>
                                                    <?php echo $count++; ?>
                                                </td>

                                                <td>
                                                    <?php echo htmlspecialchars($row['sformNo']); ?>
                                                </td>

                                                <td>
                                                    <?php echo htmlspecialchars($row['student_name']); ?>
                                                </td>

                                                <td>
                                                    <?php echo htmlspecialchars($row['stu_class']); ?>
                                                </td>

                                               <td>
                                                  Rs. <?php echo number_format($payable_amount,2); ?>
                                                </td>

                                                <td>
                                                    Rs. <?php echo number_format($paid_amount, 2); ?> </td>
                                                <td>
                                                    Rs. <?php echo number_format($remaining, 2); ?>
                                                </td>

                                                <td>

                                                    <?php if ($status === 'Paid') { ?>

                                                        <span class="badge bg-success">
                                                            Paid
                                                        </span>

                                                    <?php } elseif ($status === 'Partial') { ?>

                                                        <span class="badge bg-warning text-dark">
                                                            Partial
                                                        </span>

                                                    <?php } else { ?>

                                                        <span class="badge bg-danger">
                                                            Unpaid
                                                        </span>

                                                    <?php } ?>

                                                </td>
                                               <td>
    <a
        href="collect_fee.php?student_id=<?php echo $row['id']; ?>"
        class="btn btn-sm btn-primary">
        <i class="bi bi-cash me-1"></i>
        Collect
    </a>

    <?php
    $print_sql = "SELECT receipt_no FROM fee_payments
                  WHERE student_id = {$row['id']}
                  AND deleted_at IS NULL
                  AND receipt_no IS NOT NULL
                  AND receipt_no != ''
                  ORDER BY id DESC LIMIT 1";
    $print_result = mysqli_query($conn,$print_sql);
    $print_row = mysqli_fetch_assoc($print_result);
    ?>

    <?php if ($print_row): ?>
        <a
            href="fee_receipt.php?receipt=<?php echo urlencode($print_row['receipt_no']); ?>"
            target="_blank"
            class="btn btn-sm btn-secondary">
            <i class="bi bi-printer me-1"></i>
            Print
        </a>
    <?php endif; ?>
</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 </main>

            <div class="modal fade" id="collectFeeModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-cash-stack me-2"></i>
                                Collect Fee
                            </h5>
                            <button
                                type="button" class="btn-close" data-bs-dismiss="modal">
                            </button>
                        </div>
                        <form action="bulk_collect.php" method="POST" id="bulkFeeForm">
                            <div class="modal-body">
                                <input
                                    type="hidden"
                                    name="month"
                                    value="<?php echo $cutoff_month; ?>">

                                <input
                                    type="hidden"
                                    name="year"
                                    value="<?php echo $current_year; ?>">

                                <div class="alert alert-light border mb-3">
                                    <strong id="modalSelectedCount">0</strong>
                                    students selected.
                                </div>
                                     <div id="bulkStudentList" class="bulk-student-list mb-3">
                                     </div>
                                <div class="mb-3">
                                    <label class="form-label">   Payment Type  </label>
                                    <select
                                        id="paymentType"
                                        name="payment_type"
                                        class="form-select">
                                        <option value="full">
                                            Full Remaining Fee
                                        </option>

                                        <option value="partial">
                                            Partial Fee
                                        </option>

                                    </select>

                                </div>


                                <div class="mb-3" id="partialAmountBox">

                                    <label class="form-label">
                                        Amount Per Student
                                    </label>

                                    <input
                                        type="number"
                                        id="partialAmount"
                                        name="partial_amount"
                                        class="form-control"
                                        min="1"
                                        placeholder="Enter amount">

                                    <small class="text-muted">
                                        This amount will be collected from each selected student.
                                    </small>

                                </div>

                                <div class="alert alert-primary d-none mb-3" id="bulkTotalBox">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <span>
                                            <i class="bi bi-calculator me-1"></i>
                                            Total Collection
                                        </span>

                                        <strong id="bulkTotalAmount">
                                            Rs. 0.00
                                        </strong>

                                    </div>

                                </div>


                                <div class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Paid students will automatically be ignored.
                                </div>

                            </div>


                            <div class="modal-footer">

                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                    Cancel
                                </button>

                                 <?php if (!empty($_GET['receipt'])): ?>
        <a
            href="fee_receipt.php?receipt=<?php echo urlencode($_GET['receipt']); ?>"
            target="_blank"
            class="btn btn-secondary">
            <i class="bi bi-printer me-1"></i>
            Print Receipt
        </a>
    <?php else: ?>

                               
        <button
            type="submit"
            class="btn btn-success"
            id="confirmCollectBtn">
            <i class="bi bi-check-circle me-1"></i>
            Collect Fee
        </button>
    <?php endif; ?>

                            </div>
                        </form>
                    </div>

                </div>

            </div>







            <?php include '../layout/footer.php'; ?>



        </div>
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
        src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        crossorigin="anonymous"></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

            // Disable OverlayScrollbars on mobile devices to prevent touch interference
            const isMobile = window.innerWidth <= 992;

            if (
                sidebarWrapper &&
                OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
                !isMobile
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!--begin::Color Mode Toggle-->
    <!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
    <!--end::Color Mode Toggle-->

    <!-- OPTIONAL SCRIPTS -->

    <!-- sortablejs -->
    <script
        src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
        crossorigin="anonymous"></script>
    <!-- sortablejs -->
    <script>
        new Sortable(document.querySelector('.connectedSortable'), {
            group: 'shared',
            handle: '.card-header',
        });

        const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
        cardHeaders.forEach((cardHeader) => {
            cardHeader.style.cursor = 'move';
        });
    </script>



    <script>
        const selectAll = document.getElementById('selectAll');
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');
        const collectSelectedBtn = document.getElementById('collectSelectedBtn');
        const selectedCount = document.getElementById('selectedCount');
        const modalSelectedCount = document.getElementById('modalSelectedCount');
        const bulkStudentList = document.getElementById('bulkStudentList');
        const collectFeeModal = new bootstrap.Modal(
            document.getElementById('collectFeeModal')
        );


        // Select All
        selectAll.addEventListener('change', function() {
            const enabledCheckboxes =
                document.querySelectorAll('.student-checkbox:not(:disabled)');
            enabledCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAll.checked;
            });

            updateSelectedCount();

        });


        // Individual checkbox
        studentCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                const enabledCheckboxes =
                    document.querySelectorAll('.student-checkbox:not(:disabled)');
                const checkedCheckboxes =
                    document.querySelectorAll('.student-checkbox:not(:disabled):checked');
                selectAll.checked =
                    enabledCheckboxes.length > 0 &&
                    enabledCheckboxes.length === checkedCheckboxes.length;

            });

        });


        // Update selected count
        function updateSelectedCount() {

            const checkedBoxes =
                document.querySelectorAll('.student-checkbox:checked');

            const count = checkedBoxes.length;
            selectedCount.textContent = count;
            modalSelectedCount.textContent = count;
            updateBulkStudentList();
            updateBulkPayment();
            if (count > 0) {
                collectSelectedBtn.disabled = false;
            } else {
                collectSelectedBtn.disabled = true;
            }
        }
        collectSelectedBtn.addEventListener('click', function() {
            const checkedBoxes =
                document.querySelectorAll('.student-checkbox:checked');
            if (checkedBoxes.length === 0) {
                return;
            }

            // Remove old hidden student IDs
            document
                .querySelectorAll('.bulk-student-id')
                .forEach(function(input) {
                    input.remove();
                });

            // Add selected student IDs
            checkedBoxes.forEach(function(checkbox) {

                const hiddenInput = document.createElement('input');

                hiddenInput.type = 'hidden';
                hiddenInput.name = 'student_ids[]';
                hiddenInput.value = checkbox.value;
                hiddenInput.classList.add('bulk-student-id');

                document
                    .getElementById('bulkFeeForm')
                    .appendChild(hiddenInput);

            });

            modalSelectedCount.textContent = checkedBoxes.length;
            updateBulkStudentList();
             updateBulkPayment();
            collectFeeModal.show();

        });
    </script>



    <script>
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.get('payment') === 'success') {

            const count = urlParams.get('count') || 1;

            document.getElementById('paymentToastMessage').textContent =
                count + ' student' +
                (count > 1 ? 's' : '') +
                ' fee collected successfully!';

            const paymentToast =
                document.getElementById('paymentToast');

            const toast =
                new bootstrap.Toast(paymentToast, {
                    delay: 4000
                });

            toast.show();

            window.history.replaceState({},
                document.title,
                window.location.pathname +
                '?month=' + urlParams.get('month') +
                '&year=' + urlParams.get('year')
            );
        }
    </script>

<script>

const paymentType = document.getElementById('paymentType');
const partialAmountBox = document.getElementById('partialAmountBox');
const partialAmount = document.getElementById('partialAmount');

const bulkTotalBox = document.getElementById('bulkTotalBox');
const bulkTotalAmount = document.getElementById('bulkTotalAmount');


function updateBulkStudentList() {
    const checkedBoxes =
        document.querySelectorAll('.student-checkbox:checked');
    bulkStudentList.innerHTML = '';
    checkedBoxes.forEach(function(checkbox) {
        const row = checkbox.closest('tr');
        const studentName = row.children[3].textContent.trim();
        const studentClass = row.children[4].textContent.trim();
        const remaining = parseFloat(checkbox.dataset.remaining) || 0;
        const studentRow = document.createElement('div');
        studentRow.className =
            'd-flex justify-content-between align-items-center border-bottom py-2';

        studentRow.innerHTML = `
            <div>
                <strong>${studentName}</strong>
                <small class="text-muted d-block">${studentClass}</small>
            </div>
            <strong>
                Rs. ${remaining.toLocaleString('en-PK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}
            </strong>
        `;

        bulkStudentList.appendChild(studentRow);
    });
}


function updateBulkPayment() {

    const count =
        parseInt(modalSelectedCount.textContent) || 0;

    const amount =
        parseFloat(partialAmount.value) || 0;

    const checkedBoxes =
        document.querySelectorAll('.student-checkbox:checked');

    let totalRemaining = 0;

    checkedBoxes.forEach(function(checkbox) {
        totalRemaining +=
            parseFloat(checkbox.dataset.remaining) || 0;
    });

    // Partial Fee
    if (paymentType.value === 'partial') {

        partialAmountBox.classList.remove('d-none');

        if (count > 0 && amount > 0) {

            let total = 0;

            checkedBoxes.forEach(function(checkbox) {

                const remaining =
                    parseFloat(checkbox.dataset.remaining) || 0;

                total += Math.min(amount, remaining);
            });

            bulkTotalBox.classList.remove('d-none');

            bulkTotalAmount.textContent =
                'Rs. ' +
                total.toLocaleString('en-PK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

        } else {

            bulkTotalBox.classList.add('d-none');
        }

    }

    // Full Remaining Fee
    else {

        partialAmountBox.classList.add('d-none');

        partialAmount.value = '';

        if (count > 0 && totalRemaining > 0) {

            bulkTotalBox.classList.remove('d-none');

            bulkTotalAmount.textContent =
                'Rs. ' +
                totalRemaining.toLocaleString('en-PK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

        } else {

            bulkTotalBox.classList.add('d-none');
        }
    }
}
// Payment Type change
paymentType.addEventListener('change', function() {
    updateBulkPayment();

});


// Amount change
partialAmount.addEventListener('input', function() {
    updateBulkPayment();

});
// Modal open

document.getElementById('collectFeeModal')
    .addEventListener('shown.bs.modal', function() {
        updateBulkPayment();
    });

</script>


<?php if (!empty($_GET['receipt'])): ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const modal = document.getElementById('collectFeeModal');
    if (modal) {
        const bulkModal = new bootstrap.Modal(modal);
        bulkModal.show();
    }
});
</script>
<?php endif; ?>
</body>
<!--end::Body-->

</html>