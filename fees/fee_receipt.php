
<?php
include '../config/db_config.php';

$receipt_no = $_GET['receipt'] ?? '';

if ($receipt_no === '') {
    die("Invalid receipt.");
}

$receipt_no = mysqli_real_escape_string($conn,$receipt_no);

$sql = "SELECT
            fp.receipt_no,
            fp.payment_date,
            fp.fee_month,
            fp.fee_year,
            fp.amount,
            s.student_name,
            s.sformNo,
            s.stu_class,
            p.amount AS original_amount,
            p.discount_amount,
            p.payable_amount
        FROM fee_payments fp
        INNER JOIN students s ON s.id = fp.student_id
        INNER JOIN fee_payables p ON p.id = fp.fee_payable_id
        WHERE fp.receipt_no = '$receipt_no'
        AND fp.deleted_at IS NULL
        ORDER BY fp.fee_year ASC,fp.fee_month ASC";

$result = mysqli_query($conn,$sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Receipt not found.");
}

$rows = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

$student = $rows[0];

$students = [];

foreach ($rows as $row) {
    $student_key = $row['sformNo'];

    if (!isset($students[$student_key])) {
        $students[$student_key] = [
            'student_name' => $row['student_name'],
            'sformNo' => $row['sformNo'],
            'stu_class' => $row['stu_class']
        ];
    }
}

$is_bulk = count($students) > 1;

$total_paid = 0;
$total_amount = 0;
$total_discount = 0;

foreach ($rows as $row) {
    $total_paid += (float)$row['amount'];

    if ($is_bulk) {
        $total_amount += (float)$row['amount'];
    } else {
        $total_amount += (float)$row['original_amount'];
        $total_discount += (float)$row['discount_amount'];
    }
}

$total_after_discount = $total_amount - $total_discount;

$months = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
];

$payment_date = date('d-M-Y',strtotime($student['payment_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Fee Receipt - <?php echo htmlspecialchars($receipt_no); ?></title>

<style>
*{
    box-sizing:border-box;
}

html,body{
    margin:0;
    padding:0;
    background:#fff;
    color:#000;
    font-family:Arial,sans-serif;
}

.receipt{
    width:80mm;
    margin:0 auto;
    padding:3mm 2.5mm;
    font-size:12px;
}

.academy-name{
    text-align:center;
    font-size:16px;
    font-weight:700;
    margin-bottom:2px;
}

.receipt-title{
    text-align:center;
    font-size:12px;
    font-weight:700;
    margin-bottom:4px;
}

.line{
    border-top:1px dashed #000;
    margin:4px 0;
}

.info{
    line-height:1.45;
}

.info-row{
    display:flex;
    justify-content:space-between;
    gap:6px;
    white-space:nowrap;
}

.info-row span{
    flex:1;
}

.info-row span:last-child{
    text-align:right;
}

.student-list{
    margin-top:2px;
}

.student-row{
    display:flex;
    gap:5px;
    line-height:1.45;
    white-space:nowrap;
}

.student-name{
    flex:1;
    overflow:hidden;
    text-overflow:ellipsis;
}

.student-month{
    width:75px;
    text-align:center;
}

.student-fee{
    width:58px;
    text-align:right;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:1px;
}

.table th,
.table td{
    padding:2px 0;
    font-size:11.5px;
}

.table th{
    font-weight:700;
}

.table th:last-child,
.table td:last-child{
    text-align:right;
}

.total{
    display:flex;
    justify-content:space-between;
    font-size:13px;
    font-weight:700;
    padding:2px 0;
}

@media print{
    @page{
        size:80mm auto;
        margin:0;
    }

    html,body{
        width:80mm;
        margin:0;
        padding:0;
    }

    .receipt{
        width:80mm;
        margin:0;
        padding:3mm 2.5mm;
    }
}
</style>
</head>

<body>

<div class="receipt">

    <div class="academy-name">
        UNIQUE SCIENCE ACADEMY
    </div>

    <div class="receipt-title">
        FEE PAYMENT RECEIPT
    </div>

    <div class="line"></div>

    <div class="info">

        <div class="info-row">
            <span>
                Receipt: <?php echo htmlspecialchars($student['receipt_no']); ?>
            </span>

            <span>
                Date: <?php echo $payment_date; ?>
            </span>
        </div>

        <?php if (!$is_bulk): ?>

            <div class="info-row">
                <span>
                    Student: <?php echo htmlspecialchars($student['student_name']); ?>
                </span>

                <span>
                    Class: <?php echo htmlspecialchars($student['stu_class']); ?>
                </span>
            </div>

        <?php else: ?>

            <div class="student-list">

                <?php foreach ($students as $student_data): ?>

                    <div class="student-row">

                        <span class="student-name">
                            <?php echo htmlspecialchars($student_data['student_name']); ?>
                        </span>

                        <?php
                        $student_rows = array_filter($rows,function($row) use ($student_data){
                            return $row['sformNo'] === $student_data['sformNo'];
                        });
                        ?>

                        <?php foreach ($student_rows as $student_row): ?>

                            <span class="student-month">
                                <?php
                                echo $months[(int)$student_row['fee_month']];
                                ?>
                            </span>

                            <span class="student-fee">
                                Rs. <?php echo number_format((float)$student_row['amount'],2); ?>
                            </span>

                        <?php endforeach; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <?php if (!$is_bulk): ?>

        <div class="line"></div>

        <table class="table">

            <thead>
                <tr>
                    <th style="text-align:left;">MONTH</th>
                    <th>FEE</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($rows as $row): ?>

                    <tr>

                        <td>
                            <?php
                            echo $months[(int)$row['fee_month']] . ' ' . $row['fee_year'];
                            ?>
                        </td>

                        <td>
                            Rs. <?php echo number_format((float)$row['original_amount'],2); ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

    <div class="line"></div>

    <?php if (!$is_bulk): ?>

        <div class="total">
            <span>TOTAL AMOUNT</span>
            <span>Rs. <?php echo number_format($total_amount,2); ?></span>
        </div>

        <?php if ($total_discount > 0): ?>

            <div class="total">
                <span>DISCOUNT</span>
                <span>Rs. <?php echo number_format($total_discount,2); ?></span>
            </div>

            <div class="total">
                <span>AFTER DISCOUNT</span>
                <span>Rs. <?php echo number_format($total_after_discount,2); ?></span>
            </div>

        <?php endif; ?>

        <div class="total">
            <span>AMOUNT PAID</span>
            <span>Rs. <?php echo number_format($total_paid,2); ?></span>
        </div>

    <?php else: ?>

        <div class="total">
            <span>TOTAL AMOUNT</span>
            <span>Rs. <?php echo number_format($total_amount,2); ?></span>
        </div>

        <div class="total">
            <span>AMOUNT PAID</span>
            <span>Rs. <?php echo number_format($total_paid,2); ?></span>
        </div>

    <?php endif; ?>

</div>

<script>
window.onload = function(){
    window.print();
};
</script>

</body>
</html>
