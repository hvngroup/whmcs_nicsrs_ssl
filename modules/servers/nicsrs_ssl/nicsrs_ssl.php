<?php
/**
 * NicSRS SSL WHMCS Server Provisioning Module
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Define constants
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('NICSRS_SSL_PATH')) define('NICSRS_SSL_PATH', __DIR__ . DS);
if (!defined('NICSRS_SSL_VERSION')) define('NICSRS_SSL_VERSION', '2.0.0');

// Load configuration
require_once NICSRS_SSL_PATH . "src/config/const.php";

// Load service classes
require_once NICSRS_SSL_PATH . "src/model/Service/ApiService.php";
require_once NICSRS_SSL_PATH . "src/model/Service/CertificateFunc.php";
require_once NICSRS_SSL_PATH . "src/model/Service/ResponseFormatter.php";
require_once NICSRS_SSL_PATH . "src/model/Service/OrderRepository.php";
require_once NICSRS_SSL_PATH . "src/model/Service/TemplateHelper.php";

// Load controllers
require_once NICSRS_SSL_PATH . "src/model/Controller/PageController.php";
require_once NICSRS_SSL_PATH . "src/model/Controller/ActionController.php";

// Load dispatchers
require_once NICSRS_SSL_PATH . "src/model/Dispatcher/PageDispatcher.php";
require_once NICSRS_SSL_PATH . "src/model/Dispatcher/ActionDispatcher.php";

// Backward compatibility aliases
require_once NICSRS_SSL_PATH . "src/compatibility.php";

use nicsrsSSL\CertificateFunc;
use nicsrsSSL\OrderRepository;
use nicsrsSSL\PageDispatcher;
use nicsrsSSL\ActionDispatcher;
use nicsrsSSL\ActionController;
use nicsrsSSL\TemplateHelper;

/**
 * Module metadata
 */
function nicsrs_ssl_MetaData()
{
    return [
        'DisplayName' => 'SSL Certificate Manager',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
        'DefaultNonSSLPort' => '80',
        'DefaultSSLPort' => '443',
        'ServiceSingleSignOnLabel' => 'Manage Certificate',
        'AdminSingleSignOnLabel' => 'View Certificate Details',
    ];
}

/**
 * Module configuration options
 */
function nicsrs_ssl_ConfigOptions()
{
    return [
        'cert_type' => [
            'FriendlyName' => 'Certificate Type',
            'Type' => 'dropdown',
            'Options' => CertificateFunc::getCertAttributesDropdown(),
            'Description' => 'Select the SSL certificate type for this product',
        ],
        'api_token' => [
            'FriendlyName' => 'API Token (Optional)',
            'Type' => 'password',
            'Size' => '64',
            'Description' => 'Leave empty to use shared API token from Admin Addon',
        ],
    ];
}

/**
 * Create account - called when a new service is provisioned
 */
function nicsrs_ssl_CreateAccount(array $params)
{
    try {
        $existingOrder = OrderRepository::getByServiceId($params['serviceid']);
        
        if ($existingOrder && !empty($existingOrder->remoteid)) {
            return 'Certificate already exists for this service';
        }
        
        if (!$existingOrder) {
            // Create initial order record
            OrderRepository::create([
                'userid' => $params['userid'],
                'serviceid' => $params['serviceid'],
                'addon_id' => '',
                'remoteid' => '',
                'module' => 'nicsrs_ssl',
                'certtype' => $params['configoption1'] ?? '',
                'configdata' => json_encode([]),
                'provisiondate' => date('Y-m-d'),
                'completiondate' => '0000-00-00 00:00:00',
                'status' => 'Awaiting Configuration',
            ]);
        }
        
        return 'success';
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return $e->getMessage();
    }
}

/**
 * Suspend account
 */
