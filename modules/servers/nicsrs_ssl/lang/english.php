<?php
/**
 * NicSRS SSL Module - English Language File
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

// General
$_LANG['module_name'] = 'NicSRS SSL';
$_LANG['success'] = 'Success';
$_LANG['error'] = 'Error';
$_LANG['warning'] = 'Warning';
$_LANG['info'] = 'Information';
$_LANG['close'] = 'Close';
$_LANG['cancel'] = 'Cancel';
$_LANG['save'] = 'Save';
$_LANG['submit'] = 'Submit';
$_LANG['confirm'] = 'Confirm';
$_LANG['yes'] = 'Yes';
$_LANG['no'] = 'No';
$_LANG['optional'] = 'Optional';
$_LANG['required'] = 'Required';
$_LANG['actions'] = 'Actions';
$_LANG['view'] = 'View';
$_LANG['copy'] = 'Copy';
$_LANG['copied'] = 'Copied to clipboard!';
$_LANG['days'] = 'days';
$_LANG['days_remaining'] = 'days remaining';

// Status Labels
$_LANG['status'] = 'Status';
$_LANG['status_awaiting'] = 'Awaiting Configuration';
$_LANG['status_pending'] = 'Pending';
$_LANG['status_processing'] = 'Processing';
$_LANG['status_complete'] = 'Issued';
$_LANG['status_active'] = 'Active';
$_LANG['status_expiring'] = 'Expiring Soon';
$_LANG['status_expired'] = 'Expired';
$_LANG['status_cancelled'] = 'Cancelled';
$_LANG['status_revoked'] = 'Revoked';
$_LANG['status_rejected'] = 'Rejected';

// Status Messages
$_LANG['status_cancelled_message'] = 'This certificate order has been cancelled.';
$_LANG['status_revoked_message'] = 'This certificate has been revoked and is no longer valid.';
$_LANG['status_expired_message'] = 'This certificate has expired. Please renew your service to get a new certificate.';
$_LANG['status_rejected_message'] = 'This certificate request was rejected by the Certificate Authority.';

// Certificate Details
$_LANG['certificate_details'] = 'Certificate Details';
$_LANG['certificate_id'] = 'Certificate ID';
$_LANG['certificate_type'] = 'Certificate Type';
$_LANG['domain'] = 'Domain';
$_LANG['primary_domain'] = 'Primary Domain';
$_LANG['domains_count'] = 'Domains';
$_LANG['domains_secured'] = 'Domains Secured';
$_LANG['secured_domains'] = 'Secured Domains';
$_LANG['valid_from'] = 'Valid From';
$_LANG['valid_until'] = 'Valid Until';
$_LANG['issued_date'] = 'Issued Date';
$_LANG['expires_date'] = 'Expiry Date';
$_LANG['vendor'] = 'Vendor';
$_LANG['validation_type'] = 'Validation Type';

// Certificate States
$_LANG['certificate_issued'] = 'Certificate Issued Successfully';
$_LANG['certificate_ready_message'] = 'Your SSL certificate has been issued and is ready for installation.';
$_LANG['certificate_pending'] = 'Certificate Pending Validation';
$_LANG['pending_message'] = 'Your certificate order is being processed. Please complete domain validation below.';
$_LANG['certificate_expiring'] = 'Certificate Expiring Soon';
$_LANG['certificate_expiring_message'] = 'Your certificate will expire in';
$_LANG['please_renew'] = 'Please renew your service to get a new certificate.';

// Quick Actions
$_LANG['quick_actions'] = 'Quick Actions';
$_LANG['available_actions'] = 'Available Actions';
$_LANG['download_certificate'] = 'Download Certificate';
$_LANG['download_unavailable'] = 'Download Unavailable';
$_LANG['reissue_certificate'] = 'Reissue Certificate';
$_LANG['reissue_unavailable'] = 'Reissue Unavailable';
$_LANG['refresh_status'] = 'Refresh Status';
$_LANG['manage_certificate'] = 'Manage Certificate';
$_LANG['view_certificate'] = 'View Certificate';
$_LANG['revoke_certificate'] = 'Revoke Certificate';
$_LANG['cancel_order'] = 'Cancel Order';

// Download
$_LANG['download_now'] = 'Download Now';
$_LANG['download_format_select'] = 'The certificate package includes all formats:';
$_LANG['format_crt_desc'] = 'Certificate file';
$_LANG['format_ca_desc'] = 'CA bundle (intermediate certificates)';
$_LANG['format_fullchain_desc'] = 'Full chain for Nginx';
$_LANG['format_key_desc'] = 'Private key (if generated)';
$_LANG['format_pfx_desc'] = 'PKCS#12 for IIS/Windows';
$_LANG['format_jks_desc'] = 'Java KeyStore for Tomcat';

// Reissue
$_LANG['new_csr'] = 'New CSR (Certificate Signing Request)';
$_LANG['private_key'] = 'Private Key';
$_LANG['csr_help'] = 'Paste your new CSR here. Generate a new CSR from your server.';
$_LANG['private_key_help'] = 'If you want to store your private key, paste it here.';
$_LANG['submit_reissue'] = 'Submit Reissue Request';
$_LANG['reissue_warning'] = 'Warning: Reissuing will generate a new certificate. The current certificate will remain valid until the new one is issued.';
$_LANG['reissue_initiated'] = 'Reissue request submitted successfully';
$_LANG['confirm_reissue'] = 'Are you sure you want to reissue this certificate?';

// Order Progress
$_LANG['order_progress'] = 'Order Progress';
$_LANG['order_information'] = 'Order Information';
$_LANG['step_application'] = 'Application';
$_LANG['step_validation'] = 'Domain Validation';
$_LANG['step_issuance'] = 'Certificate Issuance';

// Domain Validation (DCV)
$_LANG['domain_validation'] = 'Domain Validation';
$_LANG['dcv_instruction'] = 'Complete domain validation for each domain below. Choose a validation method and follow the instructions.';
$_LANG['validation_instructions'] = 'Validation Instructions';
$_LANG['validation_details'] = 'Validation Details';
$_LANG['method'] = 'Method';
$_LANG['verified'] = 'Verified';
$_LANG['pending'] = 'Pending';
$_LANG['save_changes'] = 'Save Changes';

// DCV Methods
$_LANG['dcv_email'] = 'Email Validation';
$_LANG['dcv_email_desc'] = 'Verify via email to domain administrator';
$_LANG['dcv_http'] = 'HTTP File Validation';
$_LANG['dcv_http_desc'] = 'Upload validation file to web server';
$_LANG['dcv_https'] = 'HTTPS File Validation';
$_LANG['dcv_https_desc'] = 'Upload validation file with HTTPS';
$_LANG['dcv_cname'] = 'DNS CNAME Validation';
$_LANG['dcv_cname_desc'] = 'Add CNAME record to DNS';
$_LANG['dcv_dns'] = 'DNS TXT Validation';
$_LANG['dcv_dns_desc'] = 'Add TXT record to DNS';

// DCV Instructions
$_LANG['http_validation'] = 'HTTP/HTTPS File Validation';
$_LANG['http_validation_desc'] = 'Create a file with the following content at the specified path:';
$_LANG['file_name'] = 'File Name';
$_LANG['file_content'] = 'File Content';
$_LANG['file_path'] = 'File Path';
$_LANG['dns_validation'] = 'DNS Validation';
$_LANG['dns_validation_desc'] = 'Add the following DNS record to your domain:';
$_LANG['record_type'] = 'Record Type';
$_LANG['host_name'] = 'Host Name';
$_LANG['value'] = 'Value';
$_LANG['email_validation'] = 'Email Validation';
$_LANG['email_validation_desc'] = 'If using email validation, check your inbox for the validation email and click the approval link.';
$_LANG['valid_emails'] = 'Valid approval emails';

$_LANG['dcv_email_instruction'] = 'Check your email and click the validation link.';
$_LANG['dcv_http_instruction'] = 'Create the validation file at the specified path.';
$_LANG['dcv_dns_instruction'] = 'Add the DNS record to your domain.';

// Cancel
$_LANG['confirm_cancel'] = 'Confirm Cancellation';
$_LANG['cancel_confirm_message'] = 'Are you sure you want to cancel this certificate order? This action cannot be undone.';
$_LANG['reason'] = 'Reason';
$_LANG['cancel_reason_placeholder'] = 'Please provide a reason for cancellation...';
$_LANG['no_keep'] = 'No, Keep Order';
$_LANG['yes_cancel'] = 'Yes, Cancel Order';

// Installation Guide
$_LANG['installation_guide'] = 'Installation Guide';
$_LANG['install_note'] = 'Download the certificate package for files in multiple formats including PFX for IIS and JKS for Tomcat.';

// Apply Certificate Form
$_LANG['apply_certificate'] = 'Apply for SSL Certificate';
$_LANG['certificate_signing_request'] = 'Certificate Signing Request (CSR)';
$_LANG['csr_required'] = 'CSR is required to apply for an SSL certificate.';
$_LANG['generate_csr'] = 'Generate CSR';
$_LANG['paste_csr'] = 'Paste your CSR here...';
$_LANG['decode_csr'] = 'Decode CSR';
$_LANG['server_type'] = 'Server Type';
$_LANG['select_server_type'] = 'Select your web server type';

// Contact Information
$_LANG['contact_information'] = 'Contact Information';
$_LANG['admin_contact'] = 'Administrative Contact';
$_LANG['tech_contact'] = 'Technical Contact';
$_LANG['organization_info'] = 'Organization Information';
$_LANG['first_name'] = 'First Name';
$_LANG['last_name'] = 'Last Name';
$_LANG['email'] = 'Email';
$_LANG['phone'] = 'Phone';
$_LANG['job_title'] = 'Job Title';
$_LANG['organization'] = 'Organization';
$_LANG['department'] = 'Department';
$_LANG['address'] = 'Address';
$_LANG['city'] = 'City';
$_LANG['state'] = 'State/Province';
$_LANG['postal_code'] = 'Postal Code';
$_LANG['country'] = 'Country';

// Errors
$_LANG['error_no_certificate'] = 'No certificate found for this service';
$_LANG['error_not_configured'] = 'API token not configured';
$_LANG['error_invalid_csr'] = 'Invalid CSR format';
$_LANG['error_csr_required'] = 'CSR is required';
$_LANG['error_domain_required'] = 'Domain information is required';
$_LANG['error_reissue_failed'] = 'Failed to reissue certificate';
$_LANG['error_download_failed'] = 'Failed to download certificate';
$_LANG['error_cancel_failed'] = 'Failed to cancel order';
$_LANG['error_refresh_failed'] = 'Failed to refresh status';

// Loading States
$_LANG['refreshing'] = 'Refreshing...';
$_LANG['saving'] = 'Saving...';
$_LANG['downloading'] = 'Preparing download...';
$_LANG['processing'] = 'Processing...';
$_LANG['submitting'] = 'Submitting...';