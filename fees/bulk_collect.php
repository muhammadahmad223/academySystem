<?php
include '../config/db_config.php';

$month = isset($_POST['month'])
    ? (int)$_POST['month']
    : (int)date('n');
$year = isset($_POST['year'])
    ? (int)$_POST['year']
    : (int)date('Y');
$student_ids = $_POST['student_ids'] ?? [];
$payment_type = $_POST['payment_type'] ?? '';
$partial_amount = isset($_POST['partial_amount'])
    ? (float)$_POST['partial_amount']
    : 0;

if (empty($student_ids)) {
    die("No students selected.");
}

if ($payment_type !== 'full' && $payment_type !== 'partial') {
    die("Invalid payment type.");
}

if ($payment_type === 'partial' && $partial_amount <= 0) {
    die("Invalid partial payment amount.");
}

$collected_count = 0;

/* Generate one receipt number for complete bulk collection */
$receipt_result = mysqli_query($conn,"SELECT receipt_no FROM fee_payments
    WHERE receipt_no LIKE 'REC%'
    ORDER BY id DESC LIMIT 1");

if ($receipt_result && mysqli_num_rows($receipt_result) > 0) {
    $last_receipt = mysqli_fetch_assoc($receipt_result)['receipt_no'];
    preg_match('/REC(\d+)/',$last_receipt,$matches);
    $last_number = isset($matches[1]) ? (int)$matches[1] : 0;
    $receipt_no = 'REC' . str_pad($last_number + 1,3,'0',STR_PAD_LEFT);
} else {
    $receipt_no = 'REC001';
}

foreach ($student_ids as $student_id) {
    $student_id = (int)$student_id;

    if ($student_id <= 0) {
        continue;
    }

    $payable_sql = "SELECT id,fee_month,fee_year,payable_amount
                    FROM fee_payables
                    WHERE student_id = $student_id
                    AND deleted_at IS NULL
                    AND (
                        fee_year < $year
                        OR (
                            fee_year = $year
                            AND fee_month <= $month
                        )
                    )
                    AND status != 'Paid'
                    ORDER BY fee_year ASC,fee_month ASC";

    $payable_result = mysqli_query($conn,$payable_sql);

    if (!$payable_result) {
        continue;
    }

    $total_remaining = 0;
    $payables = [];

    while ($payable = mysqli_fetch_assoc($payable_result)) {
        $payable_id = (int)$payable['id'];
        $payable_amount = (float)$payable['payable_amount'];

        $payment_sql = "SELECT COALESCE(SUM(amount),0) AS paid_amount
                        FROM fee_payments
                        WHERE fee_payable_id = $payable_id
                        AND deleted_at IS NULL";

        $payment_result = mysqli_query($conn,$payment_sql);

        if (!$payment_result) {
            continue;
        }

        $payment = mysqli_fetch_assoc($payment_result);
        $paid_amount = (float)$payment['paid_amount'];
        $remaining = $payable_amount - $paid_amount;

        if ($remaining <= 0) {
            continue;
        }

        $total_remaining += $remaining;

        $payables[] = [
            'id' => $payable_id,
            'month' => (int)$payable['fee_month'],
            'year' => (int)$payable['fee_year'],
            'payable_amount' => $payable_amount,
            'paid_amount' => $paid_amount,
            'remaining' => $remaining
        ];
    }

    if ($total_remaining <= 0) {
        continue;
    }

    if ($payment_type === 'full') {
        $collect_amount = $total_remaining;
    } else {
        $collect_amount = min($partial_amount,$total_remaining);
    }

    if ($collect_amount <= 0) {
        continue;
    }

    $remaining_payment = $collect_amount;
    $student_collected = false;

    foreach ($payables as $payable) {
        if ($remaining_payment <= 0) {
            break;
        }

        $payable_id = $payable['id'];
        $payable_amount = $payable['payable_amount'];
        $paid_amount = $payable['paid_amount'];
        $month_remaining = $payable['remaining'];

        $payment_for_month = min(
            $remaining_payment,
            $month_remaining
        );

        if ($payment_for_month <= 0) {
            continue;
        }

        $new_paid_amount = $paid_amount + $payment_for_month;

        if ($new_paid_amount >= $payable_amount) {
            $new_status = 'Paid';
        } else {
            $new_status = 'Partial';
        }

        $insert_sql = "INSERT INTO fee_payments
            (
                student_id,
                fee_payable_id,
                receipt_no,
                fee_month,
                fee_year,
                amount,
                discount_amount,
                status,
                payment_date
            )
            VALUES
            (
                $student_id,
                $payable_id,
                '$receipt_no',
                {$payable['month']},
                {$payable['year']},
                $payment_for_month,
                0.00,
                '$new_status',
                CURDATE()
            )";

        $insert_result = mysqli_query($conn,$insert_sql);

        if (!$insert_result) {
            continue;
        }

        $update_sql = "UPDATE fee_payables
                       SET status = '$new_status'
                       WHERE id = $payable_id";

        mysqli_query($conn,$update_sql);

        $remaining_payment -= $payment_for_month;
        $student_collected = true;
    }

    if ($student_collected) {
        $collected_count++;
    }
}

if ($collected_count > 0) {
    header(
        "Location: fee_collection.php?month=$month&year=$year&payment=success&count=$collected_count&receipt=" . urlencode($receipt_no)
    );
} else {
    header(
        "Location: fee_collection.php?month=$month&year=$year&payment=none"
    );
}

exit;