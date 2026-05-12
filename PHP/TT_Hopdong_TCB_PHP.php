<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

// -------------------------------------------------------
// HELPER: Xây dựng closure table khi thêm nhân viên mới
// -------------------------------------------------------
function buildHierarchy($conn, $newAgentId, $sponsorAgentId) {
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
        mysqli_query($conn,
            "INSERT INTO agent_hierarchy
                 (ancestor_id, descendant_id, depth, senior_rank_at_insert, is_active)
             SELECT
                 ah.ancestor_id,
                 $newAgentId,
                 ah.depth + 1,
                 a_anc.current_rank_id,
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
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_anc ON a_anc.agent_id = ah.ancestor_id
         SET    ah.is_active = 1
         WHERE  ah.descendant_id = $agentId
           AND  ah.ancestor_id  != $agentId
           AND  ah.is_active     = 0
           AND  a_anc.current_rank_id > $newRankId"
    );
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_anc ON a_anc.agent_id = ah.ancestor_id
         SET    ah.is_active = 0
         WHERE  ah.descendant_id = $agentId
           AND  ah.ancestor_id  != $agentId
           AND  ah.is_active     = 1
           AND  a_anc.current_rank_id <= $newRankId"
    );
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_desc ON a_desc.agent_id = ah.descendant_id
         SET    ah.is_active = 1
         WHERE  ah.ancestor_id   = $agentId
           AND  ah.descendant_id != $agentId
           AND  ah.is_active      = 0
           AND  a_desc.current_rank_id < $newRankId"
    );
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

    $checkSub = mysqli_query($conn,
        "SELECT agent_id FROM agent WHERE sponsor_agent_id = $agent_id LIMIT 1"
    );
    if (mysqli_num_rows($checkSub) > 0) {
        header('location:' . $_SERVER['PHP_SELF'] . '?fmess=has_sub');
        exit;
    }

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
$sql = "
    SELECT
        hd.Iv,
        hd.SoHD,
        hd.KB                                    AS KyHieuHD,
        hd.LoaiHD,
        hd.TrangThaiHDcho,
        hd.TrangThaiHD,
        hd.NgayNopTien1,
        (hd.SoDVTC * hd.SonamHD * 105000 * 12)  AS TriGiaHD,
        hd.SoDVTC,
        hd.SonamHD,
        COALESCE(hd_sp.SoHD, hd_sp.Iv)          AS so_hd_nguoi_tuyen,
        a.agent_id,
        a.phone,
        a.id_number,
        a.agent_code                             AS MaNVBanHang,
        a.full_name                              AS TenNVBanHang,
        rc.rank_code                             AS CapBac,
        rc.rank_name                             AS TenCapBac,
        sp.full_name                             AS sponsor_name,
        sp.agent_code                            AS sponsor_code,
        sp.agent_id                              AS sponsor_agent_id,
        (SELECT COUNT(*)
             FROM agent_hierarchy ah
             WHERE ah.ancestor_id   = a.agent_id
               AND ah.depth        >= 1
               AND ah.descendant_id != a.agent_id) AS so_cap_duoi,
        (SELECT COUNT(*)
             FROM tbl_hopdong_ttchung hd2
             WHERE hd2.agent_id_banhang = a.agent_id
               AND hd2.TrangThaiHD = 'Dang_hoat_dong') AS so_hd
    FROM tbl_hopdong_ttchung hd
    LEFT JOIN agent a           ON a.agent_id      = hd.agent_id_banhang
    LEFT JOIN rank_config rc    ON rc.rank_id       = a.current_rank_id
    LEFT JOIN agent sp          ON sp.agent_id      = a.sponsor_agent_id
    LEFT JOIN tbl_hopdong_ttchung hd_sp
              ON  hd_sp.agent_id_banhang = sp.agent_id
              AND hd_sp.HDTuychonB       = 1
    WHERE hd.HDTuychonB = 1
";

if (isset($_POST['search']) && !empty($_POST['textcond'])) {
    $textcond = mysqli_real_escape_string($conn, $_POST['textcond']);
    $sql .= " AND (hd.SoHD LIKE '%$textcond%'
               OR  hd.Iv   LIKE '%$textcond%'
               OR  hd.KB   LIKE '%$textcond%')";
}
$sql .= " ORDER BY hd.NgayNopTien1 DESC";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

