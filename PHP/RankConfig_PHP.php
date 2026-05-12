<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

// -------------------------------------------------------
// XỬ LÝ THÊM MỚI
// -------------------------------------------------------
if (isset($_POST['submit_new'])) {
    $rank_code              = mysqli_real_escape_string($conn, trim($_POST['rank_code']));
    $rank_name              = mysqli_real_escape_string($conn, trim($_POST['rank_name']));
    $commission_rate        = (float) $_POST['commission_rate'] / 100; // nhập % → lưu thập phân
    $is_specialist          = isset($_POST['is_specialist']) ? 1 : 0;
    $monthly_salary_eligible= isset($_POST['monthly_salary_eligible']) ? 1 : 0;
    $description            = mysqli_real_escape_string($conn, trim($_POST['description']));

    // Kiểm tra trùng rank_code
    $check = mysqli_query($conn, "SELECT rank_id FROM rank_config WHERE rank_code = '$rank_code'");
    if (mysqli_num_rows($check) > 0) {
        header('location:' . $_SERVER['PHP_SELF'] . '?fmess=duplicate');
        exit;
    }

    $sql = "INSERT INTO rank_config
                (rank_code, rank_name, commission_rate, is_specialist, monthly_salary_eligible, description)
            VALUES
                ('$rank_code','$rank_name',$commission_rate,$is_specialist,$monthly_salary_eligible,'$description')";
    mysqli_query($conn, $sql) or die(mysqli_error($conn));
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=1');
    exit;
}

// -------------------------------------------------------
// XỬ LÝ CẬP NHẬT
// -------------------------------------------------------
if (isset($_POST['submit_edit'])) {
    $rank_id                = (int) $_POST['edit_rank_id'];
    $rank_name              = mysqli_real_escape_string($conn, trim($_POST['edit_rank_name']));
    $commission_rate        = (float) $_POST['edit_commission_rate'] / 100;
    $is_specialist          = isset($_POST['edit_is_specialist']) ? 1 : 0;
    $monthly_salary_eligible= isset($_POST['edit_monthly_salary_eligible']) ? 1 : 0;
    $description            = mysqli_real_escape_string($conn, trim($_POST['edit_description']));

    $sql = "UPDATE rank_config SET
                rank_name               = '$rank_name',
                commission_rate         = $commission_rate,
                is_specialist           = $is_specialist,
                monthly_salary_eligible = $monthly_salary_eligible,
                description             = '$description'
            WHERE rank_id = $rank_id";
    mysqli_query($conn, $sql) or die(mysqli_error($conn));
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=2');
    exit;
}

// -------------------------------------------------------
// XỬ LÝ XÓA
// -------------------------------------------------------
if (isset($_POST['delete-submit'])) {
    $rank_id = (int) $_POST['id_delete'];
    // Kiểm tra có nhân viên nào đang dùng cấp này không
    $checkUsed = mysqli_query($conn, "SELECT agent_id FROM agent WHERE current_rank_id = $rank_id LIMIT 1");
    if (mysqli_num_rows($checkUsed) > 0) {
        header('location:' . $_SERVER['PHP_SELF'] . '?fmess=used');
        exit;
    }
    mysqli_query($conn, "DELETE FROM rank_config WHERE rank_id = $rank_id") or die(mysqli_error($conn));
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=4');
    exit;
}

// -------------------------------------------------------
// XÂY DỰNG BẢNG DỮ LIỆU HIỂN THỊ
// -------------------------------------------------------
$sql = "SELECT cdr.`rank_id`, `rank_code`, `rank_name`, `rate`, `is_specialist`, `monthly_salary_eligible`, `description` FROM rank_config rc inner join commission_direct_rate cdr on cdr.rank_id = rc.rank_id where cdr.effective_from = (select max(cdr2.effective_from) from commission_direct_rate cdr2 where cdr.rank_id = cdr2.rank_id)";
if (isset($_POST['search']) && !empty($_POST['textcond'])) {
    $textcond = mysqli_real_escape_string($conn, $_POST['textcond']);
    $sql .= " WHERE rank_code LIKE '%$textcond%' OR rank_name LIKE '%$textcond%'";
}
$sql .= "order by cdr.effective_from desc";

$result  = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$xhtmlItem = '
<table class="table table-hover table-bordered" id="rank-table">
    <thead>
        <tr class="active">
            <th width="50px">#</th>
            <th width="60px">Mã cấp</th>
            <th width="160px">Tên cấp bậc</th>
            <th width="110px">Hoa hồng (%)</th>
            <th width="110px">Chuyên viên</th>
            <th width="120px">Hưởng lương</th>
            <th>Mô tả quyền lợi</th>
            <th width="100px">Thao tác</th>
        </tr>
    </thead>
    <tbody>';

$no = 0;
if (mysqli_num_rows($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $no++;
        $rate_display       = number_format($row['rate'] * 100, 2) . '%';
        $specialist_badge   = $row['is_specialist']
            ? '<span class="label label-success">Có</span>'
            : '<span class="label label-default">Không</span>';
        $salary_badge       = $row['monthly_salary_eligible']
            ? '<span class="label label-info">Có</span>'
            : '<span class="label label-default">Không</span>';
        $desc               = htmlspecialchars($row['description'] ?? '');

        $xhtmlItem .= '
        <tr>
            <td>' . $no . '</td>
            <td><strong>' . $row['rank_code'] . '</strong></td>
            <td>' . htmlspecialchars($row['rank_name']) . '</td>
            <td class="text-center"><strong class="text-primary">' . $rate_display . '</strong></td>
            <td class="text-center">' . $specialist_badge . '</td>
            <td class="text-center">' . $salary_badge . '</td>
            <td><small>' . $desc . '</small></td>
            <td>
                <a href="#" class="btn btn-xs btn-warning" onclick="editRank(
                    ' . $row['rank_id'] . ',
                    \'' . addslashes($row['rank_code']) . '\',
                    \'' . addslashes($row['rank_name']) . '\',
                    ' . ($row['rate'] * 100) . ',
                    ' . $row['is_specialist'] . ',
                    ' . $row['monthly_salary_eligible'] . ',
                    \'' . addslashes($row['description'] ?? '') . '\'
                );">Sửa</a>
                <a href="#" class="btn btn-xs btn-danger" onclick="delRank(' . $row['rank_id'] . ',\'' . addslashes($row['rank_code']) . '\');">Xóa</a>
            </td>
        </tr>';
    }
} else {
    $xhtmlItem .= '<tr><td colspan="8" class="text-center text-muted">Chưa có dữ liệu</td></tr>';
}

$xhtmlItem .= '</tbody></table>';
?>
