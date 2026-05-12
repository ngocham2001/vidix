<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

// -------------------------------------------------------
// XỬ LÝ GHI NHẬN THANH TOÁN MỚI
// -------------------------------------------------------
if (isset($_POST['submit_payment'])) {
    $contract_id  = (int)$_POST['contract_id'];
    $amount_paid  = (float)$_POST['amount_paid'];
    $payment_date = mysqli_real_escape_string($conn, trim($_POST['payment_date']));

    // Kiểm tra hợp đồng còn hoạt động không
    $cResult = mysqli_query($conn,
        "SELECT * FROM contract WHERE contract_id = $contract_id"
    );
    $contract = mysqli_fetch_assoc($cResult);

    if (!$contract || $contract['status'] === 'cancelled') {
        header('location:Contract_detail.php?id=' . $contract_id . '&fmess=err_cancelled');
        exit;
    }
    if ($contract['first_year_commission_closed']) {
        header('location:Contract_detail.php?id=' . $contract_id . '&fmess=err_closed');
        exit;
    }

    // Tổng đã nộp trong năm đầu trước lần này
    $sumR = mysqli_query($conn,
        "SELECT COALESCE(SUM(amount_paid),0) AS total
         FROM   payment_record
         WHERE  contract_id = $contract_id AND is_first_year = 1"
    );
    $sumRow      = mysqli_fetch_assoc($sumR);
    $prevTotal   = (float)$sumRow['total'];
    $annualValue = (float)$contract['annual_value'];
    $remaining   = $annualValue - $prevTotal;

    if ($remaining <= 0) {
        header('location:Contract_detail.php?id=' . $contract_id . '&fmess=err_full');
        exit;
    }

    // Chỉ tính phần tiền còn thiếu trong năm đầu
    $effective    = min($amount_paid, $remaining);
    $newCumulative= $prevTotal + $effective;

    // commission_unlock_date = payment_date + 22 ngày
    mysqli_query($conn,
        "INSERT INTO payment_record
             (contract_id, payment_date, amount_paid, cumulative_first_year,
              is_first_year, commission_unlock_date)
         VALUES
             ($contract_id,'$payment_date',$effective,$newCumulative,
              1, DATE_ADD('$payment_date', INTERVAL 22 DAY))"
    ) or die(mysqli_error($conn));

    // Kích hoạt hợp đồng nếu còn pending
    if ($contract['status'] === 'pending') {
        mysqli_query($conn,
            "UPDATE contract SET status = 'active', commission_eligible = 1
             WHERE  contract_id = $contract_id"
        );
    }

    // Đóng cờ năm đầu nếu đã đủ
    if ($newCumulative >= $annualValue) {
        mysqli_query($conn,
            "UPDATE contract SET first_year_commission_closed = 1
             WHERE  contract_id = $contract_id"
        );
    }

    header('location:Contract_detail.php?id=' . $contract_id . '&fmess=payment_ok');
    exit;
}

// -------------------------------------------------------
// XỬ LÝ XÓA THANH TOÁN (chỉ xóa nếu chưa tính hoa hồng)
// -------------------------------------------------------
if (isset($_POST['delete_payment'])) {
    $payment_id  = (int)$_POST['payment_id'];
    $contract_id = (int)$_POST['contract_id'];

    $chk = mysqli_query($conn,
        "SELECT commission_processed FROM payment_record WHERE payment_id = $payment_id"
    );
    $pr = mysqli_fetch_assoc($chk);

    if ($pr && $pr['commission_processed'] == 0) {
        mysqli_query($conn,
            "DELETE FROM payment_record WHERE payment_id = $payment_id"
        );
        // Tái tính lại cờ first_year_commission_closed
        mysqli_query($conn,
            "UPDATE contract SET first_year_commission_closed = 0
             WHERE  contract_id = $contract_id"
        );
        header('location:Contract_detail.php?id=' . $contract_id . '&fmess=payment_del');
    } else {
        header('location:Contract_detail.php?id=' . $contract_id . '&fmess=err_processed');
    }
    exit;
}
?>
