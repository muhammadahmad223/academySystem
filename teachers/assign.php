<?php

include '../config/db_config.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$teacher_id = (int) $_GET['id'];
$teacher_sql = "SELECT id, tform_no, teacher_name, teacher_img FROM teachers WHERE id = $teacher_id AND deleted_at = 0 LIMIT 1";
$teacher_result = mysqli_query($conn, $teacher_sql);
if (!$teacher_result || mysqli_num_rows($teacher_result) == 0) {
    header("Location: index.php");
    exit;
}
$teacher = mysqli_fetch_assoc($teacher_result);

if (isset($_POST['remove_assignment'])) {
    $assignment_id = (int) ($_POST['assignment_id'] ?? 0);
    if ($assignment_id <= 0) {
        header("Location: assign.php?id=$teacher_id&remove=error");
        exit;
    }
    $delete_sql = "DELETE FROM teacher_assignments WHERE id = ? AND teacher_id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    if (!$stmt) {
        header("Location: assign.php?id=$teacher_id&remove=error");
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $teacher_id);
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        header("Location: assign.php?id=$teacher_id&remove=success");
        exit;
    }
    mysqli_stmt_close($stmt);
    header("Location: assign.php?id=$teacher_id&remove=error");
    exit;
}

if (isset($_POST['assign_subjects'])) {
    $class_name = trim($_POST['class_name'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $subject_ids = $_POST['subject_id'] ?? [];
    if ($class_name == '' || $section == '' || !is_array($subject_ids) || count($subject_ids) == 0) {
        header("Location: assign.php?id=$teacher_id&assign=error");
        exit;
    }
    $insert_sql = "INSERT IGNORE INTO teacher_assignments (teacher_id, class_name, section, subject_id)
                   VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_sql);
    if (!$stmt) {
        header("Location: assign.php?id=$teacher_id&assign=error");
        exit;
    }

    $assigned_count = 0;
    foreach ($subject_ids as $subject_id) {
        $subject_id = (int) $subject_id;
        if ($subject_id <= 0) {
            continue;
        }

        mysqli_stmt_bind_param($stmt, "issi", $teacher_id, $class_name, $section, $subject_id);
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $assigned_count++;
            }
        }
    }

    mysqli_stmt_close($stmt);

    if ($assigned_count > 0) {
        header("Location: assign.php?id=$teacher_id&assign=success");
    } else {
        header("Location: assign.php?id=$teacher_id&assign=exists");
    }
    exit;
}

$subject_sql = "SELECT id, subject_name FROM subjects ORDER BY subject_name ASC";
$subject_result = mysqli_query($conn, $subject_sql);

$assignment_sql = "SELECT ta.id, ta.class_name, ta.section, s.subject_name
                   FROM teacher_assignments ta
                   INNER JOIN subjects s ON s.id = ta.subject_id
                   WHERE ta.teacher_id = $teacher_id
                   ORDER BY ta.class_name ASC, ta.section ASC, s.subject_name ASC";
$assignment_result = mysqli_query($conn, $assignment_sql);

