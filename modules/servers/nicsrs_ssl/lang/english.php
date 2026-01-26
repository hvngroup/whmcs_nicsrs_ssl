<?php
/**
 * NicSRS SSL Module - English Language File
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

// General
$_LANG['ssl_management'] = 'SSL Management';
$_LANG['ssl_certificate'] = 'SSL Certificate';
$_LANG['configure_certificate'] = 'Configure Certificate';
$_LANG['certificate_management'] = 'Certificate Management';
$_LANG['module_version'] = 'Module Version';

// Status
$_LANG['status'] = 'Status';
$_LANG['awaiting_configuration'] = 'Awaiting Configuration';
$_LANG['draft'] = 'Draft';
$_LANG['pending'] = 'Pending';
$_LANG['processing'] = 'Processing';
$_LANG['complete'] = 'Complete';
$_LANG['issued'] = 'Issued';
$_LANG['cancelled'] = 'Cancelled';
$_LANG['revoked'] = 'Revoked';
$_LANG['expired'] = 'Expired';
$_LANG['reissue_pending'] = 'Reissue Pending';
$_LANG['verified'] = 'Verified';
$_LANG['un_verified'] = 'Unverified';

// Steps
$_LANG['step'] = 'Step';
$_LANG['step_csr'] = 'CSR';
$_LANG['step_domain'] = 'Domain Validation';
$_LANG['step_contacts'] = 'Contact Information';
$_LANG['step_organization'] = 'Organization';
$_LANG['step_review'] = 'Review';

// CSR Section
$_LANG['csr'] = 'CSR';
$_LANG['csr_configuration'] = 'CSR Configuration';
$_LANG['csr_des'] = 'A Certificate Signing Request (CSR) is required to apply for an SSL certificate. You can auto-generate one or paste an existing CSR.';
$_LANG['auto_generate_csr'] = 'Auto-generate CSR';
$_LANG['manual_csr'] = 'Enter CSR manually';
$_LANG['is_manual_csr'] = 'I have my own CSR';
$_LANG['paste_csr'] = 'Paste your CSR here';
$_LANG['generate_csr'] = 'Generate CSR';
$_LANG['decode_csr'] = 'Decode CSR';
$_LANG['csr_decoded'] = 'CSR decoded successfully';
$_LANG['invalid_csr'] = 'Invalid CSR format';

// CSR Fields
$_LANG['common_name'] = 'Common Name (Domain)';
$_LANG['organization'] = 'Organization';
$_LANG['organizational_unit'] = 'Organizational Unit';
$_LANG['city'] = 'City';
$_LANG['locality'] = 'City/Locality';
$_LANG['state'] = 'State/Province';
$_LANG['province'] = 'Province';
$_LANG['country'] = 'Country';
$_LANG['email'] = 'Email';
$_LANG['email_address'] = 'Email Address';

// Domain Section
$_LANG['domain'] = 'Domain';
$_LANG['domain_info'] = 'Domain Information';
$_LANG['domain_name'] = 'Domain Name';
$_LANG['primary_domain'] = 'Primary Domain';
$_LANG['additional_domains'] = 'Additional Domains';
$_LANG['add_domain'] = 'Add Domain';
$_LANG['remove_domain'] = 'Remove';
$_LANG['max_domain'] = 'Maximum Domains';
$_LANG['add'] = 'Add';
$_LANG['set_for_all'] = 'Set for all';
$_LANG['must_same_pmain'] = 'Must be the same as the main domain';
$_LANG['overplus'] = 'You have exceeded the maximum number of domains';

// DCV (Domain Control Validation)
$_LANG['dcv'] = 'DCV';
$_LANG['dcv_method'] = 'DCV Method';
$_LANG['dcv_status'] = 'DCV Status';
$_LANG['domain_validation'] = 'Domain Validation';
$_LANG['domain_validation_required'] = 'Domain Validation Required';
$_LANG['please_choose'] = 'Please choose';
$_LANG['http_csr_hash'] = 'HTTP File Validation';
$_LANG['https_csr_hash'] = 'HTTPS File Validation';
$_LANG['cname_csr_hash'] = 'DNS CNAME Validation';
$_LANG['dns_csr_hash'] = 'DNS TXT Validation';
$_LANG['email_validation'] = 'Email Validation';
$_LANG['update_dcv'] = 'Update DCV';
$_LANG['resend_dcv'] = 'Resend DCV';
$_LANG['dcv_instructions'] = 'DCV Instructions';

// DCV Instructions
$_LANG['http_instructions'] = 'Create a file at the following path on your server:';
$_LANG['https_instructions'] = 'Create a file at the following HTTPS path on your server:';
$_LANG['dns_cname_instructions'] = 'Add the following CNAME record to your DNS:';
$_LANG['dns_txt_instructions'] = 'Add the following TXT record to your DNS:';
$_LANG['email_instructions'] = 'A verification email will be sent to the selected address.';
$_LANG['file_path'] = 'File Path';
$_LANG['file_content'] = 'File Content';
$_LANG['dns_host'] = 'DNS Host';
$_LANG['dns_value'] = 'DNS Value';
$_LANG['dns_type'] = 'DNS Type';
$_LANG['dcv_email'] = 'Verification Email';
$_LANG['down_txt'] = 'Download validation file';

// Contact Information
$_LANG['contacts'] = 'Contact Information';
$_LANG['admin_contact'] = 'Administrator Contact';
$_LANG['tech_contact'] = 'Technical Contact';
$_LANG['title'] = 'Title/Job Position';
$_LANG['first_name'] = 'First Name';
$_LANG['last_name'] = 'Last Name';
$_LANG['phone'] = 'Phone Number';
$_LANG['organization_name'] = 'Organization Name';

// Organization Information
$_LANG['organization_info'] = 'Organization Information';
$_LANG['org_name'] = 'Organization Name';
$_LANG['org_address'] = 'Address';
$_LANG['org_city'] = 'City';
$_LANG['org_state'] = 'State/Province';
$_LANG['org_country'] = 'Country';
$_LANG['org_postal'] = 'Postal Code';
$_LANG['org_phone'] = 'Phone';
$_LANG['org_division'] = 'Division/Department';
$_LANG['idType'] = 'ID Type';
$_LANG['organizationCode'] = 'Tax ID / Business Registration';
$_LANG['organizationRegNumber'] = 'Registration Number';
$_LANG['other'] = 'Other';

// Renewal
$_LANG['is_renew'] = 'Is this a renewal?';
$_LANG['is_renew_des'] = 'If this is a renewal of an existing certificate, select "Yes" for potential time bonus.';
$_LANG['is_renew_option_new'] = 'No, new certificate';
$_LANG['is_renew_option_renew'] = 'Yes, renewal';

// Actions
$_LANG['actions'] = 'Actions';
$_LANG['submit'] = 'Submit';
$_LANG['submit_request'] = 'Submit Request';
$_LANG['save_draft'] = 'Save Draft';
$_LANG['cancel'] = 'Cancel';
$_LANG['back'] = 'Back';
$_LANG['next'] = 'Next';
$_LANG['refresh'] = 'Refresh';
$_LANG['refresh_status'] = 'Refresh Status';
$_LANG['download'] = 'Download';
$_LANG['download_certificate'] = 'Download Certificate';
$_LANG['down_cert'] = 'Download Certificate';
$_LANG['down_key'] = 'Download Private Key';
$_LANG['reissue'] = 'Reissue';
$_LANG['repalce'] = 'Reissue'; // Legacy typo support
$_LANG['reissue_certificate'] = 'Reissue Certificate';
$_LANG['renew'] = 'Renew';
$_LANG['renew_certificate'] = 'Renew Certificate';
$_LANG['revoke'] = 'Revoke';
$_LANG['revoke_certificate'] = 'Revoke Certificate';
$_LANG['cancel_order'] = 'Cancel Order';

// Download Formats
$_LANG['select_download_format'] = 'Select Download Format';
$_LANG['format_all'] = 'All Formats (ZIP)';
$_LANG['format_apache'] = 'Apache (.crt + .ca-bundle)';
$_LANG['format_nginx'] = 'Nginx (.pem)';
$_LANG['format_iis'] = 'IIS (.p12)';
$_LANG['format_tomcat'] = 'Tomcat (.jks)';

// Certificate Info
$_LANG['certificate'] = 'Certificate';
$_LANG['certificate_info'] = 'Certificate Information';
$_LANG['certificate_id'] = 'Certificate ID';
$_LANG['certificate_type'] = 'Certificate Type';
$_LANG['certificate_issued'] = 'Certificate Issued';
$_LANG['certificate_pending'] = 'Certificate Pending';
$_LANG['cert_status'] = 'Certificate Status';
$_LANG['cert_begin'] = 'Issued Date';
$_LANG['cert_end'] = 'Expiry Date';
$_LANG['days_remaining'] = 'Days Remaining';
$_LANG['expiring_soon'] = 'Expiring Soon';
$_LANG['vendor_id'] = 'Vendor ID';
$_LANG['last_refresh'] = 'Last Refresh';

// Messages
$_LANG['apply_des'] = 'Please complete the form below to request your SSL certificate.';
$_LANG['message_des'] = 'Your certificate request has been submitted. Please complete domain validation.';
$_LANG['complete_des'] = 'Your certificate has been issued successfully. You can download or reissue it below.';
$_LANG['replace_des'] = 'Please provide new information for certificate reissuance.';
$_LANG['cancelled_des'] = 'This certificate has been cancelled or revoked.';
$_LANG['email_info'] = 'Email Information';
$_LANG['email_wait_info'] = 'Your request has been submitted. Please check your email to complete verification.';

// Confirmations
$_LANG['sure_to_cancel'] = 'Are you sure you want to cancel this certificate?';
$_LANG['sure_to_replace'] = 'Are you sure you want to reissue this certificate?';
$_LANG['sure_to_revoke'] = 'Are you sure you want to revoke this certificate? This action cannot be undone.';
$_LANG['confirm_action'] = 'Confirm Action';
$_LANG['cancel_reason'] = 'Cancellation Reason';
$_LANG['revoke_reason'] = 'Revocation Reason';

// Success Messages
$_LANG['success'] = 'Success';
$_LANG['submit_success'] = 'Request submitted successfully';
$_LANG['draft_saved'] = 'Draft saved successfully';
$_LANG['status_refreshed'] = 'Status refreshed';
$_LANG['dcv_updated'] = 'DCV method updated';
$_LANG['cancelled_success'] = 'Order cancelled successfully';
$_LANG['revoked_success'] = 'Certificate revoked successfully';
$_LANG['reissue_success'] = 'Reissue request submitted';

// Error Messages
$_LANG['error'] = 'Error';
$_LANG['params_error'] = 'Parameter error';
$_LANG['action_not_found'] = 'Action not supported';
$_LANG['pay_order_first'] = 'Please pay the order first';
$_LANG['product_offline'] = 'Product is not available, please contact support';
$_LANG['service_error'] = 'Service not found, please try again';
$_LANG['status_error'] = 'Invalid order status';
$_LANG['sys_error'] = 'System error, please contact support';
$_LANG['cert_not_found'] = 'Certificate not found';
$_LANG['cert_not_issued'] = 'Certificate not yet issued';
$_LANG['api_error'] = 'API Error';
$_LANG['validation_error'] = 'Validation Error';

// Server Types
$_LANG['server_type'] = 'Server Type';
$_LANG['server_other'] = 'Other';
$_LANG['server_apache'] = 'Apache';
$_LANG['server_nginx'] = 'Nginx';
$_LANG['server_iis'] = 'Microsoft IIS';
$_LANG['server_tomcat'] = 'Tomcat';
$_LANG['server_cpanel'] = 'cPanel';
$_LANG['server_plesk'] = 'Plesk';

// Misc
$_LANG['loading'] = 'Loading...';
$_LANG['please_wait'] = 'Please wait...';
$_LANG['required'] = 'Required';
$_LANG['optional'] = 'Optional';
$_LANG['yes'] = 'Yes';
$_LANG['no'] = 'No';
$_LANG['na'] = 'N/A';
$_LANG['copy'] = 'Copy';
$_LANG['copied'] = 'Copied!';
$_LANG['close'] = 'Close';
$_LANG['help'] = 'Help';
$_LANG['more_info'] = 'More Information';