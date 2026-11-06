<?php

include '../config/db_config.php';

// edit fee structure
$edit_id = 0;
$edit_class = '';
$edit_monthly_fee = '';

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    $sql_edit = "SELECT * FROM fee_structure WHERE id = $edit_id";

    $result_edit = mysqli_query($conn, $sql_edit);

    if ($result_edit && mysqli_num_rows($result_edit) > 0) {

        $edit_row = mysqli_fetch_assoc($result_edit);

        $edit_class = $edit_row['class_name'];
        $edit_monthly_fee = $edit_row['monthly_fee'];
    }
}




// posst method fees save krwana

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $class_name = trim($_POST['class_name']);
    $monthly_fee = (float)$_POST['monthly_fee'];

    $class_name = mysqli_real_escape_string($conn, $class_name);

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {

        $edit_id = (int)$_POST['edit_id'];

        $sql = "UPDATE fee_structure
                SET class_name = '$class_name',
                    monthly_fee = '$monthly_fee'
                WHERE id = $edit_id";

        $result = mysqli_query($conn, $sql);

        if ($result) {
            header("Location: fee_structure.php?update=success");
            exit;
        } else {
            header("Location: fee_structure.php?update=error");
            exit;
        }
    } else {

        $sql = "INSERT INTO fee_structure (class_name, monthly_fee)
                VALUES ('$class_name', '$monthly_fee')";

        $result = mysqli_query($conn, $sql);

        if ($result) {
            header("Location: fee_structure.php?save=success");
            exit;
        } else {
            header("Location: fee_structure.php?save=error");
            exit;
        }
    }
}
$sql = "SELECT * FROM fee_structure ORDER BY id ASC";

$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
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
                                    <h1 class="mb-0 fs-3">Fee Structure</h1>
                                </div>
                            </div>

                        </div>

                    </main>

                    <div class="card mb-4">

                        <div class="card-header">
                            <h5 class="mb-0">Add / Update Fee Structure</h5>
                        </div>

                        <div class="card-body">

                            <form method="POST">

                                <?php if ($edit_id > 0) { ?>

                                    <input
                                        type="hidden"
                                        name="edit_id"
                                        value="<?php echo $edit_id; ?>">

                                <?php } ?>

                                <div class="row g-3 align-items-end">

                                    <div class="col-md-5">

                                        <label class="form-label">
                                            Class <span class="text-danger">*</span>
                                        </label>

                                        <select name="class_name" class="form-select" required>

                                            <option value="">Choose Class...</option>

                                            <option value="Play Group" <?php echo ($edit_class == 'Play Group') ? 'selected' : ''; ?>>Play Group</option>
                                            <option value="Nursery" <?php echo ($edit_class == 'Nursery') ? 'selected' : ''; ?>>Nursery</option>
                                            <option value="Prep" <?php echo ($edit_class == 'Prep') ? 'selected' : ''; ?>>Prep</option>
                                            <option value="One" <?php echo ($edit_class == 'One') ? 'selected' : ''; ?>>One</option>
                                            <option value="Two" <?php echo ($edit_class == 'Two') ? 'selected' : ''; ?>>Two</option>
                                            <option value="Three" <?php echo ($edit_class == 'Three') ? 'selected' : ''; ?>>Three</option>
                                            <option value="Four" <?php echo ($edit_class == 'Four') ? 'selected' : ''; ?>>Four</option>
                                            <option value="Five" <?php echo ($edit_class == 'Five') ? 'selected' : ''; ?>>Five</option>
                                            <option value="Six" <?php echo ($edit_class == 'Six') ? 'selected' : ''; ?>>Six</option>
                                            <option value="Seven" <?php echo ($edit_class == 'Seven') ? 'selected' : ''; ?>>Seven</option>
                                            <option value="Eight" <?php echo ($edit_class == 'Eight') ? 'selected' : ''; ?>>Eight</option>
                                            <option value="Nine" <?php echo ($edit_class == 'Nine') ? 'selected' : ''; ?>>Nine</option>
                                            <option value="Ten" <?php echo ($edit_class == 'Ten') ? 'selected' : ''; ?>>Ten</option>
                                            <option value="Eleven" <?php echo ($edit_class == 'Eleven') ? 'selected' : ''; ?>>Eleven</option>
                                            <option value="Twelve" <?php echo ($edit_class == 'Twelve') ? 'selected' : ''; ?>>Twelve</option>

                                        </select>

                                    </div>

                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Monthly Fee <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="number"
                                            name="monthly_fee"
                                            class="form-control"
                                            placeholder="Enter Monthly Fee"
                                            value="<?php echo htmlspecialchars($edit_monthly_fee); ?>"
                                            min="0"
                                            required>

                                    </div>

                                    <div class="col-md-3">

                                        <button type="submit" class="btn btn-primary">

                                            <i class="bi bi-save me-1"></i>

                                            <?php echo ($edit_id > 0) ? 'Update Fee' : 'Save Fee'; ?>

                                        </button>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                    <div class="card">

                        <div class="card-header">
                            <h5 class="mb-0">Current Fee Structure</h5>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Class</th>
                                            <th>Monthly Fee</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count = 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                        ?>

                                            <tr>

                                                <td>
                                                    <?php echo $count++; ?>
                                                </td>

                                                <td>
                                                    <?php echo htmlspecialchars($row['class_name']); ?>
                                                </td>

                                                <td>
                                                    Rs. <?php echo number_format($row['monthly_fee'], 2); ?>
                                                </td>

                                                <td>

                                                    <a
                                                        href="fee_structure.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn btn-sm btn-primary">

                                                        <i class="bi bi-pencil"></i>

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

</body>
<!--end::Body-->

</html>