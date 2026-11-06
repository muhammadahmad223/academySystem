<?php

include '../config/db_config.php';
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$student_id = (int)$_GET['id'];
$sql = "SELECT * FROM students WHERE id = $student_id AND deleted_at = 0";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit;
}
$student = mysqli_fetch_assoc($result);
$attendance_sql = "SELECT
                    COUNT(*) AS total_attendance,
                    SUM(status = 'Present') AS present_count,
                    SUM(status = 'Absent') AS absent_count
                   FROM attendance
                   WHERE student_id = $student_id";

$attendance_result = mysqli_query($conn, $attendance_sql);
$attendance = mysqli_fetch_assoc($attendance_result);

$total_attendance = $attendance['total_attendance'] ?? 0;
$present_count = $attendance['present_count'] ?? 0;
$absent_count = $attendance['absent_count'] ?? 0;

$admission_date = (int)$student['admission_date'];

$admission_month = (int)date('n', $admission_date);
$admission_year = (int)date('Y', $admission_date);

$current_month = (int)date('n');
$current_year = (int)date('Y');

$months = (($current_year - $admission_year) * 12)
        + ($current_month - $admission_month) + 1;

if ($months < 1) {
    $months = 1;
}

$total_fee = $student['monthly_fee'] * $months;

$paid_sql = "SELECT SUM(amount) AS total_paid
             FROM fee_payments
             WHERE student_id = $student_id";

$paid_result = mysqli_query($conn, $paid_sql);
$paid_row = mysqli_fetch_assoc($paid_result);

$total_paid = $paid_row['total_paid'] ?? 0;

$remaining_fee = $total_fee - $total_paid;

if ($remaining_fee < 0) {
    $remaining_fee = 0;
}
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
    <link rel="stylesheet" href="../dist/css/adminlte.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

   



</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <?php include '../layout/sidebar.php'; ?>
        <div class="layout-page">
            <?php include '../layout/header.php'; ?>

                          <div class="app-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-2">

            <div>
                <h3 class="mb-0">Student Profile</h3>

                <div class="small text-muted mt-1">
                    Students / View Student
                </div>
            </div>

            <div class="d-flex gap-2">

                <a
                    href="edit.php?id=<?php echo $student['id']; ?>"
                    class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>

                <a
                    href="../fees/collect_fee.php?student_id=<?php echo $student['id']; ?>"
                    class="btn btn-sm btn-success">
                    <i class="bi bi-cash-coin me-1"></i>
                    Collect Fee
                </a>

                <a
                    href="index.php"
                    class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back
                </a>

            </div>

        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-3 d-flex align-items-center">
                        <?php if (!empty($student['student_img'])) { ?>
                            <img
                                src="../uploads/students/<?php echo htmlspecialchars($student['student_img']); ?>"
                                width="75" height="75" class="rounded-circle shadow-sm me-3" style="object-fit: cover;"
                                alt="Student">
                        <?php } else { ?>
                            <img src="../uploads/students/default.png" width="75" height="75" class="rounded-circle shadow-sm me-3"
                                style="object-fit: cover;" alt="Student">
                        <?php } ?>
                        <div>
                            <h4 class="mb-1">
                                <?php echo htmlspecialchars($student['student_name']); ?>
                            </h4>
                            <span class="badge text-bg-primary">
                                <?php echo htmlspecialchars($student['sformNo']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="student-info">
                                    <small>Class</small>
                                    <strong>
                                        <?php echo htmlspecialchars($student['stu_class']); ?>
                                    </strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="student-info">
                                    <small>Section</small>
                                    <strong>
                                        <?php echo !empty($student['stu_section'])
                                            ? htmlspecialchars($student['stu_section'])
                                            : '-'; ?>
                                    </strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="student-info">
                                    <small>Gender</small>
                                    <strong>
                                        <?php echo htmlspecialchars($student['stu_gender']); ?>
                                    </strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="student-info">
                                    <small>Father Name</small>
                                    <strong>
                                        <?php echo htmlspecialchars($student['father_name']); ?>
                                    </strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="student-info">
                                    <small>Father Contact</small>
                                    <strong>
                                        <?php echo htmlspecialchars($student['father_contact']); ?>
                                    </strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="student-info">
                                    <small>Monthly Fee</small>
                                    <strong>
                                        Rs. <?php echo number_format($student['monthly_fee']); ?>
                                    </strong>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="row g-2 mb-3">

    <div class="col-md-4">
        <div class="card shadow-sm mb-0">
            <div class="card-body py-2 px-3">
                <small class="text-muted">Total Fee</small>
                <h4 class="mb-0">
                    Rs. <?php echo number_format($total_fee); ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm mb-0">
            <div class="card-body py-2 px-3">
                <small class="text-muted">Paid</small>
                <h4 class="mb-0 text-success">
                    Rs. <?php echo number_format($total_paid); ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm mb-0">
            <div class="card-body py-2 px-3">
                <small class="text-muted">Remaining</small>
                <h4 class="mb-0 text-danger">
                    Rs. <?php echo number_format($remaining_fee); ?>
                </h4>
            </div>
        </div>
    </div>

</div>

        <div class="row g-2 mb-3">

            <div class="col-md-4">
                <div class="card shadow-sm mb-0">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Total Attendance</small>
                            <h4 class="mb-0"><?php echo $total_attendance; ?></h4>
                        </div>

                        <i class="bi bi-calendar-check fs-3 text-primary"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-0">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Present</small>
                            <h4 class="mb-0"><?php echo $present_count; ?></h4>
                        </div>

                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-0">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Absent</small>
                            <h4 class="mb-0"><?php echo $absent_count; ?></h4>
                        </div>

                        <i class="bi bi-x-circle fs-3 text-danger"></i>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow-sm">
            <div class="card-body py-3">
                <div class="row g-2">
                   <div class="col-md-4">
                          <small class="text-muted d-block">Admission Date</small>
                           <strong>
                              <?php echo date('d M Y', (int)$student['admission_date']); ?>
                          </strong>
                     </div>
                           <div class="col-md-4">
                              <small class="text-muted d-block">Date of Birth</small>
                           <strong><?php echo date('d M Y', (int)$student['stu_dob']); ?></strong>
                                </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">B-Form</small>
                        <strong><?php echo htmlspecialchars($student['stu_bform']); ?></strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Religion</small>
                        <strong><?php echo htmlspecialchars($student['stu_religion']); ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">City</small>
                        <strong><?php echo htmlspecialchars($student['stu_city']); ?></strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Admission Fee</small>
                        <strong>Rs. <?php echo number_format($student['admission_fee']); ?></strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Admission Year</small>
                        <strong><?php echo htmlspecialchars($student['admission_year']); ?></strong>
                    </div>

                    <div class="col-8">
                        <small class="text-muted d-block">Address</small>
                        <strong><?php echo htmlspecialchars($student['stu_address']); ?></strong>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
                    

                     
            
            <?php include '../layout/footer.php'; ?>
        </div>
    </div>

    <!--end::App Wrapper-->
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
   
 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
   <script>
    $(document).ready(function() {
        $('#subjectSelect').select2({
            placeholder: "Search and select subjects",
            width: '100%',
            closeOnSelect: false
        });
    });
</script>
   










</body>
</html>