<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

// -------------------------------------------------------
// HELPER: Xây dựng closure table khi thêm nhân viên mới
// -------------------------------------------------------
function buildHierarchy($conn, $newAgentId, $sponsorAgentId) {
    // Self-reference (depth = 0)
    $rankRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT current_rank_id FROM agent WHERE agent_id = $newAgentId"
    ));
    $rankId = (int)$rankRow['current_rank_id'];

    mysqli_query($conn,
        "INSERT INTO agent_hierarchy
             (ancestor_id, descendant_id, depth, senior_rank_at_insert, is_active)
         VALUES ($newAgentId, $newAgentId, 0, $rankId, 1)"
    );

    if (!empty($sponsorAgentId)) {
        // Sao chép toàn bộ tổ tiên của người tuyển, depth + 1
        // senior_rank_at_insert = cấp của ancestor tại thời điểm này
        mysqli_query($conn,
            "INSERT INTO agent_hierarchy
                 (ancestor_id, descendant_id, depth, senior_rank_at_insert, is_active)
             SELECT
                 ah.ancestor_id,
                 $newAgentId,
                 ah.depth + 1,
                 a_anc.current_rank_id,
                 -- is_active = 1 chỉ khi ancestor đang cao hơn newAgent
                 IF(a_anc.current_rank_id > $rankId, 1, 0)
             FROM  agent_hierarchy ah
             JOIN  agent a_anc ON a_anc.agent_id = ah.ancestor_id
             WHERE ah.descendant_id = $sponsorAgentId"
        );
    }
}

// -------------------------------------------------------
// HELPER: Cập nhật is_active sau khi thăng cấp
// -------------------------------------------------------
function updateHierarchyOnRankChange($conn, $agentId, $newRankId) {
    // Bật is_active cho các ancestor mà cấp của họ > cấp mới của agent
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_anc ON a_anc.agent_id = ah.ancestor_id
         SET    ah.is_active = 1
         WHERE  ah.descendant_id = $agentId
           AND  ah.ancestor_id  != $agentId
           AND  ah.is_active     = 0
           AND  a_anc.current_rank_id > $newRankId"
    );

    // Tắt is_active cho các ancestor mà cấp của họ <= cấp mới của agent
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_anc ON a_anc.agent_id = ah.ancestor_id
         SET    ah.is_active = 0
         WHERE  ah.descendant_id = $agentId
           AND  ah.ancestor_id  != $agentId
           AND  ah.is_active     = 1
           AND  a_anc.current_rank_id <= $newRankId"
    );

    // Bật is_active cho các descendant mà cấp của họ < cấp mới của agent
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_desc ON a_desc.agent_id = ah.descendant_id
         SET    ah.is_active = 1
         WHERE  ah.ancestor_id   = $agentId
           AND  ah.descendant_id != $agentId
           AND  ah.is_active      = 0
           AND  a_desc.current_rank_id < $newRankId"
    );

    // Tắt is_active cho các descendant mà cấp của họ >= cấp mới của agent
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_desc ON a_desc.agent_id = ah.descendant_id
         SET    ah.is_active = 0
         WHERE  ah.ancestor_id   = $agentId
           AND  ah.descendant_id != $agentId
           AND  ah.is_active      = 1
           AND  a_desc.current_rank_id >= $newRankId"
    );
}

// -------------------------------------------------------
// XỬ LÝ THÊM MỚI
// -------------------------------------------------------
if (isset($_POST['submit_new'])) {
    $full_name        = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $id_number        = mysqli_real_escape_string($conn, trim($_POST['id_number']));
    $phone            = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $email            = mysqli_real_escape_string($conn, trim($_POST['email']));
    $bank_account     = mysqli_real_escape_string($conn, trim($_POST['bank_account']));
    $bank_name        = mysqli_real_escape_string($conn, trim($_POST['bank_name']));
    $current_rank_id  = (int)$_POST['current_rank_id'];
    $sponsor_agent_id = !empty($_POST['sponsor_agent_id']) ? (int)$_POST['sponsor_agent_id'] : null;
    $join_date        = mysqli_real_escape_string($conn, trim($_POST['join_date']));
    $status           = mysqli_real_escape_string($conn, $_POST['status']);

    // Sinh agent_code tự động: NV + 4 số
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT MAX(CAST(SUBSTRING(agent_code,3) AS UNSIGNED)) AS max_num FROM agent"
    ));
    $nextNum    = ($r['max_num'] ?? 0) + 1;
    $agent_code = 'NV' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    // Kiểm tra trùng CCCD
    $check = mysqli_query($conn,
        "SELECT agent_id FROM agent WHERE id_number = '$id_number'"
    );
    if (mysqli_num_rows($check) > 0) {
        header('location:' . $_SERVER['PHP_SELF'] . '?fmess=duplicate');
        exit;
    }

    $sponsorSql = $sponsor_agent_id ? $sponsor_agent_id : 'NULL';
    $sql = "INSERT INTO agent
                (agent_code, full_name, id_number, phone, email,
                 bank_account, bank_name, current_rank_id,
                 sponsor_agent_id, join_date, status)
            VALUES
                ('$agent_code','$full_name','$id_number','$phone','$email',
                 '$bank_account','$bank_name',$current_rank_id,
                 $sponsorSql,'$join_date','$status')";

    mysqli_query($conn, $sql) or die(mysqli_error($conn));
    $newId = mysqli_insert_id($conn);

    buildHierarchy($conn, $newId, $sponsor_agent_id);

    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=1');
    exit;
}

