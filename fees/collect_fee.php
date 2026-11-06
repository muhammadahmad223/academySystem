<?php
include '../config/db_config.php';
$current_month = (int)date('n');
$current_year = (int)date('Y');
if (!isset($_GET['student_id'])) {
    header("Location: fee_collection.php");
    exit;
}
$student_id = (int)$_GET['student_id'];
$sql = "SELECT id,sformNo,student_name,stu_class,monthly_fee,admission_date
        FROM students  WHERE id = $student_id AND deleted_at = 0";
$result = mysqli_query($conn,$sql);
if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: fee_collection.php");
    exit;
}

$student = mysqli_fetch_assoc($result);
$monthly_fee = (float)$student['monthly_fee'];
$admission_date = (int)$student['admission_date'];

$latest_receipt = '';
$receipt_result = mysqli_query($conn,"SELECT receipt_no FROM fee_payments
    WHERE student_id = $student_id
    AND deleted_at IS NULL
    ORDER BY id DESC LIMIT 1");
if ($receipt_result && mysqli_num_rows($receipt_result) > 0) {
    $latest_receipt = mysqli_fetch_assoc($receipt_result)['receipt_no'];
}


$admission_month = (int)date('n',$admission_date);
$admission_year = (int)date('Y',$admission_date);
$start_number = ($admission_year * 12) + $admission_month;
$current_number = ($current_year * 12) + $current_month;
/* Create missing monthly payables */
for ($number = $start_number; $number <= $current_number; $number++) {
    $year = (int)floor(($number - 1) / 12);
    $month = (($number - 1) % 12) + 1;
    $insert_sql = "INSERT IGNORE INTO fee_payables
        (student_id,fee_month,fee_year,amount,discount_amount,payable_amount,status)
        VALUES ($student_id,$month,$year,$monthly_fee,0.00,$monthly_fee,'Unpaid')";
    mysqli_query($conn,$insert_sql);
}
/* Save payment */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $receipt_result = mysqli_query($conn,"SELECT receipt_no FROM fee_payments WHERE receipt_no LIKE 'REC%' ORDER BY id DESC LIMIT 1");
if ($receipt_result && mysqli_num_rows($receipt_result) > 0) {
    $last_receipt = mysqli_fetch_assoc($receipt_result)['receipt_no'];
    preg_match('/REC(\d+)/',$last_receipt,$matches);
    $last_number = isset($matches[1]) ? (int)$matches[1] : 0;
    $receipt_no = 'REC' . str_pad($last_number + 1,3,'0',STR_PAD_LEFT);
} else {
    $receipt_no = 'REC001';
}

    $amount = (float)($_POST['amount'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $discount_payable_id = (int)($_POST['discount_payable_id'] ?? 0);
    if ($amount < 0 || $discount < 0) {
        die("Invalid amount.");
    }
    /* Apply discount to selected month */
    if ($discount > 0) {
        $discount_sql = "SELECT id,amount FROM fee_payables WHERE id = $discount_payable_id AND student_id = $student_id
                         AND deleted_at IS NULL LIMIT 1";
        $discount_result = mysqli_query($conn,$discount_sql);
        if (!$discount_result || mysqli_num_rows($discount_result) == 0) {
            die("Invalid fee month selected.");
        }
        $discount_row = mysqli_fetch_assoc($discount_result);
        $fee_amount = (float)$discount_row['amount'];
        if ($discount > $fee_amount) {
            die("Discount cannot be greater than the fee.");
        }
        $payable_amount = $fee_amount - $discount;
        $update_discount = "UPDATE fee_payables SET discount_amount = $discount,
                                payable_amount = $payable_amount
                            WHERE id = $discount_payable_id
                            AND student_id = $student_id";
        mysqli_query($conn,$update_discount);
    }

    /* Get all due payables oldest first */
    $payables_sql = "SELECT id,fee_month,fee_year,amount,discount_amount,payable_amount
                     FROM fee_payables WHERE student_id = $student_id AND deleted_at IS NULL
                     AND status != 'Paid' ORDER BY fee_year ASC,fee_month ASC";
    $payables_result = mysqli_query($conn,$payables_sql);
    $due_amount = 0;
    while ($payable = mysqli_fetch_assoc($payables_result)) {
        $payable_id = (int)$payable['id'];
        $payable_total = (float)$payable['payable_amount'];
        $paid_sql = "SELECT COALESCE(SUM(amount),0) AS paid_amount
                     FROM fee_payments
                     WHERE fee_payable_id = $payable_id
                     AND deleted_at IS NULL";
        $paid_result = mysqli_query($conn,$paid_sql);
        $paid_row = mysqli_fetch_assoc($paid_result);
        $already_paid = (float)$paid_row['paid_amount'];
        $remaining_month = $payable_total - $already_paid;
        if ($remaining_month > 0) {
            $due_amount += $remaining_month;
        }
    }

    if ($amount > $due_amount) {
        die("Amount cannot be greater than remaining fee.");
    }
    if ($amount == 0 && $discount == 0) {
        die("Please enter payment amount or discount.");
    }
    /* Allocate payment from oldest month first */
    if ($amount > 0) {
        $payables_result = mysqli_query($conn,$payables_sql);
        $remaining_payment = $amount;
        while ($payable = mysqli_fetch_assoc($payables_result)) {
            if ($remaining_payment <= 0) {
                break;
            }
            $payable_id = (int)$payable['id'];
            $payable_total = (float)$payable['payable_amount'];
            $paid_sql = "SELECT COALESCE(SUM(amount),0) AS paid_amount
                         FROM fee_payments WHERE fee_payable_id = $payable_id
                         AND deleted_at IS NULL";
            $paid_result = mysqli_query($conn,$paid_sql);
            $paid_row = mysqli_fetch_assoc($paid_result);
            $already_paid = (float)$paid_row['paid_amount'];
            $month_remaining = $payable_total - $already_paid;
            if ($month_remaining <= 0) {
                continue;
            }
            $payment_amount = min($remaining_payment,$month_remaining);
           $new_paid = $already_paid + $payment_amount;
              if ($new_paid >= $payable_total) {
                  $status = 'Paid';
             } elseif ($new_paid > 0) {
               $status = 'Partial';
             } else {
              $status = 'Unpaid';
             }
          $insert_payment = "INSERT INTO fee_payments (student_id,fee_payable_id,receipt_no,fee_month,fee_year,amount,status,payment_date)
       VALUES($student_id,$payable_id,'$receipt_no',{$payable['fee_month']},{$payable['fee_year']},$payment_amount,'$status',CURDATE())";
             mysqli_query($conn,$insert_payment);
            mysqli_query($conn,"UPDATE fee_payables SET status = '$status'
                WHERE id = $payable_id");
            $remaining_payment -= $payment_amount;
        }
    }
    /* Recalculate all statuses */
    $status_sql = "SELECT id,payable_amount  FROM fee_payables  WHERE student_id = $student_id
                   AND deleted_at IS NULL";
    $status_result = mysqli_query($conn,$status_sql);
    while ($p = mysqli_fetch_assoc($status_result)) {
        $pid = (int)$p['id'];
        $pamount = (float)$p['payable_amount'];
        $paid_result = mysqli_query($conn,"SELECT COALESCE(SUM(amount),0) AS paid
            FROM fee_payments WHERE fee_payable_id = $pid
            AND deleted_at IS NULL");
        $paid_row = mysqli_fetch_assoc($paid_result);
        $paid = (float)$paid_row['paid'];
        if ($paid >= $pamount) {
            $status = 'Paid';
        } elseif ($paid > 0) {
            $status = 'Partial';
        } else {
            $status = 'Unpaid';
        }

        mysqli_query($conn,"UPDATE fee_payables
            SET status = '$status'
            WHERE id = $pid");
    }

   header("Location: collect_fee.php?student_id=$student_id&payment=success&receipt=$receipt_no");
exit;
}
/* Monthly fee history */
$history_sql = "SELECT
                    p.id,
                    p.fee_month,
                    p.fee_year,
                    p.amount,
                    p.discount_amount,
                    p.payable_amount,
                    p.status,
                    COALESCE(SUM(fp.amount),0) AS paid_amount
                FROM fee_payables p
                LEFT JOIN fee_payments fp
                    ON fp.fee_payable_id = p.id
                    AND fp.deleted_at IS NULL
                WHERE p.student_id = $student_id
                AND p.deleted_at IS NULL
                GROUP BY p.id,p.fee_month,p.fee_year,p.amount,p.discount_amount,p.payable_amount,p.status
                ORDER BY p.fee_year ASC,p.fee_month ASC";

$history_result = mysqli_query($conn,$history_sql);
$total_fee = 0;
$total_discount = 0;
$total_payable = 0;
$paid_amount = 0;
$remaining = 0;
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


    <style>
        /* =========================================
   Collect Fee Page
========================================= */

        .collect-icon { width: 42px; height: 42px; border-radius: 12px;
            background: #eef5ff; color: #0d6efd; display: flex; align-items: center;
            justify-content: center; font-size: 21px; }

        .student-info-card { background: #ffffff; border: 1px solid #dcecff;
            border-radius: 12px;  padding: 16px 22px;  display: flex;
            align-items: center;  gap: 18px; }

        .student-avatar { width: 72px; height: 72px; border-radius: 50%; background: #e8f2ff;
            color: #3b82f6;  display: flex;  align-items: center;  justify-content: center;
            font-size: 34px;  flex-shrink: 0; }
        .student-details {
            min-width: 0;
        }
        .student-form-badge { background: #eaf3ff;
            color: #0d6efd; font-size: 13px; font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
        }


        .student-meta {
            display: flex;
            align-items: center;
            gap: 28px;

            margin-top: 8px;

            color: #526581;
            font-size: 14px;
        }


        /* Main Card */

        .fee-main-card {
            background: #ffffff;

            border: 1px solid #e3eaf3;
            border-radius: 12px;

            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);

            overflow: hidden;
        }


        /* Fee Summary */

        .fee-summary-section {
            padding: 18px 24px 20px;
        }


        .section-heading {
            display: flex;
            align-items: center;
            gap: 12px;
        }


        .section-icon {
            width: 38px;
            height: 38px;

            border-radius: 10px;

            background: #eef5ff;
            color: #0d6efd;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 19px;
        }


        /* Stat Cards */

        .fee-stat-card{
    padding:10px 13px;
    border-radius:10px;
    min-height:55px;
    display:flex;
    align-items:center;
    gap: 12px;
}


        .total-card {
            background: #f1f7ff;
            border: 1px solid #d5e8ff;
        }


        .paid-card {
            background: #effbf4;
            border: 1px solid #d0f0dc;
        }


        .remaining-card {
            background: #fff6ec;
            border: 1px solid #ffe1c2;
        }


        .stat-icon {
            width: 42px;
            height: 42px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 20px;

            flex-shrink: 0;
        }


        .total-card .stat-icon {
            background: #ffffff;
            color: #2385ed;
        }


        .paid-card .stat-icon {
            background: #ffffff;
            color: #16a05d;
        }


        .remaining-card .stat-icon {
            background: #ffffff;
            color: #ed8a23;
        }


       .stat-label{
    font-size:11px;
    color:#64748b;
    margin-bottom:2px;
}
.stat-value{
    font-size:17px;
    font-weight:700;
    color:#0f172a;
}

        /* Payment Section */

        .payment-section {
            border-top: 1px solid #edf0f4;

            padding: 14px 18px;
        }


        .money-input {
            display: flex;
            align-items: stretch;
        }


        .money-symbol {
            min-width: 48px;

            border: 1px solid #d7e0ea;
            border-right: 0;

            border-radius: 8px 0 0 8px;

            background: #f8fafc;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #526581;
            font-weight: 600;
        }


        .money-input .form-control {
            border-radius: 0 8px 8px 0;

            height: 40px;
        }


        .amount-box {
            background: #f8fbff;

            border: 1px solid #dcecff;
            padding:11px 13px;
               border-radius:10px;
        }


        .amount-box .form-text {
            margin-bottom: 0;
        }


        /* Buttons */

        .fee-actions {
            border-top: 1px solid #edf0f4;

            padding: 14px 24px;

            display: flex;
            justify-content: center;
            gap: 4px;
        }


        .fee-actions .btn {
            height: 42px;
        }


        /* Responsive */

        @media (max-width: 767px) {

            .student-info-card {
                padding: 14px;
            }

            .student-avatar {
                width: 58px;
                height: 58px;
                font-size: 27px;
            }

            .student-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .fee-summary-section,
            .payment-section {
                padding: 16px;
            }

            .fee-actions {
                padding: 14px 16px;
            }

        }

        .fee-history-card,.fee-main-card,.student-info-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px}
.fee-history-card{padding:12px;margin-bottom:10px}
.fee-history-table{font-size:11px}
.fee-history-table th{font-size:11px;color:#64748b;background:#f8fafc;white-space:nowrap; padding:7px 8px;}
.fee-history-table td,.fee-history-table th{padding:7px 8px}
.fee-status{display:inline-block;padding:3px 7px;border-radius:20px;font-size:10px;font-weight:600}
.fee-status.paid{background:#dcfce7;color:#15803d}
.fee-status.partial{background:#fef3c7;color:#b45309}
.fee-status.unpaid{background:#fee2e2;color:#b91c1c}
.discount-card{background:#f5f3ff;border-color:#ddd6fe}
.discount-card .stat-value{color:#6d28d9}
.fee-stat-card{padding:13px 15px;border-radius:12px}
.stat-label{font-size:12px;color:#64748b;margin-bottom:3px}
.stat-value{font-size:19px;font-weight:700;color:#0f172a}
.payment-section{padding:14px 18px}
.amount-box{border:1px solid #dbeafe;background:#f8fbff;border-radius:12px;padding:14px}
@media(max-width:768px){
    .fee-history-table{min-width:700px}
    .fee-history-card{overflow:hidden}
    .payment-section{padding:14px}
}
    </style>

</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <?php include '../layout/sidebar.php'; ?>
        <div class="layout-page">
            <?php include '../layout/header.php'; ?>

          <main class="app-main py-3">
                <div class="container-fluid">
                    <div class="row align-items-center mb-3">
    <div class="col-sm-6">
        <div class="d-flex align-items-center gap-3">
            <div class="collect-icon">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <h1 class="mb-0 fs-3 fw-semibold">Collect Fee</h1>
                <p class="text--muted small mb-0">Collect fee for the selected student</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-sm-end mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="../student/index.php">Students</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fees</li>
            </ol>
        </nav>
    </div>
</div>      
                    <!-- Student Information -->
                    <div class="student-info-card mb-3">
                             <div class="student-avatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <div class="student-details">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="mb-0 fw-semibold">
                                    <?php echo htmlspecialchars($student['student_name']); ?>
                                </h4>
                                <span class="student-form-badge">
                                    <?php echo htmlspecialchars($student['sformNo']); ?>
                                </span>
                            </div>  
                                 <div class="student-meta">
                                     <span>
                                       <i class="bi bi-mortarboard-fill me-1"></i>
                                       Class:
                                       <?php echo htmlspecialchars($student['stu_class']); ?>
                                     </span>
                                             <span>
                                                 <i class="bi bi-calendar3 me-1"></i>
                                                 Fee Month:
                                               <?php echo date('F Y'); ?>
                                              </span>
                                  </div>
                        </div>
                    </div>

             <div class="fee-history-card">
                          <div class="section-heading mb-3">
                                <div class="section-icon">
                                    <i class="bi bi-calendar-month"></i>
                                </div>
                                      <div>
                                           <h5 class="mb-0 fw-semibold">Fee History</h5>
                                           <small class="text-muted">Monthly fee record</small>
                                      </div>
                          </div>
                    <div class="table-responsive">
                        <table class="table fee-history-table align-middle mb-0">
                         <thead>
                               <tr>
                                   <th>Month</th>
                                   <th>Fee</th>
                                   <th>Discount</th>
                                   <th>Payable</th>
                                   <th>Paid</th>
                                   <th>Remaining</th>
                                   <th>Status</th>
                               </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                    <?php
                    $fee = (float)$row['amount'];
                    $discount = (float)$row['discount_amount'];
                    $payable = (float)$row['payable_amount'];
                    $paid = (float)$row['paid_amount'];
                    $month_remaining = max(0,$payable - $paid);
                    $total_fee += $fee;
                    $total_discount += $discount;
                    $total_payable += $payable;
                    $paid_amount += $paid;
                    $remaining += $month_remaining;
                    if ($paid >= $payable) {
                        $status = 'Paid';
                        $status_class = 'paid';
                    } elseif ($paid > 0) {
                        $status = 'Partial';
                        $status_class = 'partial';
                    } else {
                        $status = 'Unpaid';
                        $status_class = 'unpaid';
                    }
                    ?>
                    <tr>
                        <td class="fw-semibold">
                            <?php echo date('F Y',mktime(0,0,0,$row['fee_month'],1,$row['fee_year'])); ?>
                        </td>
                        <td>Rs. <?php echo number_format($fee,2); ?></td>
                        <td>Rs. <?php echo number_format($discount,2); ?></td>
                        <td class="fw-semibold">Rs. <?php echo number_format($payable,2); ?></td>
                        <td class="text-success">Rs. <?php echo number_format($paid,2); ?></td>
                        <td class="text-danger fw-semibold">Rs. <?php echo number_format($month_remaining,2); ?></td>
                        <td>
                            <span class="fee-status <?php echo $status_class; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>


                    <!-- Main Fee Card -->
                    <div class="fee-main-card">
                        <form method="POST">
                         <!-- Fee Summary -->
                            <div class="fee-summary-section">
    <div class="section-heading">
        <div class="section-icon">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-semibold">Fee Summary</h5>
            <small class="text-muted">Overview of fee details</small>
        </div>
    </div>

    <div class="row g-2 mt-1">
        <div class="col-lg-3 col-6">
            <div class="fee-stat-card total-card">
                <div class="stat-label">Total Fee</div>
                <div class="stat-value">Rs. <?php echo number_format($total_fee,2); ?></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="fee-stat-card paid-card">
                <div class="stat-label">Already Paid</div>
                <div class="stat-value">Rs. <?php echo number_format($paid_amount,2); ?></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="fee-stat-card discount-card">
                <div class="stat-label">Discount</div>
                <div class="stat-value">Rs. <?php echo number_format($total_discount,2); ?></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="fee-stat-card remaining-card">
                <div class="stat-label">Remaining</div>
                <div class="stat-value">Rs. <?php echo number_format($remaining,2); ?></div>
            </div>
        </div>
    </div>
</div>

                            <!-- Payment Area -->
                            <div class="payment-section">
                                <div class="row g-2">
                                   <div class="col-md-6">
                                      <label class="form-label fw-semibold">Discount / Favour</label>
                                     <div class="money-input">
                                       <span class="money-symbol">Rs.</span>
                                         <input type="number" name="discount" id="discount" class="form-control" min="0" step="0.01" value="0.00" placeholder="Enter discount">
                                   </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Apply Discount To</label>
            <select name="discount_payable_id" class="form-select">
                <?php
                mysqli_data_seek($history_result,0);
                while ($row = mysqli_fetch_assoc($history_result)):
                    $payable = (float)$row['payable_amount'];
                    $paid = (float)$row['paid_amount'];
                    if ($paid >= $payable) continue;
                ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo date('F Y',mktime(0,0,0,$row['fee_month'],1,$row['fee_year'])); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-12">
            <div class="amount-box">
                <label class="form-label fw-semibold">Amount to Collect</label>
                <div class="money-input">
                    <span class="money-symbol">Rs.</span>
                    <input type="number" name="amount" id="amount" class="form-control" min="0" max="<?php echo $remaining; ?>" step="0.01" placeholder="Enter amount" required>
                </div>
                <div class="form-text">
                    Maximum: <strong>Rs. <?php echo number_format($remaining,2); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

                            <!-- Footer Buttons -->
                            <div class=" fee-actions">

                                <a
                                    href="fee_collection.php"
                                    class="btn btn-light border px-4">

                                    <i class="bi bi-x-lg me-1"></i>
                                    Cancel

                                </a>

                               <?php
$print_receipt = !empty($_GET['receipt']) ? $_GET['receipt'] : $latest_receipt;
if ($print_receipt !== ''):
?>
    <a
        href="fee_receipt.php?receipt=<?php echo urlencode($print_receipt); ?>"
        target="_blank"
        class="btn btn-secondary px-4">
        <i class="bi bi-printer me-1"></i>
        Print Receipt
    </a>
<?php endif; ?>


                                <button
                                    type="submit"
                                    class="btn btn-primary px-4">

                                    <i class="bi bi-cash-stack me-1"></i>
                                    Save Payment

                                </button>

                              </div>
                </form>
            </div>
        </div>
    </div>
</main>

<div class="toast-container position-fixed top-0 end-0 p-3">
    <div
        id="paymentToast"
        class="toast align-items-center text-bg-success border-0"
        role="alert"
        aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                Fee saved successfully!
            </div>
            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast">
            </button>
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
    <script>
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.get('payment') === 'success') {
            const paymentToast = document.getElementById('paymentToast');
            const toast = new bootstrap.Toast(paymentToast, {
                delay: 3000
            });

            toast.show();

            urlParams.delete('payment');
            urlParams.delete('receipt');
            const queryString = urlParams.toString();
            window.history.replaceState(
                {},
                document.title,
                window.location.pathname + (queryString ? '?' + queryString : '')
            );
        }
    </script>
    <script>
        const discountInput = document.getElementById('discount');
        const remainingFee = document.getElementById('remainingFee');
        const amountInput = document.getElementById('amount');
        const amountHelp = document.getElementById('amountHelp');

        const totalFee = <?php echo $total_fee; ?>;
        const paidAmount = <?php echo $paid_amount; ?>;


        discountInput.addEventListener('input', function() {

            let discount = parseFloat(this.value) || 0;


            // Discount cannot be negative
            if (discount < 0) {

                discount = 0;
                this.value = 0;

            }


            // Discount cannot exceed total fee
            if (discount > totalFee) {

                discount = totalFee;
                this.value = totalFee;

            }


            /*
             * Remaining Fee
             *
             * Total Fee
             * - Discount
             * - Already Paid
             */

            const remaining = Math.max(
                0,
                totalFee - discount - paidAmount
            );


            // Update remaining amount
            remainingFee.textContent =
                'Rs. ' +
                remaining.toLocaleString(
                    'en-PK', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );


            // Update maximum amount
            amountInput.max = remaining;


            // Update helper text
            amountHelp.innerHTML =
                'Maximum: <strong>Rs. ' +
                remaining.toLocaleString(
                    'en-PK', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                ) +
                '</strong>';


            // Prevent amount greater than remaining
            if (
                parseFloat(amountInput.value) > remaining
            ) {

                amountInput.value = remaining;

            }

        });
    </script>
</body>
<!--end::Body-->

</html>