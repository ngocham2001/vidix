<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
include_once PATH_MAIN_FUNCTION . '/pagination.php';
$conn = connection_to_database();

define('AGENT_PER_PAGE', 20);

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
// XỬ LÝ XÓA
// -------------------------------------------------------
if (isset($_POST['delete-submit'])) {
    $agent_id = (int)$_POST['id_delete'];

    // Không cho xóa nếu có nhân viên cấp dưới trực tiếp
    $checkSub = mysqli_query($conn,
        "SELECT agent_id FROM agent WHERE sponsor_agent_id = $agent_id LIMIT 1"
    );
    if (mysqli_num_rows($checkSub) > 0) {
        header('location:' . $_SERVER['PHP_SELF'] . '?fmess=has_sub');
        exit;
    }

    // Không cho xóa nếu đã có hợp đồng
    $checkContract = mysqli_query($conn,
        "SELECT SoHD FROM tbl_hopdong_ttchung
         WHERE agent_id_banhang = $agent_id LIMIT 1"
    );
    if (mysqli_num_rows($checkContract) > 0) {
        header('location:' . $_SERVER['PHP_SELF'] . '?fmess=has_contract');
        exit;
    }

    mysqli_query($conn,
        "DELETE FROM agent_hierarchy
         WHERE ancestor_id = $agent_id OR descendant_id = $agent_id"
    );
    mysqli_query($conn, "DELETE FROM agent WHERE agent_id = $agent_id")
        or die(mysqli_error($conn));

    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=4');
    exit;
}

// -------------------------------------------------------
// DỮ LIỆU PHỤ: dropdown rank & sponsor
// -------------------------------------------------------
$rankOptions = '<option value="">-- Chọn cấp bậc --</option>';
$rankResult  = mysqli_query($conn,
    "SELECT rank_id, rank_code, rank_name
     FROM rank_config ORDER BY rank_id ASC"
);
while ($r = mysqli_fetch_assoc($rankResult)) {
    $rankOptions .= "<option value='{$r['rank_id']}'>"
                  . "{$r['rank_code']} - {$r['rank_name']}</option>";
}
$xhtmlSelectRank = "<select name='current_rank_id' id='new_current_rank_id'
                            class='input-sm' style='width:200px;'>$rankOptions</select>";
$xhtmlSelectRankEdit = "<select name='edit_current_rank_id' id='edit_current_rank_id'
                                class='input-sm' style='width:200px;'>$rankOptions</select>";

$sponsorOptions = '<option value="">-- Chọn NV tuyển dụng --</option>';
$sponsorResult  = mysqli_query($conn,
    "SELECT a.agent_id, a.agent_code, a.full_name, rc.rank_code
     FROM   agent a
     JOIN   rank_config rc ON rc.rank_id = a.current_rank_id
     WHERE  a.status = 'active'
     ORDER  BY rc.rank_id DESC, a.full_name ASC"
);
while ($r = mysqli_fetch_assoc($sponsorResult)) {
    $sponsorOptions .= "<option value='{$r['agent_id']}'>"
                     . "[{$r['rank_code']}] {$r['full_name']}</option>";
}
$xhtmlSelectSponsor = "<select name='sponsor_agent_id' id='new_sponsor_agent_id'
                               class='input-sm' style='width:260px;'>$sponsorOptions</select>";

// -------------------------------------------------------
// XÂY DỰNG BẢNG HIỂN THỊ
// -------------------------------------------------------
// -------------------------------------------------------
// XÂY DỰNG ĐIỀU KIỆN WHERE (dùng chung cho COUNT và SELECT)
// -------------------------------------------------------
$whereAgent = "WHERE 1";

if (isset($_POST['search']) && !empty($_POST['textcond'])) {
    $textcond    = mysqli_real_escape_string($conn, $_POST['textcond']);
    $whereAgent .= " AND (a.full_name  LIKE '%$textcond%'
               OR  a.id_number  LIKE '%$textcond%'
               OR  a.phone      LIKE '%$textcond%'
               OR  a.agent_code LIKE '%$textcond%'
               OR  rc.rank_code LIKE '%$textcond%')";
}
if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') {
    $fStatus     = mysqli_real_escape_string($conn, $_POST['filter_status']);
    $whereAgent .= " AND a.status = '$fStatus'";
}

// -------------------------------------------------------
// PHÂN TRANG: đếm tổng bản ghi
// -------------------------------------------------------
$countSqlAgent = "
    SELECT COUNT(*) AS total
    FROM   agent a
    JOIN   rank_config rc ON rc.rank_id  = a.current_rank_id
    $whereAgent";
$totalRowsAgent = (int)mysqli_fetch_assoc(mysqli_query($conn, $countSqlAgent))['total'];

$requestedPage = isset($_POST['page']) ? (int)$_POST['page'] : 1;
[$currentPage, $totalPages, $offset] = getPaginationParams($totalRowsAgent, $requestedPage, AGENT_PER_PAGE);

