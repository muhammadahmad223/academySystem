<?php
include '../config/db_config.php';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $class = isset($_GET['class']) ? trim($_GET['class']) : '';
    $sql = "SELECT * FROM students WHERE deleted_at = 0";
        if ($search != '') {
            $search = mysqli_real_escape_string($conn, $search);
            $sql .= " AND (student_name LIKE '%$search%'
                OR sformNo LIKE '%$search%'
                OR id LIKE '%$search%'
            )";
        }
            if ($class != '') {
                $class = mysqli_real_escape_string($conn, $class);
                $sql .= " AND stu_class = '$class'";
            }
    $sql .= " ORDER BY id DESC";
    $result = mysqli_query($conn, $sql) or die("Query Unsuccessful"); {
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
                                <div class="row align-items-center mb-3">
                                <div class="col-md-6">
                                    <h1 class="mb-0 fs-3">Manage Students</h1>
                                </div>
                                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                    <a href="create.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>  Add Student  </a>
                                </div>
                            </div>
                            </div>
                        </main>

                        <div class="card ">
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <form method="GET" action="index.php" class="row g-3 col-md-9">
                                        <div class="col-md-5">
                                            <label class="form-label">Search Student</label>
                                            <input type="text" name="search" class="form-control" placeholder="Search by Name, Form No or ID"
                                                value="<?php echo htmlspecialchars($search); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Class</label>
                                            <select name="class" class="form-select">
                                                <option value="">All Classes</option>
                                                <option value="Play Group" <?php echo ($class == 'Play Group') ? 'selected' : ''; ?>>Play Group</option>
                                                <option value="Nursery" <?php echo ($class == 'Nursery') ? 'selected' : ''; ?>>Nursery</option>
                                                <option value="Prep" <?php echo ($class == 'Prep') ? 'selected' : ''; ?>>Prep</option>
                                                <option value="One" <?php echo ($class == 'One') ? 'selected' : ''; ?>>One</option>
                                                <option value="Two" <?php echo ($class == 'Two') ? 'selected' : ''; ?>>Two</option>
                                                <option value="Three" <?php echo ($class == 'Three') ? 'selected' : ''; ?>>Three</option>
                                                <option value="Four" <?php echo ($class == 'Four') ? 'selected' : ''; ?>>Four</option>
                                                <option value="Five" <?php echo ($class == 'Five') ? 'selected' : ''; ?>>Five</option>
                                                <option value="Six" <?php echo ($class == 'Six') ? 'selected' : ''; ?>>Six</option>
                                                <option value="Seven" <?php echo ($class == 'Seven') ? 'selected' : ''; ?>>Seven</option>
                                                <option value="Eight" <?php echo ($class == 'Eight') ? 'selected' : ''; ?>>Eight</option>
                                                <option value="Nine" <?php echo ($class == 'Nine') ? 'selected' : ''; ?>>Nine</option>
                                                <option value="Ten" <?php echo ($class == 'Ten') ? 'selected' : ''; ?>>Ten</option>
                                                <option value="Eleven" <?php echo ($class == 'Eleven') ? 'selected' : ''; ?>>Eleven</option>
                                                <option value="Twelve" <?php echo ($class == 'Twelve') ? 'selected' : ''; ?>>Twelve</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 d-flex align-items-end gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search me-1"></i>
                                                Search
                                            </button>
                                            <a href="index.php" class="btn btn-secondary">
                                                Reset
                                            </a>
                                        </div>
                                    </form>

                                    <!-- Promotion button -->
                                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                                        <button type="button" class="btn btn-warning" id="start-promotion">
                                            <i class="bi bi-arrow-up-circle me-1"></i>
                                            Promote Students
                                        </button>
                                    </div>
                                </div>
                                <!-- pormote function -->
                                <div id="promotion-controls" class="d-none mb-3">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <div class="row align-items-end g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">
                                                        Promote Selected Students To
                                                    </label>
                                                    <select id="promote-to-class" class="form-select">
                                                        <option value="">All Classes</option>
                                                        <option value="Play Group" <?php echo ($class == 'Play Group') ? 'selected' : ''; ?>>Play Group</option>
                                                        <option value="Nursery" <?php echo ($class == 'Nursery') ? 'selected' : ''; ?>>Nursery</option>
                                                        <option value="Prep" <?php echo ($class == 'Prep') ? 'selected' : ''; ?>>Prep</option>
                                                        <option value="One" <?php echo ($class == 'One') ? 'selected' : ''; ?>>One</option>
                                                        <option value="Two" <?php echo ($class == 'Two') ? 'selected' : ''; ?>>Two</option>
                                                        <option value="Three" <?php echo ($class == 'Three') ? 'selected' : ''; ?>>Three</option>
                                                        <option value="Four" <?php echo ($class == 'Four') ? 'selected' : ''; ?>>Four</option>
                                                        <option value="Five" <?php echo ($class == 'Five') ? 'selected' : ''; ?>>Five</option>
                                                        <option value="Six" <?php echo ($class == 'Six') ? 'selected' : ''; ?>>Six</option>
                                                        <option value="Seven" <?php echo ($class == 'Seven') ? 'selected' : ''; ?>>Seven</option>
                                                        <option value="Eight" <?php echo ($class == 'Eight') ? 'selected' : ''; ?>>Eight</option>
                                                        <option value="Nine" <?php echo ($class == 'Nine') ? 'selected' : ''; ?>>Nine</option>
                                                        <option value="Ten" <?php echo ($class == 'Ten') ? 'selected' : ''; ?>>Ten</option>
                                                        <option value="Eleven" <?php echo ($class == 'Eleven') ? 'selected' : ''; ?>>Eleven</option>
                                                        <option value="Twelve" <?php echo ($class == 'Twelve') ? 'selected' : ''; ?>>Twelve</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-auto">
                                                    <button type="button" class="btn btn-success" id="promote-selected">
                                                        <i class="bi bi-arrow-up-circle me-1"></i>
                                                        Promote Selected
                                                    </button>
                                                </div>
                                                <div class="col-md-auto">
                                                    <button type="button" class="btn btn-secondary" id="cancel-promotion">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- table  -->
                                <div class="table-responsive text-nowrap">
                                    <table class="table">
                                        <thead>
                                            <tr class="text-nowrap">
                                                <th class="promotion-column d-none">
                                                    <input type="checkbox" id="select-all">
                                                </th>
                                                <th>#</th>
                                                <th>Form No</th>
                                                <th>Student Name</th>
                                                <th>Admission Date</th>
                                                <th>Class</th>
                                                <th>Admission Fee</th>
                                                <th> Image</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                                <tr>
                                                    <td class="promotion-column d-none">
                                                        <input type="checkbox" class="student-checkbox" name="student_ids[]"
                                                            value="<?php echo $row['id']; ?>">
                                                    </td>
                                                    <td><?php echo $row['id']; ?></td>
                                                    <td><?php echo $row['sformNo']; ?></td>
                                                    <td><?php echo $row['student_name']; ?></td>
                                                    <td> <?php echo date('d M Y', (int)$row['admission_date']); ?></td>
                                                    <td><?php echo $row['stu_class']; ?></td>
                                                    <td><?php echo $row['admission_fee']; ?></td>
                                                    <td>
                                                        <?php if (!empty($row['student_img'])) { ?>
                                                            <img src="../uploads/students/<?php echo $row['student_img']; ?>"
                                                                width="50" height="50"  class="rounded-circle"
                                                                style="object-fit: cover;" alt="Student">
                                                        <?php } else { ?>
                                                            No Image
                                                        <?php } ?>
                                                    </td>
                                                   <td>
                                                         <div class="dropdown">
                                                             <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                 <i class="bi bi-three-dots-vertical"></i>
                                                                            Action
                                                             </button>
                                                             <ul class="dropdown-menu">
                                                                 <li>
                                                                    <a class="dropdown-item" href="view.php?id=<?php echo $row['id']; ?>">
                                                                     <i class="bi bi-eye me-2"></i>
                                                                         View Student
                                                                        </a>
                                                                </li>
                                                                     <li>
                                                                        <a class="dropdown-item" href="edit.php?id=<?php echo $row['id']; ?>">
                                                                         <i class="bi bi-pencil me-2"></i>
                                                                          Edit Student
                                                                              </a>
                                                                      </li>
                                                                           <li>
                                                                              <a class="dropdown-item" href="../fees/collect_fee.php?student_id=<?php echo $row['id']; ?>">
                                                                              <i class="bi bi-cash-coin me-2"></i>
                                                                              Collect Fee
                                                                              </a>
                                                                          </li>

                                                                              <li>
                                                                               <a class="dropdown-item" href="../fees/fee_collection.php?search=<?php echo urlencode($row['father_contact']); ?>">
                                                                               <i class="bi bi-people me-2"></i>
                                                                                 Sibling Fee
                                                                                 </a>
                                                                              </li>
                                                                                 <li>
                                                                                     <a class="dropdown-item"
                                                                                       href="../attendance/history.php?student_id=<?php echo $row['id']; ?>">
                                                                                        <i class="bi bi-clock-history me-2"></i>
                                                                                         Attendance History
                                                                                    </a>
                                                                                 </li>
                                                                                       <li>
                                                                                          <a class="dropdown-item" href="admission_form.php?id=<?php echo $row['id']; ?>" target="_blank">
                                                                                          <i class="bi bi-printer me-2"></i>
                                                                                           Print Admission Form
                                                                                           </a>
                                                                                        </li>

                                                                                             <li>
                                                                                                 <hr class="dropdown-divider">
                                                                                              </li>
                                                                                                 <li>
                                                                                                  <button type="button" class="dropdown-item text-danger"
                                                                                                   onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                                                                                     <i class="bi bi-trash me-2"></i>
                                                                                                        Delete Student
                                                                                                     </button>
                                                                                                 </li>
                                                                                                    </ul>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>

                                                                                                                    <?php } ?>
