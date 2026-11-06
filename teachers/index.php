<?php
include '../config/db_config.php';
        $search = "";
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }
       $sql = "SELECT t.*, GROUP_CONCAT(DISTINCT CONCAT(ta.class_name, ' - ', s.subject_name) SEPARATOR ' |') AS assignments
        FROM teachers t
        LEFT JOIN teacher_assignments ta ON t.id = ta.teacher_id
        LEFT JOIN subjects s ON ta.subject_id = s.id
        WHERE t.deleted_at = 0 AND (
            t.tform_no LIKE '%$search%'
            OR t.teacher_name LIKE '%$search%'
            OR t.qualification LIKE '%$search%'
        )
        GROUP BY t.id
        ORDER BY t.id DESC";

$result = mysqli_query($conn, $sql);
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
                    <?php if (isset($_GET['update']) && $_GET['update'] == 'success') { ?>
                        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
                            <div class="toast teacher-success-toast show align-items-center text-bg-success border-0"
                                role="alert"  aria-live="assertive"  aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        Teacher updated successfully.
                                    </div>
                                    <button type="button"
                                        class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast">
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <main class="app-main py-4">
                        <div class="container-fluid">
                            <div class="row align-items-center mb-3">
                                <div class="col-md-6">
                                    <h1 class="mb-0 fs-3">Manage Teachers</h1>
                                </div>
                                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                    <a href="create.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>  Add Teacher  </a>
                                </div>
                            </div>
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <form method="GET">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-6 col-lg-5">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-search"></i>
                                                    </span>
                                                    <input  type="text"  name="search"  class="form-control"  placeholder="Search by Teacher ID or Name..."
                                                        value="<?php echo htmlspecialchars($search); ?>">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-search me-1"></i>
                                                    Search
                                                </button>
                                            </div>
                                            <div class="col-auto">
                                                <a href="index.php" class="btn btn-outline-secondary">
                                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                                    Reset
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>TID</th>
                                                    <th>Teacher Name</th>
                                                    <th>Joining Date</th>
                                                    <th>Qualification</th>
                                                    <th>Salary</th>
                                                    <th>Image</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $count = 1;
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                    <tr>
                                                        <td> <?php echo $count++; ?> </td>
                                                        <td>
                                                            <span class="fw-semibold">
                                                                <?php echo $row['tform_no']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                           <span class="fw-semibold">
                                                        <?php echo $row['teacher_name']; ?>
                                                          </span>

                                                          <?php if (!empty($row['assignments'])) { ?>
                                                              <br>
                                                           <small class="text-muted">
                                                             <?php echo htmlspecialchars($row['assignments']); ?>
                                                             </small>
                                                         <?php } else { ?>
                                                           <br>
                                                               <small class="text-danger">Not Assigned</small>
                                                            <?php } ?>
                                                               </td>
                                                         <td> <?php echo date('d M Y', (int)$row['joining_date']); ?></td>
                                                        <td> <?php echo $row['qualification']; ?></td>
                                                        <td><?php echo $row['basic_salary']; ?></td>
                                                        <td>
                                                            <?php if (!empty($row['teacher_img'])) { ?>
                                                                <img src="../uploads/teachers/<?php echo $row['teacher_img']; ?>" width="45" height="45" class="rounded-circle" style="object-fit: cover;" alt="Teacher">
                                                            <?php } else { ?>
                                                                <img src="../dist/assets/img/defaultimage.jpg" width="45" height="45" class="rounded-circle" style="object-fit: cover;" alt="Teacher">
                                                            <?php } ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <a  href="edit.php?id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm btn-outline-primary me-1"
                                                                title="Edit Teacher">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a  href="assign.php?id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm btn-outline-success me-1" title="Assign Class & Subject">
                                                                <i class="bi bi-person-plus"></i>
                                                            </a>
                                                            <a
                                                                href="#"
                                                                class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="modal" data-bs-target="#deleteTeacherModal"
                                                                data-id="<?php echo $row['id']; ?>"
                                                                title="Delete Teacher">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
                        <div class="modal fade" id="deleteTeacherModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                Delete Teacher
                            </h5>
                            <button  type="button"  class="btn-close"  data-bs-dismiss="modal"> </button>
                        </div>
                             <div class="modal-body text-center py-4">
                            <i class="bi bi-trash3 text-danger fs-1"></i>
                            <h5 class="mt-3"> Are you sure? </h5>
                            <p class="text-muted mb-0">   Are you sure you want to delete this teacher? </p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button   type="button"  class="btn btn-outline-secondary"   data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <a  href="#"  id="confirmDeleteTeacher"   class="btn btn-danger">
                                <i class="bi bi-trash-fill me-1"></i>
                                Yes, Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (isset($_GET['delete']) && $_GET['delete'] == 'success') { ?>
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
                    <div class="toast teacher-success-toast show align-items-center text-bg-success border-0"
                        role="alert"
                        aria-live="assertive"
                        aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Teacher deleted successfully.
                            </div>
                            <button type="button"
                                class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast">
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
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
        document.querySelectorAll('[data-bs-target="#deleteTeacherModal"]').forEach(function(button) {
            button.addEventListener('click', function() {
                let teacherId = this.getAttribute('data-id');
                let deleteButton = document.getElementById('confirmDeleteTeacher');
                deleteButton.href = 'delete.php?id=' + teacherId;
            });
        });
    </script>
<script> document.addEventListener("DOMContentLoaded", function () {
    const toasts = document.querySelectorAll(".teacher-success-toast");
    toasts.forEach(function (toast) {
        setTimeout(function () {
            toast.remove();
        }, 3000);
    });
    // URL se success parameters remove
    const url = new URL(window.location.href);
    let changed = false;
    if (url.searchParams.has("update")) {
        url.searchParams.delete("update");
        changed = true;
    }
    if (url.searchParams.has("delete")) {
        url.searchParams.delete("delete");
        changed = true;
    }
    if (changed) {
        window.history.replaceState(
            {},
            document.title,
            url.pathname + url.search + url.hash
        );
    }
});
</script>
</body>
</html>