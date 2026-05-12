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
$sql = "SELECT b.`condition_id`, a1.`rank_code` as from_rank_code, a2.`rank_code` as to_rank_code, b.`min_points_required`, b.`min_direct_agents`, b.`min_sub_agents`, b.`effective_date`, b.`created_at` FROM `rank_upgrade_condition` b left join rank_config a1 on b.from_rank_id = a1.rank_id left join rank_config a2 on b.to_rank_id = a2.rank_id";
if (isset($_POST['search']) && !empty($_POST['textcond'])) {
    $textcond = mysqli_real_escape_string($conn, $_POST['textcond']);
    $sql .= " WHERE rank_code LIKE '%$textcond%' OR rank_name LIKE '%$textcond%'";
}
$sql .= " ORDER BY from_rank_id ASC, effective_date desc";

$result  = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$xhtmlItem = '
<table class="table table-hover table-bordered" id="rank-table">
    <thead>
        <tr class="active">
            <th width="50px">#</th>
            <th width="80px">Từ cấp</th>
            <th width="80px">Lên cấp</th>
            <th width="110px">Số điểm <br/> tối thiểu</th>
            <th width="140px">SL nhân viên <br/>trực tiếp tối thiểu</th>
            <th>Áp dụng từ ngày</th>
            <th width="100px">Thao tác</th>
        </tr>
    </thead>
    <tbody>';

$no = 0;
if (mysqli_num_rows($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $no++;
        $min_points_required       = number_format($row['min_points_required']);
        $min_direct_agents       = number_format($row['min_direct_agents']);
        $min_sub_agents       = number_format($row['min_sub_agents']);
		
        $xhtmlItem .= '
        <tr>
            <td>' . $no . '</td>
            <td><strong>' . $row['from_rank_code'] . '</strong></td>
            <td><span class="strong-text">' . $row['to_rank_code'] . '</span></td>
            <td class="text-right"><strong class="text-primary">'.$min_points_required.'</strong></td>
            <td class="text-center">' . $min_direct_agents . '</td>
            <td><small>' . $row['effective_date'] . '</small></td>
            <td>
                <a href="#" class="btn btn-xs btn-warning" onclick="editRank(
                    ' . $row['condition_id'] . '
                );">Sửa</a>
                <a href="#" class="btn btn-xs btn-danger" onclick="delRank(' . $row['condition_id'] . ');">Xóa</a>
            </td>
        </tr>';
    }
} else {
    $xhtmlItem .= '<tr><td colspan="8" class="text-center text-muted">Chưa có dữ liệu</td></tr>';
}

$xhtmlItem .= '</tbody></table>';
?>

                  