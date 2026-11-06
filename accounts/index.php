<?php
include '../config/db_config.php';
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$sql = "SELECT * FROM accounts
        WHERE deleted_at IS NULL AND (
            name LIKE '%$search%'
            OR phoneno LIKE '%$search%'
            OR account_type LIKE '%$search%'
        )
        ORDER BY id DESC";
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
                    <main class="app-main py-4">
                           
                    <?php if (isset($_GET['save']) && $_GET['save'] == 'success') { ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="successToast" class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Account created successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (isset($_GET['update']) && $_GET['update'] == 'success') { ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="successToast" class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Account updated successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'success') { ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="successToast" class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Account status updated successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php } ?>


                        <div class="container-fluid">
                            <div class="row align-items-center mb-3">
                                <div class="col-md-6">
                                    <h1 class="mb-0 fs-3">Manage Accounts</h1>
                                </div>
                                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                    <a href="create.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Add Account
                                    </a>
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
                                                    <input type="text" name="search" class="form-control"
                                                        placeholder="Search by Name, Phone or Type..."
                                                        value="<?php echo htmlspecialchars($search); ?>">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-search me-1"></i> Search
                                                </button>
                                            </div>
                                            <div class="col-auto">
                                                <a href="index.php" class="btn btn-outline-secondary">
                                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
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
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Account Type</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 1;
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $count++; ?></td>
                                                        <td>
                                                            <span class="fw-semibold">
                                                                <?php echo htmlspecialchars($row['name']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['phoneno']); ?></td>
                                                        <td>
                                                            <span class="badge text-bg-info">
                                                                <?php echo htmlspecialchars($row['account_type']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($row['status'] == 'Active') { ?>
                                                                <span class="badge text-bg-success">Active</span>
                                                            <?php } else { ?>
                                                                <span class="badge text-bg-secondary">Inactive</span>
                                                            <?php } ?>
                                                        </td>
                                                        <td>
                                                            <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="view.php?id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm btn-outline-primary"
                                                                title="View Account">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="edit.php?id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm btn-outline-warning"
                                                                title="Edit Account">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
<?php if ($row['status'] == 'Active') { ?>
    <button type="button"
        class="btn btn-sm btn-outline-danger"
        title="Deactivate Account"
        data-bs-toggle="modal"
        data-bs-target="#statusModal"
        data-id="<?= $row['id']; ?>"
        data-status="Inactive"
        data-name="<?= htmlspecialchars($row['name']); ?>">
        <i class="bi bi-person-dash"></i>
    </button>
<?php } else { ?>
    <button type="button"
        class="btn btn-sm btn-outline-success"
        title="Activate Account"
        data-bs-toggle="modal"
        data-bs-target="#statusModal"
        data-id="<?= $row['id']; ?>"
        data-status="Active"
        data-name="<?= htmlspecialchars($row['name']); ?>">
        <i class="bi bi-person-check"></i>
    </button>
<?php } ?>
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

                         <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div id="statusIcon" class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger"
                        style="width:64px;height:64px;">
                        <i class="bi bi-person-dash fs-3"></i>
                    </div>
                </div>

                <h5 class="fw-semibold mb-2" id="statusTitle">Deactivate Account</h5>

                <p class="text-muted mb-1" id=""statusMessage>
                    Are you sure you want to deactivate
                </p>

                <div class="fw-semibold mb-3" id="statusName"></div>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <a href="#" id="statusConfirm" class="btn btn-danger px-3">
                        Deactivate
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>



                    </main>
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
const toast = document.getElementById('successToast');

if (toast) {
    const url = new URL(window.location.href);
    url.searchParams.delete('save');
    url.searchParams.delete('update');
    url.searchParams.delete('status');
    window.history.replaceState({}, document.title, url.pathname + url.search);

    setTimeout(function() {
        bootstrap.Toast.getOrCreateInstance(toast).hide();
    }, 3000);
}
</script>

<script>
document.getElementById('statusModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const status = button.getAttribute('data-status');
    const name = button.getAttribute('data-name');

    const title = document.getElementById('statusTitle');
    const statusName = document.getElementById('statusName');
    const confirm = document.getElementById('statusConfirm');
    const icon = document.getElementById('statusIcon');
    const statusMessage = document.getElementById('statusMessage');
    statusName.textContent = '"' + name + '"';

    if (status === 'Inactive') {
        title.textContent = 'Deactivate Account';
        statusMessage.textContent = 'Are you sure you want to deactivate';
        confirm.textContent = 'Deactivate';
        confirm.className = 'btn btn-danger px-3';
        icon.innerHTML = `
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger"
                style="width:64px;height:64px;">
                <i class="bi bi-person-dash fs-3"></i>
            </div>
        `;
    } else {
        title.textContent = 'Activate Account';
        statusMessage.textContent = 'Are you sure you want to activate';
        confirm.textContent = 'Activate';
        confirm.className = 'btn btn-success px-3';
        icon.innerHTML = `
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success"
                style="width:64px;height:64px;">
                <i class="bi bi-person-check fs-3"></i>
            </div>
        `;
    }

    confirm.href = 'status.php?id=' + id + '&status=' + status;
});
</script>
</body>
</html>