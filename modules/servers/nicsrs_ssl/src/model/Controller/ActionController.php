<?php
/**
 * NicSRS SSL Action Controller
 * 
 * Handles AJAX actions for certificate management
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

namespace nicsrsSSL;

use WHMCS\Database\Capsule;
use Exception;

class ActionController
{
    /**
     * @var array Module parameters
     */
    protected $params = [];
    
    /**
     * @var string API token
     */
    protected $apiToken = '';

    /**
     * Get API token from params
     * 
     * @param array $params Module parameters
     * @return string API token
     */
    protected function getApiToken(array $params)
    {
        if (!empty($params['api_token'])) {
            return $params['api_token'];
        }
        
        return nicsrsAPI::getApiToken($params);
    }

    /**
     * Get order by service ID
     * 
     * @param int $serviceId Service ID
     * @return object|null Order
     */
    protected function getOrder($serviceId)
    {
        return nicsrsSSLSql::GetSSLProduct($serviceId);
    }

    /**
     * Validate and get request data
     * 
     * @param string $key Data key
     * @return array Decoded data
     * @throws Exception
     */
    protected function checkData($key)
    {
        if (empty($_POST[$key])) {
            throw new Exception("Missing required data: {$key}");
        }
        
        $data = $_POST[$key];
        
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        return is_array($data) ? $data : [$data];
    }

    /**
     * Validate order exists and has certificate
     * 
     * @param int $serviceId Service ID
     * @return object Order
     * @throws Exception
     */
    protected function checkProduct($serviceId)
    {
        $order = $this->getOrder($serviceId);
        
        if (!$order) {
            throw new Exception("Order not found for service ID: {$serviceId}");
        }
        
        return $order;
    }

    // ========================================
    // Certificate Application Actions
    // ========================================

    /**
     * Decode and validate CSR
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function decodeCsr(array $params)
    {
        try {
            if (empty($_POST['csr'])) {
                return nicsrsResponse::error('CSR is required');
            }
            
            $csr = trim($_POST['csr']);
            
            // Validate CSR format
            if (!nicsrsFunc::validateCsr($csr)) {
                return nicsrsResponse::error('Invalid CSR format');
            }
            
            // Parse CSR
            $parsed = nicsrsFunc::parseCsr($csr);
            
            if (!$parsed) {
                return nicsrsResponse::error('Failed to parse CSR');
            }
            
            return nicsrsResponse::success([
                'commonName' => $parsed['commonName'],
                'organization' => $parsed['organization'],
                'organizationalUnit' => $parsed['organizationalUnit'],
                'locality' => $parsed['locality'],
                'state' => $parsed['state'],
                'country' => $parsed['country'],
            ]);
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    /**
     * Submit certificate application
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function submitApply(array $params)
    {
        try {
            $data = $this->checkData('data');
            $order = $this->checkProduct($params['serviceid']);
            
            // Validate required fields
            if (empty($data['csr'])) {
                return nicsrsResponse::error('CSR is required');
            }
            
            if (empty($data['domainInfo'])) {
                return nicsrsResponse::error('Domain information is required');
            }
            
            $apiToken = $this->getApiToken($params);
            if (empty($apiToken)) {
                return nicsrsResponse::error('API token not configured');
            }
            
            // Get certificate type
            $certType = $params['configoption1'] ?? $order->certtype;
            
            // Prepare API request
            $placeData = [
                'api_token' => $apiToken,
                'productCode' => $certType,
                'csr' => $data['csr'],
                'domainInfo' => json_encode($data['domainInfo']),
                'server' => $data['server'] ?? 'other',
            ];
            
            // Add organization info for OV/EV certificates
            $validationType = $this->getValidationType($certType);
            
            if (in_array($validationType, ['ov', 'ev'])) {
                if (!empty($data['Administrator'])) {
                    $placeData['Administrator'] = json_encode($data['Administrator']);
                }
                if (!empty($data['tech'])) {
                    $placeData['tech'] = json_encode($data['tech']);
                }
                if (!empty($data['finance'])) {
                    $placeData['finance'] = json_encode($data['finance']);
                }
                if (!empty($data['organizationInfo'])) {
                    $placeData['organizationInfo'] = json_encode($data['organizationInfo']);
                }
            }
            
            // Validate request first
            $validateResult = nicsrsAPI::call('validate', [
                'api_token' => $apiToken,
                'productCode' => $certType,
                'csr' => $data['csr'],
                'domainInfo' => json_encode($data['domainInfo']),
            ]);
            
            if (!isset($validateResult->code) || $validateResult->code != 1) {
                return nicsrsResponse::error($validateResult->msg ?? 'Validation failed');
            }
            
            // Place order
            $placeResult = nicsrsAPI::call('place', [
                'api_token' => $apiToken,
                'params' => json_encode($placeData),
            ]);
            
            if (!isset($placeResult->code) || $placeResult->code != 1) {
                return nicsrsResponse::error($placeResult->msg ?? 'Order placement failed');
            }
            
            // Update order in database
            $configData = [
                'csr' => $data['csr'],
                'privateKey' => $data['privateKey'] ?? '',
                'domainInfo' => $data['domainInfo'],
                'server' => $data['server'] ?? 'other',
                'applyReturn' => (array) $placeResult->data,
                'applyParams' => $data,
                'appliedAt' => date('Y-m-d H:i:s'),
            ];
            
            // Add contact info if provided
            if (!empty($data['Administrator'])) {
                $configData['Administrator'] = $data['Administrator'];
            }
            if (!empty($data['organizationInfo'])) {
                $configData['organizationInfo'] = $data['organizationInfo'];
            }
            
            nicsrsSSLSql::UpdateConfigData($order->id, $configData);
            nicsrsSSLSql::UpdateRemoteId($order->id, $placeResult->data->certId);
            nicsrsSSLSql::UpdateOrderStatus($order->id, 'pending');
            nicsrsSSLSql::UpdateCertType($order->id, $certType);
            
            return nicsrsResponse::success([
                'certId' => $placeResult->data->certId,
                'message' => 'Certificate order submitted successfully',
            ]);
            
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'submitApply', $params, $e->getMessage(), $e->getTraceAsString());
            return nicsrsResponse::error($e->getMessage());
        }
    }

    // ========================================
    // Certificate Status Actions
    // ========================================

    /**
     * Refresh certificate status
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function refreshStatus(array $params)
    {
        try {
            $order = $this->checkProduct($params['serviceid']);
            
            if (empty($order->remoteid)) {
                return nicsrsResponse::error('No certificate to refresh');
            }
            
            $apiToken = $this->getApiToken($params);
            if (empty($apiToken)) {
                return nicsrsResponse::error('API token not configured');
            }
            
            $collectResult = nicsrsAPI::call('collect', [
                'api_token' => $apiToken,
                'certId' => $order->remoteid,
            ]);
            
            if (!isset($collectResult->code) || !in_array($collectResult->code, [1, 2])) {
                return nicsrsResponse::error($collectResult->msg ?? 'Failed to refresh status');
            }
            
            // Update order
            nicsrsSSLSql::UpdateFromApiResponse($order->id, $collectResult->data);
            
            // Update status
            $newStatus = nicsrsFunc::mapApiStatusToOrder($collectResult->data);
            if ($newStatus) {
                nicsrsSSLSql::UpdateOrderStatus($order->id, $newStatus);
            }
            
            return nicsrsResponse::success([
                'status' => $newStatus ?? $order->status,
                'message' => 'Status refreshed successfully',
                'data' => $collectResult->data,
            ]);
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    // ========================================
    // DCV Actions
    // ========================================

    /**
     * Batch update DCV methods
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function batchUpdateDCV(array $params)
    {
        try {
            $data = $this->checkData('data');
            $order = $this->checkProduct($params['serviceid']);
            
            if (empty($order->remoteid)) {
                return nicsrsResponse::error('Certificate not found');
            }
            
            $domainInfos = $data['domainInfo'] ?? [];
            if (empty($domainInfos)) {
                return nicsrsResponse::error('Domain info is required');
            }
            
            $apiToken = $this->getApiToken($params);
            if (empty($apiToken)) {
                return nicsrsResponse::error('API token not configured');
            }
            
            // Format domain info for API
            $formattedDomains = [];
            foreach ($domainInfos as $domain) {
                $dcvMethod = $domain['dcvMethod'];
                $one = [
                    'domainName' => $domain['domainName'],
                ];
                
                if (nicsrsFunc::checkEmail($dcvMethod)) {
                    $one['dcvMethod'] = 'EMAIL';
                    $one['dcvEmail'] = $dcvMethod;
                } else {
                    $one['dcvMethod'] = $dcvMethod;
                    $one['dcvEmail'] = '';
                }
                
                $formattedDomains[] = $one;
            }
            
            $result = nicsrsAPI::call('batchUpdateDCV', [
                'api_token' => $apiToken,
                'certId' => $order->remoteid,
                'domainInfo' => json_encode($formattedDomains),
            ]);
            
            if (!isset($result->code) || $result->code != 1) {
                return nicsrsResponse::error($result->msg ?? 'Failed to update DCV');
            }
            
            // Update configdata with new DCV info
            $configData = json_decode($order->configdata, true) ?: [];
            $configData['domainInfo'] = $domainInfos;
            $configData['lastDcvUpdate'] = date('Y-m-d H:i:s');
            
            nicsrsSSLSql::UpdateConfigData($order->id, $configData, false);
            
            return nicsrsResponse::success([
                'message' => 'DCV methods updated successfully',
            ]);
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    /**
     * Get DCV email options for domain
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function getDcvEmails(array $params)
    {
        try {
            $domain = $_POST['domain'] ?? '';
            
            if (empty($domain)) {
                return nicsrsResponse::error('Domain is required');
            }
            
            $apiToken = $this->getApiToken($params);
            if (empty($apiToken)) {
                return nicsrsResponse::error('API token not configured');
            }
            
            $result = nicsrsAPI::call('email', [
                'api_token' => $apiToken,
                'domainName' => $domain,
            ]);
            
            if (!isset($result->code) || $result->code != 1) {
                return nicsrsResponse::error($result->msg ?? 'Failed to get emails');
            }
            
            return nicsrsResponse::success([
                'emails' => $result->data->emails ?? [],
            ]);
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    // ========================================
    // Certificate Management Actions
    // ========================================

    /**
     * Download certificate
     * 
     * @param array $params Module parameters
     * @return string JSON response or file download
     */
    public function downCert(array $params)
    {
        try {
            $order = $this->checkProduct($params['serviceid']);
            
            if (empty($order->remoteid)) {
                return nicsrsResponse::error('Certificate not found');
            }
            
            $configData = json_decode($order->configdata, true) ?: [];
            
            if (empty($configData['applyReturn']['certificate'])) {
                return nicsrsResponse::error('Certificate not issued yet');
            }
            
            $domainInfo = $configData['domainInfo'] ?? [];
            $primaryDomain = $domainInfo[0]['domainName'] ?? 'certificate';
            
            // Need to collect latest cert data if missing CA
            if (empty($configData['applyReturn']['caCertificate'])) {
                $apiToken = $this->getApiToken($params);
                $collectResult = nicsrsAPI::call('collect', [
                    'api_token' => $apiToken,
                    'certId' => $order->remoteid,
                ]);
                
                if (isset($collectResult->code) && $collectResult->code == 1) {
                    nicsrsSSLSql::UpdateFromApiResponse($order->id, $collectResult->data);
                    $configData['applyReturn'] = array_merge(
                        $configData['applyReturn'],
                        (array) $collectResult->data
                    );
                }
            }
            
            // Generate ZIP file
            $zipResult = nicsrsFunc::zipCert(
                (object) ['data' => (object) $configData['applyReturn']],
                $primaryDomain
            );
            
            if ($zipResult['status'] != 1) {
                return nicsrsResponse::error($zipResult['error'] ?? 'Failed to create download');
            }
            
            $file = $zipResult['data']['file'];
            $filename = $zipResult['data']['filename'];
            
            // For AJAX request, return URL
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                
                // Store file path in session for download
                $_SESSION['nicsrs_download'] = [
                    'file' => $file,
                    'filename' => $filename,
                    'expires' => time() + 300,
                ];
                
                return nicsrsResponse::success([
                    'downloadUrl' => 'clientarea.php?action=productdetails&id=' . $params['serviceid'] . '&step=downloadFile',
                ]);
            }
            
            // Direct download
            if (file_exists($file)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                unlink($file);
                exit;
            }
            
            return nicsrsResponse::error('Download file not found');
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    /**
     * Download file from session
     * 
     * @param array $params Module parameters
     * @return void
     */
    public function downloadFile(array $params)
    {
        if (empty($_SESSION['nicsrs_download']) || 
            $_SESSION['nicsrs_download']['expires'] < time()) {
            die('Download expired or invalid');
        }
        
        $file = $_SESSION['nicsrs_download']['file'];
        $filename = $_SESSION['nicsrs_download']['filename'];
        
        unset($_SESSION['nicsrs_download']);
        
        if (file_exists($file)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            exit;
        }
        
        die('File not found');
    }

    /**
     * Reissue certificate
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function reissueCertificate(array $params)
    {
        try {
            $data = $this->checkData('data');
            $order = $this->checkProduct($params['serviceid']);
            
            if (empty($order->remoteid)) {
                return nicsrsResponse::error('Certificate not found');
            }
            
            if (!in_array($order->status, ['complete', 'issued'])) {
                return nicsrsResponse::error('Only issued certificates can be reissued');
            }
            
            if (empty($data['csr'])) {
                return nicsrsResponse::error('New CSR is required');
            }
            
            $apiToken = $this->getApiToken($params);
            if (empty($apiToken)) {
                return nicsrsResponse::error('API token not configured');
            }
            
            // Get existing domain info if not provided
            $configData = json_decode($order->configdata, true) ?: [];
            $domainInfo = $data['domainInfo'] ?? $configData['domainInfo'] ?? [];
            
            $result = nicsrsAPI::call('reissue', [
                'api_token' => $apiToken,
                'certId' => $order->remoteid,
                'csr' => $data['csr'],
                'domainInfo' => json_encode($domainInfo),
            ]);
            
            if (!isset($result->code) || $result->code != 1) {
                return nicsrsResponse::error($result->msg ?? 'Reissue failed');
            }
            
            // Update order
            $configData['reissue'] = [
                'reissued_at' => date('Y-m-d H:i:s'),
                'previous_csr' => $configData['csr'] ?? '',
                'new_csr' => $data['csr'],
            ];
            $configData['csr'] = $data['csr'];
            
            if (!empty($data['privateKey'])) {
                $configData['privateKey'] = $data['privateKey'];
            }
            
            nicsrsSSLSql::UpdateConfigData($order->id, $configData);
            nicsrsSSLSql::UpdateOrderStatus($order->id, 'pending');
            
            // Update remoteid if new one provided
            if (!empty($result->data->certId) && $result->data->certId !== $order->remoteid) {
                nicsrsSSLSql::UpdateRemoteId($order->id, $result->data->certId);
            }
            
            return nicsrsResponse::success([
                'message' => 'Certificate reissue initiated',
                'certId' => $result->data->certId ?? $order->remoteid,
            ]);
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    /**
     * Cancel certificate order
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function cancelOrder(array $params)
    {
        try {
            $order = $this->checkProduct($params['serviceid']);
            
            if (empty($order->remoteid)) {
                return nicsrsResponse::error('Certificate not found');
            }
            
            if (!in_array($order->status, ['pending', 'processing', 'awaiting'])) {
                return nicsrsResponse::error('Certificate cannot be cancelled');
            }
            
            $reason = $_POST['reason'] ?? 'Customer requested cancellation';
            
            $apiToken = $this->getApiToken($params);
            if (empty($apiToken)) {
                return nicsrsResponse::error('API token not configured');
            }
            
            $result = nicsrsAPI::call('cancel', [
                'api_token' => $apiToken,
                'certId' => $order->remoteid,
                'reason' => $reason,
            ]);
            
            if (!isset($result->code) || $result->code != 1) {
                return nicsrsResponse::error($result->msg ?? 'Cancellation failed');
            }
            
            nicsrsSSLSql::UpdateOrderStatus($order->id, 'cancelled');
            nicsrsSSLSql::UpdateConfigField($order->id, 'cancelled_at', date('Y-m-d H:i:s'));
            nicsrsSSLSql::UpdateConfigField($order->id, 'cancel_reason', $reason);
            
            return nicsrsResponse::success([
                'message' => 'Certificate order cancelled',
            ]);
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    /**
     * Revoke certificate
     * 
     * @param array $params Module parameters
     * @return string JSON response
     */
    public function revokeCertificate(array $params)
    {
        try {
            $order = $this->checkProduct($params['serviceid']);
            
            if (empty($order->remoteid)) {
                return nicsrsResponse::error('Certificate not found');
            }
            
            if ($order->status !== 'complete' && $order->status !== 'issued') {
                return nicsrsResponse::error('Only issued certificates can be revoked');
            }
            
            $reason = $_POST['reason'] ?? 'Customer requested revocation';
            
            $apiToken = $this->getApiToken($params);
            if (empty($apiToken)) {
                return nicsrsResponse::error('API token not configured');
            }
            
            $result = nicsrsAPI::call('revoke', [
                'api_token' => $apiToken,
                'certId' => $order->remoteid,
                'reason' => $reason,
            ]);
            
            if (!isset($result->code) || $result->code != 1) {
                return nicsrsResponse::error($result->msg ?? 'Revocation failed');
            }
            
            nicsrsSSLSql::UpdateOrderStatus($order->id, 'revoked');
            nicsrsSSLSql::UpdateConfigField($order->id, 'revoked_at', date('Y-m-d H:i:s'));
            nicsrsSSLSql::UpdateConfigField($order->id, 'revoke_reason', $reason);
            
            return nicsrsResponse::success([
                'message' => 'Certificate revoked',
            ]);
            
        } catch (Exception $e) {
            return nicsrsResponse::error($e->getMessage());
        }
    }

    // ========================================
    // Helper Methods
    // ========================================

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
        
        $certType = strtolower($certType);
        
        if (strpos($certType, '-ev') !== false || strpos($certType, '_ev') !== false) {
            return 'ev';
        }
        
        if (strpos($certType, '-ov') !== false || strpos($certType, '_ov') !== false) {
            return 'ov';
        }
        
        return 'dv';
    }
}