// -------------------------------------------------------
// XỬ LÝ CẬP NHẬT
// -------------------------------------------------------
if (isset($_POST['submit_edit'])) {
    $agent_id        = (int)$_POST['edit_agent_id'];
    $full_name       = mysqli_real_escape_string($conn, trim($_POST['edit_full_name']));
    $phone           = mysqli_real_escape_string($conn, trim($_POST['edit_phone']));
    $email           = mysqli_real_escape_string($conn, trim($_POST['edit_email']));
    $bank_account    = mysqli_real_escape_string($conn, trim($_POST['edit_bank_account']));
    $bank_name       = mysqli_real_escape_string($conn, trim($_POST['edit_bank_name']));
    $current_rank_id = (int)$_POST['edit_current_rank_id'];
    $status          = mysqli_real_escape_string($conn, $_POST['edit_status']);

    // Lấy cấp cũ để kiểm tra có thay đổi không
    $oldRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT current_rank_id FROM agent WHERE agent_id = $agent_id"
    ));
    $oldRankId = (int)$oldRow['current_rank_id'];

    $sql = "UPDATE agent SET
                full_name       = '$full_name',
                phone           = '$phone',
                email           = '$email',
                bank_account    = '$bank_account',
                bank_name       = '$bank_name',
                current_rank_id = $current_rank_id,
                status          = '$status'
            WHERE agent_id = $agent_id";

    mysqli_query($conn, $sql) or die(mysqli_error($conn));

    // Nếu cấp bậc thay đổi → cập nhật is_active trong hierarchy
    if ($oldRankId !== $current_rank_id) {
        updateHierarchyOnRankChange($conn, $agent_id, $current_rank_id);
    }

    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=2');
    exit;
}

// -------------------------------------------------------
// XỬ LÝ HỦY HỢP ĐỒNG
// -------------------------------------------------------
if (isset($_POST['delete-submit'])) {

    $ngay_huy_hd = intval($_POST['ngay_huy_hd']);
    $idDelete = intval($_POST['id_delete']);
	list($mm, $dd, $yyyy) = explode('-', $ngay_huy_hd);
	$ngayhuyHD = $yyyy . '-' . $mm . '-' . $dd;

    if ($idDelete > 0) {
        $sql = "
            UPDATE tbl_hopdong_ttchung
            SET
                TrangThaiHDCho = 0,
                TrangThaiHD = 'Da_huy_trong_21_ngay',
                NgayHuyHD = '".$ngayhuyHD."'
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idDelete);

        if (mysqli_stmt_execute($stmt)) {
            // ✅ Hủy thành công
            echo "<script>
                alert('Hủy hợp đồng thành công!');
                window.location.href = window.location.href;
            </script>";
        } else {
            // ❌ Lỗi SQL
            echo "<script>
                alert('Có lỗi khi hủy hợp đồng. Vui lòng thử lại!');
            </script>";
        }

        mysqli_stmt_close($stmt);
    }
}

// -------------------------------------------------------
// XÂY DỰNG BẢNG HIỂN THỊ
// -------------------------------------------------------
$sql = "SELECT
		hd.id,
		kh.`MaKH`,
		hd.`LoaiHD`,
		hd.`NgayNopTien1`,
		hd.SoDVTC * hd.SonamHD * 1260000 AS trigia_hd,
		CASE
			WHEN DATEDIFF(NOW(), hd.NgayNopTien1) >= 21 THEN 'du_dk'
			ELSE 'chua_du'
		END AS status_cho,
		hd.`maNV_nhap`,
		hd.`fullname_NVnhap`,
		hd.`maNV_banhang`,
		hd.`KB`,
		hd.`Iv`,
		hd.`HSs`,
		hd.`KB`,
		a.id_number,
		kh.CCCD,
		a.full_name
	FROM
		`tbl_hopdong_ttchung` hd
		inner join `tbl_khachhang` kh on kh.id = hd.khachhang_id
		inner join `agent` a on a.agent_id = hd.agent_id_banhang
	WHERE hd.TrangThaiHDCho = 1
