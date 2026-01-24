<?php
/**
 * NicSRS SSL Module Constants
 * 
 * Defines module constants and configurations
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Module Version
 */
if (!defined('NICSRS_SSL_VERSION')) {
    define('NICSRS_SSL_VERSION', '2.0.0');
}

/**
 * API Configuration
 */
if (!defined('NICSRS_API_URL')) {
    define('NICSRS_API_URL', 'https://portal.nicsrs.com/ssl');
}

if (!defined('NICSRS_API_TIMEOUT')) {
    define('NICSRS_API_TIMEOUT', 60);
}

/**
 * Certificate Status Constants
 */
if (!defined('CERT_STATUS_AWAITING')) {
    define('CERT_STATUS_AWAITING', 'awaiting');
    define('CERT_STATUS_DRAFT', 'draft');
    define('CERT_STATUS_PENDING', 'pending');
    define('CERT_STATUS_PROCESSING', 'processing');
    define('CERT_STATUS_COMPLETE', 'complete');
    define('CERT_STATUS_ISSUED', 'issued');
    define('CERT_STATUS_CANCELLED', 'cancelled');
    define('CERT_STATUS_REVOKED', 'revoked');
    define('CERT_STATUS_EXPIRED', 'expired');
    define('CERT_STATUS_REJECTED', 'rejected');
    define('CERT_STATUS_EXPIRING', 'expiring');
}

/**
 * DCV Methods
 */
if (!defined('DCV_METHOD_EMAIL')) {
    define('DCV_METHOD_EMAIL', 'EMAIL');
    define('DCV_METHOD_HTTP', 'HTTP_CSR_HASH');
    define('DCV_METHOD_HTTPS', 'HTTPS_CSR_HASH');
    define('DCV_METHOD_CNAME', 'CNAME_CSR_HASH');
    define('DCV_METHOD_DNS', 'DNS_CSR_HASH');
}

/**
 * Validation Types
 */
if (!defined('VALIDATION_DV')) {
    define('VALIDATION_DV', 'dv');
    define('VALIDATION_OV', 'ov');
    define('VALIDATION_EV', 'ev');
}

/**
 * Server Types
 */
if (!defined('SERVER_TYPE_OTHER')) {
    define('SERVER_TYPE_OTHER', 'other');
    define('SERVER_TYPE_APACHE', 'apache');
    define('SERVER_TYPE_NGINX', 'nginx');
    define('SERVER_TYPE_IIS', 'iis');
    define('SERVER_TYPE_TOMCAT', 'tomcat');
    define('SERVER_TYPE_CPANEL', 'cpanel');
    define('SERVER_TYPE_PLESK', 'plesk');
}

/**
 * API Response Codes
 */
if (!defined('API_CODE_SUCCESS')) {
    define('API_CODE_SUCCESS', 1);
    define('API_CODE_PROCESSING', 2);
    define('API_CODE_ERROR', 0);
    define('API_CODE_VALIDATION_ERROR', -1);
    define('API_CODE_UNKNOWN_ERROR', -2);
    define('API_CODE_PRODUCT_ERROR', -3);
    define('API_CODE_INSUFFICIENT_CREDIT', -4);
    define('API_CODE_CA_ERROR', -6);
    define('API_CODE_PERMISSION_DENIED', 400);
}

/**
 * DCV Methods Array
 */
$GLOBALS['NICSRS_DCV_METHODS'] = [
    DCV_METHOD_EMAIL => [
        'name' => 'Email Validation',
        'type' => 'email',
        'description' => 'Verify via email to domain administrator',
    ],
    DCV_METHOD_HTTP => [
        'name' => 'HTTP File Validation',
        'type' => 'http',
        'description' => 'Upload validation file to web server via HTTP',
    ],
    DCV_METHOD_HTTPS => [
        'name' => 'HTTPS File Validation',
        'type' => 'https',
        'description' => 'Upload validation file to web server via HTTPS',
    ],
    DCV_METHOD_CNAME => [
        'name' => 'DNS CNAME Validation',
        'type' => 'dns',
        'description' => 'Add a CNAME record to your DNS',
    ],
    DCV_METHOD_DNS => [
        'name' => 'DNS TXT Validation',
        'type' => 'dns',
        'description' => 'Add a TXT record to your DNS',
    ],
];