// -------------------------------------------------------
// RENDER BẢNG
// -------------------------------------------------------
$xhtmlItem = '
<table class="table table-hover table-bordered" id="agent-table">
    <thead>
        <tr class="active">
            <th width="40px">#</th>
            <th width="260px">Thông tin hợp đồng</th>
            <th width="230px">Thông tin nhân viên</th>
            <th width="430px">Thông tin cấp dưới &amp; điểm thưởng</th>
        </tr>
    </thead>
    <tbody>';

$no = 0;
if (mysqli_num_rows($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $no++;

        // ---------------------------------------------------
        // Hiển thị số HĐ của chính nhân viên này (HĐ tùy chọn B)
        // ---------------------------------------------------
        $soHDhienthi = !empty($row['SoHD']) ? htmlspecialchars($row['SoHD'])
                                             : htmlspecialchars($row['Iv']);

        // Label trạng thái
        $hdStatusLabel = '';
        if (empty($row['SoHD']) && $row['TrangThaiHDcho'] === '1') {
            $hdStatusLabel = ' <small>(<em class="text-danger">HĐ chờ</em>)</small>';
        }

        // ---------------------------------------------------
        // Số HĐ B của người tuyển dụng (sponsor) — hiển thị, click xem cây CỦA SPONSOR
        // ---------------------------------------------------
        $sponsorDisplay = '';
        if (!empty($row['so_hd_nguoi_tuyen'])) {
            // Click mở cây của SPONSOR (dùng sponsor_agent_id)
            $sponsorAgentIdEsc = (int)$row['sponsor_agent_id'];
            $sponsorHdEsc      = addslashes($row['so_hd_nguoi_tuyen']);
            $sponsorDisplay    = '<a href="#" class="agent-name-link"
                   onclick="showAgentTree(' . $sponsorAgentIdEsc . ', \''
                   . $sponsorHdEsc . '\'); return false;">'
                   . '<span class="text-muted">' . htmlspecialchars($row['so_hd_nguoi_tuyen']) . '</span>'
                   . '</a>';
        } else {
            $sponsorDisplay = '<span class="text-muted"><em>NULL</em></span>';
        }

        // ---------------------------------------------------
        // Click vào số HĐ của chính họ → mở cây của CHÍNH HỌ (dùng agent_id)
        // ---------------------------------------------------
        $agentIdEsc  = (int)$row['agent_id'];
        $soHDEscJs   = addslashes(!empty($row['SoHD']) ? $row['SoHD'] : $row['Iv']);

        $xhtmlItem .= '
        <tr>
            <td>' . $no . '</td>
            <td>
                <!-- SỐ HĐ CỦA CHÍNH HỌ — click mở cây của chính họ -->
                <a href="#" class="agent-name-link"
                   onclick="showAgentTree(' . $agentIdEsc . ', \'' . $soHDEscJs . '\'); return false;">
                    <strong>' . $soHDhienthi . '</strong>
                </a>' . $hdStatusLabel . '
                &bull;
                <span title="HĐ người tuyển dụng">&#x1fab5;: ' . $sponsorDisplay . '</span>
                <br/>'
                . htmlspecialchars($row['LoaiHD'])
                . '<small class="text-muted"> (Trị giá: '
                . htmlspecialchars(number_format($row['TriGiaHD'])) . ' đ)';

        if ($row['LoaiHD'] == 'A') {
            $xhtmlItem .= ' &bull; ' . htmlspecialchars($row['SonamHD']) . ' năm';
        }

        $xhtmlItem .= '</small>
            </td>
            <td>
                <strong>' . htmlspecialchars($row['MaNVBanHang']) . '</strong>
                &bull; ' . htmlspecialchars($row['TenNVBanHang']) . '
                <br/>' . htmlspecialchars($row['CapBac'])
                . ' <small class="text-muted">&bull; SĐT: '
                . htmlspecialchars($row['phone']) . '</small>
            </td>
            <td>
                TV tuyến dưới: <strong>' . $row['so_cap_duoi'] . '</strong>
                &bull; HĐ đã ký: <strong>' . $row['so_hd'] . '</strong>
            </td>
        </tr>';
    }
} else {
    $xhtmlItem .= '<tr><td colspan="4" class="text-center text-muted">Chưa có dữ liệu</td></tr>';
}

$xhtmlItem .= '</tbody></table>';
?>
