
<?php

include '../config/db_config.php';

$selected_account = $_GET['id'] ?? '';

$accounts = mysqli_query($conn, "SELECT id, name, account_type FROM accounts WHERE deleted_at IS NULL AND status='Active' ORDER BY id DESC");
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
                    <div class="container-fluid">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <h1 class="mb-0 fs-3">Add Transaction</h1>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">Transaction Information</h5>
                            </div>
                            <div class="card-body">
                                <form action="transaction_save.php" method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Account</label>
                                            <select name="account_id" id="account_id" class="form-select" required>
                                            <option value="">Select Account</option>
                                             <?php while ($account = mysqli_fetch_assoc($accounts)) { ?>
                                             <option value="<?= $account['id'] ?>" data-type="<?= $account['account_type'] ?>" <?= $selected_account == $account['id'] ? 'selected' : '' ?>>
                                             <?= htmlspecialchars($account['name']) ?> - <?= htmlspecialchars($account['account_type']) ?>
                                            </option>
                                           <?php } ?>
                                      </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Transaction Type</label>
                                            <select name="transaction_type" id="transaction_type" class="form-select" required>
                                               <option value="">Select Type</option>
                                                <option value="Withdraw">Withdraw</option>
                                                <option value="Expense">Expense</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Amount</label>
                                            <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Transaction Date</label>
                                            <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Reference</label>
                                            <input type="text" name="reference" class="form-control" placeholder="e.g. September Salary / Plumbing Work">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Reconcile</label>
                                            <select name="is_reconcile" class="form-select">
                                                <option value="0">Pending</option>
                                                <option value="1">Verified</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Additional details"></textarea>
                                        </div>

                                        <div class="col-12 text-center">
                                            <button type="reset" class="btn btn-outline-secondary me-1">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-1"></i> Save Transaction
                                            </button>
                                        </div>
                                    </div>
                                </form>
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


<script src="../dist/js/adminlte.js"></script>

<script>
function setTransactionType() {
    const account = document.getElementById('account_id');
    const selectedOption = account.options[account.selectedIndex];
    const accountType = selectedOption.getAttribute('data-type');
    const transactionType = document.getElementById('transaction_type');

    if (accountType === 'Expense') {
        transactionType.value = 'Expense';
    } else if (accountType === 'Teacher') {
        transactionType.value = 'Withdraw';
    } else {
        transactionType.value = '';
    }
}

document.getElementById('account_id').addEventListener('change', setTransactionType);
setTransactionType();
</script>


</body>
</html>