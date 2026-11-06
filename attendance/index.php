
<?php
include '../config/db_config.php';
    $classes = ['Play Group', 'Nursery', 'Prep', 'One', 'Two', 'Three','Four', 'Five', 'Six', 'Seven', 
                  'Eight', 'Nine', 'Ten','Eleven', 'Twelve' ];
    $selected_class = $_GET['class'] ?? '';
    $selected_section = $_GET['section'] ?? '';
    $attendance_date = $_GET['date'] ?? date('Y-m-d');
    $students = [];
        if (!empty($selected_class)) { $sql = "SELECT students.id, students.sformNo, students.student_name, students.stu_class, students.stu_section,
            attendance.status AS attendance_status FROM students LEFT JOIN attendance ON students.id = attendance.student_id
            AND attendance.attendance_date = '$attendance_date' WHERE students.deleted_at = 0
            AND students.stu_class = '$selected_class'";
            if (!empty($selected_section)) {
                 $sql .= " AND students.stu_section = '$selected_section'";
                }
            $sql .= " ORDER BY students.student_name ASC";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $students[] = $row;
            }
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
                        <div class="app-content">
                            <div class="container-fluid">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                               <h3 class="mb-1">Student Attendance</h3>
                                               <p class="text-muted mb-0">Mark Daily Student Attendance</p>
                                        </div>
                                     <div class="col-sm-6">
                                    <nav aria-label="breadcrumb">
                                       <ol class="breadcrumb float-sm-end">
                                           <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                                           <li class="breadcrumb-item"><a href="../student/index.php">Students</a></li>
                                            <li class="breadcrumb-item"><a href="../fees/fee_collection.php">Fees</a></li>
                                           <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                                        </ol>
                                   </nav>
                                </div>
                               </div>
                     <div class="card shadow-sm border-0 mb-4">
                         <div class="card-body">
                           <form method="GET">
                              <div class="row g-3 align-items-end">
                                 <div class="col-md-4">
                                    <label class="form-label fw-semibold">Date</label>
                                    <input type="date" name="date" class="form-control"
                                     value="<?php echo htmlspecialchars($attendance_date); ?>"
                                     required>
                                 </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Class</label>
                            <select name="class" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class) { ?>
                                    <option
                                        value="<?php echo htmlspecialchars($class); ?>"
                                        <?php echo ($selected_class == $class) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Section <span class="text-muted">(Optional)</span>
                            </label>
                            <select name="section" class="form-select">
                                <option value="">All Sections</option>
                                <option value="Red" <?php echo ($selected_section == 'Red') ? 'selected' : ''; ?>>Red</option>
                                <option value="Blue" <?php echo ($selected_section == 'Blue') ? 'selected' : ''; ?>>Blue</option>
                                <option value="Green" <?php echo ($selected_section == 'Green') ? 'selected' : ''; ?>>Green</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-people me-1"></i>
                                Load Students
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php if (!empty($selected_class)) { ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <?php echo htmlspecialchars($selected_class); ?> Students
                        </h5>
                        <small class="text-muted">
                            <?php echo date('d-m-Y', strtotime($attendance_date)); ?>
                            <?php if (!empty($selected_section)) { ?>
                                · Section <?php echo htmlspecialchars($selected_section); ?>
                            <?php } ?>
                        </small>
                   </div>
                    <span class="badge text-bg-primary">
                        <?php echo count($students); ?> Students
                    </span>
                </div>
                <div class="card-body p-0">
                <div class="p-3 border-bottom">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded p-3">
                <small class="text-muted">Total Students</small>
                <h4 class="mb-0" id="totalStudents"><?php echo count($students); ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3">
                <small class="text-muted">Present</small>
                <h4 class="mb-0 text-success" id="presentCount"><?php echo count($students); ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3">
                <small class="text-muted">Absent</small>
                <h4 class="mb-0 text-danger" id="absentCount">0</h4>
            </div>
        </div>
    </div>
    <div class=" d-flex justify-content-between align-items-center mt-3">
       
    <input type="text" id="studentSearch" class="form-control" style="max-width: 300px;"
            placeholder="Search student...">
            
            
            <select id="attendanceFilter" class="form-select" style="max-width: 160px;">
                  <option value="All">All Students</option>
                 <option value="Present">Present</option>
                 <option value="Absent">Absent</option>
                    </select>
                    
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" id="markAllPresent">
                <i class="bi bi-check-circle me-1"></i>
                Mark All Present
            </button>
            <button type="button" class="btn btn-outline-danger" id="markAllAbsent">
                <i class="bi bi-x-circle me-1"></i>
                Mark All Absent
            </button>
        </div>
    </div>
</div>

                    <?php if (!empty($students)) { ?>
                        <div class="table-responsive">
                            <form method="POST" action="saveattendance.php" id="attendanceForm">
                              <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($attendance_date); ?>">
                              <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($selected_class); ?>">
                              <input type="hidden" name="section" value="<?php echo htmlspecialchars($selected_section); ?>">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Form No</th>
                                        <th>Student Name</th>
                                        <th>Section</th>
                                        <th class="text-center">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    foreach ($students as $student) {
                                    ?>
                                        <tr class="student-row">
                                            <td><?php echo $count++; ?></td>
                                            <td>
                                                <span class="fw-semibold">
                                                    <?php echo htmlspecialchars($student['sformNo']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($student['student_name']); ?>
                                            </td>
                                            <td>
                                                <?php echo !empty($student['stu_section'])
                                                    ? htmlspecialchars($student['stu_section'])
                                                    : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td class="text-center">
                                             <input type="hidden" value="Present"
                                                name="status[<?php echo $student['id']; ?>]"
                                                 class="attendance-status">
                                          <?php
                                         $status = !empty($student['attendance_status'])
                                         ? $student['attendance_status']
                                            : 'Present';

                                if ($status === 'Absent') {
                                      $button_class = 'btn-danger';
                                       $button_icon = 'bi-x-circle';
                               } else {
                                    $button_class = 'btn-success';
                                    $button_icon = 'bi-check-circle';
                               }
                                ?>

                                   <td class="text-center">
                                              <input
                                                  type="hidden"
                                                  name="status[<?php echo $student['id']; ?>]"
                                               value="<?php echo $status; ?>"
                                               class="attendance-status">

                                                 <button
                                                     type="button"
                                                     class="btn btn-sm <?php echo $button_class; ?> attendance-btn"
                                                     data-status="<?php echo $status; ?>">
                                                     <i class="bi <?php echo $button_icon; ?> me-1"></i>
                                                     <?php echo $status; ?>
                                             </button>
                                            </td>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="p-3 border-top text-center">
                            <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                              Save Attendance
                            </button>
                              </div>
                            </form>
                        </div>
                    <?php } else { ?>
                        <div class="text-center py-5">
                            <i class="bi bi-person-x fs-1 text-muted"></i>
                            <h5 class="mt-3">No Students Found</h5>
                            <p class="text-muted mb-0">
                                No students are available for the selected class.
                            </p>
                       </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</main>
</div>
</div>       
             <div class="toast-container position-fixed top-0 end-0 p-3">
               <div id="attendanceToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="d-flex">
                     <div class="toast-body">
                          <i class="bi bi-check-circle me-2"></i>
                              <span id="attendanceToastMessage">
                                  Attendance saved successfully!
                              </span>
                    </div>

            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast">
           </button>
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
    
    




<script>
    document.addEventListener('DOMContentLoaded', function () {
    const attendanceButtons = document.querySelectorAll('.attendance-btn');
    const presentCount = document.getElementById('presentCount');
    const absentCount = document.getElementById('absentCount');
    const studentSearch = document.getElementById('studentSearch');
    const markAllPresent = document.getElementById('markAllPresent');
    const markAllAbsent = document.getElementById('markAllAbsent');
    function updateCounts() {
        let present = 0;
        let absent = 0;
        attendanceButtons.forEach(function (button) {
            if (button.dataset.status === 'Present') {
                present++;
            } else {
                absent++;
            }
        });
        presentCount.textContent = present;
        absentCount.textContent = absent;
    }
    attendanceButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.dataset.status === 'Present') {
                button.dataset.status = 'Absent';
                button.classList.remove('btn-success');
                button.classList.add('btn-danger');
                button.innerHTML = '<i class="bi bi-x-circle me-1"></i> Absent';
            } else {
                button.dataset.status = 'Present';
                button.classList.remove('btn-danger');
                button.classList.add('btn-success');
                button.innerHTML = '<i class="bi bi-check-circle me-1"></i> Present';
            }
              const statusInput = button.closest('td').querySelector('.attendance-status');
              statusInput.value = button.dataset.status;
              console.log(statusInput.value);
            updateCounts();
        });
    });

   markAllPresent.addEventListener('click', function () {
    attendanceButtons.forEach(function (button) {
        button.dataset.status = 'Present';
        button.classList.remove('btn-danger');
        button.classList.add('btn-success');
        button.innerHTML = '<i class="bi bi-check-circle me-1"></i> Present';
        const statusInput = button.closest('td').querySelector('.attendance-status');
        statusInput.value = 'Present';
    });
    updateCounts();
    filterStudents();
});
   markAllAbsent.addEventListener('click', function () {
    if (!confirm('Are you sure you want to mark all students absent?')) {
        return;
    }
    attendanceButtons.forEach(function (button) {
        button.dataset.status = 'Absent';
        button.classList.remove('btn-success');
        button.classList.add('btn-danger');
        button.innerHTML = '<i class="bi bi-x-circle me-1"></i> Absent';
        const statusInput = button.closest('td').querySelector('.attendance-status');
        statusInput.value = 'Absent';
    });
    updateCounts();
    filterStudents();
});
   const attendanceFilter = document.getElementById('attendanceFilter');
    function filterStudents() {
    const searchValue = studentSearch.value.toLowerCase();
    const statusValue = attendanceFilter.value;
    document.querySelectorAll('.student-row').forEach(function (row) {
        const studentName = row.cells[2].textContent.toLowerCase();
        const formNo = row.cells[1].textContent.toLowerCase();
        const button = row.querySelector('.attendance-btn');
        const studentStatus = button.dataset.status;
        const matchesSearch =
            studentName.includes(searchValue) ||
            formNo.includes(searchValue);
        const matchesStatus =
            statusValue === 'All' ||
            studentStatus === statusValue;
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }

    });
}

studentSearch.addEventListener('keyup', filterStudents);
attendanceFilter.addEventListener('change', filterStudents);
    });
</script>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('attendance') === 'success') {
        const attendanceToast =   document.getElementById('attendanceToast');
        const toast =  new bootstrap.Toast(attendanceToast, {
                delay: 4000
            });
        toast.show();
        window.history.replaceState({},
            document.title,
            window.location.pathname +
            '?class=' + urlParams.get('class') +
            '&section=' + urlParams.get('section') +
            '&date=' + urlParams.get('date')
        );
    }
</script>


</body>
</html>