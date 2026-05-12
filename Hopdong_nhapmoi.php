<?php
include __DIR__ . '/define.php';
include_once 'PHP/Hopdong_nhapmoi_PHP.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include_once 'html/headertitle.php'; ?>
    <link href="css/hopdong_nhapmoi.css" rel="stylesheet">
</head>
<body>

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

<div class="page-header-custom">
    <div class="container">
        <h2><span class="glyphicon glyphicon-file"></span>&nbsp;Nhập hồ sơ chờ — Đăng ký hợp đồng mới</h2>
        <p>Điền đầy đủ thông tin hồ sơ. Hợp đồng có hiệu lực chính thức sau 21 ngày nếu khách hàng không hủy.</p>
    </div>
</div>

<div class="container">

    <div class="alert-custom" id="main-alert"></div>

    <!-- STEPPER 5 bước -->
    <div class="stepper">
        <div class="step">
            <div class="step-circle active" id="step1-circle">1</div>
            <span class="step-label active">Khách hàng</span>
        </div>
        <div class="step-line" id="line-1"></div>
        <div class="step">
            <div class="step-circle waiting" id="step2-circle">2</div>
            <span class="step-label waiting">Mã hồ sơ</span>
        </div>
        <div class="step-line" id="line-2"></div>
        <div class="step">
            <div class="step-circle waiting" id="step3-circle">3</div>
            <span class="step-label waiting">Tùy chọn HĐ</span>
        </div>
        <div class="step-line" id="line-3"></div>
        <div class="step">
            <div class="step-circle waiting" id="step4-circle">4</div>
            <span class="step-label waiting">Người liên hệ</span>
        </div>
        <div class="step-line" id="line-4"></div>
        <div class="step">
            <div class="step-circle waiting" id="step5-circle">5</div>
            <span class="step-label waiting">Người thụ hưởng</span>
        </div>
    </div>

    <form id="main-form" autocomplete="off">

    <!-- ══════════════════════════════════════════════════ -->
    <!-- CARD 1: THÔNG TIN KHÁCH HÀNG                      -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-card">
        <div class="form-card-header">
            <div class="card-icon green">👤</div>
            <div>
                <h3>Thông tin khách hàng</h3>
                <p>Nhập CCCD — hệ thống tự tra cứu, điền và khóa thông tin nếu đã có</p>
            </div>
        </div>
        <div class="form-card-body">

            <div class="field-row">
                <div class="field-group" style="max-width:320px;">
                    <label class="field-label" for="cccd">
                        Số CCCD / Hộ chiếu <span class="req">*</span>
                        <span class="tip">Nhập xong — tự tra cứu</span>
                    </label>
                    <div style="position:relative;">
                        <input type="text" id="cccd" name="cccd"
                               class="form-input" style="padding-right:36px;"
                               placeholder="Nhập số CCCD..." maxlength="20"/>
                        <span id="cccd-spinner" style="display:none;position:absolute;right:8px;top:10px;">
                            <span class="spinner-inline"></span>
                        </span>
                    </div>
                    <div class="cccd-status" id="cccd-status"></div>
                    <span class="field-error" id="err-cccd">Vui lòng nhập số CCCD</span>
                </div>
                <div class="field-group" style="max-width:200px;">
                    <label class="field-label" for="ma_khach">
                        Mã khách hàng
                        <span class="tip">(tự sinh nếu KH mới)</span>
                    </label>
                    <input type="text" id="ma_khach" name="ma_khach"
                           class="form-input" placeholder="VD: KH00005"/>
                    <span class="field-error" id="err-ma_khach">Mã KH không được để trống</span>
                </div>
            </div>

            <div class="lock-banner" id="lock-banner">
                <span class="lock-icon">🔒</span>
                Khách hàng đã có trong hệ thống — thông tin được điền tự động và khóa chỉnh sửa.
            </div>

            <div class="field-row">
                <div class="field-group" style="flex:2;min-width:200px;">
                    <label class="field-label" for="ho_ten">Họ và tên <span class="req">*</span></label>
                    <input type="text" id="ho_ten" name="ho_ten" class="form-input" placeholder="Nguyễn Văn A"/>
                    <span class="field-error" id="err-ho_ten">Vui lòng nhập họ tên</span>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="so_dt">Số điện thoại <span class="req">*</span></label>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <input type="text" id="so_dt" name="so_dt" class="form-input" placeholder="09xxxxxxxx"/>
                        <button type="button" id="btn-edit-sodt" class="btn-inline-edit" style="display:none;"
                                onclick="enableInlineEdit('so_dt','btn-edit-sodt','btn-save-sodt')">✏️</button>
                        <button type="button" id="btn-save-sodt" class="btn-inline-save" style="display:none;"
                                onclick="saveInlineEdit('so_dt','btn-edit-sodt','btn-save-sodt')">💾</button>
                    </div>
                    <span class="field-error" id="err-so_dt">Vui lòng nhập SĐT</span>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="ngay_sinh">Ngày sinh</label>
                    <input type="date" id="ngay_sinh" name="ngay_sinh" class="form-input"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="ngay_cap_cccd">Ngày cấp CCCD</label>
                    <input type="date" id="ngay_cap_cccd" name="ngay_cap_cccd" class="form-input"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="gioi_tinh">Giới tính</label>
                    <select id="gioi_tinh" name="gioi_tinh" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group w-half">
                    <label class="field-label" for="email">Email</label>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <input type="email" id="email" name="email" class="form-input" placeholder="example@email.com"/>
                        <button type="button" id="btn-edit-email" class="btn-inline-edit" style="display:none;"
                                onclick="enableInlineEdit('email','btn-edit-email','btn-save-email')">✏️</button>
                        <button type="button" id="btn-save-email" class="btn-inline-save" style="display:none;"
                                onclick="saveInlineEdit('email','btn-edit-email','btn-save-email')">💾</button>
                    </div>
                </div>
                <div class="field-group w-half">
                    <label class="field-label" for="dan_toc">Dân tộc</label>
                    <input type="text" id="dan_toc" name="dan_toc" class="form-input" placeholder="Kinh"/>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group w-half">
                    <label class="field-label" for="quoc_tich">Quốc tịch</label>
                    <input type="text" id="quoc_tich" name="quoc_tich" class="form-input" value="Việt Nam"/>
                </div>
                <div class="field-group w-half">
                    <label class="field-label" for="tinh_trang_hon_nhan">Tình trạng hôn nhân</label>
                    <select id="tinh_trang_hon_nhan" name="tinh_trang_hon_nhan" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="Độc thân">Độc thân</option>
                        <option value="Đã kết hôn">Đã kết hôn</option>
                        <option value="Nuôi con đơn thân">Nuôi con đơn thân</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group w-half">
                    <label class="field-label" for="tinh_trang_suc_khoe_text">
                        Tình trạng sức khỏe <span class="tip">(mô tả)</span>
                    </label>
                    <input type="text" id="tinh_trang_suc_khoe_text" name="tinh_trang_suc_khoe_text"
                           class="form-input" placeholder="VD: Tốt, không bệnh nền..."/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="tinh_trang_suc_khoe_bhyt">BHYT</label>
                    <select id="tinh_trang_suc_khoe_bhyt" name="tinh_trang_suc_khoe_bhyt" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="Có BHYT">Có BHYT</option>
                        <option value="Không có BHYT">Không có BHYT</option>
                    </select>
                </div>
                <input type="hidden" id="tinh_trang_suc_khoe_combined" name="tinh_trang_suc_khoe"/>
            </div>

            <div class="field-row">
                <div class="field-group w-half">
                    <label class="field-label" for="trinh_do_hoc_van">Trình độ học vấn</label>
                    <select id="trinh_do_hoc_van" name="trinh_do_hoc_van" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="PTTH-Trung Cấp">PTTH / Trung cấp</option>
                        <option value="Cao đẳng">Cao đẳng</option>
                        <option value="Đại học">Đại học</option>
                        <option value="Trên Đại học">Trên Đại học</option>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group w-full">
                    <label class="field-label" for="noi_o_hien_tai">Nơi ở hiện tại</label>
                    <input type="text" id="noi_o_hien_tai" name="noi_o_hien_tai"
                           class="form-input" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/TP"/>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group w-full">
                    <label class="field-label" for="hk_thuong_tru">Hộ khẩu thường trú</label>
                    <input type="text" id="hk_thuong_tru" name="hk_thuong_tru"
                           class="form-input" placeholder="Địa chỉ hộ khẩu thường trú"/>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group w-full">
                    <label class="field-label" for="ghi_chu_kh">Ghi chú khách hàng</label>
                    <textarea id="ghi_chu_kh" name="ghi_chu_kh" class="form-input" rows="2"
                              placeholder="Thông tin bổ sung (nếu có)"></textarea>
                </div>
            </div>

            <!-- Hiện khi KH đồng thời là CTV trong bảng agent -->
            <div id="block-nv-tu-dong" style="display:none;">
                <hr class="field-divider"/>
                <p style="font-size:12px;font-weight:700;color:#1d4ed8;
                          text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
                    🤝 Khách hàng này đồng thời là cộng tác viên — Tự động chọn làm NV tư vấn
                </p>
                <div class="field-row">
                    <div class="field-group w-half">
                        <label class="field-label">Mã nhân viên</label>
                        <input type="text" id="nv_tu_dong_code" class="form-input" readonly placeholder="Tự động"/>
                        <input type="hidden" id="agent_id_tu_dong"/>
                    </div>
                    <div class="field-group w-half">
                        <label class="field-label">Tên nhân viên</label>
                        <input type="text" id="nv_tu_dong_name" class="form-input" readonly/>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- CARD 2: MÃ HỒ SƠ                                  -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-card">
        <div class="form-card-header">
            <div class="card-icon blue">📋</div>
            <div>
                <h3>Mã hồ sơ</h3>
                <p>Nhập Mã VP, Iv và KB — HSS và ngày HĐ chính thức tự động tính</p>
            </div>
        </div>
        <div class="form-card-body">

            <!-- Dòng 1: Mã VP | Iv | KB -->
            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="ma_vp_input">
                        Mã văn phòng <span class="req">*</span>
                    </label>
                    <input type="text" id="ma_vp_input" name="ma_vp"
                           class="form-input code-input" placeholder="VD: 04" maxlength="5"/>
                    <span class="field-error" id="err-ma_vp">Vui lòng nhập mã VP</span>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="so_iv">
                        Số Iv <span class="req">*</span>
                        <span class="tip">Thứ(1) + Tuần(2) + Năm(4) + 04/ STT hồ sơ</span>
                    </label>
                    <input type="text" id="so_iv" name="so_iv"
                           class="form-input code-input"
                           placeholder="VD: 201202604/001" maxlength="20"/>
                    <span class="field-error" id="err-so_iv">Vui lòng nhập số Iv</span>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="so_kb">
                        Số KB <span class="req">*</span>
                        <span class="tip">DDMMYYYYHHmmss</span>
                    </label>
                    <input type="text" id="so_kb" name="so_kb"
                           class="form-input code-input"
                           placeholder="VD: 24042026153045" maxlength="20"/>
                    <span class="field-error" id="err-so_kb">Vui lòng nhập số KB</span>
                </div>
            </div>

            <!-- Dòng 2: HSS (tự tính) | Ngày HĐ chính thức (tự tính) -->
            <div class="field-row">
                <div class="field-group w-half">
                    <label class="field-label">
                        Số HSS
                        <span class="tip">(tự tính: NgaySinh + "04/" + MaVP + Năm PHHĐ)</span>
                    </label>
                    <input type="text" id="so_hss_display" class="form-input code-input"
                           readonly placeholder="Tự động tính khi đủ dữ liệu..."/>
                    <input type="hidden" id="so_hss" name="so_hss"/>
                </div>
                <div class="field-group w-half">
                    <label class="field-label">
                        Ngày HĐ chính thức
                        <span class="tip">(ngày KB + 21 ngày)</span>
                    </label>
                    <input type="text" id="han_huy_display" class="form-input"
                           readonly placeholder="Tự động từ KB..."/>
                    <input type="hidden" id="han_huy_sql" name="han_huy_sql"/>
                    <input type="hidden" id="ngay_khoi_tao_sql" name="ngay_khoi_tao_sql"/>
                </div>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- CARD 3: TÙY CHỌN HỢP ĐỒNG                        -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-card">
        <div class="form-card-header">
            <div class="card-icon orange">⚙️</div>
            <div>
                <h3>Tùy chọn hợp đồng</h3>
                <p>Chọn tùy chọn tham gia, loại hợp đồng và số tín chỉ</p>
            </div>
        </div>
        <div class="form-card-body">

            <!-- Tùy chọn tham gia -->
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Tùy chọn tham gia <span class="req">*</span></label>
                    <div class="option-group" id="tuy-chon-thamgia-group">
                        <div class="option-btn">
                            <input type="checkbox" id="opt_A" name="tuy_chon_thamgia" value="A"/>
                            <label for="opt_A">
                                <span class="opt-icon">👤</span> Tùy chọn A
                                <small style="font-weight:400;opacity:.7;">(Chỉ là khách hàng)</small>
                            </label>
                        </div>
                        <div class="option-btn">
                            <input type="checkbox" id="opt_B" name="tuy_chon_thamgia" value="B"/>
                            <label for="opt_B">
                                <span class="opt-icon">🤝</span> Tùy chọn B
                                <small style="font-weight:400;opacity:.7;">(KH + Nhân viên)</small>
                            </label>
                        </div>
                        <div class="option-btn">
                            <input type="checkbox" id="opt_khac" name="tuy_chon_thamgia" value="Khac"/>
                            <label for="opt_khac">
                                <span class="opt-icon">📄</span> Tùy chọn khác
                            </label>
                        </div>
                    </div>
                    <span class="field-error" id="err-loai_hd">Vui lòng chọn tùy chọn tham gia</span>
                </div>
            </div>

            <!-- Loại HĐ — hiện luôn -->
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Loại hợp đồng <span class="req">*</span></label>
                    <div class="option-group" id="loai-hd-group">
                        <div class="option-btn">
                            <input type="checkbox" id="opt_HD_A" name="loai_hd" value="A"/>
                            <label for="opt_HD_A">
                                <span class="opt-icon">🅰</span> Hợp đồng A
                                <small style="font-weight:400;opacity:.7;">(Tích lũy lãi năm)</small>
                            </label>
                        </div>
                        <div class="option-btn">
                            <input type="checkbox" id="opt_HD_AG" name="loai_hd" value="AG"/>
                            <label for="opt_HD_AG">
                                <span class="opt-icon">🔄</span> Hợp đồng AG
                                <small style="font-weight:400;opacity:.7;">(Nhận lãi hàng tháng)</small>
                            </label>
                        </div>
                    </div>
                    <span class="field-error" id="err-loai_hd2">Vui lòng chọn loại hợp đồng</span>
                </div>
            </div>

            <div id="banner-tao-nv" style="display:none;background:#eff6ff;border:1.5px solid #93c5fd;
                 border-radius:8px;padding:10px 14px;font-size:13px;color:#1d4ed8;margin-bottom:10px;">
                <strong>🤝 Tùy chọn B:</strong> Khách hàng sẽ được tạo tài khoản nhân viên cấp C1 sau khi lưu.
            </div>

            <!-- Số DVTC + năm + trị giá -->
            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="so_dvtc">
                        Số đơn vị tín chỉ <span class="req">*</span>
                        <span class="tip">(1 TCOx = 12 ĐVTC)</span>
                    </label>
                    <input type="number" id="so_dvtc" name="so_dvtc"
                           class="form-input" min="1" step="1" placeholder="VD: 12"/>
                    <span class="field-error" id="err-so_dvtc">Vui lòng nhập số ĐVTC</span>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="so_nam_hd">Số năm HĐ <span class="req">*</span></label>
                    <select id="so_nam_hd" name="so_nam_hd" class="form-input">
                        <option value="">-- Chọn số năm --</option>
                        <option value="16">16 năm</option>
                        <option value="26">26 năm</option>
                        <option value="36">36 năm</option>
                        <option value="46">46 năm</option>
                    </select>
                    <span class="field-error" id="err-so_nam_hd">Vui lòng chọn số năm</span>
                </div>
                <div class="field-group w-third">
                    <label class="field-label">
                        Trị giá hợp đồng
                        <span class="tip">(ĐVTC × năm × 105.000 × 12)</span>
                    </label>
                    <input type="text" id="trigia_hd_display" class="form-input"
                           readonly placeholder="Tự động tính"/>
                </div>
            </div>

            <div class="field-row">
				<div class="field-group w-third">
					<label class="field-label" for="so_tien_nop">
						Số tiền nộp lần đầu <span class="req">*</span>
						<span class="tip" id="tip-so-tien-nop"></span>
					</label>
					<input type="text" id="so_tien_nop" name="so_tien_nop"
						   class="form-input" placeholder="Nhập số tiền..."/>
					<span class="field-error" id="err-so_tien_nop">Vui lòng nhập số tiền nộp</span>
				</div>

				<div class="field-group w-third" style="justify-content:flex-end;padding-bottom:2px;">
					<label style="display:flex;align-items:center;gap:8px;cursor:pointer;
								  font-size:13px;"> 
						<input type="checkbox" id="tu_dong_tang_ag" name="tu_dong_tang_ag" value="1"
							   style="width:16px;height:16px;flex-shrink:0;"/>
						<span style="color:var(--muted);">Tự động chuyển A → AG khi đủ điều kiện</span>
					</label>
				</div>
            </div>

            <!-- NV tư vấn — ẩn khi chọn B -->
            <div id="block-nv-tuvao">
                <hr class="field-divider"/>
                <p id="label-block-nv" style="font-size:12px;font-weight:700;color:var(--navy);
                          text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
                    🧑‍💼 Nhân viên tư vấn bán hàng
                </p>
                <div class="field-row">
                    <div class="field-group w-half">
                        <label class="field-label" for="agent_id_banhang">
                            <span id="label-agent-select">Nhân viên tư vấn </span>
							<span class="req">*</span>
                        </label>
                        <?php echo $xhtmlSelectAgent; ?>
                        <span class="field-error" id="err-agent">Vui lòng chọn nhân viên tư vấn</span>
                    </div>
                    <div class="field-group w-half">
                        <label class="field-label" for="ghi_chu_hd">Ghi chú hợp đồng</label>
                        <textarea id="ghi_chu_hd" name="ghi_chu_hd" class="form-input" rows="2"
                                  placeholder="Ghi chú thêm (nếu có)"></textarea>
                    </div>
                </div>
				<!-- Hidden: Lwu sponsor_agent_id khi tuy chon B-->
				<input type = "hidden" id = "sponsor_agent_id" name = "sponsor_agent_id" value = ""/>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- CARD 4: NGƯỜI LIÊN HỆ & NGÂN HÀNG                 -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-card">
        <div class="form-card-header">
            <div class="card-icon purple">📑</div>
            <div>
                <h3>Người liên hệ &amp; tài khoản ngân hàng</h3>
                <p>Thông tin người liên hệ và tài khoản nhận lãi của chủ hợp đồng</p>
            </div>
        </div>
        <div class="form-card-body">

            <p style="font-size:12px;font-weight:700;color:var(--navy);
                      text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
                🏦 Tài khoản ngân hàng
            </p>
            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="so_tai_khoan">Số tài khoản</label>
                    <input type="text" id="so_tai_khoan" name="so_tai_khoan"
                           class="form-input" placeholder="0123456789"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="ten_ngan_hang">Tên ngân hàng</label>
                    <input type="text" id="ten_ngan_hang" name="ten_ngan_hang"
                           class="form-input" placeholder="Vietcombank, BIDV..."/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="ho_ten_chu_tk">Chủ tài khoản</label>
                    <input type="text" id="ho_ten_chu_tk" name="ho_ten_chu_tk"
                           class="form-input" placeholder="Tên chủ tài khoản"/>
                </div>
            </div>

            <hr class="field-divider"/>

            <p style="font-size:12px;font-weight:700;color:var(--navy);
                      text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
                👥 Người liên hệ
            </p>

            <div id="nlh-banner" style="display:none;background:#eff6ff;border:1.5px solid #93c5fd;
                 border-radius:8px;padding:9px 14px;margin-bottom:14px;
                 font-size:13px;color:#1d4ed8;font-weight:600;">
                📋 Thông tin lấy từ hợp đồng cũ — có thể chỉnh sửa nếu cần.
            </div>

            <div class="field-row" id="field-row-nlh-first">
                <div class="field-group" style="flex:2;min-width:200px;">
                    <label class="field-label" for="ho_ten_nlh">Họ và tên NLH</label>
                    <input type="text" id="ho_ten_nlh" name="ho_ten_nlh"
                           class="form-input" placeholder="Họ tên người liên hệ"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="mqh_chu_hd">Mối quan hệ với chủ HĐ</label>
                    <select id="mqh_chu_hd" name="mqh_chu_hd" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="Bố mẹ">Bố mẹ</option>
                        <option value="Vợ chồng">Vợ / Chồng</option>
                        <option value="Anh chị em ruột">Anh chị em ruột</option>
                        <option value="Con">Con</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="ngay_sinh_nlh">Ngày sinh NLH</label>
                    <input type="date" id="ngay_sinh_nlh" name="ngay_sinh_nlh" class="form-input"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="gioi_tinh_nlh">Giới tính NLH</label>
                    <select id="gioi_tinh_nlh" name="gioi_tinh_nlh" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="so_cccd_nlh">CCCD NLH</label>
                    <input type="text" id="so_cccd_nlh" name="so_cccd_nlh"
                           class="form-input" placeholder="Số CCCD người liên hệ"/>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group w-half">
                    <label class="field-label" for="noi_o_nlh">Nơi ở hiện tại NLH</label>
                    <input type="text" id="noi_o_nlh" name="noi_o_nlh"
                           class="form-input" placeholder="Địa chỉ người liên hệ"/>
                </div>
                <div class="field-group w-half">
                    <label class="field-label" for="hk_nlh">Hộ khẩu thường trú NLH</label>
                    <input type="text" id="hk_nlh" name="hk_nlh"
                           class="form-input" placeholder="HK thường trú người liên hệ"/>
                </div>
            </div>

            <!-- Checkbox NLH = người thụ hưởng -->
            <div class="field-row" style="margin-top:10px;">
                <div class="field-group">
                    <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;
                                  padding:10px 16px;border:2px solid var(--border);border-radius:8px;
                                  font-size:13px;font-weight:600;color:var(--navy);
                                  transition:border-color 0.2s;" id="label-nlh-thu-huong">
                        <input type="checkbox" id="nlh_la_thu_huong" name="nlh_la_thu_huong" value="1"
                               style="width:16px;height:16px;cursor:pointer;"/>
                        <span>👥 Người liên hệ đồng thời là người thụ hưởng</span>
                    </label>
                </div>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- CARD 5: THÔNG TIN NGƯỜI THỤ HƯỞNG                 -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-card" id="card-thu-huong">
        <div class="form-card-header">
            <div class="card-icon" style="background:#fce7f3;">🎁</div>
            <div>
                <h3>Thông tin người thụ hưởng</h3>
                <p>Người nhận quyền lợi bảo hiểm </p>
            </div>
        </div>
        <div class="form-card-body">

            <!-- Banner copy từ NLH -->
            <div id="banner-copy-nlh" style="display:none;background:#d1fae5;border:1.5px solid #6ee7b7;
                 border-radius:8px;padding:9px 14px;margin-bottom:14px;
                 font-size:13px;color:var(--success);font-weight:600;">
                ✅ Thông tin sao chép từ người liên hệ — có thể chỉnh sửa nếu cần.
            </div>

            <!-- Checkbox doanh nghiệp thừa kế -->
            <div class="field-row">
                <div class="field-group">
                    <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;
                                  padding:10px 16px;border:2px solid var(--border);border-radius:8px;
                                  font-size:13px;font-weight:600;color:var(--navy);
                                  transition:border-color 0.2s;" id="label-doanh-nghiep">
                        <input type="checkbox" id="doanh_nghiep_thua_ke" name="DoanhNghiepThuaKe" value="1"
                               style="width:16px;height:16px;cursor:pointer;"/>
                        <span>🏢 Doanh nghiệp thừa kế</span>
                    </label>
                </div>
            </div>

            <!-- Tên DN + MST — chỉ hiện khi tick -->
            <div id="block-doanh-nghiep" style="display:none;">
                <div class="field-row">
                    <div class="field-group w-half">
                        <label class="field-label" for="ten_dn">
                            Tên doanh nghiệp <span class="req">*</span>
                        </label>
                        <input type="text" id="ten_dn" name="TenDN"
                               class="form-input" placeholder="Tên công ty / doanh nghiệp"/>
                    </div>
                    <div class="field-group w-half">
                        <label class="field-label" for="mst">Mã số thuế</label>
                        <input type="text" id="mst" name="MST"
                               class="form-input" placeholder="Mã số thuế doanh nghiệp"/>
                    </div>
                </div>
            </div>

            <!-- Thông tin cá nhân thụ hưởng -->
            <div class="field-row">
                <div class="field-group" style="flex:2;min-width:200px;">
                    <label class="field-label" for="thu_huong_ho_ten">
                        Họ và tên người thụ hưởng <span class="req">*</span>
                        <span class="tip">(hoặc người đại diện DN)</span>
                    </label>
                    <input type="text" id="thu_huong_ho_ten" name="thu_huong_HoTen"
                           class="form-input" placeholder="Họ tên người thụ hưởng"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_mqh">Mối quan hệ với chủ HĐ</label>
                    <select id="thu_huong_mqh" name="thu_huong_MQH_chuHD" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="Bố mẹ">Bố mẹ</option>
                        <option value="Vợ chồng">Vợ / Chồng</option>
                        <option value="Anh chị em ruột">Anh chị em ruột</option>
                        <option value="Con">Con</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_ngay_sinh">Ngày sinh</label>
                    <input type="date" id="thu_huong_ngay_sinh" name="thu_huong_Ngaysinh" class="form-input"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_gioi_tinh">Giới tính</label>
                    <select id="thu_huong_gioi_tinh" name="thu_huong_Gioitinh" class="form-input">
                        <option value="">-- Chọn --</option>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_cccd">CCCD</label>
                    <input type="text" id="thu_huong_cccd" name="thu_huong_SoCCCD"
                           class="form-input" placeholder="Số CCCD người thụ hưởng"/>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_sdt">Số điện thoại</label>
                    <input type="text" id="thu_huong_sdt" name="thu_huong_SoDT"
                           class="form-input" placeholder="09xxxxxxxx"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_email">Email</label>
                    <input type="email" id="thu_huong_email" name="thu_huong_Email"
                           class="form-input" placeholder="email@example.com"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_dan_toc">Dân tộc</label>
                    <input type="text" id="thu_huong_dan_toc" name="thu_huong_Dantoc"
                           class="form-input" placeholder="Kinh"/>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group w-half">
                    <label class="field-label" for="thu_huong_dia_chi">Địa chỉ hiện tại</label>
                    <input type="text" id="thu_huong_dia_chi" name="thu_huong_DiaChiHientai"
                           class="form-input" placeholder="Địa chỉ người thụ hưởng"/>
                </div>
                <div class="field-group w-half">
                    <label class="field-label" for="thu_huong_hk">Hộ khẩu thường trú</label>
                    <input type="text" id="thu_huong_hk" name="thu_huong_DCThuongtru"
                           class="form-input" placeholder="HK thường trú người thụ hưởng"/>
                </div>
            </div>

            <hr class="field-divider"/>

            <p style="font-size:12px;font-weight:700;color:var(--navy);
                      text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
                🏦 Tài khoản ngân hàng người thụ hưởng
            </p>
            <div class="field-row">
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_stk">Số tài khoản</label>
                    <input type="text" id="thu_huong_stk" name="thu_huong_Sotaikhoan"
                           class="form-input" placeholder="Số tài khoản"/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_ngan_hang">Tên ngân hàng</label>
                    <input type="text" id="thu_huong_ngan_hang" name="thu_huong_TenNganHang"
                           class="form-input" placeholder="Vietcombank, BIDV..."/>
                </div>
                <div class="field-group w-third">
                    <label class="field-label" for="thu_huong_chu_tk">Chủ tài khoản</label>
                    <input type="text" id="thu_huong_chu_tk" name="thu_huong_HotenChuTK"
                           class="form-input" placeholder="Tên chủ tài khoản"/>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group" style="max-width:180px;">
                    <label class="field-label" for="phan_tram_thu_huong">
                        % Thụ hưởng <span class="req">*</span>
                    </label>
                    <input type="number" id="phan_tram_thu_huong" name="thu_huong_PhantramThuhuong"
                           class="form-input" min="0" max="100" step="0.01"
                           placeholder="100" value="100"/>
                </div>
                <div class="field-group w-full">
                    <label class="field-label" for="thu_huong_ghi_chu">Ghi chú</label>
                    <input type="text" id="thu_huong_ghi_chu" name="thu_huong_GhiChu"
                           class="form-input" placeholder="Ghi chú (nếu có)"/>
                </div>
            </div>

        </div>
    </div>

    <!-- SUBMIT BAR -->
    <div class="submit-bar">
        <div class="submit-info">
            <span class="glyphicon glyphicon-info-sign"></span>
            &nbsp;Sau khi lưu, hồ sơ sẽ ở trạng thái <strong>Chờ xác nhận</strong> trong 21 ngày.
        </div>
        <div class="btn-group-right">
            <button type="button" class="btn-reset" id="btn-reset">
                <span class="glyphicon glyphicon-refresh"></span> Nhập lại
            </button>
            <button type="button" class="btn-submit" id="btn-submit">
                <span class="glyphicon glyphicon-floppy-disk"></span>
                &nbsp;Lưu hồ sơ chờ
            </button>
        </div>
    </div>

    </form>
</div>

<?php include_once 'html/emb_js.php'; ?>
<script src="js/vidix/Hopdong_nhapmoi.js" type="text/javascript"></script>

</body>
</html>