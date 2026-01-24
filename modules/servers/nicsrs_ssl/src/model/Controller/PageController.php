<?php
/**
 * NicSRS SSL Page Controller
 * 
 * Handles page rendering based on certificate status
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

namespace nicsrsSSL;

use WHMCS\Database\Capsule;
use Exception;

class PageController
{
    /**
     * @var array Module parameters
     */
    protected $params = [];
    
    /**
     * @var array Language strings
     */
    protected $_LANG = [];
    
    /**
     * @var string Base path for templates
     */
    protected $templatePath = '';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->templatePath = dirname(dirname(dirname(__FILE__))) . '/view/';
    }

    /**
     * Main index action - Route to appropriate view based on order status
     * 
     * @param array $params Module parameters
     * @return array Template and variables
     */
    public function index(array $params)
    {
        $this->params = $params;
        $this->_LANG = nicsrsFunc::loadLanguage($_GET['language'] ?? '', $params['userid'] ?? 0);
        
        // Get SSL order
        $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        
        // No order yet - show application form
        if (empty($order) || empty($order->remoteid)) {
            return $this->showApplyCert($params, $order);
        }
        
        // Route based on status
        $status = strtolower($order->status);
        
        switch ($status) {
            case 'complete':
            case 'issued':
            case 'active':
                return $this->showComplete($params, $order);
                
            case 'pending':
            case 'processing':
                return $this->showPending($params, $order);
                
            case 'awaiting':
            case 'draft':
                return $this->showApplyCert($params, $order);
                
            case 'expiring':
                return $this->showExpiring($params, $order);
                
            case 'cancelled':
            case 'revoked':
            case 'expired':
            case 'rejected':
                return $this->showInactive($params, $order);
                
            default:
                return $this->showApplyCert($params, $order);
        }
    }

    /**
     * Show certificate management page
     * 
     * @param array $params Module parameters
     * @return array Template and variables
     */
    public function manage(array $params)
    {
        $this->params = $params;
        $this->_LANG = nicsrsFunc::loadLanguage($_GET['language'] ?? '', $params['userid'] ?? 0);
        
        $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        
        if (!$order || !in_array($order->status, ['complete', 'issued', 'expiring'])) {
            return $this->showError('Certificate not available for management');
        }
        
        return $this->showComplete($params, $order, true);
    }

    /**
     * Show reissue form
     * 
     * @param array $params Module parameters
     * @return array Template and variables
     */
    public function reissue(array $params)
    {
        $this->params = $params;
        $this->_LANG = nicsrsFunc::loadLanguage($_GET['language'] ?? '', $params['userid'] ?? 0);
        
        $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        
        if (!$order || !in_array($order->status, ['complete', 'issued'])) {
            return $this->showError('Certificate cannot be reissued');
        }
        
        $configData = json_decode($order->configdata, true) ?: [];
        
        return [
            'tabOverviewReplacementTemplate' => 'view/reissue.tpl',
            'templateVariables' => [
                'order' => $order,
                'certId' => $order->remoteid,
                'configData' => $configData,
                'domain' => $configData['domainInfo'][0]['domainName'] ?? '',
                'domainInfo' => $configData['domainInfo'] ?? [],
                'currentCsr' => $configData['csr'] ?? '',
                'serviceId' => $params['serviceid'],
                'moduleLink' => $this->getModuleLink($params),
                'countryList' => nicsrsFunc::getCountryList(),
                '_LANG' => $this->_LANG,
                'LANG' => $this->_LANG,
            ],
        ];
    }

    /**
     * Show certificate application form
     * 
     * @param array $params Module parameters
     * @param object|null $order Existing order
     * @return array Template and variables
     */
    protected function showApplyCert(array $params, $order = null)
    {
        $configData = [];
        
        if ($order) {
            $configData = json_decode($order->configdata, true) ?: [];
        }
        
        // Get certificate type info
        $certType = $params['configoption1'] ?? '';
        $certInfo = nicsrsFunc::getCertAttributes($certType);
        
        // Get product info from Addon if available
        $productInfo = $this->getProductInfo($certType);
        
        return [
            'tabOverviewReplacementTemplate' => 'view/applycert.tpl',
            'templateVariables' => [
                'order' => $order,
                'configData' => $configData,
                'certType' => $certType,
                'certInfo' => $certInfo,
                'productInfo' => $productInfo,
                'serviceId' => $params['serviceid'],
                'domain' => $configData['domain'] ?? ($params['domain'] ?? ''),
                'moduleLink' => $this->getModuleLink($params),
                'countryList' => nicsrsFunc::getCountryList(),
                'dcvMethods' => $this->getDcvMethods(),
                'validationRequired' => $this->getValidationType($certType),
                '_LANG' => $this->_LANG,
                'LANG' => $this->_LANG,
            ],
        ];
    }

    /**
     * Show completed certificate view
     * 
     * @param array $params Module parameters
     * @param object $order Order object
     * @param bool $manageMode Show management options
     * @return array Template and variables
     */
    protected function showComplete(array $params, $order, $manageMode = false)
    {
        $configData = json_decode($order->configdata, true) ?: [];
        
        // Refresh status if needed
        if ($this->shouldRefreshStatus($configData)) {
            $this->refreshOrderStatus($params, $order);
            $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
            $configData = json_decode($order->configdata, true) ?: [];
        }
        
        $applyReturn = $configData['applyReturn'] ?? [];
        
        // Calculate expiry info
        $expiryInfo = $this->calculateExpiryInfo($applyReturn['endDate'] ?? '');
        
        return [
            'tabOverviewReplacementTemplate' => 'view/complete.tpl',
            'templateVariables' => [
                'order' => $order,
                'certId' => $order->remoteid,
                'configData' => $configData,
                'applyReturn' => $applyReturn,
                'domain' => $configData['domainInfo'][0]['domainName'] ?? '',
                'domainInfo' => $configData['domainInfo'] ?? [],
                'validFrom' => $applyReturn['beginDate'] ?? '',
                'validTo' => $applyReturn['endDate'] ?? '',
                'expiryInfo' => $expiryInfo,
                'hasCertificate' => !empty($applyReturn['certificate']),
                'hasPrivateKey' => !empty($applyReturn['privateKey']) || !empty($configData['privateKey']),
                'canReissue' => true,
                'canDownload' => !empty($applyReturn['certificate']),
                'manageMode' => $manageMode,
                'serviceId' => $params['serviceid'],
                'moduleLink' => $this->getModuleLink($params),
                '_LANG' => $this->_LANG,
                'LANG' => $this->_LANG,
            ],
        ];
    }

    /**
     * Show pending certificate view (waiting for validation)
     * 
     * @param array $params Module parameters
     * @param object $order Order object
     * @return array Template and variables
     */
    protected function showPending(array $params, $order)
    {
        $configData = json_decode($order->configdata, true) ?: [];
        
        // Refresh status from API
        $this->refreshOrderStatus($params, $order);
        
        // Reload order after refresh
        $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        $configData = json_decode($order->configdata, true) ?: [];
        
        // Check if completed after refresh
        if (in_array($order->status, ['complete', 'issued'])) {
            return $this->showComplete($params, $order);
        }
        
        $applyReturn = $configData['applyReturn'] ?? [];
        $domainInfo = $configData['domainInfo'] ?? [];
        
        // Prepare DCV instructions
        $dcvInstructions = $this->prepareDcvInstructions($applyReturn, $domainInfo);
        
        return [
            'tabOverviewReplacementTemplate' => 'view/pending.tpl',
            'templateVariables' => [
                'order' => $order,
                'certId' => $order->remoteid,
                'configData' => $configData,
                'applyReturn' => $applyReturn,
                'domain' => $domainInfo[0]['domainName'] ?? '',
                'domainInfo' => $domainInfo,
                'dcvInstructions' => $dcvInstructions,
                'dcvMethods' => $this->getDcvMethods(),
                'applicationStatus' => $applyReturn['application']['status'] ?? 'pending',
                'dcvStatus' => $applyReturn['dcv']['status'] ?? 'pending',
                'issuedStatus' => $applyReturn['issued']['status'] ?? 'pending',
                'serviceId' => $params['serviceid'],
                'moduleLink' => $this->getModuleLink($params),
                '_LANG' => $this->_LANG,
                'LANG' => $this->_LANG,
            ],
        ];
    }

    /**
     * Show expiring certificate view
     * 
     * @param array $params Module parameters
     * @param object $order Order object
     * @return array Template and variables
     */
    protected function showExpiring(array $params, $order)
    {
        // Show complete view with expiring warning
        $result = $this->showComplete($params, $order);
        $result['templateVariables']['isExpiring'] = true;
        $result['templateVariables']['showRenewalWarning'] = true;
        
        return $result;
    }

    /**
     * Show inactive certificate (cancelled, revoked, expired)
     * 
     * @param array $params Module parameters
     * @param object $order Order object
     * @return array Template and variables
     */
    protected function showInactive(array $params, $order)
    {
        $configData = json_decode($order->configdata, true) ?: [];
        
        $statusMessages = [
            'cancelled' => $this->_LANG['status_cancelled_message'] ?? 'This certificate order has been cancelled.',
            'revoked' => $this->_LANG['status_revoked_message'] ?? 'This certificate has been revoked and is no longer valid.',
            'expired' => $this->_LANG['status_expired_message'] ?? 'This certificate has expired. Please renew your service to get a new certificate.',
            'rejected' => $this->_LANG['status_rejected_message'] ?? 'This certificate request was rejected by the CA.',
        ];
        
        return [
            'tabOverviewReplacementTemplate' => 'view/message.tpl',
            'templateVariables' => [
                'order' => $order,
                'status' => $order->status,
                'statusMessage' => $statusMessages[$order->status] ?? 'Certificate is not active.',
                'configData' => $configData,
                'domain' => $configData['domainInfo'][0]['domainName'] ?? '',
                'canReapply' => in_array($order->status, ['cancelled', 'expired', 'rejected']),
                'serviceId' => $params['serviceid'],
                'moduleLink' => $this->getModuleLink($params),
                '_LANG' => $this->_LANG,
                'LANG' => $this->_LANG,
            ],
        ];
    }

    /**
     * Show error message
     * 
     * @param string $message Error message
     * @return array Template and variables
     */
    protected function showError($message)
    {
        return [
            'tabOverviewReplacementTemplate' => 'view/error.tpl',
            'templateVariables' => [
                'usefulErrorHelper' => $message,
                '_LANG' => $this->_LANG,
                'LANG' => $this->_LANG,
            ],
        ];
    }

    /**
     * Refresh order status from NicSRS API
     * 
     * @param array $params Module parameters
     * @param object $order Order object
     * @return bool Success
     */
    protected function refreshOrderStatus(array $params, $order)
    {
        if (empty($order->remoteid)) {
            return false;
        }
        
        try {
            $apiToken = $params['api_token'] ?? nicsrsAPI::getApiToken($params);
            
            if (empty($apiToken)) {
                return false;
            }
            
            $collectResult = nicsrsAPI::call('collect', [
                'api_token' => $apiToken,
                'certId' => $order->remoteid,
            ]);
            
            if (isset($collectResult->code) && in_array($collectResult->code, [1, 2])) {
                // Update order with API response
                nicsrsSSLSql::UpdateFromApiResponse($order->id, $collectResult->data);
                
                // Update status
                $newStatus = nicsrsFunc::mapApiStatusToOrder($collectResult->data);
                if ($newStatus && $newStatus !== $order->status) {
                    nicsrsSSLSql::UpdateOrderStatus($order->id, $newStatus);
                    
                    // Update completion date if now complete
                    if ($newStatus === 'complete' && $order->completiondate === '0000-00-00 00:00:00') {
                        Capsule::table('nicsrs_sslorders')
                            ->where('id', $order->id)
                            ->update(['completiondate' => date('Y-m-d H:i:s')]);
                    }
                }
                
                return true;
            }
            
        } catch (Exception $e) {
            logModuleCall(
                'nicsrs_ssl',
                'refreshOrderStatus',
                ['certId' => $order->remoteid],
                $e->getMessage(),
                ''
            );
        }
        
        return false;
    }

    /**
     * Check if status should be refreshed
     * 
     * @param array $configData Config data
     * @return bool
     */
    protected function shouldRefreshStatus(array $configData)
    {
        // Don't refresh too frequently
        $lastRefresh = $configData['lastRefresh'] ?? null;
        
        if (!$lastRefresh) {
            return true;
        }
        
        // Refresh if last refresh was more than 5 minutes ago
        $lastRefreshTime = strtotime($lastRefresh);
        return (time() - $lastRefreshTime) > 300;
    }

    /**
     * Calculate expiry information
     * 
     * @param string $endDate End date
     * @return array Expiry info
     */
    protected function calculateExpiryInfo($endDate)
    {
        if (empty($endDate)) {
            return [
                'daysRemaining' => null,
                'isExpiring' => false,
                'isExpired' => false,
                'statusClass' => 'default',
            ];
        }
        
        $days = nicsrsFunc::daysUntilExpiry($endDate);
        
        $isExpired = $days !== null && $days < 0;
        $isExpiring = $days !== null && $days >= 0 && $days <= 30;
        
        $statusClass = 'success';
        if ($isExpired) {
            $statusClass = 'danger';
        } elseif ($isExpiring) {
            $statusClass = 'warning';
        }
        
        return [
            'daysRemaining' => $days,
            'isExpiring' => $isExpiring,
            'isExpired' => $isExpired,
            'statusClass' => $statusClass,
        ];
    }

    /**
     * Prepare DCV instructions for display
     * 
     * @param array $applyReturn Apply return data
     * @param array $domainInfo Domain info
     * @return array DCV instructions
     */
    protected function prepareDcvInstructions(array $applyReturn, array $domainInfo)
    {
        $instructions = [];
        
        foreach ($domainInfo as $domain) {
            $domainName = $domain['domainName'] ?? '';
            $dcvMethod = $domain['dcvMethod'] ?? 'EMAIL';
            $isVerified = $domain['isVerified'] ?? ($domain['is_verify'] === 'verified');
            
            $instruction = [
                'domain' => $domainName,
                'method' => $dcvMethod,
                'methodName' => nicsrsFunc::getDcvMethodName($dcvMethod),
                'isVerified' => $isVerified,
                'details' => [],
            ];
            
            // Add method-specific details
            if (!$isVerified) {
                switch ($dcvMethod) {
                    case 'EMAIL':
                        $instruction['details']['email'] = $domain['dcvEmail'] ?? '';
                        $instruction['details']['message'] = $this->_LANG['dcv_email_instruction'] ?? 
                            'Check your email and click the validation link.';
                        break;
                        
                    case 'HTTP_CSR_HASH':
                    case 'HTTPS_CSR_HASH':
                        $instruction['details']['fileName'] = $applyReturn['DCVfileName'] ?? '';
                        $instruction['details']['fileContent'] = $applyReturn['DCVfileContent'] ?? '';
                        $instruction['details']['filePath'] = $applyReturn['DCVfilePath'] ?? 
                            "http://{$domainName}/.well-known/pki-validation/" . ($applyReturn['DCVfileName'] ?? '');
                        $instruction['details']['message'] = $this->_LANG['dcv_http_instruction'] ?? 
                            'Create the validation file at the specified path.';
                        break;
                        
                    case 'CNAME_CSR_HASH':
                    case 'DNS_CSR_HASH':
                        $instruction['details']['dnsHost'] = $applyReturn['DCVdnsHost'] ?? '';
                        $instruction['details']['dnsValue'] = $applyReturn['DCVdnsValue'] ?? '';
                        $instruction['details']['dnsType'] = $applyReturn['DCVdnsType'] ?? 'CNAME';
                        $instruction['details']['message'] = $this->_LANG['dcv_dns_instruction'] ?? 
                            'Add the DNS record to your domain.';
                        break;
                }
            }
            
            $instructions[] = $instruction;
        }
        
        return $instructions;
    }

    /**
     * Get DCV methods for dropdown
     * 
     * @return array DCV methods
     */
    protected function getDcvMethods()
    {
        return [
            'EMAIL' => [
                'name' => $this->_LANG['dcv_email'] ?? 'Email Validation',
                'description' => $this->_LANG['dcv_email_desc'] ?? 'Verify via email to domain administrator',
            ],
            'HTTP_CSR_HASH' => [
                'name' => $this->_LANG['dcv_http'] ?? 'HTTP File Validation',
                'description' => $this->_LANG['dcv_http_desc'] ?? 'Upload validation file to web server',
            ],
            'HTTPS_CSR_HASH' => [
                'name' => $this->_LANG['dcv_https'] ?? 'HTTPS File Validation',
                'description' => $this->_LANG['dcv_https_desc'] ?? 'Upload validation file with HTTPS',
            ],
            'CNAME_CSR_HASH' => [
                'name' => $this->_LANG['dcv_cname'] ?? 'DNS CNAME Validation',
                'description' => $this->_LANG['dcv_cname_desc'] ?? 'Add CNAME record to DNS',
            ],
            'DNS_CSR_HASH' => [
                'name' => $this->_LANG['dcv_dns'] ?? 'DNS TXT Validation',
                'description' => $this->_LANG['dcv_dns_desc'] ?? 'Add TXT record to DNS',
            ],
        ];
    }

    /**
     * Get validation type for certificate
     * 
     * @param string $certType Certificate type code
     * @return string Validation type (dv, ov, ev)
     */
    protected function getValidationType($certType)
    {
        $certInfo = nicsrsFunc::getCertAttributes($certType);
        
        if ($certInfo && isset($certInfo['validation'])) {
            return $certInfo['validation'];
        }
        
        // Try to determine from product name
        $certType = strtolower($certType);
        
        if (strpos($certType, '-ev') !== false || strpos($certType, '_ev') !== false) {
            return 'ev';
        }
        
        if (strpos($certType, '-ov') !== false || strpos($certType, '_ov') !== false) {
            return 'ov';
        }
        
        return 'dv';
    }

    /**
     * Get product info from Addon Module cache
     * 
     * @param string $productCode Product code
     * @return array|null Product info
     */
    protected function getProductInfo($productCode)
    {
        try {
            $product = Capsule::table('mod_nicsrs_products')
                ->where('product_code', $productCode)
                ->first();
            
            if ($product) {
                return [
                    'name' => $product->product_name,
                    'vendor' => $product->vendor,
                    'validation' => $product->validation_type,
                    'maxDomains' => $product->max_domains,
                    'wildcard' => (bool) $product->wildcard,
                ];
            }
        } catch (Exception $e) {
            // Table might not exist
        }
        
        return null;
    }

    /**
     * Get module link for AJAX calls
     * 
     * @param array $params Module parameters
     * @return string Module link URL
     */
    protected function getModuleLink(array $params)
    {
        $systemUrl = Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')
            ->value('value');
        
        if (!$systemUrl) {
            $systemUrl = '';
        }
        
        return rtrim($systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . $params['serviceid'];
    }
}