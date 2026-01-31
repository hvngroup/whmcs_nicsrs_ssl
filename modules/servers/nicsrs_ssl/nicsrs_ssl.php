<?php
/**
 * NicSRS SSL WHMCS Server Provisioning Module
 * 
 * @package    nicsrs_ssl
 * @version    2.0.1
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Define constants
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('NICSRS_SSL_PATH')) define('NICSRS_SSL_PATH', __DIR__ . DS);
if (!defined('NICSRS_SSL_VERSION')) define('NICSRS_SSL_VERSION', '2.0.1');

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
        'DisplayName' => 'NicSRS SSL',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
        'DefaultNonSSLPort' => '80',
        'DefaultSSLPort' => '443',
        'ServiceSingleSignOnLabel' => 'Manage Certificate',
        'AdminSingleSignOnLabel' => 'View Certificate Details',
    ];
}

/**
 * Test API connection - returns simple success/fail
 * 
 * @return array ['success' => bool, 'message' => string]
 */
function testApiConnection(): array
{
    try {
        // Get token from addon module
        $addonToken = \WHMCS\Database\Capsule::table('tbladdonmodules')
            ->where('module', 'nicsrs_ssl_admin')
            ->where('setting', 'api_token')
            ->first();
        
        if (!$addonToken || empty($addonToken->value)) {
            return ['success' => false, 'message' => 'Token not configured'];
        }
        
        $apiToken = $addonToken->value;
        
        // Quick API test
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://portal.nicsrs.com/ssl/productList',
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'api_token' => $apiToken,
                'vendor' => 'Sectigo',
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            return ['success' => false, 'message' => 'Connection failed'];
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['code']) && $data['code'] == 1) {
            return ['success' => true, 'message' => 'Connected'];
        }
        
        return ['success' => false, 'message' => 'API error'];
        
    } catch (\Exception $e) {
        return ['success' => false, 'message' => 'Error'];
    }
}

/**
 * Get certificate types from addon cache or static fallback
 */
function getCertTypeOptionsForDropdown(): string
{
    try {
        if (\WHMCS\Database\Capsule::schema()->hasTable('mod_nicsrs_products')) {
            $products = \WHMCS\Database\Capsule::table('mod_nicsrs_products')
                ->orderBy('vendor')
                ->orderBy('product_name')
                ->pluck('product_code')
                ->toArray();
            
            if (count($products) > 0) {
                return implode(',', $products);
            }
        }
    } catch (\Exception $e) {}
    
    return \nicsrsSSL\CertificateFunc::getCertAttributesDropdown();
}

/**
 * Module configuration options
 */