$classes = ['Play Group','Nursery','Prep','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve'];
$sections = ['Red','Green','Blue'];
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

                    <div class="content-wrapper">

    <main class="app-main py-4">

        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row align-items-center mb-4">

                <div class="col-md-8">

                    <h1 class="mb-1 fs-3">
                        Assign Class & Subject
                    </h1>

                    <p class="text-muted mb-0">
                        Manage classes and subjects assigned to this teacher.
                    </p>

                </div>

                <div class="col-md-4 text-md-end mt-2 mt-md-0">

                    <a href="index.php" class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-left me-1"></i>

                        Back to Teachers

                    </a>

                </div>

            </div>


            <!-- Teacher Information -->
            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <?php if (!empty($teacher['teacher_img'])) { ?>

                            <img
                                src="../uploads/teachers/<?php echo htmlspecialchars($teacher['teacher_img']); ?>"
                                width="60"
                                height="60"
                                class="rounded-circle me-3"
                                style="object-fit: cover;"
                                alt="Teacher">

                        <?php } else { ?>

                            <img
                                src="../dist/assets/img/defaultimage.jpg"
                                width="60"
                                height="60"
                                class="rounded-circle me-3"
                                style="object-fit: cover;"
                                alt="Teacher">

                        <?php } ?>


                        <div>

                            <h5 class="mb-1">
                                <?php echo htmlspecialchars($teacher['teacher_name']); ?>
                            </h5>

                            <div class="text-muted">
                                Teacher ID:
                                <span class="fw-semibold">
                                    <?php echo htmlspecialchars($teacher['tform_no']); ?>
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Assignment Form -->
            <div class="card shadow-sm mb-4">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>
                        Add Assignment
                    </h5>

                </div>


                <div class="card-body">

                    <form method="POST">

                        <div class="row g-3">


                            <!-- Class -->
                            <div class="col-md-4">

                                <label class="form-label fw-semibold">
                                    Class
                                </label>
                                <select   name="class_name"  class="form-select"  required>
                                    <option value="">   Select Class  </option>
                                    <?php foreach ($classes as $class) { ?>
                                        <option value="<?php echo htmlspecialchars($class); ?>">
                                            <?php echo htmlspecialchars($class); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>


                            <!-- Section -->
                            <div class="col-md-4">

                                <label class="form-label fw-semibold">
                                    Section
                                </label>

                                <select
                                    name="section"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Select Section
                                    </option>

                                    <?php foreach ($sections as $section) { ?>

                                        <option value="<?php echo htmlspecialchars($section); ?>">
                                            <?php echo htmlspecialchars($section); ?>
                                        </option>

                                    <?php } ?>

                                </select>

                            </div>


                   <div class="col-md-4">

    <label for="subjectSelect" class="form-label fw-semibold ">
        Subjects
    </label>

    <select
         name="subject_id[]" id="subjectSelect" class="form-select" multiple required>
        <?php while ($subject = mysqli_fetch_assoc($subject_result)) { ?>
            <option value="<?php echo $subject['id']; ?>">
                <?php echo htmlspecialchars($subject['subject_name']); ?>
            </option>
        <?php } ?>
    </select>
    <small class="text-muted">
        Select one or multiple subjects.
    </small>

</div>


                        </div>
                        <!-- Button -->
                        <div class="mt-4">
                            <button
                              type="submit" name="assign_subjects" class="btn btn-primary">
                              <i class="bi bi-plus-circle me-1"></i>
                              Assign Subjects
                            </button>
                        </div>

                    </form>

                </div>

            </div>


            <!-- Current Assignments -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>
                        Current Assignments
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Subject</th>
                                    <th class="text-center">Action </th>
                                </tr>
                            </thead>
                <tbody>
                    <?php if ($assignment_result && mysqli_num_rows($assignment_result) > 0) { ?>
                       <?php $count = 1; ?>
                            <?php while ($assignment = mysqli_fetch_assoc($assignment_result)) { ?>
                             <tr>
                                 <td><?php echo $count++; ?></td>
                                 <td>
                                      <span class="fw-semibold">
                                      <?php echo htmlspecialchars($assignment['class_name']); ?>
                                     </span>
                                </td>
                                <td>
                                       <span class="badge text-bg-light border">
                                       <?php echo htmlspecialchars($assignment['section']); ?>
                                       </span>
                                </td>
            <td> <?php echo htmlspecialchars($assignment['subject_name']); ?> </td>
            <td class="text-center">
                <button  type="button" class="btn btn-sm btn-outline-danger"
                         onclick="removeAssignment(<?php echo $assignment['id']; ?>)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
                  <?php } ?>
           <?php } else { ?>
    <tr>
        <td colspan="5" class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
            No assignments found.
        </td>
    </tr>

<?php } ?>

</tbody>

                        </table>


                        <div class="modal fade" id="removeAssignmentModal" tabindex="-1" aria-labelledby="removeAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeAssignmentModalLabel">
                    Remove Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to remove this assignment?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <form method="POST" class="d-inline">
                    <input type="hidden" name="assignment_id" id="removeAssignmentId">

                    <button type="submit" name="remove_assignment" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Remove
                    </button>
                </form>
            </div>
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
   

<script>
function removeAssignment(assignmentId) {
    document.getElementById('removeAssignmentId').value = assignmentId;

    const modal = new bootstrap.Modal(
        document.getElementById('removeAssignmentModal')
    );

    modal.show();
}

</script>


<?php if (isset($_GET['remove']) && $_GET['remove'] == 'success') { ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div class="toast show align-items-center text-bg-success border-0 assignment-success-toast"
            role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Assignment removed successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (isset($_GET['assign']) && $_GET['assign'] == 'success') { ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div class="toast show align-items-center text-bg-success border-0 assignment-success-toast"
            role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Teacher assigned successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (isset($_GET['assign']) && $_GET['assign'] == 'exists') { ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div class="toast show align-items-center text-bg-warning border-0 assignment-warning-toast"
            role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body text-dark">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Subject already assigned!
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php } ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toasts = document.querySelectorAll(".assignment-success-toast, .assignment-error-toast, .assignment-warning-toast");

    toasts.forEach(function (toast) {
        setTimeout(function () {
            toast.remove();
        }, 3000);
    });

    const url = new URL(window.location.href);

    if (url.searchParams.has("remove")) {
        url.searchParams.delete("remove");
    }

    if (url.searchParams.has("assign")) {
        url.searchParams.delete("assign");
    }

    window.history.replaceState(
        {},
        document.title,
        url.pathname + url.search + url.hash
    );
});
</script>
</body>
</html>