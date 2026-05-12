/**
 * Hopdong_nhapmoi.js — Phiên bản 2.0
 * Logic cho form nhập hợp đồng mới với 5 card
 */

$(document).ready(function () {

    // ═══════════════════════════════════════════════════════
    // THÔNG BÁO SAU REDIRECT (nếu cần)
    // ═══════════════════════════════════════════════════════
    $.urlParam = function (name) {
        var r = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        return (r && r.length > 1) ? decodeURIComponent(r[1]) : null;
    };

    // ═══════════════════════════════════════════════════════
    // CARD 1 — TRA CỨU CCCD TỰ ĐỘNG
    // Khi nhập đủ 9 hoặc 12 ký tự → tự động tra cứu
    // ═══════════════════════════════════════════════════════
    var timerCCCD;
    var khachHangFound = false;

    $('#cccd').on('input', function () {
        clearTimeout(timerCCCD);
        var cccd = $.trim($(this).val());
        $('#cccd-status').text('').removeClass('found notfound error');

        // CCCD VN: 9 số (cũ) hoặc 12 số (mới), hộ chiếu: 8 ký tự
        if (cccd.length < 8) return;

        timerCCCD = setTimeout(function () {
            traXuatCCCD(cccd);
        }, 600); // Debounce 600ms
    });

    function traXuatCCCD(cccd) {
        $('#cccd-spinner').show();
        $('#cccd-status').text('Đang tra cứu...').removeClass('found notfound error');

        $.ajax({
            url:      'VIDIX_function/getKhachHangByCCCD.php',
            type:     'POST',
            dataType: 'json',
            data:     { cccd: cccd },
            success: function (data) {
                $('#cccd-spinner').hide();

                if (data.found) {
                    fillAndLockKhachHang(data.khach_hang);

                    // Kiểm tra xem KH có đồng thời là CTV không
                    if (data.agent) {
                        $('#nv_tu_dong_code').val(data.agent.agent_code);
                        $('#nv_tu_dong_name').val(data.agent.full_name);
                        $('#agent_id_tu_dong').val(data.agent.agent_id);
                        $('#block-nv-tu-dong').slideDown(200);
                    } else {
                        $('#block-nv-tu-dong').slideUp(200);
                    }

                    // Điền NLH nếu có HĐ cũ
                    if (data.nlh) {
                        fillNLH(data.nlh);
                        var soHDCu = data.nlh.SoHD ? ' (từ HĐ ' + escHtml(data.nlh.SoHD) + ')' : '';
                        $('#cccd-status').addClass('found')
                            .html('✅ <strong>' + escHtml(data.khach_hang.HoTen) + '</strong>'
                                + '<br><small>📋 Đã điền thông tin NLH' + soHDCu + '</small>');
                    } else {
                        $('#cccd-status').addClass('found')
                            .html('✅ <strong>' + escHtml(data.khach_hang.HoTen) + '</strong>');
                    }

                    khachHangFound = true;
                } else {
                    unlockKhachHang();
                    clearNLH();
                    $('#block-nv-tu-dong').slideUp(200);
                    autoGenMaKH();
                    $('#cccd-status').addClass('notfound')
                        .html('ℹ️ Chưa có trong hệ thống — vui lòng nhập thông tin mới.');
                    khachHangFound = false;
                }

                // Tính lại HSS vì ngày sinh có thể đã thay đổi
                tinhHSS();
                updateStepper();
            },
            error: function (xhr) {
                $('#cccd-spinner').hide();
                var msg = '❌ Lỗi kết nối, vui lòng thử lại.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.message) msg = '❌ ' + resp.message;
                } catch(e) {}
                $('#cccd-status').addClass('error').html(msg);
            }
        });
    }

    // ═══════════════════════════════════════════════════════
    // ĐIỀN & KHÓA THÔNG TIN KHÁCH HÀNG
    // ═══════════════════════════════════════════════════════
    var FIELD_MAP = {
        ma_khach:                 'MaKH',
        ho_ten:                   'HoTen',
        so_dt:                    'SoDT',
        ngay_sinh:                'NgaySinh',
        ngay_cap_cccd:            'NgaycapCCCD',
        gioi_tinh:                'GioiTinh',
        email:                    'Email',
        dan_toc:                  'DanToc',
        quoc_tich:                'QuocTich',
        tinh_trang_hon_nhan:      'TinhTrangHonnhan',
        tinh_trang_suc_khoe_text: 'TinhTrangSucKhoe_text',
        tinh_trang_suc_khoe_bhyt: 'TinhTrangSucKhoe_bhyt',
        trinh_do_hoc_van:         'TrinhDoHocVan',
        noi_o_hien_tai:           'NoiOHientai',
        hk_thuong_tru:            'HKThuongtru',
        ghi_chu_kh:               'GhiChu',
    };

    function fillAndLockKhachHang(kh) {
        $.each(FIELD_MAP, function (fieldId, colName) {
            var val = kh[colName] || '';
            $('#' + fieldId).val(val).prop('disabled', true).addClass('kh-locked');
        });
        $('#lock-banner').slideDown(200);
        $('#btn-edit-sodt, #btn-edit-email').show();
        $('#btn-save-sodt, #btn-save-email').hide();
    }

    function unlockKhachHang() {
        $.each(FIELD_MAP, function (fieldId) {
            $('#' + fieldId).prop('disabled', false).removeClass('kh-locked');
        });
        $('#lock-banner').slideUp(200);
        $('#btn-edit-sodt, #btn-edit-email').hide();
        $('#btn-save-sodt, #btn-save-email').hide();
        $('#so_dt, #email').removeClass('inline-editing');
    }

    function autoGenMaKH() {
        $.ajax({
            url: 'VIDIX_function/genMaKH.php',
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                if (data.ma_kh) $('#ma_khach').val(data.ma_kh);
            }
        });
    }

    // ═══════════════════════════════════════════════════════
    // CARD 2 — TÍNH SỐ HSS VÀ NGÀY HĐ CHÍNH THỨC
    // ═══════════════════════════════════════════════════════

    // Tính ngày HĐ chính thức từ KB (8 ký tự đầu = DDMMYYYYHHmmss)
    $('#so_kb').on('input blur', function () {
        var kb = $.trim($(this).val());
        $('#han_huy_display').val('');
        $('#han_huy_sql').val('');
        $('#ngay_khoi_tao_sql').val('');

        if (kb.length < 8) return;

        var dd   = kb.substring(0, 2);
        var mm   = kb.substring(2, 4);
        var yyyy = kb.substring(4, 8);

        var d = parseInt(dd, 10);
        var m = parseInt(mm, 10) - 1;
        var y = parseInt(yyyy, 10);

        if (isNaN(d) || isNaN(m) || isNaN(y) || d < 1 || d > 31 || m < 0 || m > 11) return;

        var ngayKB = new Date(y, m, d);
        if (isNaN(ngayKB.getTime())) return;

        // Ngày chính thức = KB + 21 ngày
        var ngayChinhThuc = new Date(ngayKB);
        ngayChinhThuc.setDate(ngayChinhThuc.getDate() + 21);

        var d2 = String(ngayChinhThuc.getDate()).padStart(2, '0');
        var m2 = String(ngayChinhThuc.getMonth() + 1).padStart(2, '0');
        var y2 = ngayChinhThuc.getFullYear();

        $('#han_huy_display').val(d2 + '/' + m2 + '/' + y2);
        $('#han_huy_sql').val(y2 + '-' + m2 + '-' + d2);

        var d1 = String(ngayKB.getDate()).padStart(2, '0');
        var m1 = String(ngayKB.getMonth() + 1).padStart(2, '0');
        $('#ngay_khoi_tao_sql').val(y + '-' + m1 + '-' + d1);

        tinhHSS();
        updateStepper();
    });

    // Tính HSS khi thay đổi: ngày sinh, mã VP, Iv
    $('#ngay_sinh, #ma_vp_input, #so_iv').on('input blur change', function () {
        tinhHSS();
    });

    /**
     * Công thức HSS:
     * DDMMYYYY + "04/" + MaVP + Iv[3..7] (ký tự 4-8, 0-indexed: index 3 đến 7) + "/" + STT
     * STT lấy từ phần cuối của Iv (sau ký tự thứ 7)
     *
     * Iv format: Thứ(1) + Tuần(2) + Năm(4) + STT(n)
     * VD Iv = "2012026001" → thứ2, tuần01, năm2026, stt=001
     *   Iv[3..6] = "2026" (ký tự index 3,4,5,6 = 3 số cuối của năm index 4,5,6)
     *
     * Thực tế: HSS = DDMMYYYY + "04/" + VP + Iv[4..7] + "/" + Iv[7+]
     * Ví dụ: Ngày sinh 24/04/2001, VP=04, Iv=2012026001
     * → HSS = "2404200104/04026/001"
     */
    function tinhHSS() {
        var ngaySinh = $('#ngay_sinh').val(); // YYYY-MM-DD
        var maVP     = $.trim($('#ma_vp_input').val());
        var iv       = $.trim($('#so_iv').val());

        if (!ngaySinh || !maVP || iv.length < 7) {
            $('#so_hss_display').val('');
            $('#so_hss').val('');
            return;
        }

        // Chuyển YYYY-MM-DD → DDMMYYYY
        var parts = ngaySinh.split('-');
        if (parts.length !== 3) return;
        var ddmmyyyy = parts[2] + parts[1] + parts[0];

        // Lấy 3 số cuối của năm từ Iv (index 4,5,6)
        var namTuIv = iv.substring(3, 7); // 4 ký tự từ index 3 = "2026"
        var namCuoi3 = namTuIv.length >= 4 ? namTuIv.substring(1) : namTuIv; // "026"

        // STT = các ký tự từ index 7 trở đi
        var stt = iv.length > 7 ? iv.substring(7) : '';

        var hss = ddmmyyyy + '04/' + maVP + namCuoi3 ;

        $('#so_hss_display').val(hss);
        $('#so_hss').val(hss);
        updateStepper();
    }

    // ═══════════════════════════════════════════════════════
    // CARD 3 — CHECKBOX TÙY CHỌN (radio-like)
    // ═══════════════════════════════════════════════════════
    $('#tuy-chon-thamgia-group input[type="checkbox"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#tuy-chon-thamgia-group input').not(this).prop('checked', false);
        }
        var val = $(this).val();
        var isChecked = $(this).is(':checked');

        if (isChecked && val === 'B') {
            $('#banner-tao-nv').slideDown(200);
            //-- Tùy chọn B: Đổi nhãn thành "Nhân viên tuyển dụng:
			$('#label-block-nv').html('‍  🧑‍💼  Nhân viên tư vấn (cấp trên trực tiếp)');
			$('#label-agent-select').text('Nhân viên tuyển dụng');
			$('#err-agent').text('Vui lòng chọn nhân viên tuyển dụng');
			
			// Vẫn giữ và hiện block NV (không ẩn):
            $('#block-nv-tuvao').slideDown(200);
			$('#sponsor_agent_id').val($('#agent_id_banhang').val());
			
			//Khi chọn NV tuyển dụng -> lưu vào sponsor_agent_id:
			$('#agent_id_banhang').off('change.sponsorB').on('change.sponsorB',function(){
				$('#sponsor_agent_id').val($(this).val());
			});
			
        } else {
            $('#banner-tao-nv').slideUp(200);
            $('#block-nv-tuvao').slideDown(200);
			
			//--Tùy chọn A/Khác: trả lại nhãn gốc:
			$('#label-block-nv').html('‍  🧑‍💼  Nhân viên tư vấn bán hàng');
			$('#label-agent-select').text('Nhân viên tư vấn');
			$('#err-agent').text('Vui lòng chọn nhân viên tư vấn');
			
			//Bỏ event sponsor:
			$('#agent_id_banhang').off('change.sponsorB');
			$('#sponsor_agent_id').val('');
			 $('#agent_id_banhang').val('');
			
        }
        updateStepper();
    });

    // Loại HĐ — chỉ chọn 1
    $('#loai-hd-group input[type="checkbox"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#loai-hd-group input').not(this).prop('checked', false);
        }
        updateStepper();
    });

    // // Tính trị giá HĐ
    // $('#so_dvtc, #so_nam_hd').on('change input', function () {
        // var dvtc = parseInt($('#so_dvtc').val()) || 0;
        // var nam  = parseInt($('#so_nam_hd').val()) || 0;
        // if (dvtc > 0 && nam > 0) {
            // var trigia = dvtc * nam * 105000 * 12;
            // $('#trigia_hd_display').val(formatNumber(trigia) + ' đ');
            // $('#so_tien_nop').val(trigia);
        // } else {
            // $('#trigia_hd_display').val('');
            // $('#so_tien_nop').val('');
        // }
        // updateStepper();
    // });