<div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Delete Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-trash3 text-danger fs-1"></i>
                <h5 class="mt-3">Are you sure?</h5>
                <p class="text-muted mb-0">Are you sure you want to delete this student?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <a href="#" id="confirmDeleteStudent" class="btn btn-danger">
                    <i class="bi bi-trash-fill me-1"></i>
                    Yes, Delete
                </a>
            </div>
        </div>
    </div>
</div>
                      <?php if (isset($_GET['delete']) && $_GET['delete'] == 'success') { ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="deleteToast" class="toast student-success-toast  align-items-center text-bg-success border-0"
            role="alert"
            aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Student deleted successfully.
                </div>
                <button type="button"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast">
                </button>
            </div>
        </div>
    </div>
<?php } ?>       
                                        </tbody>
                                    </table>
                                <?php }
                            mysqli_close($conn);
                                ?>

                                </div>
                            </div>
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
        <!-- apexcharts -->
        <script
            src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
            integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
            crossorigin="anonymous"></script>

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
            document.addEventListener('DOMContentLoaded', function() {
                const startPromotion = document.getElementById('start-promotion');
                const promotionControls = document.getElementById('promotion-controls');
                const promotionColumns = document.querySelectorAll('.promotion-column');
                const selectAll = document.getElementById('select-all');
                const studentCheckboxes = document.querySelectorAll('.student-checkbox');
                const cancelPromotion = document.getElementById('cancel-promotion');
                const promoteSelected = document.getElementById('promote-selected');
                // Start promotion mode
                startPromotion.addEventListener('click', function() {
                    promotionControls.classList.remove('d-none');
                    promotionColumns.forEach(function(column) {
                        column.classList.remove('d-none');
                    });
                });
                // Select all students
                selectAll.addEventListener('change', function() {
                    studentCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });
                // Cancel promotion
                cancelPromotion.addEventListener('click', function() {
                    promotionControls.classList.add('d-none');
                    promotionColumns.forEach(function(column) {
                        column.classList.add('d-none');
                    });
                    selectAll.checked = false;
                    studentCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = false;
                    });
                });
                // Promote button
                promoteSelected.addEventListener('click', function() {
                    const selectedStudents = document.querySelectorAll(
                        '.student-checkbox:checked'
                    );
                    const selectedClass = document.getElementById('promote-to-class').value;
                    if (selectedStudents.length === 0) {
                        alert('Please select at least one student.');
                        return;
                    }
                    if (selectedClass === '') {
                        alert('Please select the class to promote students.');
                        return;
                    }
                    const confirmPromotion = confirm(
                        selectedStudents.length +
                        ' student(s) will be promoted to Class ' +
                        selectedClass +
                        '.\n\nAre you sure?'
                    );
                    if (!confirmPromotion) {
                        return;
                    }
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'promote.php';
                    selectedStudents.forEach(function(checkbox) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'student_ids[]';
                        input.value = checkbox.value;
                        form.appendChild(input);

                    });
                    const classInput = document.createElement('input');
                    classInput.type = 'hidden';
                    classInput.name = 'promote_to_class';
                    classInput.value = selectedClass;
                    form.appendChild(classInput);
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        </script>

       <script>
function confirmDelete(studentId) {
    document.getElementById('confirmDeleteStudent').href = 'delete.php?id=' + studentId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteStudentModal'));
    deleteModal.show();
}
</script>

    <script>
    document.addEventListener('DOMContentLoaded',function(){
        var toastElement=document.getElementById('deleteToast');
        var toast=new bootstrap.Toast(toastElement,{delay:3000});
        toast.show();

        setTimeout(function(){
            var url=new URL(window.location.href);
            url.searchParams.delete('delete');
            window.history.replaceState({},document.title,url.pathname+url.search);
        },3000);
    });
    </script>

    </body>
    <!--end::Body-->

    </html>