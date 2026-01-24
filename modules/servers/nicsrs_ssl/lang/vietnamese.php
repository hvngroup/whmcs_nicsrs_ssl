<?php
/**
 * NicSRS SSL Module - Vietnamese Language File
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

// General
$_LANG['module_name'] = 'NicSRS SSL';
$_LANG['success'] = 'Thành công';
$_LANG['error'] = 'Lỗi';
$_LANG['warning'] = 'Cảnh báo';
$_LANG['info'] = 'Thông tin';
$_LANG['close'] = 'Đóng';
$_LANG['cancel'] = 'Hủy';
$_LANG['save'] = 'Lưu';
$_LANG['submit'] = 'Gửi';
$_LANG['confirm'] = 'Xác nhận';
$_LANG['yes'] = 'Có';
$_LANG['no'] = 'Không';
$_LANG['optional'] = 'Tùy chọn';
$_LANG['required'] = 'Bắt buộc';
$_LANG['actions'] = 'Thao tác';
$_LANG['view'] = 'Xem';
$_LANG['copy'] = 'Sao chép';
$_LANG['copied'] = 'Đã sao chép!';
$_LANG['days'] = 'ngày';
$_LANG['days_remaining'] = 'ngày còn lại';

// Status Labels
$_LANG['status'] = 'Trạng thái';
$_LANG['status_awaiting'] = 'Chờ cấu hình';
$_LANG['status_pending'] = 'Đang chờ';
$_LANG['status_processing'] = 'Đang xử lý';
$_LANG['status_complete'] = 'Đã cấp';
$_LANG['status_active'] = 'Đang hoạt động';
$_LANG['status_expiring'] = 'Sắp hết hạn';
$_LANG['status_expired'] = 'Đã hết hạn';
$_LANG['status_cancelled'] = 'Đã hủy';
$_LANG['status_revoked'] = 'Đã thu hồi';
$_LANG['status_rejected'] = 'Bị từ chối';

// Status Messages
$_LANG['status_cancelled_message'] = 'Đơn hàng chứng chỉ này đã bị hủy.';
$_LANG['status_revoked_message'] = 'Chứng chỉ này đã bị thu hồi và không còn hiệu lực.';
$_LANG['status_expired_message'] = 'Chứng chỉ này đã hết hạn. Vui lòng gia hạn dịch vụ để nhận chứng chỉ mới.';
$_LANG['status_rejected_message'] = 'Yêu cầu chứng chỉ đã bị từ chối bởi Nhà cung cấp chứng chỉ.';

// Certificate Details
$_LANG['certificate_details'] = 'Chi tiết chứng chỉ';
$_LANG['certificate_id'] = 'Mã chứng chỉ';
$_LANG['certificate_type'] = 'Loại chứng chỉ';
$_LANG['domain'] = 'Tên miền';
$_LANG['primary_domain'] = 'Tên miền chính';
$_LANG['domains_count'] = 'Số tên miền';
$_LANG['domains_secured'] = 'Tên miền được bảo vệ';
$_LANG['secured_domains'] = 'Các tên miền được bảo vệ';
$_LANG['valid_from'] = 'Có hiệu lực từ';
$_LANG['valid_until'] = 'Có hiệu lực đến';
$_LANG['issued_date'] = 'Ngày cấp';
$_LANG['expires_date'] = 'Ngày hết hạn';
$_LANG['vendor'] = 'Nhà cung cấp';
$_LANG['validation_type'] = 'Loại xác thực';

// Certificate States
$_LANG['certificate_issued'] = 'Chứng chỉ đã được cấp thành công';
$_LANG['certificate_ready_message'] = 'Chứng chỉ SSL của bạn đã được cấp và sẵn sàng để cài đặt.';
$_LANG['certificate_pending'] = 'Chứng chỉ đang chờ xác thực';
$_LANG['pending_message'] = 'Đơn hàng chứng chỉ đang được xử lý. Vui lòng hoàn tất xác thực tên miền bên dưới.';
$_LANG['certificate_expiring'] = 'Chứng chỉ sắp hết hạn';
$_LANG['certificate_expiring_message'] = 'Chứng chỉ của bạn sẽ hết hạn trong';
$_LANG['please_renew'] = 'Vui lòng gia hạn dịch vụ để nhận chứng chỉ mới.';

// Quick Actions
$_LANG['quick_actions'] = 'Thao tác nhanh';
$_LANG['available_actions'] = 'Các thao tác có sẵn';
$_LANG['download_certificate'] = 'Tải chứng chỉ';
$_LANG['download_unavailable'] = 'Không thể tải xuống';
$_LANG['reissue_certificate'] = 'Cấp lại chứng chỉ';
$_LANG['reissue_unavailable'] = 'Không thể cấp lại';
$_LANG['refresh_status'] = 'Làm mới trạng thái';
$_LANG['manage_certificate'] = 'Quản lý chứng chỉ';
$_LANG['view_certificate'] = 'Xem chứng chỉ';
$_LANG['revoke_certificate'] = 'Thu hồi chứng chỉ';
$_LANG['cancel_order'] = 'Hủy đơn hàng';

// Download
$_LANG['download_now'] = 'Tải xuống ngay';
$_LANG['download_format_select'] = 'Gói chứng chỉ bao gồm các định dạng sau:';
$_LANG['format_crt_desc'] = 'File chứng chỉ';
$_LANG['format_ca_desc'] = 'CA bundle (chứng chỉ trung gian)';
$_LANG['format_fullchain_desc'] = 'Full chain cho Nginx';
$_LANG['format_key_desc'] = 'Private key (nếu được tạo)';
$_LANG['format_pfx_desc'] = 'PKCS#12 cho IIS/Windows';
$_LANG['format_jks_desc'] = 'Java KeyStore cho Tomcat';

// Reissue
$_LANG['new_csr'] = 'CSR mới (Certificate Signing Request)';
$_LANG['private_key'] = 'Private Key';
$_LANG['csr_help'] = 'Dán CSR mới của bạn vào đây. Tạo CSR mới từ máy chủ của bạn.';
$_LANG['private_key_help'] = 'Nếu bạn muốn lưu private key, hãy dán vào đây.';
$_LANG['submit_reissue'] = 'Gửi yêu cầu cấp lại';
$_LANG['reissue_warning'] = 'Cảnh báo: Cấp lại sẽ tạo chứng chỉ mới. Chứng chỉ hiện tại vẫn có hiệu lực cho đến khi chứng chỉ mới được cấp.';
$_LANG['reissue_initiated'] = 'Yêu cầu cấp lại đã được gửi thành công';
$_LANG['confirm_reissue'] = 'Bạn có chắc chắn muốn cấp lại chứng chỉ này?';

// Order Progress
$_LANG['order_progress'] = 'Tiến độ đơn hàng';
$_LANG['order_information'] = 'Thông tin đơn hàng';
$_LANG['step_application'] = 'Đăng ký';
$_LANG['step_validation'] = 'Xác thực tên miền';
$_LANG['step_issuance'] = 'Cấp chứng chỉ';

// Domain Validation (DCV)
$_LANG['domain_validation'] = 'Xác thực tên miền';
$_LANG['dcv_instruction'] = 'Hoàn tất xác thực tên miền cho mỗi tên miền bên dưới. Chọn phương thức xác thực và làm theo hướng dẫn.';
$_LANG['validation_instructions'] = 'Hướng dẫn xác thực';
$_LANG['validation_details'] = 'Chi tiết xác thực';
$_LANG['method'] = 'Phương thức';
$_LANG['verified'] = 'Đã xác thực';
$_LANG['pending'] = 'Đang chờ';
$_LANG['save_changes'] = 'Lưu thay đổi';

// DCV Methods
$_LANG['dcv_email'] = 'Xác thực Email';
$_LANG['dcv_email_desc'] = 'Xác thực qua email gửi đến quản trị viên tên miền';
$_LANG['dcv_http'] = 'Xác thực HTTP File';
$_LANG['dcv_http_desc'] = 'Tải file xác thực lên máy chủ web';
$_LANG['dcv_https'] = 'Xác thực HTTPS File';
$_LANG['dcv_https_desc'] = 'Tải file xác thực với HTTPS';
$_LANG['dcv_cname'] = 'Xác thực DNS CNAME';
$_LANG['dcv_cname_desc'] = 'Thêm bản ghi CNAME vào DNS';
$_LANG['dcv_dns'] = 'Xác thực DNS TXT';
$_LANG['dcv_dns_desc'] = 'Thêm bản ghi TXT vào DNS';

// DCV Instructions
$_LANG['http_validation'] = 'Xác thực HTTP/HTTPS File';
$_LANG['http_validation_desc'] = 'Tạo file với nội dung sau tại đường dẫn được chỉ định:';
$_LANG['file_name'] = 'Tên file';
$_LANG['file_content'] = 'Nội dung file';
$_LANG['file_path'] = 'Đường dẫn file';
$_LANG['dns_validation'] = 'Xác thực DNS';
$_LANG['dns_validation_desc'] = 'Thêm bản ghi DNS sau vào tên miền của bạn:';
$_LANG['record_type'] = 'Loại bản ghi';
$_LANG['host_name'] = 'Tên host';
$_LANG['value'] = 'Giá trị';
$_LANG['email_validation'] = 'Xác thực Email';
$_LANG['email_validation_desc'] = 'Nếu sử dụng xác thực email, kiểm tra hộp thư đến và nhấp vào liên kết xác thực.';
$_LANG['valid_emails'] = 'Email xác thực hợp lệ';

$_LANG['dcv_email_instruction'] = 'Kiểm tra email và nhấp vào liên kết xác thực.';
$_LANG['dcv_http_instruction'] = 'Tạo file xác thực tại đường dẫn được chỉ định.';
$_LANG['dcv_dns_instruction'] = 'Thêm bản ghi DNS vào tên miền của bạn.';

// Cancel
$_LANG['confirm_cancel'] = 'Xác nhận hủy';
$_LANG['cancel_confirm_message'] = 'Bạn có chắc chắn muốn hủy đơn hàng chứng chỉ này? Thao tác này không thể hoàn tác.';
$_LANG['reason'] = 'Lý do';
$_LANG['cancel_reason_placeholder'] = 'Vui lòng cung cấp lý do hủy...';
$_LANG['no_keep'] = 'Không, giữ đơn hàng';
$_LANG['yes_cancel'] = 'Có, hủy đơn hàng';

// Installation Guide
$_LANG['installation_guide'] = 'Hướng dẫn cài đặt';
$_LANG['install_note'] = 'Tải gói chứng chỉ để có các file ở nhiều định dạng bao gồm PFX cho IIS và JKS cho Tomcat.';

// Apply Certificate Form
$_LANG['apply_certificate'] = 'Đăng ký chứng chỉ SSL';
$_LANG['certificate_signing_request'] = 'Certificate Signing Request (CSR)';
$_LANG['csr_required'] = 'CSR là bắt buộc để đăng ký chứng chỉ SSL.';
$_LANG['generate_csr'] = 'Tạo CSR';
$_LANG['paste_csr'] = 'Dán CSR của bạn vào đây...';
$_LANG['decode_csr'] = 'Giải mã CSR';
$_LANG['server_type'] = 'Loại máy chủ';
$_LANG['select_server_type'] = 'Chọn loại máy chủ web của bạn';

// Contact Information
$_LANG['contact_information'] = 'Thông tin liên hệ';
$_LANG['admin_contact'] = 'Liên hệ quản trị';
$_LANG['tech_contact'] = 'Liên hệ kỹ thuật';
$_LANG['organization_info'] = 'Thông tin tổ chức';
$_LANG['first_name'] = 'Tên';
$_LANG['last_name'] = 'Họ';
$_LANG['email'] = 'Email';
$_LANG['phone'] = 'Điện thoại';
$_LANG['job_title'] = 'Chức vụ';
$_LANG['organization'] = 'Tổ chức';
$_LANG['department'] = 'Phòng ban';
$_LANG['address'] = 'Địa chỉ';
$_LANG['city'] = 'Thành phố';
$_LANG['state'] = 'Tỉnh/Thành';
$_LANG['postal_code'] = 'Mã bưu chính';
$_LANG['country'] = 'Quốc gia';

// Errors
$_LANG['error_no_certificate'] = 'Không tìm thấy chứng chỉ cho dịch vụ này';
$_LANG['error_not_configured'] = 'API token chưa được cấu hình';
$_LANG['error_invalid_csr'] = 'Định dạng CSR không hợp lệ';
$_LANG['error_csr_required'] = 'CSR là bắt buộc';
$_LANG['error_domain_required'] = 'Thông tin tên miền là bắt buộc';
$_LANG['error_reissue_failed'] = 'Không thể cấp lại chứng chỉ';
$_LANG['error_download_failed'] = 'Không thể tải chứng chỉ';
$_LANG['error_cancel_failed'] = 'Không thể hủy đơn hàng';
$_LANG['error_refresh_failed'] = 'Không thể làm mới trạng thái';

// Loading States
$_LANG['refreshing'] = 'Đang làm mới...';
$_LANG['saving'] = 'Đang lưu...';
$_LANG['downloading'] = 'Đang chuẩn bị tải xuống...';
$_LANG['processing'] = 'Đang xử lý...';
$_LANG['submitting'] = 'Đang gửi...';