// -------------------------------------------------------
// QUERY CHÍNH có LIMIT/OFFSET
// -------------------------------------------------------
$sql = "
    SELECT
        a.*,
        rc.rank_code, rc.rank_name,
        sp.full_name  AS sponsor_name,
        sp.agent_code AS sponsor_code,
        (SELECT COUNT(*)
         FROM agent_hierarchy ah
         WHERE ah.ancestor_id   = a.agent_id
           AND ah.depth         = 1
           AND ah.descendant_id != a.agent_id) AS so_cap_duoi,
        (SELECT COUNT(*)
         FROM tbl_hopdong_ttchung hd
         WHERE hd.agent_id_banhang = a.agent_id
           AND hd.TrangThaiHD = 'Dang_hoat_dong') AS so_hd
    FROM  agent a
    JOIN  rank_config rc ON rc.rank_id  = a.current_rank_id
    LEFT JOIN agent sp   ON sp.agent_id = a.sponsor_agent_id
    $whereAgent
    ORDER BY rc.rank_id DESC, a.join_date ASC
    LIMIT $offset, " . AGENT_PER_PAGE;

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$statusLabels = [
    'active'    => '<span class="label label-success">Đang hoạt động</span>',
    'inactive'  => '<span class="label label-default">Ngừng hoạt động</span>',
    'suspended' => '<span class="label label-danger">Tạm đình chỉ</span>',
];

// -------------------------------------------------------
// RENDER BẢNG
// -------------------------------------------------------
$xhtmlItem = '
<table class="table table-hover table-bordered" id="agent-table">
    <thead>
        <tr class="active">
            <th width="40px">#</th>
            <th width="180px">Họ và tên</th>
            <th width="110px">CCCD</th>
            <th width="100px">Điện thoại</th>
            <th width="90px" class="text-center">Cấp bậc</th>
            <th width="160px">Người tuyển dụng</th>
            <th width="70px" class="text-center">Cấp dưới</th>
            <th width="60px" class="text-center">HĐ</th>
            <th width="95px">Ngày tham gia</th>
            <th width="130px" class="text-center">Trạng thái</th>
            <th width="80px" class="text-center">Thao tác</th>
        </tr>
    </thead>
    <tbody>';

$no = 0;
if (mysqli_num_rows($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $no++;
        $statusBadge    = $statusLabels[$row['status']] ?? $row['status'];
        $sponsorDisplay = $row['sponsor_name']
            ? '[' . htmlspecialchars($row['sponsor_code']) . '] '
              . htmlspecialchars($row['sponsor_name'])
            : '<span class="text-muted"><i>Gốc</i></span>';

        // Nút xem cây — truyền agent_id và tên
        $nameEsc = addslashes($row['full_name']);

        $xhtmlItem .= '
        <tr>
            <td>' . $no . '</td>
            <td>
                <!-- TÊN CÓ THỂ CLICK ĐỂ XEM CÂY ĐA CẤP -->
                <a href="#" class="agent-name-link"
                   onclick="showAgentTree('
                       . $row['agent_id'] . ', \''
                       . $nameEsc . '\'); return false;">
                    <strong>' . htmlspecialchars($row['full_name']) . '</strong>
                </a>
                <br/>
                <small class="text-muted">'
                    . htmlspecialchars($row['agent_code']) . ' &bull; '
                    . htmlspecialchars($row['email'] ?? '')
                . '</small>
            </td>
            <td>' . htmlspecialchars($row['id_number']) . '</td>
            <td>' . htmlspecialchars($row['phone']) . '</td>
            <td class="text-center">
                <strong>' . $row['rank_code'] . '</strong><br/>
                <small class="text-muted">' . htmlspecialchars($row['rank_name']) . '</small>
            </td>
            <td>' . $sponsorDisplay . '</td>
            <td class="text-center">
                <span class="badge">' . $row['so_cap_duoi'] . '</span>
            </td>
            <td class="text-center">
                <span class="badge" style="background:#27ae60;">'
                    . $row['so_hd'] . '</span>
            </td>
            <td>' . $row['join_date'] . '</td>
            <td class="text-center">' . $statusBadge . '</td>
            <td class="text-center">
                <a href="#" class="btn btn-xs btn-warning" onclick="editAgent(
                    '  . $row['agent_id'] . ',
                    \'' . addslashes($row['full_name'])       . '\',
                    \'' . addslashes($row['id_number'])       . '\',
                    \'' . addslashes($row['phone'])           . '\',
                    \'' . addslashes($row['email'] ?? '')     . '\',
                    \'' . addslashes($row['bank_account'] ?? '') . '\',
                    \'' . addslashes($row['bank_name'] ?? '')    . '\',
                    '  . $row['current_rank_id']              . ',
                    \'' . $row['status'] . '\'
                ); return false;" title="Sửa thông tin">
                    <span class="glyphicon glyphicon-pencil"></span>
                </a>
                <a href="#" class="btn btn-xs btn-danger"
                   onclick="delAgent('
                       . $row['agent_id'] . ',\''
                       . addslashes($row['full_name']) . '\'); return false;"
                   title="Xóa">
                    <span class="glyphicon glyphicon-trash"></span>
                </a>
            </td>
        </tr>';
    }
} else {
    $xhtmlItem .= '<tr><td colspan="11" class="text-center text-muted">Chưa có dữ liệu</td></tr>';
}
$xhtmlItem .= '</tbody></table>';
$xhtmlItem .= renderPagination($currentPage, $totalPages, $totalRowsAgent, AGENT_PER_PAGE);
?>