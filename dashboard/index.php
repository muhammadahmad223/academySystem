<?php
include '../config/db_config.php';

$today = date('Y-m-d');
$current_month = date('n');
$current_year = date('Y');

$students = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM students WHERE deleted_at=0"));
$total_students = $students['total'];

$teachers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM teachers WHERE deleted_at=0"));
$total_teachers = $teachers['total'];

$present = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM attendance WHERE attendance_date='$today' AND status='Present'"));
$today_present = $present['total'];

$absent = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM attendance WHERE attendance_date='$today' AND status='Absent'"));
$today_absent = $absent['total'];

$total_attendance = $today_present + $today_absent;
$attendance_percentage = $total_attendance > 0 ? round(($today_present / $total_attendance) * 100) : 0;

$fee_collection = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(amount),0) AS total FROM fee_payments WHERE fee_month='$current_month' AND fee_year='$current_year' AND deleted_at IS NULL"));
$monthly_collection = $fee_collection['total'];
$paid_students = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT student_id) AS total FROM fee_payments WHERE fee_month='$current_month' AND fee_year='$current_year' AND status='Paid' AND deleted_at IS NULL"));
$paid_students = $paid_students['total'];

$partial_students = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT student_id) AS total FROM fee_payments WHERE fee_month='$current_month' AND fee_year='$current_year' AND status='Partial' AND deleted_at IS NULL"));
$partial_students = $partial_students['total'];