/**
 * Certificate Status Array
 */
$GLOBALS['NICSRS_CERT_STATUSES'] = [
    CERT_STATUS_AWAITING => [
        'label' => 'Awaiting Configuration',
        'class' => 'default',
        'icon' => 'hourglass-start',
    ],
    CERT_STATUS_DRAFT => [
        'label' => 'Draft',
        'class' => 'default',
        'icon' => 'file-o',
    ],
    CERT_STATUS_PENDING => [
        'label' => 'Pending',
        'class' => 'warning',
        'icon' => 'clock-o',
    ],
    CERT_STATUS_PROCESSING => [
        'label' => 'Processing',
        'class' => 'info',
        'icon' => 'spinner',
    ],
    CERT_STATUS_COMPLETE => [
        'label' => 'Issued',
        'class' => 'success',
        'icon' => 'check-circle',
    ],
    CERT_STATUS_ISSUED => [
        'label' => 'Issued',
        'class' => 'success',
        'icon' => 'check-circle',
    ],
    CERT_STATUS_CANCELLED => [
        'label' => 'Cancelled',
        'class' => 'danger',
        'icon' => 'ban',
    ],
    CERT_STATUS_REVOKED => [
        'label' => 'Revoked',
        'class' => 'danger',
        'icon' => 'times-circle',
    ],
    CERT_STATUS_EXPIRED => [
        'label' => 'Expired',
        'class' => 'danger',
        'icon' => 'calendar-times-o',
    ],
    CERT_STATUS_REJECTED => [
        'label' => 'Rejected',
        'class' => 'danger',
        'icon' => 'exclamation-circle',
    ],
    CERT_STATUS_EXPIRING => [
        'label' => 'Expiring Soon',
        'class' => 'warning',
        'icon' => 'exclamation-triangle',
    ],
];

/**
 * Server Types Array
 */
$GLOBALS['NICSRS_SERVER_TYPES'] = [
    SERVER_TYPE_OTHER => 'Other',
    SERVER_TYPE_APACHE => 'Apache',
    SERVER_TYPE_NGINX => 'Nginx',
    SERVER_TYPE_IIS => 'Microsoft IIS',
    SERVER_TYPE_TOMCAT => 'Tomcat',
    SERVER_TYPE_CPANEL => 'cPanel',
    SERVER_TYPE_PLESK => 'Plesk',
];

/**
 * Validation Types Array
 */
$GLOBALS['NICSRS_VALIDATION_TYPES'] = [
    VALIDATION_DV => [
        'name' => 'Domain Validation',
        'short' => 'DV',
        'description' => 'Basic domain ownership verification',
    ],
    VALIDATION_OV => [
        'name' => 'Organization Validation',
        'short' => 'OV',
        'description' => 'Verifies domain and organization details',
    ],
    VALIDATION_EV => [
        'name' => 'Extended Validation',
        'short' => 'EV',
        'description' => 'Highest level of validation with green bar',
    ],
];

/**
 * Helper function to get DCV methods
 * 
 * @return array
 */
function nicsrs_getDcvMethods()
{
    return $GLOBALS['NICSRS_DCV_METHODS'];
}

/**
 * Helper function to get certificate statuses
 * 
 * @return array
 */
function nicsrs_getCertStatuses()
{
    return $GLOBALS['NICSRS_CERT_STATUSES'];
}

/**
 * Helper function to get status configuration
 * 
 * @param string $status Status code
 * @return array|null
 */
function nicsrs_getStatusConfig($status)
{
    $status = strtolower($status);
    return $GLOBALS['NICSRS_CERT_STATUSES'][$status] ?? null;
}

/**
 * Helper function to get server types
 * 
 * @return array
 */
function nicsrs_getServerTypes()
{
    return $GLOBALS['NICSRS_SERVER_TYPES'];
}

/**
 * Helper function to get validation types
 * 
 * @return array
 */
function nicsrs_getValidationTypes()
{
    return $GLOBALS['NICSRS_VALIDATION_TYPES'];
}