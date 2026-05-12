<?php
/**
 * getAgentTree.php
 * Trả về JSON chứa toàn bộ cây đa cấp của 1 nhân viên:
 *   - sponsor: người tuyển dụng ra họ
 *   - self:    thông tin bản thân
 *   - tree:    danh sách cấp dưới (đã sắp xếp theo depth, rank)
 */
session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

header('Content-Type: application/json; charset=utf-8');

$agent_id = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;
if ($agent_id <= 0) {
    echo json_encode(['error' => 'Thiếu agent_id']);
    exit;
}

// -------------------------------------------------------
// 1. Thông tin bản thân
// -------------------------------------------------------
$sqlSelf = "
    SELECT a.agent_id, a.agent_code, a.full_name, a.phone, a.email,
           a.join_date, a.status,
           rc.rank_id, rc.rank_code, rc.rank_name,
           sp.agent_id   AS sponsor_id,
           sp.full_name  AS sponsor_name,
           sp.agent_code AS sponsor_code,
           sp_rc.rank_code AS sponsor_rank_code,
           -- Đếm HĐ đang hoạt động
           (SELECT COUNT(*) FROM tbl_hopdong_ttchung hd
            WHERE hd.agent_id_banhang = a.agent_id
              AND hd.TrangThaiHD = 'Đang hoạt động') AS so_hd,
           -- Đếm trực tiếp cấp dưới
           (SELECT COUNT(*) FROM agent_hierarchy ah
            WHERE ah.ancestor_id = a.agent_id AND ah.depth = 1
              AND ah.descendant_id != a.agent_id) AS so_cap_duoi_tt
    FROM  agent a
    JOIN  rank_config rc ON rc.rank_id = a.current_rank_id
    LEFT JOIN agent sp       ON sp.agent_id = a.sponsor_agent_id
    LEFT JOIN rank_config sp_rc ON sp_rc.rank_id = sp.current_rank_id
    WHERE a.agent_id = $agent_id
";
$selfRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlSelf));
if (!$selfRow) {
    echo json_encode(['error' => 'Không tìm thấy nhân viên']);
    exit;
}

// -------------------------------------------------------
// 2. Toàn bộ cấp dưới qua closure table (depth >= 1)
//    Dùng senior_rank_at_insert để biết trạng thái is_active
// -------------------------------------------------------
$sqlTree = "
    SELECT
        a.agent_id,
        a.agent_code,
        a.full_name,
        a.phone,
        a.join_date,
        a.status,
        rc.rank_id,
        rc.rank_code,
        rc.rank_name,
        ah.depth,
        ah.is_active,
        -- Người tuyển trực tiếp của người này (depth=1 từ phía họ)
        sp.full_name  AS sponsor_name,
        sp.agent_code AS sponsor_code,
        -- Số HĐ đang hoạt động
        (SELECT COUNT(*) FROM tbl_hopdong_ttchung hd
         WHERE hd.agent_id_banhang = a.agent_id
           AND hd.TrangThaiHD = 'Đang hoạt động') AS so_hd,
        -- Số cấp dưới trực tiếp của người này
        (SELECT COUNT(*) FROM agent_hierarchy ah2
         WHERE ah2.ancestor_id = a.agent_id
           AND ah2.depth = 1
           AND ah2.descendant_id != a.agent_id) AS so_cap_duoi
    FROM  agent_hierarchy ah
    JOIN  agent a        ON a.agent_id  = ah.descendant_id
    JOIN  rank_config rc ON rc.rank_id  = a.current_rank_id
    LEFT JOIN agent sp   ON sp.agent_id = a.sponsor_agent_id
    WHERE ah.ancestor_id  = $agent_id
      AND ah.descendant_id != $agent_id
      AND ah.depth >= 1
    ORDER BY ah.depth ASC, rc.rank_id DESC, a.join_date ASC
";
$treeResult = mysqli_query($conn, $sqlTree);
$treeData   = [];
while ($r = mysqli_fetch_assoc($treeResult)) {
    $treeData[] = $r;
}

// -------------------------------------------------------
// 3. Thống kê tổng hợp cây
// -------------------------------------------------------
$stats = [
    'tong_cap_duoi' => count($treeData),
    'tong_hd'       => 0,
    'phan_bo_cap'   => [],
];
foreach ($treeData as $node) {
    $stats['tong_hd'] += (int)$node['so_hd'];
    $code = $node['rank_code'];
    if (!isset($stats['phan_bo_cap'][$code])) {
        $stats['phan_bo_cap'][$code] = [
            'rank_code' => $code,
            'rank_name' => $node['rank_name'],
            'count'     => 0,
        ];
    }
    $stats['phan_bo_cap'][$code]['count']++;
}
// Thêm HĐ của bản thân vào tổng
$stats['tong_hd'] += (int)$selfRow['so_hd'];

echo json_encode([
    'self'  => $selfRow,
    'tree'  => $treeData,
    'stats' => $stats,
]);