$recent_students = mysqli_query($conn,"SELECT sformNo, student_name, stu_class, admission_date FROM students WHERE deleted_at=0 ORDER BY sformNO DESC LIMIT 5");
$late_fees = mysqli_query($conn,"SELECT p.id,p.student_id,p.fee_month,p.fee_year,p.amount,p.payable_amount,p.discount_amount,s.student_name,s.sformNo,s.stu_class,COALESCE(SUM(fp.amount),0) AS paid_amount,(p.payable_amount-COALESCE(SUM(fp.amount),0)) AS remaining FROM fee_payables p INNER JOIN students s ON s.id=p.student_id LEFT JOIN fee_payments fp ON fp.fee_payable_id=p.id AND fp.deleted_at IS NULL WHERE p.deleted_at IS NULL GROUP BY p.id,p.student_id,p.fee_month,p.fee_year,p.amount,p.payable_amount,p.discount_amount,s.student_name,s.sformNo,s.stu_class HAVING remaining > 0 ORDER BY p.fee_year ASC,p.fee_month ASC LIMIT 5");
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
  <title>Unique Science Academy</title>

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
  <link rel="stylesheet" href="../dist/css/custom.css">
  <!--end::Required Plugin(AdminLTE)-->

  <!-- apexcharts -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
    crossorigin="anonymous" />
  <!-- jsvectormap -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
    integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
    crossorigin="anonymous" />
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
  <main class="app-main">
    <div class="app-content-header">
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-6">
            <h1 class="mb-0 fs-3">Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="../dashboard/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="app-content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box text-bg-primary">
              <div class="inner">
                <h3><?= $total_students ?></h3>
                <p>Total Students</p>
              </div>
              <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0H5z"></path>
              </svg>
              <a href="../student/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                View Students <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box text-bg-success">
              <div class="inner">
                <h3><?= $total_teachers ?></h3>
                <p>Total Teachers</p>
              </div>
              <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0H5z"></path>
              </svg>
              <a href="../teachers/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                View Teachers <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box text-bg-warning">
              <div class="inner">
                <h3><?= $today_present ?></h3>
                <p>Present Today</p>
              </div>
              <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <a href="../attendance/index.php" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                Today's Attendance <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box text-bg-danger">
              <div class="inner">
                <h3><?= $today_absent ?></h3>
                <p>Absent Today</p>
              </div>
              <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A2 2 0 004.5 20h15a2 2 0 001.71-3.14l-7.5-13a2 2 0 00-3.42 0z"></path>
              </svg>
              <a href="../attendance/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                View Attendance <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">Today's Attendance</h3>
                <div class="card-tools">
                  <a href="../attendance/" class="btn btn-sm btn-primary">View Attendance</a>
                </div>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-4">
                    <h3 class="mb-1"><?= $today_present ?></h3>
                    <small class="text-success">Present</small>
                  </div>
                  <div class="col-4">
                    <h3 class="mb-1"><?= $today_absent ?></h3>
                    <small class="text-danger">Absent</small>
                  </div>
                  <div class="col-4">
                    <h3 class="mb-1"><?= $attendance_percentage ?>%</h3>
                    <small class="text-muted">Attendance</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">This Month Fee Collection</h3>
                <div class="card-tools">
                  <a href="../fees/fee_collection.php" class="btn btn-sm btn-primary">Collect Fee</a>
                </div>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-4">
                    <h3 class="mb-1">Rs. <?= number_format($monthly_collection) ?></h3>
                    <small class="text-success">Collected</small>
                  </div>
                  <div class="col-4">
                    <h3 class="mb-1"><?= $paid_students ?></h3>
                    <small class="text-success">Paid</small>
                  </div>
                  <div class="col-4">
                    <h3 class="mb-1"><?= $partial_students ?></h3>
                    <small class="text-warning">Partial</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">Recent Students</h3>
                <div class="card-tools">
                  <a href="../student/index.php" class="btn btn-sm btn-primary">View All</a>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Form No</th>
                        <th>Student</th>
                        <th>Class</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $i = 1;
                      if(mysqli_num_rows($recent_students) > 0){
                          while($student = mysqli_fetch_assoc($recent_students)){
                      ?>
                      <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($student['sformNo']) ?></td>
                        <td><?= htmlspecialchars($student['student_name']) ?></td>
                        <td><?= htmlspecialchars($student['stu_class']) ?></td>
                      </tr>
                      <?php
                          }
                      }else{
                      ?>
                      <tr>
                        <td colspan="4" class="text-center text-muted py-3">No students found.</td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">Late Fee Payments</h3>
                <div class="card-tools">
                  <a href="../fees/fee_collection.php" class="btn btn-sm btn-primary">View All</a>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Month</th>
                        <th>Remaining</th>
                      </tr>
                    </thead>
                    <tbody>
                     <?php
$i = 1;
if(mysqli_num_rows($late_fees) > 0){
    while($fee = mysqli_fetch_assoc($late_fees)){
?>
<tr>
  <td><?= $i++ ?></td>
  <td>
    <?= htmlspecialchars($fee['student_name']) ?>
    <small class="d-block text-muted"><?= htmlspecialchars($fee['sformNo']) ?> - <?= htmlspecialchars($fee['stu_class']) ?></small>
  </td>
  <td><?= date('F',mktime(0,0,0,$fee['fee_month'],1)) ?> <?= $fee['fee_year'] ?></td>
  <td>Rs. <?= number_format($fee['remaining']) ?></td>
</tr>
<?php
    }
}else{
?>
<tr>
  <td colspan="4" class="text-center text-muted py-3">No late fees found.</td>
</tr>
<?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
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
    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"></script>
    <!-- ChartJS -->
    <script>
      // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
      // IT'S ALL JUST JUNK FOR DEMO
      // ++++++++++++++++++++++++++++++++++++++++++

      const sales_chart_options = {
        series: [{
            name: 'Digital Goods',
            data: [28, 48, 40, 19, 86, 27, 90],
          },
          {
            name: 'Electronics',
            data: [65, 59, 80, 81, 56, 55, 40],
          },
        ],
        chart: {
          height: 300,
          type: 'area',
          toolbar: {
            show: false,
          },
        },
        legend: {
          show: false,
        },
        colors: ['#0d6efd', '#20c997'],
        dataLabels: {
          enabled: false,
        },
        stroke: {
          curve: 'smooth',
        },
        xaxis: {
          type: 'datetime',
          categories: [
            '2023-01-01',
            '2023-02-01',
            '2023-03-01',
            '2023-04-01',
            '2023-05-01',
            '2023-06-01',
            '2023-07-01',
          ],
        },
        tooltip: {
          x: {
            format: 'MMMM yyyy',
          },
        },
      };

      const sales_chart = new ApexCharts(
        document.querySelector('#revenue-chart'),
        sales_chart_options,
      );
      sales_chart.render();
    </script>
    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"></script>
    <!-- jsvectormap -->
    <script>
      // World map by jsVectorMap
      new jsVectorMap({
        selector: '#world-map',
        map: 'world',
      });

      // Sparkline charts
      const option_sparkline1 = {
        series: [{
          data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
        }, ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
      sparkline1.render();

      const option_sparkline2 = {
        series: [{
          data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
        }, ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
      sparkline2.render();

      const option_sparkline3 = {
        series: [{
          data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
        }, ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
      sparkline3.render();
    </script>
    <!--end::Script-->
</body>
<!--end::Body-->

</html>