function nicsrs_ssl_ConfigOptions()
{
    // Test API connection
    $apiTest = testApiConnection();
    $apiStatus = $apiTest['success'] ? '✅ ' . $apiTest['message'] : '❌ ' . $apiTest['message'];
    
    // Count cached products
    $cachedCount = 0;
    try {
        if (\WHMCS\Database\Capsule::schema()->hasTable('mod_nicsrs_products')) {
            $cachedCount = \WHMCS\Database\Capsule::table('mod_nicsrs_products')->count();
        }
    } catch (\Exception $e) {}
    
    return [
        'cert_type' => [
            'FriendlyName' => 'Certificate Type',
            'Type' => 'dropdown',
            'Options' => getCertTypeOptionsForDropdown(),
            'Description' => "{$cachedCount} products in cache. <a href=\"addonmodules.php?module=nicsrs_ssl_admin&action=products\" target=\"_blank\">Sync Products</a>",
        ],
        'api_token' => [
            'FriendlyName' => 'API Token (Override)',
            'Type' => 'password',
            'Size' => '64',
            'Description' => "API Status: {$apiStatus}. Leave empty to use shared token from <a href=\"addonmodules.php?module=nicsrs_ssl_admin\" target=\"_blank\">Admin Addon</a>.",
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
 * 
 * Called when admin terminates a service. This will:
 * - Revoke certificate if it was issued (Complete/Issued status)
 * - Cancel certificate if it was pending (Pending/Processing status)
 * - Update local status to Terminated
 * 
 * @param array $params Module parameters from WHMCS
 * @return string 'success' or error message
 */
function nicsrs_ssl_TerminateAccount(array $params)
{
    try {
        $order = OrderRepository::getByServiceId($params['serviceid']);
        
        // If no order exists, just return success
        if (!$order) {
            return 'success';
        }
        
        $apiActionTaken = false;
        $apiResult = null;
        
        // Only attempt API call if we have a remote certificate ID
        if (!empty($order->remoteid)) {
            $currentStatus = strtolower($order->status);
            
            // Determine which API action to take based on certificate status
            switch ($currentStatus) {
                // Certificate has been issued - need to REVOKE
                case 'complete':
                case 'issued':
                    try {
                        $apiResult = ApiService::revoke(
                            $params, 
                            $order->remoteid, 
                            'Service terminated by administrator'
                        );
                        $parsed = ApiService::parseResponse($apiResult);
                        
                        if ($parsed['success']) {
                            $apiActionTaken = true;
                            logModuleCall('nicsrs_ssl', 'TerminateAccount::revoke', [
                                'serviceid' => $params['serviceid'],
                                'remoteid' => $order->remoteid,
                            ], 'Certificate revoked successfully');
                        } else {
                            // Log but don't fail - certificate might already be revoked/expired
                            logModuleCall('nicsrs_ssl', 'TerminateAccount::revoke', [
                                'serviceid' => $params['serviceid'],
                                'remoteid' => $order->remoteid,
                            ], 'Revoke API returned: ' . ($parsed['message'] ?? 'Unknown error'));
                        }
                    } catch (Exception $e) {
                        // Log error but continue with local termination
                        logModuleCall('nicsrs_ssl', 'TerminateAccount::revoke', [
                            'serviceid' => $params['serviceid'],
                            'remoteid' => $order->remoteid,
                        ], 'Revoke exception: ' . $e->getMessage());
                    }
                    break;
                
                // Certificate is pending - need to CANCEL
                case 'pending':
                case 'processing':
                case 'awaiting configuration':
                case 'draft':
                    try {
                        $apiResult = ApiService::cancel(
                            $params, 
                            $order->remoteid, 
                            'Service terminated by administrator'
                        );
                        $parsed = ApiService::parseResponse($apiResult);
                        
                        if ($parsed['success']) {
                            $apiActionTaken = true;
                            logModuleCall('nicsrs_ssl', 'TerminateAccount::cancel', [
                                'serviceid' => $params['serviceid'],
                                'remoteid' => $order->remoteid,
                            ], 'Certificate order cancelled successfully');
                        } else {
                            logModuleCall('nicsrs_ssl', 'TerminateAccount::cancel', [
                                'serviceid' => $params['serviceid'],
                                'remoteid' => $order->remoteid,
                            ], 'Cancel API returned: ' . ($parsed['message'] ?? 'Unknown error'));
                        }
                    } catch (Exception $e) {
                        logModuleCall('nicsrs_ssl', 'TerminateAccount::cancel', [
                            'serviceid' => $params['serviceid'],
                            'remoteid' => $order->remoteid,
                        ], 'Cancel exception: ' . $e->getMessage());
                    }
                    break;
                
                // Already terminated/cancelled/revoked - no API action needed
                case 'cancelled':
                case 'revoked':
                case 'terminated':
                case 'expired':
                    // No action needed, already in terminal state
                    break;
                    
                default:
                    // Unknown status - try cancel as fallback
                    try {
                        $apiResult = ApiService::cancel(
                            $params, 
                            $order->remoteid, 
                            'Service terminated by administrator'
                        );
                        ApiService::parseResponse($apiResult);
                    } catch (Exception $e) {
                        // Ignore errors for unknown status
                    }
                    break;
            }
        }
        
        // Always update local status to Terminated
        OrderRepository::updateStatusByServiceId($params['serviceid'], 'Terminated');
        
        // Update configdata with termination info
        $configdata = json_decode($order->configdata, true) ?: [];
        $configdata['terminatedAt'] = date('Y-m-d H:i:s');
        $configdata['terminatedBy'] = 'admin';
        $configdata['apiActionTaken'] = $apiActionTaken;
        
        OrderRepository::updateConfigData($order->id, $configdata);
        
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
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
        return [
            'Status' => '<span class="label label-default">No certificate order found</span>',
            'Action' => '<a href="addonmodules.php?module=nicsrs_ssl_admin&action=orders" class="btn btn-xs btn-primary" target="_blank"><i class="fa fa-list"></i> View All Orders</a>',
        ];
    }
    
    $configdata = json_decode($order->configdata, true) ?: [];
    $applyReturn = $configdata['applyReturn'] ?? [];
    $domainInfo = $configdata['domainInfo'] ?? [];
    
    $domain = !empty($domainInfo) ? $domainInfo[0]['domainName'] ?? 'N/A' : 'N/A';
    
    // Build manage link
    $manageUrl = 'addonmodules.php?module=nicsrs_ssl_admin&action=order&id=' . $order->id;
    $manageLink = '<a href="' . $manageUrl . '" class="btn btn-xs btn-success" target="_blank">'
                . '<i class="fa fa-external-link"></i> Manage</a>';
    
    return [
        'Order ID' => '#' . $order->id . ' ' . $manageLink,
        'Certificate ID' => $order->remoteid ?: '<span class="text-muted">Not yet assigned</span>',
        'Status' => '<span class="label label-' . getStatusLabelClass($order->status) . '">' . $order->status . '</span>',
        'Domain' => '<code>' . htmlspecialchars($domain) . '</code>',
        'Certificate Type' => $order->certtype ?: 'N/A',
        'Issued Date' => $applyReturn['beginDate'] ?? '<span class="text-muted">N/A</span>',
        'Expiry Date' => $applyReturn['endDate'] ?? '<span class="text-muted">N/A</span>',
        'Vendor ID' => $applyReturn['vendorId'] ?? 'N/A',
        'Last Refresh' => $configdata['lastRefresh'] ?? '<span class="text-muted">Never</span>',
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
        'Manage Order' => 'AdminManageOrder',
        'Refresh Status' => 'AdminRefreshStatus',
        'Resend DCV Email' => 'AdminResendDCV',
    ];
}

/**
 * Admin Manage Order - Redirect to addon module order detail page
 * 
 * @param array $params Module parameters
 * @return string Result message or redirect
 */
function nicsrs_ssl_AdminManageOrder(array $params)
{
    try {
        $order = OrderRepository::getByServiceId($params['serviceid']);
        
        if (!$order) {
            return 'No certificate order found for this service';
        }
        
        $addonUrl = 'addonmodules.php?module=nicsrs_ssl_admin&action=order&id=' . $order->id;
        
        echo '<script>window.location.href="' . $addonUrl . '";</script>';
        exit;
        
    } catch (Exception $e) {
        logModuleCall('nicsrs_ssl', __FUNCTION__, $params, $e->getMessage());
        return $e->getMessage();
    }
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
    // AJAX ACTION HANDLING - Check 'step' parameter FIRST
    // =====================================================
    
    // Get step from URL (like old module)
    $step = $_GET['step'] ?? $_REQUEST['step'] ?? '';
    
    // Map step names to action methods
    // Includes backward compatibility with old module step names
    // FIXED v2.0.1: Added getDcvEmails action
    $stepToAction = [
        // Certificate application
        'applyssl'      => 'submitApply',
        'savedraft'     => 'saveDraft',
        'submitApply'   => 'submitApply',
        'saveDraft'     => 'saveDraft',
        
        // Status actions
        'refreshStatus' => 'refreshStatus',
        'refresh'       => 'refreshStatus',
        
        // Download actions
        'downCert'      => 'downCert',
        'downcert'      => 'downCert',      // Lowercase variant
        'download'      => 'downCert',
        'downkey'       => 'downCert',      // Old module: download private key
        
        // DCV actions
        'batchUpdateDCV'=> 'batchUpdateDCV',
        'resendDCVEmail'=> 'resendDCVEmail',
        'getDcvEmails'  => 'getDcvEmails',  // NEW v2.0.1: Get DCV email options for domain
        
        // Order management
        'cancelOrder'   => 'cancelOrder',
        'cancleOrder'   => 'cancelOrder',   // Old module typo
        'revoke'        => 'revoke',
        
        // Reissue/Replace
        'submitReissue' => 'submitReissue',
        'reissue'       => 'submitReissue',
        'replacessl'    => 'submitReissue', // Old module name
        'submitReplace' => 'submitReissue', // Alternative name
        
        // Renew
        'renew'         => 'renew',
        
        // CSR tools
        'generateCSR'   => 'generateCSR',
        'decodeCsr'     => 'decodeCsr',
    ];
    
    // Check if this is an AJAX action request
    if (!empty($step) && isset($stepToAction[$step])) {
        // Clear ALL output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        
        try {
            $action = $stepToAction[$step];
            
            // Log for debugging
            logModuleCall('nicsrs_ssl', 'AJAX_Request', [
                'step' => $step,
                'action' => $action,
                'POST_keys' => array_keys($_POST),
                'has_data' => isset($_POST['data']),
            ], 'Processing AJAX request');
            
            // Check if action method exists
            if (!method_exists(ActionController::class, $action)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown action: ' . $action
                ]);
                exit;
            }
            
            // FIXED v2.0.1: Improved POST data handling
            // Handle old module data format: {"data": {...}} or data[key]=value
            // DO NOT merge into $_POST to avoid conflicts - let ActionController handle it
            
            // Call the action controller method
            $result = ActionController::$action($params);
            
            // Log result
            logModuleCall('nicsrs_ssl', 'AJAX_Response', [
                'action' => $action,
                'success' => $result['success'] ?? false,
            ], $result);
            
            // Output JSON response
            if (is_array($result)) {
                echo json_encode($result);
            }
            
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'AJAX_Error', [
                'step' => $step,
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
    
    // Also check for 'a' parameter (custom actions like reissue)
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
            // FIXED v2.0.1: Store both isRenew and originalfromOthers for compatibility
            OrderRepository::update($existingOrder->id, [
                'remoteid' => '',
                'status' => 'Awaiting Configuration',
                'configdata' => json_encode([
                    'previousCertId' => $existingOrder->remoteid,
                    'isRenewal' => true,
                    'isRenew' => '1',
                    'originalfromOthers' => '1',
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