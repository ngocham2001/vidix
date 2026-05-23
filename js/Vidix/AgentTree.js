/**
 * AgentTree.js
 * Xử lý hiển thị cây mạng lưới với chức năng drill-down
 */

// Lưu lịch sử navigation (back button)
let treeNavigationHistory = [];

/**
 * Hiển thị cây agent từ cấp chỉ định
 * @param {number} agentId - Agent ID để xem cây
 * @param {string} agentName - Tên agent
 */
function showAgentTree(agentId, agentName) {
    // Reset lịch sử khi mở modal mới
    treeNavigationHistory = [agentId];
    
    $('#tree-modal-name').text(agentName);
    $('#modal-agent-tree').modal('show');
    
    loadAgentTreeData(agentId);
}

/**
 * Tải dữ liệu cây từ server
 */
function loadAgentTreeData(agentId) {
    const modalBody = $('#tree-modal-body');
    
    modalBody.html(`
        <div style="text-align:center; padding:20px;">
            <div class="spinner"></div><br/>
            Đang tải dữ liệu...
        </div>
    `);
    
    $.ajax({
        url: 'VIDIX_function/API_getAgentTree.php',
        type: 'GET',
        data: { agent_id: agentId },
        dataType: 'json',
        xhrFields: {
            withCredentials: true  // ✅ GỬI KÈM COOKIE/SESSION
        },
        crossDomain: true,
        success: function(data) {
            if (data.error) {
                modalBody.html('<div class="alert alert-danger">' + data.error + '</div>');
                return;
            }
            
            renderAgentTree(data);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error, 'Status:', xhr.status);
            let errorMsg = 'Lỗi tải dữ liệu';
            
            if (xhr.status === 401) {
                errorMsg = '❌ Phiên đăng nhập hết hạn. <br/><a href="index.php">Vui lòng đăng nhập lại</a>';
            } else if (xhr.status === 404) {
                errorMsg = '❌ API không tìm thấy (404)';
            } else if (xhr.status === 500) {
                errorMsg = '❌ Lỗi server';
            }
            
            modalBody.html('<div class="alert alert-danger" role="alert">' + errorMsg + '</div>');
        }
    });
}

/**
 * Render giao diện cây agent
 */
