<?php
/**
 * VIDIX_function/API_getAgentTree.php
 * API endpoint để lấy dữ liệu cây agent drill-down
 */

session_start();

$rootPath = dirname(__DIR__);
include_once $rootPath . '/define.php';
include_once $rootPath . '/functions/conn-login-logout.php';

header('Content-Type: application/json; charset=utf-8');

// ===== KIỂM TRA SESSION - BẢO MẬT =====
if (!isset($_SESSION['user_info']['logon_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized - Please login first']));
}

// Validate input
if (!isset($_GET['agent_id']) || !is_numeric($_GET['agent_id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing or invalid agent_id']));
}

$agent_id = (int)$_GET['agent_id'];
$conn = connection_to_database();

try {
    // ===== BƯỚC 1: Lấy thông tin agent hiện tại =====
    $agentQuery = "SELECT 
        a.agent_id, a.agent_code, a.full_name, a.current_rank_id,
        rc.rank_code, rc.rank_name
    FROM agent a
    JOIN rank_config rc ON rc.rank_id = a.current_rank_id
    WHERE a.agent_id = $agent_id";
    
    $agentResult = mysqli_query($conn, $agentQuery);
    if (!$agentResult || mysqli_num_rows($agentResult) === 0) {
        http_response_code(404);
        die(json_encode(['error' => 'Agent not found']));
    }
    
    $agentRow = mysqli_fetch_assoc($agentResult);
    
    // ===== BƯỚC 2: Lấy tất cả cấp dưới trực tiếp (depth = 1) =====
    $subAgentsQuery = "SELECT DISTINCT
        a.agent_id, 
        a.full_name, 
        a.agent_code, 
        a.current_rank_id,
        rc.rank_code, 
        rc.rank_name,
        (SELECT COUNT(*) FROM tbl_hopdong_ttchung hd
         WHERE hd.agent_id_banhang = a.agent_id
         AND hd.TrangThaiHD = 'Dang_hoat_dong') AS so_hd,
        (SELECT COUNT(*) FROM agent_hierarchy ah
         WHERE ah.ancestor_id = a.agent_id AND ah.depth = 1
         AND ah.descendant_id != a.agent_id) AS so_cap_duoi
    FROM agent_hierarchy ah
    JOIN agent a ON a.agent_id = ah.descendant_id
    JOIN rank_config rc ON rc.rank_id = a.current_rank_id
    WHERE ah.ancestor_id = $agent_id 
    AND ah.depth = 1
    AND ah.descendant_id != $agent_id
    ORDER BY rc.rank_id DESC, a.full_name ASC";
    
    $subAgents = [];
    $subAgentsResult = mysqli_query($conn, $subAgentsQuery);
    if ($subAgentsResult) {
        while ($row = mysqli_fetch_assoc($subAgentsResult)) {
            $subAgents[] = $row;
        }
    }
    
    // ===== BƯỚC 3: Lấy điều kiện thăng cấp của agent hiện tại =====
    $currentRank = (int)$agentRow['current_rank_id'];
    $upgradeCondition = null;
    
    if ($currentRank < 8) {
        $upgQuery = "SELECT * FROM rank_upgrade_condition
        WHERE from_rank_id = $currentRank
        AND effective_date <= CURDATE()
        ORDER BY effective_date DESC LIMIT 1";
        
        $upgResult = mysqli_query($conn, $upgQuery);
        
        if ($upgResult && mysqli_num_rows($upgResult) > 0) {
            $upgRow = mysqli_fetch_assoc($upgResult);
            
            $pointsQuery = "SELECT COALESCE(SUM(points), 0) AS total
            FROM point_transaction
            WHERE agent_id = $agent_id 
            AND is_expired = 0
            AND expiry_date > CURDATE()";
            
            $pointsResult = mysqli_query($conn, $pointsQuery);
            $pointsRow = mysqli_fetch_assoc($pointsResult);
            $totalPoints = (float)($pointsRow['total'] ?? 0);
            
            $directAgentsQuery = "SELECT COUNT(*) AS cnt FROM agent_hierarchy
            WHERE ancestor_id = $agent_id 
            AND depth = 1
            AND descendant_id != $agent_id";
            
            $directResult = mysqli_query($conn, $directAgentsQuery);
            $directRow = mysqli_fetch_assoc($directResult);
            $directAgents = (int)($directRow['cnt'] ?? 0);
            
            $minPointsRequired = (float)$upgRow['min_points_total'];
            $minDirectRequired = (int)$upgRow['min_direct_agents'];
            
            $pointsProgress = $minPointsRequired > 0 
                ? min(100, round(($totalPoints / $minPointsRequired) * 100))
                : 100;
            
            $agentsProgress = $minDirectRequired > 0 
                ? min(100, round(($directAgents / $minDirectRequired) * 100))
                : 100;
            
            $upgradeCondition = [
                'to_rank_id' => (int)$upgRow['to_rank_id'],
                'min_points_required' => $minPointsRequired,
                'min_direct_agents' => $minDirectRequired,
                'current_points' => $totalPoints,
                'current_direct_agents' => $directAgents,
                'points_progress' => $pointsProgress,
                'agents_progress' => $agentsProgress
            ];
        }
    }
    
    // ===== BƯỚC 4: Trả về JSON response =====
    $response = [
        'agent' => $agentRow,
        'sub_agents' => $subAgents,
        'upgrade_condition' => $upgradeCondition
    ];
    
    http_response_code(200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} finally {
    mysqli_close($conn);
}
?>