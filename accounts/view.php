<?php
include '../config/db_config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$account_id = $_GET['id'];

$sql = "SELECT * FROM accounts WHERE id = '$account_id' AND deleted_at IS NULL";
$result = mysqli_query($conn, $sql);
$account = mysqli_fetch_assoc($result);

if (!$account) {
    header("Location: index.php");
    exit;
}

$transaction_sql = "SELECT * FROM transactions
                    WHERE account_id = '$account_id' AND deleted_at IS NULL
                    ORDER BY transaction_date DESC, id DESC";
$transaction_result = mysqli_query($conn, $transaction_sql);
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
        <main class="app-main py-3">
            <div class="container-fluid">

                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h1 class="mb-0 fs-3">Account Details</h1>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Account Information</h6>
                        <span class="badge text-bg-info">
                            <?= htmlspecialchars($account['account_type']); ?>
                        </span>
                    </div>

                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Name</small>
                                <strong><?= htmlspecialchars($account['name']); ?></strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">Phone</small>
                                <?= htmlspecialchars($account['phoneno']); ?>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">Status</small>
                                <?php if ($account['status'] == 'Active') { ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge text-bg-secondary">Inactive</span>
                                <?php } ?>
                            </div>

                            <div class="col-md-5">
                                <small class="text-muted d-block">Address</small>
                                <?= htmlspecialchars($account['address']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Transaction History</h6>
                            <small class="text-muted">
                                Total Transactions:
                                <?= mysqli_num_rows($transaction_result); ?>
                            </small>
                        </div>

                        <a href="transaction_create.php?id=<?= $account_id ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Transaction
                        </a>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Reference</th>
                                        <th>Remarks</th>
                                        <th>Reconcile</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $count = 1;
                                    while ($transaction = mysqli_fetch_assoc($transaction_result)) {
                                    ?>
                                        <tr>
                                            <td class="ps-3"><?= $count++; ?></td>

                                            <td>
                                                <?= date('d M Y', strtotime($transaction['transaction_date'])); ?>
                                            </td>

                                            <td>
                                                <?php if ($transaction['transaction_type'] == 'Withdraw') { ?>
                                                    <span class="badge text-bg-warning">Withdraw</span>
                                                <?php } else { ?>
                                                    <span class="badge text-bg-danger">Expense</span>
                                                <?php } ?>
                                            </td>

                                            <td class="fw-semibold">
                                                <?= number_format($transaction['amount'], 2); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($transaction['reference']); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($transaction['remarks']); ?>
                                            </td>

                                            <td>
                                                <?php if ($transaction['is_reconcile'] == 1) { ?>
                                                    <span class="badge text-bg-success">Verified</span>
                                                <?php } else { ?>
                                                    <span class="badge text-bg-secondary">Pending</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if (mysqli_num_rows($transaction_result) == 0) { ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No transactions found.
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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

</body>
</html>