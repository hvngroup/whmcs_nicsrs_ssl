<?php
/**
 * NicSRS SSL Module - Vietnamese Language File
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

// General
$_LANG['ssl_management'] = 'Quản lý SSL';
$_LANG['ssl_certificate'] = 'Chứng chỉ SSL';
$_LANG['configure_certificate'] = 'Cấu hình Chứng chỉ';
$_LANG['certificate_management'] = 'Quản lý Chứng chỉ';
$_LANG['module_version'] = 'Phiên bản Module';

// Status
$_LANG['status'] = 'Trạng thái';
$_LANG['awaiting_configuration'] = 'Chờ cấu hình';
$_LANG['draft'] = 'Bản nháp';
$_LANG['pending'] = 'Đang chờ xử lý';
$_LANG['processing'] = 'Đang xử lý';
$_LANG['complete'] = 'Hoàn thành';
$_LANG['issued'] = 'Đã cấp phát';
$_LANG['cancelled'] = 'Đã hủy';
$_LANG['revoked'] = 'Đã thu hồi';
$_LANG['expired'] = 'Đã hết hạn';
$_LANG['reissue_pending'] = 'Đang chờ cấp lại';
$_LANG['verified'] = 'Đã xác minh';
$_LANG['un_verified'] = 'Chưa xác minh';

// Steps
$_LANG['step'] = 'Bước';
$_LANG['step_csr'] = 'CSR';
$_LANG['step_domain'] = 'Xác minh Tên miền';
$_LANG['step_contacts'] = 'Thông tin Liên hệ';
$_LANG['step_organization'] = 'Thông tin Tổ chức';
$_LANG['step_review'] = 'Xem lại';

// CSR Section
$_LANG['csr'] = 'CSR';
$_LANG['csr_configuration'] = 'Cấu hình CSR';
$_LANG['csr_des'] = 'Yêu cầu ký chứng chỉ (CSR) là bắt buộc để đăng ký chứng chỉ SSL. Bạn có thể tự động tạo hoặc dán CSR có sẵn.';
$_LANG['auto_generate_csr'] = 'Tự động tạo CSR';
$_LANG['manual_csr'] = 'Nhập CSR thủ công';
$_LANG['is_manual_csr'] = 'Tôi có CSR riêng';
$_LANG['paste_csr'] = 'Dán CSR của bạn vào đây';
$_LANG['generate_csr'] = 'Tạo CSR';
$_LANG['decode_csr'] = 'Giải mã CSR';
$_LANG['csr_decoded'] = 'Giải mã CSR thành công';
$_LANG['invalid_csr'] = 'Định dạng CSR không hợp lệ';

// CSR Fields
$_LANG['common_name'] = 'Tên miền chính (Common Name)';
$_LANG['organization'] = 'Tổ chức';
$_LANG['organizational_unit'] = 'Đơn vị/Phòng ban';
$_LANG['city'] = 'Thành phố';
$_LANG['locality'] = 'Thành phố/Địa phương';
$_LANG['state'] = 'Tỉnh/Thành';
$_LANG['province'] = 'Tỉnh';
$_LANG['country'] = 'Quốc gia';
$_LANG['email'] = 'Email';
$_LANG['email_address'] = 'Địa chỉ Email';

// Domain Section
$_LANG['domain'] = 'Tên miền';
$_LANG['domain_info'] = 'Thông tin Tên miền';
$_LANG['domain_name'] = 'Tên miền';
$_LANG['primary_domain'] = 'Tên miền chính';
$_LANG['additional_domains'] = 'Tên miền bổ sung';
$_LANG['add_domain'] = 'Thêm tên miền';
$_LANG['remove_domain'] = 'Xóa';
$_LANG['max_domain'] = 'Số lượng tên miền tối đa';
$_LANG['add'] = 'Thêm';
$_LANG['set_for_all'] = 'Áp dụng cho tất cả';
$_LANG['must_same_pmain'] = 'Phải giống với tên miền chính';
$_LANG['overplus'] = 'Bạn đã vượt quá số lượng tên miền cho phép';

// DCV (Domain Control Validation)
$_LANG['dcv'] = 'Xác minh DCV';
$_LANG['dcv_method'] = 'Phương thức DCV';
$_LANG['dcv_status'] = 'Trạng thái DCV';
$_LANG['domain_validation'] = 'Xác minh Tên miền';
$_LANG['domain_validation_required'] = 'Yêu cầu Xác minh Tên miền';
$_LANG['please_choose'] = 'Vui lòng chọn';
$_LANG['http_csr_hash'] = 'Xác minh qua File HTTP';
$_LANG['https_csr_hash'] = 'Xác minh qua File HTTPS';
$_LANG['cname_csr_hash'] = 'Xác minh qua DNS CNAME';
$_LANG['dns_csr_hash'] = 'Xác minh qua DNS TXT';
$_LANG['email_validation'] = 'Xác minh qua Email';
$_LANG['update_dcv'] = 'Cập nhật DCV';
$_LANG['resend_dcv'] = 'Gửi lại DCV';
$_LANG['dcv_instructions'] = 'Hướng dẫn DCV';

// DCV Instructions
$_LANG['http_instructions'] = 'Tạo file tại đường dẫn sau trên máy chủ của bạn:';
$_LANG['https_instructions'] = 'Tạo file tại đường dẫn HTTPS sau trên máy chủ của bạn:';
$_LANG['dns_cname_instructions'] = 'Thêm bản ghi CNAME sau vào DNS của bạn:';
$_LANG['dns_txt_instructions'] = 'Thêm bản ghi TXT sau vào DNS của bạn:';
$_LANG['email_instructions'] = 'Email xác minh sẽ được gửi đến địa chỉ đã chọn.';
$_LANG['file_path'] = 'Đường dẫn File';
$_LANG['file_content'] = 'Nội dung File';
$_LANG['dns_host'] = 'Host DNS';
$_LANG['dns_value'] = 'Giá trị DNS';
$_LANG['dns_type'] = 'Loại DNS';
$_LANG['dcv_email'] = 'Email Xác minh';
$_LANG['down_txt'] = 'Tải file xác minh';

// Contact Information
$_LANG['contacts'] = 'Thông tin Liên hệ';
$_LANG['admin_contact'] = 'Liên hệ Quản trị';
$_LANG['tech_contact'] = 'Liên hệ Kỹ thuật';
$_LANG['title'] = 'Chức danh';
$_LANG['first_name'] = 'Tên';
$_LANG['last_name'] = 'Họ';
$_LANG['phone'] = 'Số điện thoại';
$_LANG['organization_name'] = 'Tên Tổ chức';

// Organization Information
$_LANG['organization_info'] = 'Thông tin Tổ chức';
$_LANG['org_name'] = 'Tên Tổ chức';
$_LANG['org_address'] = 'Địa chỉ';
$_LANG['org_city'] = 'Thành phố';
$_LANG['org_state'] = 'Tỉnh/Thành';
$_LANG['org_country'] = 'Quốc gia';
$_LANG['org_postal'] = 'Mã bưu điện';
$_LANG['org_phone'] = 'Điện thoại';
$_LANG['org_division'] = 'Phòng/Ban';
$_LANG['idType'] = 'Loại giấy tờ';
$_LANG['organizationCode'] = 'Mã số thuế / ĐKKD';
$_LANG['organizationRegNumber'] = 'Số đăng ký';
$_LANG['other'] = 'Khác';

// Renewal
$_LANG['is_renew'] = 'Đây là gia hạn?';
$_LANG['is_renew_des'] = 'Nếu đây là gia hạn chứng chỉ hiện có, chọn "Có" để có thể nhận thêm thời gian bonus.';
$_LANG['is_renew_option_new'] = 'Không, chứng chỉ mới';
$_LANG['is_renew_option_renew'] = 'Có, gia hạn';

// Actions
$_LANG['actions'] = 'Thao tác';
$_LANG['submit'] = 'Gửi';
$_LANG['submit_request'] = 'Gửi Yêu cầu';
$_LANG['save_draft'] = 'Lưu Bản nháp';
$_LANG['cancel'] = 'Hủy';
$_LANG['back'] = 'Quay lại';
$_LANG['next'] = 'Tiếp theo';
$_LANG['refresh'] = 'Làm mới';
$_LANG['refresh_status'] = 'Làm mới Trạng thái';
$_LANG['download'] = 'Tải xuống';
$_LANG['download_certificate'] = 'Tải Chứng chỉ';
$_LANG['down_cert'] = 'Tải Chứng chỉ';
$_LANG['down_key'] = 'Tải Private Key';
$_LANG['reissue'] = 'Cấp lại';
$_LANG['repalce'] = 'Cấp lại';
$_LANG['reissue_certificate'] = 'Cấp lại Chứng chỉ';
$_LANG['renew'] = 'Gia hạn';
$_LANG['renew_certificate'] = 'Gia hạn Chứng chỉ';
$_LANG['revoke'] = 'Thu hồi';
$_LANG['revoke_certificate'] = 'Thu hồi Chứng chỉ';
$_LANG['cancel_order'] = 'Hủy Đơn hàng';

// Download Formats
$_LANG['select_download_format'] = 'Chọn Định dạng Tải xuống';
$_LANG['format_all'] = 'Tất cả Định dạng (ZIP)';
$_LANG['format_apache'] = 'Apache (.crt + .ca-bundle)';
$_LANG['format_nginx'] = 'Nginx (.pem)';
$_LANG['format_iis'] = 'IIS (.p12)';
$_LANG['format_tomcat'] = 'Tomcat (.jks)';

// Certificate Info
$_LANG['certificate'] = 'Chứng chỉ';
$_LANG['certificate_info'] = 'Thông tin Chứng chỉ';
$_LANG['certificate_id'] = 'Mã Chứng chỉ';
$_LANG['certificate_type'] = 'Loại Chứng chỉ';
$_LANG['certificate_issued'] = 'Chứng chỉ đã Cấp phát';
$_LANG['certificate_pending'] = 'Chứng chỉ đang Chờ';
$_LANG['cert_status'] = 'Trạng thái Chứng chỉ';
$_LANG['cert_begin'] = 'Ngày cấp';
$_LANG['cert_end'] = 'Ngày hết hạn';
$_LANG['days_remaining'] = 'Số ngày còn lại';
$_LANG['expiring_soon'] = 'Sắp hết hạn';
$_LANG['vendor_id'] = 'Mã Nhà cung cấp';
$_LANG['last_refresh'] = 'Cập nhật lần cuối';

// Messages
$_LANG['apply_des'] = 'Vui lòng hoàn thành biểu mẫu bên dưới để yêu cầu chứng chỉ SSL.';
$_LANG['message_des'] = 'Yêu cầu chứng chỉ đã được gửi. Vui lòng hoàn thành xác minh tên miền.';
$_LANG['complete_des'] = 'Chứng chỉ của bạn đã được cấp phát thành công. Bạn có thể tải xuống hoặc cấp lại bên dưới.';
$_LANG['replace_des'] = 'Vui lòng cung cấp thông tin mới để cấp lại chứng chỉ.';
$_LANG['cancelled_des'] = 'Chứng chỉ này đã bị hủy hoặc thu hồi.';
$_LANG['email_info'] = 'Thông tin Email';
$_LANG['email_wait_info'] = 'Yêu cầu đã được gửi. Vui lòng kiểm tra email để hoàn thành xác minh.';

// Confirmations
$_LANG['sure_to_cancel'] = 'Bạn có chắc muốn hủy chứng chỉ này?';
$_LANG['sure_to_replace'] = 'Bạn có chắc muốn cấp lại chứng chỉ này?';
$_LANG['sure_to_revoke'] = 'Bạn có chắc muốn thu hồi chứng chỉ này? Hành động này không thể hoàn tác.';
$_LANG['confirm_action'] = 'Xác nhận Thao tác';
$_LANG['cancel_reason'] = 'Lý do Hủy';
$_LANG['revoke_reason'] = 'Lý do Thu hồi';

// Success Messages
$_LANG['success'] = 'Thành công';
$_LANG['submit_success'] = 'Gửi yêu cầu thành công';
$_LANG['draft_saved'] = 'Lưu bản nháp thành công';
$_LANG['status_refreshed'] = 'Đã cập nhật trạng thái';
$_LANG['dcv_updated'] = 'Đã cập nhật phương thức DCV';
$_LANG['cancelled_success'] = 'Hủy đơn hàng thành công';
$_LANG['revoked_success'] = 'Thu hồi chứng chỉ thành công';
$_LANG['reissue_success'] = 'Gửi yêu cầu cấp lại thành công';

// Error Messages
$_LANG['error'] = 'Lỗi';
$_LANG['params_error'] = 'Lỗi tham số';
$_LANG['action_not_found'] = 'Thao tác không được hỗ trợ';
$_LANG['pay_order_first'] = 'Vui lòng thanh toán đơn hàng trước';
$_LANG['product_offline'] = 'Sản phẩm không khả dụng, vui lòng liên hệ hỗ trợ';
$_LANG['service_error'] = 'Không tìm thấy dịch vụ, vui lòng thử lại';
$_LANG['status_error'] = 'Trạng thái đơn hàng không hợp lệ';
$_LANG['sys_error'] = 'Lỗi hệ thống, vui lòng liên hệ hỗ trợ';
$_LANG['cert_not_found'] = 'Không tìm thấy chứng chỉ';
$_LANG['cert_not_issued'] = 'Chứng chỉ chưa được cấp phát';
$_LANG['api_error'] = 'Lỗi API';
$_LANG['validation_error'] = 'Lỗi Xác thực';

// Server Types
$_LANG['server_type'] = 'Loại Máy chủ';
$_LANG['server_other'] = 'Khác';
$_LANG['server_apache'] = 'Apache';
$_LANG['server_nginx'] = 'Nginx';
$_LANG['server_iis'] = 'Microsoft IIS';
$_LANG['server_tomcat'] = 'Tomcat';
$_LANG['server_cpanel'] = 'cPanel';
$_LANG['server_plesk'] = 'Plesk';

// Misc
$_LANG['loading'] = 'Đang tải...';
$_LANG['please_wait'] = 'Vui lòng đợi...';
$_LANG['required'] = 'Bắt buộc';
$_LANG['optional'] = 'Tùy chọn';
$_LANG['yes'] = 'Có';
$_LANG['no'] = 'Không';
$_LANG['na'] = 'N/A';
$_LANG['copy'] = 'Sao chép';
$_LANG['copied'] = 'Đã sao chép!';
$_LANG['close'] = 'Đóng';
$_LANG['help'] = 'Trợ giúp';
$_LANG['more_info'] = 'Thông tin thêm';