function nicsrs_ssl_SuspendAccount(array $params)
{
    try {
        OrderRepository::updateStatusByServiceId($params['serviceid'], 'Suspended');
        return 'success';
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Unsuspend account
 */
function nicsrs_ssl_UnsuspendAccount(array $params)
{
    try {
        $order = OrderRepository::getByServiceId($params['serviceid']);
        if ($order) {
            $previousStatus = $order->status === 'Suspended' ? 'Pending' : $order->status;
            OrderRepository::updateStatusByServiceId($params['serviceid'], $previousStatus);
        }
        return 'success';
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Terminate account
 */
function nicsrs_ssl_TerminateAccount(array $params)
{
    try {
        $order = OrderRepository::getByServiceId($params['serviceid']);
        
        if ($order && !empty($order->remoteid)) {
            // Optionally cancel/revoke certificate at provider
            // This depends on business requirements
        }
        
        OrderRepository::updateStatusByServiceId($params['serviceid'], 'Terminated');
        return 'success';
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Admin services tab fields
 */
function nicsrs_ssl_AdminServicesTabFields(array $params)
{
    $order = OrderRepository::getByServiceId($params['serviceid']);
    
    if (!$order) {
        return ['Status' => 'No certificate order found'];
    }
    
    $configdata = json_decode($order->configdata, true) ?: [];
    $applyReturn = $configdata['applyReturn'] ?? [];
    $domainInfo = $configdata['domainInfo'] ?? [];
    
    $domain = !empty($domainInfo) ? $domainInfo[0]['domainName'] ?? 'N/A' : 'N/A';
    
    return [
        'Certificate ID' => $order->remoteid ?: 'Not yet assigned',
        'Status' => '<span class="label label-' . getStatusLabelClass($order->status) . '">' . $order->status . '</span>',
        'Domain' => $domain,
        'Certificate Type' => $order->certtype ?: 'N/A',
        'Issued Date' => $applyReturn['beginDate'] ?? 'N/A',
        'Expiry Date' => $applyReturn['endDate'] ?? 'N/A',
        'Vendor ID' => $applyReturn['vendorId'] ?? 'N/A',
        'Last Refresh' => $configdata['lastRefresh'] ?? 'Never',
    ];
}

/**
 * Get status label class for admin display
 */
function getStatusLabelClass($status)
{
    $classes = [
        'Complete' => 'success',
        'Issued' => 'success',
        'Pending' => 'warning',
        'Awaiting Configuration' => 'default',
        'Draft' => 'default',
        'Cancelled' => 'danger',
        'Revoked' => 'danger',
        'Terminated' => 'danger',
        'Suspended' => 'warning',
        'Reissue' => 'info',
    ];
    return $classes[$status] ?? 'default';
}

/**
 * Admin custom button array
 */
function nicsrs_ssl_AdminCustomButtonArray()
{
    return [
        'Refresh Status' => 'AdminRefreshStatus',
        'Resend DCV Email' => 'AdminResendDCV',
    ];
}

/**
 * Admin refresh status action
 */
function nicsrs_ssl_AdminRefreshStatus(array $params)
{
    try {
        $result = ActionDispatcher::dispatch('refreshStatus', $params);
        
        if (isset($result['success']) && $result['success']) {
            return 'success';
        }
        
        return $result['message'] ?? 'Failed to refresh status';
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Admin resend DCV email action
 */
function nicsrs_ssl_AdminResendDCV(array $params)
{
    try {
        $result = ActionDispatcher::dispatch('resendDCVEmail', $params);
        
        if (isset($result['success']) && $result['success']) {
            return 'success';
        }
        
        return $result['message'] ?? 'Failed to resend DCV email';
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Client area custom button array
 */
function nicsrs_ssl_ClientAreaCustomButtonArray(array $params)
{
    $order = OrderRepository::getByServiceId($params['serviceid']);
    
    if (!$order) {
        return [];
    }
    
    $buttons = [];
    
    switch ($order->status) {
        case 'Complete':
        case 'Issued':
            $buttons = [
                'Download Certificate' => 'clientDownload',
                'Reissue Certificate' => 'clientReissue',
                'Refresh Status' => 'clientRefresh',
            ];
            break;
        case 'Pending':
            $buttons = [
                'Refresh Status' => 'clientRefresh',
            ];
            break;
        default:
            $buttons = [];
    }
    
    return $buttons;
}

/**
 * Client download button action
 */
function nicsrs_ssl_clientDownload(array $params)
{
    return ActionDispatcher::dispatch('downCert', $params);
}

/**
 * Client reissue button action
 */
function nicsrs_ssl_clientReissue(array $params)
{
    // Redirect to reissue page
    header('Location: clientarea.php?action=productdetails&id=' . $params['serviceid'] . '&modop=custom&a=reissue');
    exit;
}

/**
 * Client refresh button action
 */
function nicsrs_ssl_clientRefresh(array $params)
{
    return ActionDispatcher::dispatch('refreshStatus', $params);
}

/**
 * Client area output - Main entry point for client area
 */
function nicsrs_ssl_ClientArea(array $params)
{
    // Ensure database table exists
    OrderRepository::ensureTableExists();
    
    // =====================================================
    // AJAX ACTION HANDLING - Must be checked FIRST
    // =====================================================
    $postAction = $_POST['action'] ?? '';
    $isXhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // List of AJAX actions that should return JSON
    $ajaxActions = [
        'submitApply',
        'saveDraft', 
        'refreshStatus',
        'downCert',
        'batchUpdateDCV',
        'resendDCVEmail',
        'cancelOrder',
        'revoke',
        'submitReissue',
        'renew',
        'generateCSR',
        'decodeCsr',
    ];
    
    // Check if this is an AJAX action request
    if ($isXhr || in_array($postAction, $ajaxActions)) {
        // Clear ALL output buffers to prevent HTML contamination
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        
        try {
            $action = $postAction ?: ($_GET['action'] ?? '');
            
            // Log for debugging
            logModuleCall('nicsrs_ssl', 'AJAX_Request', [
                'action' => $action,
                'POST' => $_POST,
                'isXhr' => $isXhr,
            ], 'Processing AJAX request');
            
            if (empty($action)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No action specified'
                ]);
                exit;
            }
            
            // Check if action method exists
            if (!method_exists(ActionController::class, $action)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Unknown action: ' . $action
                ]);
                exit;
            }
            
            // Call the action controller method
            // Note: ActionController methods will call jsonResponse() and exit
            $result = ActionController::$action($params);
            
            // If we get here, the method didn't exit (fallback)
            if (is_array($result)) {
                echo json_encode($result);
            }
            
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'AJAX_Error', [
                'action' => $postAction,
                'error' => $e->getMessage(),
            ], $e->getTraceAsString());
            
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    // =====================================================
    // PAGE VIEW HANDLING
    // =====================================================
    
    // Get requested page/step
    $requestedAction = $_REQUEST['step'] ?? 'index';
    
    // Also check for 'a' parameter (custom actions)
    if (isset($_REQUEST['modop']) && $_REQUEST['modop'] === 'custom' && isset($_REQUEST['a'])) {
        $requestedAction = $_REQUEST['a'];
    }
    
    // Handle page views
    if ($requestedAction === 'index' || empty($requestedAction)) {
        try {
            $result = PageDispatcher::dispatchByStatus($params);
            
            // Convert to WHMCS expected format
            if (isset($result['templatefile'])) {
                return [
                    'tabOverviewReplacementTemplate' => 'view/' . $result['templatefile'] . '.tpl',
                    'templateVariables' => $result['vars'] ?? [],
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
            
            return [
                'tabOverviewReplacementTemplate' => 'view/error.tpl',
                'templateVariables' => [
                    'errorTitle' => 'Error',
                    'errorMessage' => $e->getMessage(),
                ],
            ];
        }
    }
    
    // Handle other page views (reissue, manage, etc.)
    try {
        $result = PageDispatcher::dispatch($requestedAction, $params);
        
        if (isset($result['templatefile'])) {
            return [
                'tabOverviewReplacementTemplate' => 'view/' . $result['templatefile'] . '.tpl',
                'templateVariables' => $result['vars'] ?? [],
            ];
        }
        
        return $result;
        
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        
        return [
            'tabOverviewReplacementTemplate' => 'view/error.tpl',
            'templateVariables' => [
                'errorTitle' => 'Error',
                'errorMessage' => $e->getMessage(),
            ],
        ];
    }
}

/**
 * Renew certificate hook
 */
function nicsrs_ssl_Renew(array $params)
{
    try {
        // Create new order for renewal
        $existingOrder = OrderRepository::getByServiceId($params['serviceid']);
        
        if ($existingOrder) {
            // Reset status for new certificate application
            OrderRepository::update($existingOrder->id, [
                'remoteid' => '',
                'status' => 'Awaiting Configuration',
                'configdata' => json_encode([
                    'previousCertId' => $existingOrder->remoteid,
                    'isRenewal' => true,
                ]),
                'completiondate' => '0000-00-00 00:00:00',
            ]);
        }
        
        return 'success';
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage());
        return $e->getMessage();
    }
}