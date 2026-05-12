<?php
include_once 'PHP/Dashboard_PHP.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include_once 'html/headertitle.php'; ?>
    <link href="css/dashboard.css" rel="stylesheet">

</head>
<body>

<!-- NAVBAR -->
<div class="container">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header"><?php include_once 'html/topmenu-left.php'; ?></div>
            <div id="navbar" class="navbar-collapse collapse">
                <?php include_once 'html/topmenu-right.php'; ?>
            </div>
        </div>
    </nav>
</div>

<div class="container">

    <!-- ===== TIÊU ĐỀ TRANG ===== -->
    <div class="dashboard-header">
        <h2>
            <span class="glyphicon glyphicon-th-large"></span>
            &nbsp; TỔNG QUAN HỆ THỐNG
        </h2>
        <span class="date-badge">
            <span class="glyphicon glyphicon-calendar"></span>
            &nbsp;<?= date('d/m/Y') ?>
        </span>
    </div>

    <!-- ===== HÀNG 1: SỐ LIỆU NHANH ===== -->
    <div class="row" style="margin-bottom:20px;">

        <div class="col-sm-3">
            <div class="stat-card blue">
                <span class="stat-icon glyphicon glyphicon-file"></span>
                <div class="stat-number"><a href="TT_Hopdong_chung.php?v=<?php echo urlencode(base64_encode("Dang_hoat_dong")); ?>"><?= number_format($tongHD) ?></a></div>
                <div class="stat-label">Hợp đồng đang hoạt động</div>
                <div class="stat-sub">
                    <span class="glyphicon glyphicon-plus-sign text-success"></span>
                    <?= $hdMoiThang ?> hợp đồng mới tháng này
					
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="stat-card orange">
                <span class="stat-icon glyphicon glyphicon-usd"></span>
                <div class="stat-number"><?= number_format($hoaHongTotal, 0, ',', '.') ?>đ</div>
                <div class="stat-label">Hoa hồng chờ duyệt</div>
                <div class="stat-sub">
                    <span class="glyphicon glyphicon-time text-warning"></span>
                    <?= $hoaHongCnt ?> giao dịch đang chờ
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="stat-card teal">
                <span class="stat-icon glyphicon glyphicon-bell"></span>
                <div class="stat-number"><?= number_format($tongLaiAGThang, 0, ',', '.') ?>đ</div>
                <div class="stat-label">Lãi AG phải trả tháng này</div>
                <div class="stat-sub">
                    <span class="glyphicon glyphicon-transfer" style="color:#16a085;"></span>
                    <?= $soLaiAG ?> hợp đồng đến hạn trả lãi
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="stat-card green">
                <span class="stat-icon glyphicon glyphicon-user"></span>
                <div class="stat-number"><?= number_format($tongAgent) ?></div>
                <div class="stat-label">Nhân viên đang hoạt động</div>
                <div class="stat-sub">
                    <span class="glyphicon glyphicon-plus-sign text-success"></span>
                    <?= $agentMoiThang ?> nhân viên mới tháng này
                </div>
            </div>
        </div>

    </div>

    <!-- ===== HÀNG 2: THÔNG BÁO HÀNH ĐỘNG ===== -->
    <div class="section-title">
        <span class="glyphicon glyphicon-exclamation-sign"></span>
        &nbsp; Thông báo cần xử lý
    </div>

    <div class="row" style="margin-bottom:20px;">
        <div class="col-md-12">
			
			<!-- Alert chuyển hợp đồng chờ sang hợp đồng chính thức -->
			<div class="action-alert alert-chuyen-chinhthuc">
				<span class="alert-icon">⬇️</span>
				<div class="alert-body">
					<div class="alert-title">
						Hợp đồng chờ đủ 21 ngày
					</div>
					<div class="alert-desc">
						Các hợp đồng đã đủ thời gian 21 ngày xem xét,
						chờ chuyển sang hợp đồng chính thức.
					</div>
				</div>
				<span class="alert-count"><?= $soHDCho ?></span>
				
				<a href="TT_Hopdong_ChuyenChinhThuc.php"
				   class="btn btn-alert-chinhthuc btn-sm">
					Xem danh sách &rarr;
				</a>

			</div>
						
            <!-- Alert chuyển A → AG -->
            <div class="action-alert alert-chuyen-ag">
                <span class="alert-icon">🔄</span>
                <div class="alert-body">
                    <div class="alert-title">Hợp đồng A đủ điều kiện chuyển sang AG</div>
                    <div class="alert-desc">
                        Các hợp đồng tùy chọn A đã tích lũy đủ số tiền theo số tín chỉ đăng ký,
                        có thể chuyển sang tùy chọn AG để nhận lãi hàng tháng.
                    </div>
                </div>
                <span class="alert-count"><?= $soHDChuyenAG ?></span>
                <a href="TT_Hopdong_DuDKChuyenAG.php" class="btn btn-warning btn-sm">
                    Xem danh sách &rarr;
                </a>
            </div>

            <!-- Alert tăng TCOx -->
            <div class="action-alert alert-tang-tcox">
                <span class="alert-icon">⬆️</span>
                <div class="alert-body">
                    <div class="alert-title">Hợp đồng đủ điều kiện tăng hạn mức TCOx</div>
                    <div class="alert-desc">
                        Các hợp đồng A/AG đã tích lũy đủ tiền để nâng hạn mức tín chỉ,
                        giúp tăng quyền lợi tri ân cho chủ hợp đồng khi có rủi ro.
                    </div>
                </div>
                <span class="alert-count"><?= $soHDTangTCOx ?></span>
                <a href="TT_Hopdong_DuDKChuyenAG.php" class="btn btn-success btn-sm">
                    Xem danh sách &rarr;
                </a>
            </div>

        </div>
    </div>

    <!-- ===== HÀNG 3: LÃI ĐẾN HẠN ===== -->
    <div class="row" style="margin-bottom:20px;">

        <!-- Lãi HĐ A sắp đến hạn -->
        <div class="col-md-6">
            <div class="dash-panel">
                <div class="section-title">
                    <span class="glyphicon glyphicon-calendar"></span>
                    &nbsp; HĐ loại A — Lãi năm sắp đến hạn (30 ngày tới)
                    <span class="badge" style="background:var(--warning);float:right;">
                        <?= $soLaiA ?>
                    </span>
                </div>

                <?php if (empty($dsLaiA)): ?>
                    <div class="no-data">
                        <span class="no-data-icon">✅</span>
                        Không có hợp đồng A nào đến hạn tính lãi trong 30 ngày tới
                    </div>
                <?php else: ?>
                    <?php foreach ($dsLaiA as $item): ?>
                    <div class="lai-item">
                        <div class="lai-info">
                            <div class="lai-sohd">
                                <a href="TT_Hopdong_Chitiet.php?var=<?= urlencode(base64_encode($item['SoHD'])) ?>">
                                    <?= htmlspecialchars($item['SoHD']) ?>
                                </a>
                            </div>
                            <div class="lai-kh"><?= htmlspecialchars($item['HoTen']) ?></div>
                            <div class="lai-ngay">
                                <span class="glyphicon glyphicon-time"></span>
                                Đến hạn: <?= $item['ngay_lai_tiep_theo'] ?>
                            </div>
                        </div>
                        <div class="lai-so">
                            +<?= number_format($item['lai_du_kien'], 0, ',', '.') ?>đ
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="text-align:right; margin-top:8px;">
                        <a href="TT_Hopdong_chung.php" style="font-size:12px; color:var(--primary-lt);">
                            Xem tất cả hợp đồng A &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lãi HĐ AG tháng này -->
        <div class="col-md-6">
            <div class="dash-panel">
                <div class="section-title">
                    <span class="glyphicon glyphicon-transfer"></span>
                    &nbsp; HĐ loại AG — Lãi tháng <?= date('m/Y') ?>
                    <span class="badge" style="background:var(--info);float:right;">
                        <?= $soLaiAG ?>
                    </span>
                </div>

                <?php if (empty($dsLaiAG)): ?>
                    <div class="no-data">
                        <span class="no-data-icon">✅</span>
                        Không có hợp đồng AG nào đến hạn trả lãi trong tuần này
                    </div>
                <?php else: ?>
                    <?php foreach ($dsLaiAG as $item): ?>
                    <div class="lai-item">
                        <div class="lai-info">
                            <div class="lai-sohd">
                                <a href="TT_Hopdong_Chitiet.php?var=<?= urlencode(base64_encode($item['SoHD'])) ?>">
                                    <?= htmlspecialchars($item['SoHD']) ?>
                                </a>
                            </div>
                            <div class="lai-kh">
                                <?= htmlspecialchars($item['HoTen']) ?>
                                &nbsp;|&nbsp; <?= htmlspecialchars($item['SoDT']) ?>
                            </div>
                            <div class="lai-ngay">
                                <span class="glyphicon glyphicon-time"></span>
                                Ngày trả: <?= $item['ngay_tra_lai_thang_nay'] ?>
                            </div>
                        </div>
                        <div class="lai-so">
                            <?= number_format($item['lai_thang'], 0, ',', '.') ?>đ/tháng
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="text-align:right; margin-top:8px;">
                        <a href="TT_Hopdong_chung.php" style="font-size:12px; color:var(--primary-lt);">
                            Xem tất cả hợp đồng AG &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ===== HÀNG 4: TOP AGENT & PHÂN BỔ CẤP BẬC ===== -->
    <div class="row">

        <!-- Top nhân viên -->
        <div class="col-md-7">
            <div class="dash-panel">
                <div class="section-title">
                    <span class="glyphicon glyphicon-stats"></span>
                    &nbsp; Top nhân viên — Số hợp đồng đang quản lý
                </div>

                <?php if (empty($topAgents)): ?>
                    <div class="no-data">
                        <span class="no-data-icon">👤</span>
                        Chưa có dữ liệu nhân viên
                    </div>
                <?php else: ?>
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th width="40">Top</th>
                            <th>Nhân viên</th>
                            <th width="60" class="text-center">Cấp</th>
                            <th width="70" class="text-center">Số HĐ</th>
                            <th class="text-right">Doanh số tháng</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topAgents as $i => $ag): ?>
                    <tr class="top-agent-row">
                        <td class="text-center">
                            <?php if ($i === 0): ?>
                                <span style="font-size:18px;">🥇</span>
                            <?php elseif ($i === 1): ?>
                                <span style="font-size:18px;">🥈</span>
                            <?php elseif ($i === 2): ?>
                                <span style="font-size:18px;">🥉</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-weight:700;">#<?= $i+1 ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($ag['full_name']) ?></strong>
                            <br>
                            <span style="color:var(--text-muted);font-size:11px;">
                                <?= htmlspecialchars($ag['agent_code']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge-rank"><?= $ag['rank_code'] ?></span>
                        </td>
                        <td class="text-center">
                            <strong><?= $ag['so_hd'] ?></strong>
                        </td>
                        <td class="text-right">
                            <strong><?= number_format($ag['tong_nop'], 0, ',', '.') ?>đ</strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <div style="text-align:right; margin-top:10px;">
                    <a href="agent.php" style="font-size:12px; color:var(--primary-lt);">
                        Xem danh sách nhân viên &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Phân bổ cấp bậc -->
        <div class="col-md-5">
            <div class="dash-panel">
                <div class="section-title">
                    <span class="glyphicon glyphicon-th-list"></span>
                    &nbsp; Phân bổ nhân viên theo cấp bậc
                </div>

                <?php foreach ($rankStats as $rs):
                    $barPct = $tongAgent > 0
                        ? min(100, round($rs['cnt'] / $tongAgent * 100))
                        : 0;
                ?>
                <div class="rank-bar-wrap">
                    <div class="rank-bar-label">
                        <span class="badge-rank" style="font-size:10px;"><?= $rs['rank_code'] ?></span>
                        &nbsp;
                        <span style="font-size:11px; color:var(--text-muted);">
                            <?= mb_strimwidth(htmlspecialchars($rs['rank_name']), 0, 14, '…') ?>
                        </span>
                    </div>
                    <div class="rank-bar-outer">
                        <div class="rank-bar-inner" style="width:<?= $barPct ?>%"></div>
                    </div>
                    <div class="rank-bar-count"><?= $rs['cnt'] ?></div>
                </div>
                <?php endforeach; ?>

                <div style="text-align:right; margin-top:10px;">
                    <a href="RankConfig.php" style="font-size:12px; color:var(--primary-lt);">
                        Cấu hình cấp bậc &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div>
    <!-- /row hàng 4 -->

</div>
<!-- /container -->

<?php include_once 'html/emb_js.php'; ?>

</body>
</html>