";

if (isset($_POST['search']) && !empty($_POST['textcond'])) {
    $textcond = mysqli_real_escape_string($conn, $_POST['textcond']);
    $sql .= " AND (MaKH  LIKE '%$textcond%'
               OR  KB LIKE '%$textcond%'
               OR  maNV_banhang LIKE '%$textcond%'
			   OR  CCCD LIKE '%$textcond%'
			   OR  id_number LIKE '%$textcond%'
               OR  HSs LIKE '%$textcond%'
               OR  KB LIKE '%$textcond%')";
}
if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') {
    $fStatus = mysqli_real_escape_string($conn, $_POST['filter_status']);
	if ($fStatus === 'du_dk')  $sql .= " AND DATEDIFF(NOW(), NgayNopTien1) >= 21";
	if ($fStatus =='chua_du')  $sql .= " AND DATEDIFF(NOW(), NgayNopTien1) < 21";
}
$sql .= " ORDER BY NgayNopTien1 ASC";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$statusLabels = [
    'du_dk'    => '<span class="label label-success">Đủ 21 ngày</span>',
    'chua_du'  => '<span class="label label-default">Chưa đủ 21 ngày</span>',
];

// -------------------------------------------------------
// RENDER BẢNG
// -------------------------------------------------------
$xhtmlItem = '
<table class="table table-hover table-bordered" id="agent-table">
    <thead>
        <tr class="active">
            <th width="40px">#</th>
            <th width="350px">Thông tin HĐ</th>
            <th width="150px">Thông tin KH</th>
            <th width="100px">Ngày nộp tiền</th>
            <th width="120px">Status</th>
            <th width="310px">Thông tin NV</th>
            <th width="80px" class="text-center">Thao tác</th>
        </tr>
    </thead>
    <tbody>';

$no = 0;
if (mysqli_num_rows($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $no++;
        $statusBadge    = $statusLabels[$row['status_cho']] ?? $row['status_cho'];
        $xhtmlItem .= '
        <tr>
            <td>' . $no . '</td>
            <td>              
                <strong>KB: ' . htmlspecialchars($row['KB']) . '</strong> ('.htmlspecialchars($row['LoaiHD']).' 
					) - Trị giá: '.htmlspecialchars(number_format($row['trigia_hd'])).'  
                <br/>
                <small class="text-muted">';
		if(!empty($row['KB'])) $xhtmlItem.='KB: '.htmlspecialchars($row['KB']); else $xhtmlItem.='KB: NULL'; 
		if(!empty($row['HSs'])) $xhtmlItem.=' &bull; HSs: '.htmlspecialchars($row['HSs']); else $xhtmlItem.=' &bull; HSs: NULL'; 
                    
        $xhtmlItem .= '</small>
            </td>
            <td>' . htmlspecialchars($row['MaKH']);
		if($row['id_number'] === $row['CCCD']){
			$xhtmlItem .= '<br/> <span class = "text-success">(KH đăng ký TC B)</span>';
		}
		$xhtmlItem .= '</td>
            <td>' . htmlspecialchars($row['NgayNopTien1']) . '</td>
            <td class="text-center" style="padding-top:15px;">' . $statusBadge . '</td>
			<td>
				NVBH:'.htmlspecialchars($row['maNV_banhang']).' - '.htmlspecialchars($row['full_name']).'
				<br/><small class = "text-muted">NVNL: '.htmlspecialchars($row['maNV_nhap']).' - '.htmlspecialchars($row['fullname_NVnhap']).'</small>
			</td>
            <td class="text-center action-cell">';
		if($row['status_cho'] ==='du_dk'){
			$xhtmlItem .= '<a href="#" class="btn btn-xs btn-warning action-btn" title="Chuyển chính thức">
						<span class="glyphicon glyphicon-share-alt text-primary"></span>
							</a> &nbsp;';
		}
        $xhtmlItem .= '<a href="#" class="btn btn-xs btn-danger action-btn"
                   onclick="CancelContract(' . $row['id'] . '); return false;" title="Hủy hợp đồng">
                    <span class="glyphicon glyphicon-remove-sign"></span>
					   </a>
					</td>
				</tr>';
    }
} else {
    $xhtmlItem .= '<tr><td colspan="11" class="text-center text-muted">Chưa có dữ liệu</td></tr>';
}
$xhtmlItem .= '</tbody></table>';
?>