function renderAgentTree(data) {
    const agent = data.agent;
    const subAgents = data.sub_agents;
    const upgradeCondition = data.upgrade_condition;
    const modalBody = $('#tree-modal-body');
    
    let html = '';
    
    // ===== PHẦN 1: THÔNG TIN NGƯỜI HIỆN TẠI =====
    html += `
        <div style="background:#f8f9fa; border-left:5px solid #1a3c5e; padding:15px; margin-bottom:20px; border-radius:4px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4 style="margin:0 0 5px 0;">
                        <span class="badge" style="background:#1a3c5e; font-size:13px; padding:5px 10px;">
                            ${agent.rank_code}
                        </span>
                        ${agent.full_name} (${agent.agent_code})
                    </h4>
                    <p style="margin:3px 0; color:#666; font-size:13px;">
                        Tổng HĐ phụ trách: <strong>${subAgents.reduce((sum, a) => sum + parseInt(a.so_hd || 0), 0)}</strong> 
                        &nbsp; | &nbsp; Cấp dưới trực tiếp: <strong>${subAgents.length}</strong>
                    </p>
                </div>
            </div>
        </div>
    `;
    
    // ===== PHẦN 2: ĐIỀU KIỆN THĂNG CẤP =====
    if (upgradeCondition) {
        const nextRankRow = getNextRankInfo(upgradeCondition.to_rank_id);
        const pointsOK = upgradeCondition.current_points >= upgradeCondition.min_points_required;
        const agentsOK = upgradeCondition.current_direct_agents >= upgradeCondition.min_direct_agents;
        
        html += `
            <div style="background:#eff6ff; border:2px solid #93c5fd; border-radius:6px; padding:15px; margin-bottom:20px;">
                <p style="margin-top:0; font-weight:700; color:#1d4ed8; font-size:12px; text-transform:uppercase;">
                    📊 Điều kiện thăng cấp lên ${nextRankRow ? nextRankRow.rank_code + ' - ' + nextRankRow.rank_name : 'Cấp cao hơn'}
                </p>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:10px;">
                    <!-- Điểm -->
                    <div>
                        <p style="margin:0 0 5px 0; font-size:13px; color:#1d4ed8; font-weight:600;">
                            Điểm: ${pointsOK ? '✅' : '⏳'} ${upgradeCondition.current_points.toFixed(0)} / ${upgradeCondition.min_points_required.toFixed(0)}
                        </p>
                        <div style="height:8px; background:#dbeafe; border-radius:4px; overflow:hidden;">
                            <div style="height:100%; background:${pointsOK ? '#10b981' : '#f59e0b'}; width:${upgradeCondition.points_progress}%;"></div>
                        </div>
                    </div>
                    
                    <!-- Nhân viên trực tiếp -->
                    <div>
                        <p style="margin:0 0 5px 0; font-size:13px; color:#1d4ed8; font-weight:600;">
                            NV trực tiếp: ${agentsOK ? '✅' : '⏳'} ${upgradeCondition.current_direct_agents} / ${upgradeCondition.min_direct_agents}
                        </p>
                        <div style="height:8px; background:#dbeafe; border-radius:4px; overflow:hidden;">
                            <div style="height:100%; background:${agentsOK ? '#10b981' : '#f59e0b'}; width:${upgradeCondition.agents_progress}%;"></div>
                        </div>
                    </div>
                </div>
                
                <p style="margin:10px 0 0 0; font-size:12px; color:#1d4ed8;">
                    ${pointsOK && agentsOK 
                        ? '✅ Đủ điều kiện thăng cấp!' 
                        : '⏳ Chưa đủ điều kiện'}
                </p>
            </div>
        `;
    }
    
    // ===== PHẦN 3: DANH SÁCH CẤP DƯỚI =====
    if (subAgents.length > 0) {
        html += `
            <div style="margin-bottom:20px;">
                <p style="font-weight:700; color:#1a3c5e; margin-bottom:10px;">
                    👥 Cộng tác viên cấp dưới (${subAgents.length} người)
                </p>
        `;
        
        subAgents.forEach((sub, idx) => {
            const canUpgrade = sub.so_hd >= 0;  // Thêm logic kiểm tra điều kiện nếu cần
            
            html += `
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:6px; padding:12px; 
                           margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;
                           transition:all 0.2s; cursor:pointer;" 
                     onmouseover="this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'"
                     onmouseout="this.style.boxShadow='none'">
                    
                    <div style="flex:1;">
                        <h5 style="margin:0 0 3px 0;">
                            <span class="badge" style="background:#3b82f6; font-size:11px; padding:3px 8px;">
                                ${sub.rank_code}
                            </span>
                            <strong>${sub.full_name}</strong>
                            <span style="color:#999; font-size:12px;">(${sub.agent_code})</span>
                        </h5>
                        <p style="margin:0; font-size:12px; color:#666;">
                            <span style="display:inline-block; margin-right:15px;">
                                📄 HĐ: <strong style="color:#059669; font-size:13px;">${sub.so_hd}</strong>
                            </span>
                            <span style="display:inline-block;">
                                👥 Cấp dưới: <strong>${sub.so_cap_duoi}</strong>
                            </span>
                        </p>
                    </div>
                    
                    <!-- Nút xem cây của cấp này -->
                    <button class="btn btn-sm btn-primary" 
                            onclick="drillDownTree(${sub.agent_id}, '${escapeHtml(sub.full_name)}'); return false;"
                            style="margin-left:10px;">
                        Xem chi tiết →
                    </button>
                </div>
            `;
        });
        
        html += `</div>`;
    } else {
        html += `
            <div class="alert alert-info">
                <span class="glyphicon glyphicon-info-sign"></span>
                Chưa có cộng tác viên cấp dưới
            </div>
        `;
    }
    
    // ===== NỚP BACK BUTTON NẾU CÓ LỊCH SỬ =====
    if (treeNavigationHistory.length > 1) {
        html += `
            <div style="text-align:center; margin-top:15px; padding-top:15px; border-top:1px solid #e5e7eb;">
                <button class="btn btn-sm btn-default" onclick="goBackInTree(); return false;">
                    <span class="glyphicon glyphicon-chevron-left"></span> Quay lại
                </button>
            </div>
        `;
    }
    
    $('#tree-modal-body').html(html);
}

/**
 * Drill-down: Xem cây của một agent cấp dưới
 */
function drillDownTree(agentId, agentName) {
    treeNavigationHistory.push(agentId);
    $('#tree-modal-name').text(agentName);
    loadAgentTreeData(agentId);
}

/**
 * Quay lại level trước
 */
function goBackInTree() {
    if (treeNavigationHistory.length > 1) {
        treeNavigationHistory.pop();
        const prevAgentId = treeNavigationHistory[treeNavigationHistory.length - 1];
        loadAgentTreeData(prevAgentId);
    }
}

/**
 * Lấy thông tin rank từ rank_id (để hiển thị tên rank tiếp theo)
 * ⚠️ Thêm vào phần response của API hoặc hardcode nếu cần
 */
function getNextRankInfo(rankId) {
    const ranks = {
        1: { rank_code: 'C1', rank_name: 'Cộng tác viên' },
        2: { rank_code: 'C2', rank_name: 'Chuyên viên thị trường' },
        3: { rank_code: 'C3', rank_name: 'Trưởng nhóm thị trường' },
        4: { rank_code: 'C4', rank_name: 'Phó giám đốc thị trường' },
        5: { rank_code: 'C5', rank_name: 'Giám đốc thị trường' },
        6: { rank_code: 'C6', rank_name: 'Phó giám đốc KD VIDIX' },
        7: { rank_code: 'C7', rank_name: 'Giám đốc KD VIDIX' },
        8: { rank_code: 'C8', rank_name: 'Giám đốc KD Tổng hợp' }
    };
    return ranks[rankId] || null;
}

/**
 * Escape HTML để tránh XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}