// Tính trị giá HĐ và xử lý so_tien_nop theo loại HĐ
$('#so_dvtc, #so_nam_hd').on('change input', function () {
    capNhatTriGiaVaTienNop();
});

	// Khi đổi loại HĐ → cập nhật lại
	$('#loai-hd-group input[type="checkbox"]').on('change', function () {
		if ($(this).is(':checked')) {
			$('#loai-hd-group input').not(this).prop('checked', false);
		}
		capNhatTriGiaVaTienNop();
		updateStepper();
	});

	function capNhatTriGiaVaTienNop() {
		var dvtc   = parseInt($('#so_dvtc').val())   || 0;
		var nam    = parseInt($('#so_nam_hd').val())  || 0;
		var loaiHD = $('#loai-hd-group input:checked').val() || '';

		if (dvtc > 0 && nam > 0) {
			var trigia = dvtc * nam * 105000 * 12;
			$('#trigia_hd_display').val(formatNumber(trigia) + ' đ');

			if (loaiHD === 'AG') {
				// AG: số tiền nộp = đúng bằng trị giá HĐ, khóa lại
				$('#so_tien_nop')
					.val(formatNumber(trigia))
					.prop('readonly', true)
					.css('background', '#f1f5f9');
				$('#tip-so-tien-nop').text('(tự động = trị giá HĐ)');
			} else {
				// A: NV tự nhập, mở khóa
				$('#so_tien_nop')
					.prop('readonly', false)
					.css('background', '');
				$('#tip-so-tien-nop').text('(nhập số tiền KH nộp)');
				// Gợi ý tối đa = trị giá HĐ nhưng không ép
				if (!$('#so_tien_nop').val()) {
					$('#so_tien_nop').attr('placeholder',
						'Tối đa: ' + formatNumber(trigia));
				}
			}
		} else {
			$('#trigia_hd_display').val('');
			$('#so_tien_nop').val('').prop('readonly', false).css('background', '');
			$('#tip-so-tien-nop').text('');
		}
		updateStepper();
	}
    // ═══════════════════════════════════════════════════════
    // CARD 4 — NGƯỜI LIÊN HỆ
    // ═══════════════════════════════════════════════════════
    var NLH_FIELD_MAP = {
        ho_ten_nlh:      'HoTenNLH',
        mqh_chu_hd:      'MQH_chuHD',
        ngay_sinh_nlh:   'NgaysinhNLH',
        gioi_tinh_nlh:   'GioitinhNLH',
        so_cccd_nlh:     'SoCCCDNLH',
        noi_o_nlh:       'NoiohiennayNLH',
        hk_nlh:          'DCThuongtruNLH',
        so_tai_khoan:    'Sotaikhoan',
        ten_ngan_hang:   'TenNganHang',
        ho_ten_chu_tk:   'HotenChuTK',
    };

    function fillNLH(nlh) {
        $.each(NLH_FIELD_MAP, function (fieldId, colName) {
            $('#' + fieldId).val(nlh[colName] || '');
        });
        $('#nlh-banner').slideDown(200);
    }

    function clearNLH() {
        $.each(NLH_FIELD_MAP, function (fieldId) {
            $('#' + fieldId).val('');
        });
        $('#nlh-banner').slideUp(200);
    }

    // ═══════════════════════════════════════════════════════
    // CARD 4 — CHECKBOX: NLH = Người thụ hưởng
    // ═══════════════════════════════════════════════════════
    $('#nlh_la_thu_huong').on('change', function () {
        var $label = $('#label-nlh-thu-huong');
        if ($(this).is(':checked')) {
            $label.css('border-color', 'var(--blue)').css('background', '#eff6ff');
            copyNLHtoThuHuong();
            $('#banner-copy-nlh').slideDown(200);
        } else {
            $label.css('border-color', '').css('background', '');
            clearThuHuong();
            $('#banner-copy-nlh').slideUp(200);
        }
    });

    // Map field NLH → field thụ hưởng
    var NLH_TO_TH_MAP = {
        'ho_ten_nlh':    'thu_huong_ho_ten',
        'mqh_chu_hd':    'thu_huong_mqh',
        'ngay_sinh_nlh': 'thu_huong_ngay_sinh',
        'gioi_tinh_nlh': 'thu_huong_gioi_tinh',
        'so_cccd_nlh':   'thu_huong_cccd',
        'noi_o_nlh':     'thu_huong_dia_chi',
        'hk_nlh':        'thu_huong_hk',
        // Ngân hàng
        'so_tai_khoan':  'thu_huong_stk',
        'ten_ngan_hang': 'thu_huong_ngan_hang',
        'ho_ten_chu_tk': 'thu_huong_chu_tk',
    };

    function copyNLHtoThuHuong() {
        $.each(NLH_TO_TH_MAP, function (srcId, dstId) {
            $('#' + dstId).val($('#' + srcId).val());
        });
        // Mặc định % thụ hưởng = 100
        if (!$('#phan_tram_thu_huong').val()) {
            $('#phan_tram_thu_huong').val(100);
        }
    }

    function clearThuHuong() {
        $.each(NLH_TO_TH_MAP, function (srcId, dstId) {
            $('#' + dstId).val('');
        });
    }

    // Khi NLH thay đổi → cập nhật thụ hưởng nếu đang được sync
    $('#ho_ten_nlh, #mqh_chu_hd, #ngay_sinh_nlh, #gioi_tinh_nlh, #so_cccd_nlh, #noi_o_nlh, #hk_nlh,#so_tai_khoan, #ten_ngan_hang, #ho_ten_chu_tk')
        .on('change input', function () {
            if ($('#nlh_la_thu_huong').is(':checked')) {
                copyNLHtoThuHuong();
            }
        });

    // ═══════════════════════════════════════════════════════
    // CARD 5 — DOANH NGHIỆP THỪA KẾ
    // ═══════════════════════════════════════════════════════
    $('#doanh_nghiep_thua_ke').on('change', function () {
        var $label = $('#label-doanh-nghiep');
        if ($(this).is(':checked')) {
            $label.css('border-color', 'var(--blue)').css('background', '#eff6ff');
            $('#block-doanh-nghiep').slideDown(200);
        } else {
            $label.css('border-color', '').css('background', '');
            $('#block-doanh-nghiep').slideUp(200);
            $('#ten_dn, #mst').val('');
        }
    });

    // ═══════════════════════════════════════════════════════
    // INLINE EDIT: SĐT / EMAIL
    // ═══════════════════════════════════════════════════════
    window.enableInlineEdit = function (fieldId, btnEditId, btnSaveId) {
        $('#' + fieldId).prop('disabled', false)
                        .removeClass('kh-locked')
                        .addClass('inline-editing')
                        .focus();
        $('#' + btnEditId).hide();
        $('#' + btnSaveId).show();
    };

    window.saveInlineEdit = function (fieldId, btnEditId, btnSaveId) {
        var newVal  = $.trim($('#' + fieldId).val());
        var maKhach = $.trim($('#ma_khach').val());

        if (fieldId === 'so_dt' && !newVal) {
            alert('Số điện thoại không được để trống!');
            return;
        }

        var $btn = $('#' + btnSaveId);
        $btn.prop('disabled', true).text('⏳');

        $.ajax({
            url:      'VIDIX_function/updateKhachHang_SdtEmail.php',
            type:     'POST',
            dataType: 'json',
            data:     { ma_khach: maKhach, field: fieldId, value: newVal },
            success: function (data) {
                $btn.prop('disabled', false).text('💾');
                if (data.success) {
                    $('#' + fieldId).prop('disabled', true)
                                   .addClass('kh-locked')
                                   .removeClass('inline-editing')
                                   .css('border-color', 'var(--success)');
                    setTimeout(function () { $('#' + fieldId).css('border-color', ''); }, 2000);
                    $('#' + btnSaveId).hide();
                    $('#' + btnEditId).show();
                } else {
                    alert('Lỗi: ' + (data.message || 'Vui lòng thử lại'));
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('💾');
                alert('Lỗi kết nối!');
            }
        });
    };

    // ═══════════════════════════════════════════════════════
    // STEPPER
    // ═══════════════════════════════════════════════════════
    function updateStepper() {
        var step1ok = $.trim($('#cccd').val()).length >= 8
                   && $.trim($('#ho_ten').val())
                   && $.trim($('#so_dt').val());

        var step2ok = $.trim($('#so_kb').val())
                   && $.trim($('#so_hss_display').val())
                   && $.trim($('#han_huy_display').val());

        var tuychon = $('#tuy-chon-thamgia-group input:checked').val();
        var step3ok = !!tuychon
                   && $('#loai-hd-group input:checked').length > 0
                   && parseInt($('#so_dvtc').val()) > 0
                   && $('#so_nam_hd').val()
                   && (tuychon === 'B' || !!$('#agent_id_banhang').val());

        var step4ok = $.trim($('#ho_ten_nlh').val()).length > 0;

        var step5ok = $.trim($('#thu_huong_ho_ten').val()).length > 0;

        setStepState(1, step1ok ? 'done' : 'active');
        setStepState(2, step2ok ? 'done' : (step1ok ? 'active' : 'waiting'));
        setStepState(3, step3ok ? 'done' : (step2ok ? 'active' : 'waiting'));
        setStepState(4, step4ok ? 'done' : (step3ok ? 'active' : 'waiting'));
        setStepState(5, step5ok ? 'done' : (step4ok ? 'active' : 'waiting'));

        $('#line-1').toggleClass('done', !!step1ok);
        $('#line-2').toggleClass('done', !!step2ok);
        $('#line-3').toggleClass('done', !!step3ok);
        $('#line-4').toggleClass('done', !!step4ok);
    }

    function setStepState(n, state) {
        var $c = $('#step' + n + '-circle');
        var $l = $c.siblings('.step-label');
        $c.removeClass('active done waiting').addClass(state);
        $l.removeClass('active done waiting').addClass(state);
        $c.html(state === 'done' ? '✓' : n);
    }

    $('#main-form input, #main-form select, #main-form textarea')
        .on('change input', updateStepper);
    updateStepper();

    // ═══════════════════════════════════════════════════════
    // SUBMIT AJAX
    // ═══════════════════════════════════════════════════════
    $('#btn-submit').on('click', function () {
        if (!validateForm()) return;

        // Ghép tình trạng sức khỏe
        var skText = $.trim($('#tinh_trang_suc_khoe_text').val());
        var skBHYT = $.trim($('#tinh_trang_suc_khoe_bhyt').val());
        $('#tinh_trang_suc_khoe_combined').val(skText + (skBHYT ? ' – ' + skBHYT : ''));

        // Bỏ disabled trước khi serialize
        $('.kh-locked').prop('disabled', false);

        // Thêm agent_id nếu là tùy chọn B và KH là CTV
        var tuychon = $('#tuy-chon-thamgia-group input:checked').val();
        var extraData = '&submit_ho_so=1';
        if (tuychon === 'B' && $('#agent_id_tu_dong').val()) {
            extraData += '&agent_id_banhang=' + $('#agent_id_tu_dong').val();
        }

        var $btn = $(this);
        $btn.prop('disabled', true)
            .html('<span class="spinner-inline"></span> Đang lưu...');

        $.ajax({
            url:      'PHP/Hopdong_nhapmoi_PHP.php',
            type:     'POST',
            data:     $('#main-form').serialize() + extraData,
            dataType: 'json',
            success: function (data) {
                $btn.prop('disabled', false)
                    .html('<span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;Lưu hồ sơ chờ');
                if (data.success) {
                    if (data.warn_option_b) {
						showMainAlert('warning', '⚠️ ' + data.warn_option_b);
						// Đợi 4 giây để NV đọc cảnh báo rồi mới reset
						setTimeout(function () { $('#btn-reset').trigger('click'); }, 4000);
					} else {
						showMainAlert('success', '✅ Lưu hồ sơ thành công! ID: ' + data.id
							+ ' - Mã khách hàng: ' + (data.ma_khach || ''));
						setTimeout(function () { $('#btn-reset').trigger('click'); }, 2000);
					}
                } else {
                    showMainAlert('danger', '❌ Lỗi: ' + data.message);
                    console.error('[HoSoCho] Lỗi:', data);
                }
                // Khóa lại KH fields nếu đã tìm thấy
                if (khachHangFound) {
                    $.each(FIELD_MAP, function (fieldId) {
                        $('#' + fieldId).prop('disabled', true).addClass('kh-locked');
                    });
                }
            },
            error: function (xhr, status, err) {
                $btn.prop('disabled', false)
                    .html('<span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;Lưu hồ sơ chờ');
                console.error('[HoSoCho] HTTP Error:', status, err);
                console.error('[HoSoCho] Response:', xhr.responseText);
                showMainAlert('danger', '❌ Lỗi kết nối hoặc PHP. Xem Console (F12) để biết chi tiết.');
            }
        });
    });

    // ═══════════════════════════════════════════════════════
    // VALIDATE
    // ═══════════════════════════════════════════════════════
    function validateForm() {
        var valid = true;
        clearAllErrors();

        // Card 1
        if ($.trim($('#cccd').val()).length < 8)
            { showErr('cccd', 'Vui lòng nhập số CCCD hợp lệ'); valid = false; }
        if (!$.trim($('#ho_ten').val()))
            { showErr('ho_ten', 'Vui lòng nhập họ tên'); valid = false; }
        if (!$.trim($('#so_dt').val()))
            { showErr('so_dt', 'Vui lòng nhập số điện thoại'); valid = false; }

        // Card 2
        if (!$.trim($('#so_kb').val()))
            { showErr('so_kb', 'Vui lòng nhập số KB'); valid = false; }
        if (!$.trim($('#han_huy_display').val()))
            { showErr('so_kb', 'Số KB không hợp lệ'); valid = false; }
        if (!$.trim($('#ma_vp_input').val()))
            { showErr('ma_vp', 'Vui lòng nhập mã văn phòng'); valid = false; }
        if (!$.trim($('#so_iv').val()))
            { showErr('so_iv', 'Vui lòng nhập số Iv'); valid = false; }

        // Card 3
        if ($('#tuy-chon-thamgia-group input:checked').length === 0)
            { $('#err-loai_hd').text('Vui lòng chọn tùy chọn tham gia').addClass('show'); valid = false; }
        if ($('#loai-hd-group input:checked').length === 0)
            { $('#err-loai_hd2').text('Vui lòng chọn loại hợp đồng').addClass('show'); valid = false; }
        if (!(parseInt($('#so_dvtc').val()) > 0))
            { showErr('so_dvtc', 'Vui lòng nhập số ĐVTC hợp lệ'); valid = false; }
        if (!$('#so_nam_hd').val())
            { showErr('so_nam_hd', 'Vui lòng chọn số năm HĐ'); valid = false; }

        var tuychon = $('#tuy-chon-thamgia-group input:checked').val();
        if (tuychon !== 'B' && tuychon !== 'Khac' && !$('#agent_id_banhang').val())
            { showErr('agent', 'Vui lòng chọn nhân viên tư vấn'); valid = false; }

        if (!valid) {
            $('html, body').animate({ scrollTop: $('.field-error.show').first().offset().top - 120 }, 400);
        }
        return valid;
    }

    // ═══════════════════════════════════════════════════════
    // RESET
    // ═══════════════════════════════════════════════════════
    $('#btn-reset').on('click', function () {
        if (!confirm('Bạn có chắc muốn xóa toàn bộ dữ liệu đã nhập?')) return;
        $('#main-form')[0].reset();
        unlockKhachHang();
        clearNLH();
        clearThuHuong();
        $('#cccd-status').text('').removeClass('found notfound error');
        $('#cccd-spinner').hide();
        $('#han_huy_display, #so_hss_display').val('');
		$('#so_tien_nop').val('').prop('readonly', false).css('background', '');
        $('#han_huy_sql, #ngay_khoi_tao_sql, #so_hss').val('');
        $('#trigia_hd_display').val('');
        $('#lock-banner, #nlh-banner, #banner-copy-nlh, #banner-tao-nv').slideUp(200);
        $('#block-nv-tu-dong, #block-doanh-nghiep').slideUp(200);
        $('#block-nv-tuvao').slideDown(200);
        clearAllErrors();
        updateStepper();
        khachHangFound = false;
    });

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════
    function showErr(fieldId, msg) {
		
        $('#' + fieldId).addClass('is-invalid');
        $('#err-' + fieldId).text(msg).addClass('show');
    }

    function clearAllErrors() {
        $('.form-input').removeClass('is-invalid');
        $('.field-error').removeClass('show').text('');
    }

    function showMainAlert(type, msg) {
        $('#main-alert').removeClass('success danger').addClass(type)
                        .html(msg).show();
        if (type === 'success') {
            setTimeout(function () { $('#main-alert').fadeOut(); }, 6000);
        }
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    function formatNumber(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatDate(ddmmyyyy) {
        return ddmmyyyy.replace(/^(\d{2})(\d{2})(\d{4})$/, '$3-$2-$1');
    }

}); // end document.ready