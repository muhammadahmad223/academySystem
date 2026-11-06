<?php

include '../config/db_config.php';
if (!isset($_GET['student_id'])) {
    header("Location: ../student/index.php");
    exit;
}
$student_id = (int)$_GET['student_id'];
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$selected_status = $_GET['status'] ?? '';
// Student information
$student_sql = "SELECT id, sformNo, student_name, stu_class, stu_section, student_img
                FROM students WHERE id = $student_id AND deleted_at = 0";
$student_result = mysqli_query($conn, $student_sql);
if (!$student_result || mysqli_num_rows($student_result) == 0) {
    header("Location: ../student/index.php");
    exit;
}
$student = mysqli_fetch_assoc($student_result);
// Attendance history
$attendance_sql = "SELECT attendance_date, status FROM attendance WHERE student_id = $student_id";
                if (!empty($from_date)) {
                    $attendance_sql .= " AND attendance_date >= '" . mysqli_real_escape_string($conn, $from_date) . "'";
                    }
                 if (!empty($to_date)) {
                    $attendance_sql .= " AND attendance_date <= '" . mysqli_real_escape_string($conn, $to_date) . "'";
                     }
                 if (!empty($selected_status)) {
                    $attendance_sql .= " AND status = '" . mysqli_real_escape_string($conn, $selected_status) . "'";
                    }

$attendance_sql .= " ORDER BY attendance_date DESC";
$attendance_result = mysqli_query($conn, $attendance_sql);
// Attendance summary
$summary_sql = "SELECT COUNT(*) AS total_days, SUM(status = 'Present') AS present_days, SUM(status = 'Absent') AS absent_days
                FROM attendance WHERE student_id = $student_id";           
                 if (!empty($from_date)) {
                     $summary_sql .= " AND attendance_date >= '" . mysqli_real_escape_string($conn, $from_date) . "'";
                 }
                 if (!empty($to_date)) {
                     $summary_sql .= " AND attendance_date <= '" . mysqli_real_escape_string($conn, $to_date) . "'";
                 }
                 if (!empty($selected_status)) {
                     $summary_sql .= " AND status = '" . mysqli_real_escape_string($conn, $selected_status) . "'";
                 }
$summary_result = mysqli_query($conn, $summary_sql);
$summary = mysqli_fetch_assoc($summary_result);
$total_days = $summary['total_days'] ?? 0;
$present_days = $summary['present_days'] ?? 0;
$absent_days = $summary['absent_days'] ?? 0;
$attendance_percentage = $total_days > 0
    ? round(($present_days / $total_days) * 100, 2)
    : 0;

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

</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <?php include '../layout/sidebar.php'; ?>
        <div class="layout-page">
            <?php include '../layout/header.php'; ?>

  <main class="app-main py-4">
<div class="app-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0">Attendance History</h3>
                <small class="text-muted">
                    <?php echo htmlspecialchars($student['student_name']); ?>
                    — <?php echo htmlspecialchars($student['sformNo']); ?>
                </small>
            </div>

            <a href="../student/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>
        </div>
        <!-- Student Information -->
        <div class="card mb-3">
            <div class="card-body">

                <div class="d-flex align-items-center">

                    <img
                        src="../uploads/students/<?php echo htmlspecialchars($student['student_img']); ?>"
                        alt="Student"
                        width="55"
                        height="55"
                        class="rounded-circle me-3 object-fit-cover">

                    <div>
                        <h5 class="mb-1">
                            <?php echo htmlspecialchars($student['student_name']); ?>
                        </h5>

                        <div class="text-muted small">
                            Form No:
                            <strong><?php echo htmlspecialchars($student['sformNo']); ?></strong>
                            &nbsp; | &nbsp;

                            Class:
                            <strong><?php echo htmlspecialchars($student['stu_class']); ?></strong>
                            &nbsp; | &nbsp;

                            Section:
                            <strong><?php echo htmlspecialchars($student['stu_section'] ?: '-'); ?></strong>
                        </div>
                    </div>

                </div>

            </div>
        </div>

               <div class="row g-3 mb-3">

    <div class="col-md-3">
        <div class="card mb-0">
            <div class="card-body">
                <small class="text-muted">Total Days</small>
                <h4 class="mb-0">
                    <?php echo $total_days; ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mb-0">
            <div class="card-body">
                <small class="text-muted">Present</small>
                <h4 class="mb-0 text-success">
                    <?php echo $present_days; ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mb-0">
            <div class="card-body">
                <small class="text-muted">Absent</small>
                <h4 class="mb-0 text-danger">
                    <?php echo $absent_days; ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mb-0">
            <div class="card-body">
                <small class="text-muted">Attendance Rate</small>
                <h4 class="mb-0">
                    <?php echo $attendance_percentage; ?>%
                </h4>
            </div>
        </div>
    </div>

</div>

<div class="card mb-3">
    <div class="card-body">

        <form method="GET">

            <input
                type="hidden"
                name="student_id"
                value="<?php echo $student_id; ?>">

            <div class="row g-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input
                        type="date"
                        name="from_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($from_date ?? ''); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input
                        type="date"
                        name="to_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($to_date ?? ''); ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>

                        <option value="Present">
                            Present
                        </option>

                        <option value="Absent">
                            Absent
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>
                        Filter
                    </button>

                    <a
                        href="history.php?student_id=<?php echo $student_id; ?>"
                        class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <a href="attendance_history_pdf.php?<?php echo http_build_query($_GET); ?>"
                       target="_blank"
                       class="btn btn-danger ">
                      <i class="bi bi-file-earmark-pdf me-1"></i>
                         Export PDF
                      </a>

                </div>

            </div>

        </form>

    </div>
</div>

      
            <div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Attendance History
        </h3>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    if (mysqli_num_rows($attendance_result) > 0) {
                        while ($row = mysqli_fetch_assoc($attendance_result)) {
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td>
                            <?php echo date('d M Y', strtotime($row['attendance_date'])); ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'Present') { ?>

                                <span class="badge text-bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Present
                                </span>

                            <?php } else { ?>

                                <span class="badge text-bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Absent
                                </span>

                            <?php } ?>
                        </td>
                    </tr>

                    <?php
                        }

                    } else {
                    ?>

                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            No attendance record found.
                        </td>
                    </tr>

                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


</main>

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
    
    




</body>
</html>