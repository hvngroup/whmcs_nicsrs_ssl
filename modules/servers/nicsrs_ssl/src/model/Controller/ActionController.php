<?php
/**
 * NicSRS SSL Module - Action Controller
 * Handles certificate actions (submit, download, reissue, etc.)
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace nicsrsSSL;

use Exception;

class ActionController
{
    /**
     * Generate CSR
     */
    public static function generateCSR(array $params): array
    {
        try {
            $csrData = [
                'cn' => $_POST['cn'] ?? $_POST['commonName'] ?? '',
                'org' => $_POST['org'] ?? $_POST['organization'] ?? '',
                'ou' => $_POST['ou'] ?? $_POST['organizationalUnit'] ?? '',
                'city' => $_POST['city'] ?? $_POST['locality'] ?? '',
                'state' => $_POST['state'] ?? '',
                'country' => $_POST['country'] ?? '',
                'email' => $_POST['email'] ?? '',
            ];

            $result = CertificateFunc::generateCSR($csrData);

            return ResponseFormatter::success('CSR generated successfully', [
                'csr' => $result['csr'],
                'privateKey' => $result['privateKey'],
            ]);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Decode CSR
     */
    public static function decodeCsr(array $params): array
    {
        try {
            $csr = $_POST['csr'] ?? '';

            if (empty($csr)) {
                return ResponseFormatter::error('CSR is required');
            }

            $result = CertificateFunc::decodeCSR($csr);

            return ResponseFormatter::success('CSR decoded successfully', $result);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Submit certificate application
     */
    public static function submitApply(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            // Check status
            if (!in_array($order->status, [SSL_STATUS_AWAITING, SSL_STATUS_DRAFT])) {
                return ResponseFormatter::error('Certificate already submitted');
            }

            // Get form data
            $formData = self::parseApplyFormData($params);

            // Validate form data
            $errors = self::validateApplyData($formData, $params);
            if (!empty($errors)) {
                return ResponseFormatter::validationError($errors);
            }

            // Build API request
            $apiRequest = self::buildApiRequest($formData, $params);

            // Call validate API first
            $validateResponse = ApiService::validate($params, [
                'productCode' => $order->certtype,
                'csr' => $formData['csr'],
                'domainInfo' => json_encode($formData['domainInfo']),
            ]);

            $validateParsed = ApiService::parseResponse($validateResponse);
            if (!$validateParsed['success']) {
                return ResponseFormatter::error($validateParsed['message']);
            }

            // Call place API
            $apiRequest['productCode'] = $order->certtype;
            $placeResponse = ApiService::place($params, $apiRequest);

            $placeParsed = ApiService::parseResponse($placeResponse);
            if (!$placeParsed['success'] && !$placeParsed['isProcessing']) {
                return ResponseFormatter::error($placeParsed['message']);
            }

            // Update order
            $configdata = [
                'csr' => $formData['csr'],
                'privateKey' => $formData['privateKey'] ?? '',
                'domainInfo' => $formData['domainInfo'],
                'server' => $formData['server'] ?? 'other',
                'originalfromOthers' => $formData['originalfromOthers'] ?? '0',
                'applyReturn' => (array) ($placeParsed['data'] ?? []),
                'applyParams' => $apiRequest,
                'lastRefresh' => date('Y-m-d H:i:s'),
            ];

            // Add contact info
            if (!empty($formData['Administrator'])) {
                $configdata['Administrator'] = $formData['Administrator'];
            }
            if (!empty($formData['tech'])) {
                $configdata['tech'] = $formData['tech'];
            }
            if (!empty($formData['organizationInfo'])) {
                $configdata['organizationInfo'] = $formData['organizationInfo'];
            }

            // Get certId from response
            $certId = '';
            if (isset($placeParsed['data'])) {
                $certId = $placeParsed['data']->certId ?? '';
            }

            OrderRepository::update($order->id, [
                'remoteid' => $certId,
                'status' => SSL_STATUS_PENDING,
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('Certificate request submitted successfully', [
                'certId' => $certId,
                'status' => 'Pending',
            ]);

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'submitApply', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Save draft
     */
    public static function saveDraft(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            // Parse form data using fixed function
            $formData = self::parseApplyFormData($params);
            
            // Get existing configdata
            $configdata = json_decode($order->configdata, true) ?: [];
            
            // Merge new form data - overwrite with new values if not empty
            if (!empty($formData['csr'])) {
                $configdata['csr'] = $formData['csr'];
            }
            if (!empty($formData['privateKey'])) {
                $configdata['privateKey'] = $formData['privateKey'];
            }
            if (!empty($formData['domainInfo'])) {
                $configdata['domainInfo'] = $formData['domainInfo'];
            }
            if (!empty($formData['Administrator'])) {
                $configdata['Administrator'] = $formData['Administrator'];
            }
            if (!empty($formData['organizationInfo'])) {
                $configdata['organizationInfo'] = $formData['organizationInfo'];
            }
            
            $configdata['renewOrNot'] = $formData['renewOrNot'] ?? 'purchase';
            $configdata['originalfromOthers'] = $formData['originalfromOthers'] ?? '0';
            $configdata['server'] = $formData['server'] ?? 'other';
            $configdata['draftSavedAt'] = date('Y-m-d H:i:s');
            
            // Update order
            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_DRAFT,
                'configdata' => json_encode($configdata),
            ]);
            
            logModuleCall('nicsrs_ssl', 'saveDraft_success', [], [
                'domainCount' => count($configdata['domainInfo'] ?? []),
                'hasCsr' => !empty($configdata['csr']),
            ]);

            return ResponseFormatter::success('Draft saved successfully', [
                'savedAt' => $configdata['draftSavedAt'],
                'hasCsr' => !empty($configdata['csr']),
                'domainCount' => count($configdata['domainInfo'] ?? []),
            ]);

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'saveDraft_error', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Refresh certificate status
     */
    public static function refreshStatus(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            if (empty($order->remoteid)) {
                return ResponseFormatter::error('No certificate ID found');
            }

            $apiResponse = ApiService::collect($params, $order->remoteid);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success'] && !$parsed['isProcessing']) {
                return ResponseFormatter::error($parsed['message']);
            }

            // Update configdata
            $configdata = json_decode($order->configdata, true) ?: [];
            
            if ($parsed['data']) {
                $configdata['applyReturn'] = array_merge(
                    $configdata['applyReturn'] ?? [],
                    (array) $parsed['data']
                );
            }
            $configdata['lastRefresh'] = date('Y-m-d H:i:s');

            // Check for status change
            $apiStatus = strtoupper($parsed['status'] ?? '');
            $newStatus = $order->status;

            $statusMap = [
                'COMPLETE' => SSL_STATUS_COMPLETE,
                'ISSUED' => SSL_STATUS_COMPLETE,
                'PENDING' => SSL_STATUS_PENDING,
                'PROCESSING' => SSL_STATUS_PENDING,
                'CANCELLED' => SSL_STATUS_CANCELLED,
                'REVOKED' => SSL_STATUS_REVOKED,
                'EXPIRED' => SSL_STATUS_EXPIRED,
            ];

            if (isset($statusMap[$apiStatus])) {
                $newStatus = $statusMap[$apiStatus];
            }

            $updateData = [
                'configdata' => json_encode($configdata),
            ];

            if ($newStatus !== $order->status) {
                $updateData['status'] = $newStatus;
                
                if ($newStatus === SSL_STATUS_COMPLETE) {
                    $updateData['completiondate'] = date('Y-m-d H:i:s');
                }
            }

            OrderRepository::update($order->id, $updateData);

            return ResponseFormatter::success('Status refreshed', [
                'status' => $newStatus,
                'apiStatus' => $apiStatus,
                'data' => $parsed['data'],
            ]);

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'refreshStatus', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Download certificate
     */
    public static function downCert(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            $configdata = json_decode($order->configdata, true) ?: [];
            $applyReturn = $configdata['applyReturn'] ?? [];

            if (empty($applyReturn['certificate'])) {
                return ResponseFormatter::error('Certificate not yet issued');
            }

            // Get download format
            $format = $_POST['format'] ?? $_GET['format'] ?? 'all';

            // Get domain for filename
            $domainInfo = $configdata['domainInfo'] ?? [];
            $domain = !empty($domainInfo) ? $domainInfo[0]['domainName'] : 'certificate';

            // Build certificate data
            $certData = [
                'domain' => $domain,
                'certificate' => $applyReturn['certificate'] ?? '',
                'caCertificate' => $applyReturn['caCertificate'] ?? '',
                'privateKey' => $configdata['privateKey'] ?? $applyReturn['privateKey'] ?? '',
                'pkcs12' => $applyReturn['pkcs12'] ?? '',
                'jks' => $applyReturn['jks'] ?? '',
                'pkcsPass' => $applyReturn['pkcsPass'] ?? '',
                'jksPass' => $applyReturn['jksPass'] ?? '',
            ];

            // Create ZIP
            $zipContent = CertificateFunc::createCertificateZip($certData, $format);
            $safeDomain = preg_replace('/[^a-zA-Z0-9.-]/', '_', $domain);
            $filename = "{$safeDomain}_ssl_certificate.zip";

            return ResponseFormatter::download($filename, $zipContent, 'application/zip');

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'downCert', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Batch update DCV methods
     */
    public static function batchUpdateDCV(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            if (empty($order->remoteid)) {
                return ResponseFormatter::error('No certificate ID found');
            }

            $domainInfo = $_POST['domainInfo'] ?? [];
            if (empty($domainInfo)) {
                return ResponseFormatter::error('No domain info provided');
            }

            if (is_string($domainInfo)) {
                $domainInfo = json_decode($domainInfo, true);
            }

            $apiResponse = ApiService::batchUpdateDCV($params, $order->remoteid, $domainInfo);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            // Update local domainInfo
            $configdata = json_decode($order->configdata, true) ?: [];
            $configdata['domainInfo'] = $domainInfo;
            OrderRepository::updateConfigData($order->id, $configdata);

            return ResponseFormatter::success('DCV methods updated', $parsed['data']);

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'batchUpdateDCV', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Resend DCV email
     */
    public static function resendDCVEmail(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            $domain = $_POST['domain'] ?? '';
            $email = $_POST['email'] ?? '';

            if (empty($domain) || empty($email)) {
                return ResponseFormatter::error('Domain and email are required');
            }

            $apiResponse = ApiService::resendDCVEmail($params, $order->remoteid, $domain, $email);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            return ResponseFormatter::success('DCV email sent');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Cancel order
     */
    public static function cancelOrder(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            if (in_array($order->status, [SSL_STATUS_CANCELLED, SSL_STATUS_REVOKED])) {
                return ResponseFormatter::error('Order already cancelled');
            }

            $reason = $_POST['reason'] ?? 'Customer requested cancellation';

            if (!empty($order->remoteid)) {
                $apiResponse = ApiService::cancel($params, $order->remoteid, $reason);
                $parsed = ApiService::parseResponse($apiResponse);

                if (!$parsed['success']) {
                    return ResponseFormatter::error($parsed['message']);
                }
            }

            OrderRepository::update($order->id, ['status' => SSL_STATUS_CANCELLED]);

            return ResponseFormatter::success('Order cancelled successfully');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Revoke certificate
     */
    public static function revoke(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            if (!in_array($order->status, [SSL_STATUS_COMPLETE, SSL_STATUS_ISSUED])) {
                return ResponseFormatter::error('Certificate must be issued to revoke');
            }

            $reason = $_POST['reason'] ?? 'Customer requested revocation';

            $apiResponse = ApiService::revoke($params, $order->remoteid, $reason);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            OrderRepository::update($order->id, ['status' => SSL_STATUS_REVOKED]);

            return ResponseFormatter::success('Certificate revoked successfully');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Submit reissue request
     */
    public static function submitReissue(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            if (!in_array($order->status, [SSL_STATUS_COMPLETE, SSL_STATUS_ISSUED])) {
                return ResponseFormatter::error('Certificate must be issued to reissue');
            }

            $formData = self::parseApplyFormData($params);

            // Build reissue request
            $reissueRequest = [];
            
            if (!empty($formData['csr'])) {
                $reissueRequest['csr'] = $formData['csr'];
            }
            
            if (!empty($formData['domainInfo'])) {
                $reissueRequest['domainInfo'] = $formData['domainInfo'];
            }

            $apiResponse = ApiService::reissue($params, $order->remoteid, $reissueRequest);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success'] && !$parsed['isProcessing']) {
                return ResponseFormatter::error($parsed['message']);
            }

            // Update configdata
            $configdata = json_decode($order->configdata, true) ?: [];
            
            if (!empty($formData['csr'])) {
                $configdata['csr'] = $formData['csr'];
            }
            if (!empty($formData['privateKey'])) {
                $configdata['privateKey'] = $formData['privateKey'];
            }
            if (!empty($formData['domainInfo'])) {
                $configdata['domainInfo'] = $formData['domainInfo'];
            }
            
            $configdata['reissueReturn'] = (array) ($parsed['data'] ?? []);
            $configdata['lastRefresh'] = date('Y-m-d H:i:s');

            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_REISSUE,
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('Reissue request submitted', $parsed['data']);

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'submitReissue', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Renew certificate
     */
    public static function renew(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            $apiResponse = ApiService::renew($params, $order->remoteid);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            return ResponseFormatter::success('Renewal initiated', $parsed['data']);

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    /**
     * Parse form data from POST - Updated for new JS format
     */
    private static function parseApplyFormData(array $params = []): array
    {
        $data = [];
        
        // ==========================================
        // CRITICAL FIX: Extract data from JSON wrapper
        // JavaScript sends: xhr.send('data=' + encodeURIComponent(JSON.stringify(data)))
        // So all form data is inside $_POST['data'] as JSON string
        // ==========================================
        $postData = $_POST;
        
        if (isset($_POST['data'])) {
            $jsonData = $_POST['data'];
            
            if (is_string($jsonData)) {
                $decoded = json_decode($jsonData, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Use decoded JSON as primary data source
                    $postData = array_merge($postData, $decoded);
                    
                    logModuleCall('nicsrs_ssl', 'parseApplyFormData_decoded', 
                        ['raw_json' => substr($jsonData, 0, 500)], 
                        ['decoded_keys' => array_keys($decoded)]
                    );
                } else {
                    logModuleCall('nicsrs_ssl', 'parseApplyFormData_json_error', 
                        ['raw' => substr($jsonData, 0, 500)], 
                        ['error' => json_last_error_msg()]
                    );
                }
            }
        }
        
        // ==========================================
        // Parse Domain Info (from decoded JSON)
        // ==========================================
        $data['domainInfo'] = [];
        $primaryDomain = '';
        
        // domainInfo is array of {domainName, dcvMethod}
        if (isset($postData['domainInfo']) && is_array($postData['domainInfo'])) {
            foreach ($postData['domainInfo'] as $domain) {
                if (!empty($domain['domainName'])) {
                    $data['domainInfo'][] = [
                        'domainName' => trim($domain['domainName']),
                        'dcvMethod' => $domain['dcvMethod'] ?? 'CNAME_CSR_HASH',
                        'dcvEmail' => $domain['dcvEmail'] ?? '',
                    ];
                    if (empty($primaryDomain)) {
                        $primaryDomain = trim($domain['domainName']);
                    }
                }
            }
        }
        
        // Fallback: Parse from domains[] array (HTML form format)
        if (empty($data['domainInfo']) && isset($postData['domains']) && is_array($postData['domains'])) {
            foreach ($postData['domains'] as $d) {
                if (!empty($d['name'])) {
                    $data['domainInfo'][] = [
                        'domainName' => trim($d['name']),
                        'dcvMethod' => $d['dcvMethod'] ?? 'CNAME_CSR_HASH',
                        'dcvEmail' => $d['dcvEmail'] ?? '',
                    ];
                    if (empty($primaryDomain)) {
                        $primaryDomain = trim($d['name']);
                    }
                }
            }
        }
        
        // ==========================================
        // Parse Administrator/Contact Info
        // ==========================================
        $data['Administrator'] = [];
        
        // Administrator is object from JS
        if (isset($postData['Administrator']) && is_array($postData['Administrator'])) {
            $data['Administrator'] = $postData['Administrator'];
        }
        
        // Fallback: Parse from individual form fields
        if (empty($data['Administrator']['firstName']) && !empty($postData['adminFirstName'])) {
            $data['Administrator'] = [
                'firstName' => $postData['adminFirstName'] ?? '',
                'lastName' => $postData['adminLastName'] ?? '',
                'email' => $postData['adminEmail'] ?? '',
                'mobile' => $postData['adminPhone'] ?? '',
                'job' => $postData['adminTitle'] ?? 'IT Manager',
                'organation' => $postData['adminOrganizationName'] ?? '',
                'country' => $postData['adminCountry'] ?? '',
                'address' => $postData['adminAddress'] ?? '',
                'city' => $postData['adminCity'] ?? '',
                'state' => $postData['adminProvince'] ?? '',
                'postCode' => $postData['adminPostcode'] ?? '',
            ];
        }
        
        // Copy admin to tech and finance
        $data['tech'] = $data['Administrator'];
        $data['finance'] = $data['Administrator'];
        
        // ==========================================
        // Parse Organization Info (for OV/EV certs)
        // ==========================================
        if (isset($postData['organizationInfo']) && is_array($postData['organizationInfo'])) {
            $data['organizationInfo'] = $postData['organizationInfo'];
        } elseif (!empty($postData['organizationName'])) {
            $data['organizationInfo'] = [
                'organizationName' => $postData['organizationName'] ?? '',
                'division' => $postData['division'] ?? 'IT Department',
                'organizationCity' => $postData['organizationCity'] ?? '',
                'organizationCountry' => $postData['organizationCountry'] ?? '',
                'organizationAddress' => $postData['organizationAddress'] ?? '',
                'organizationPostalCode' => $postData['organizationPostalCode'] ?? '',
            ];
        }
        
        // ==========================================
        // Handle CSR
        // ==========================================
        $isManualCsr = false;
        
        // Check originalfromOthers from JS (indicates manual CSR mode)
        if (isset($postData['originalfromOthers'])) {
            $isManualCsr = $postData['originalfromOthers'] === '1' || $postData['originalfromOthers'] === 1;
        }
        
        $data['originalfromOthers'] = $isManualCsr ? '1' : '0';
        
        if ($isManualCsr && !empty($postData['csr'])) {
            // Manual CSR provided
            $data['csr'] = trim($postData['csr']);
            $data['privateKey'] = '';
        } else {
            // Auto-generate CSR from contact info + domain
            $admin = $data['Administrator'];
            
            $csrParams = [
                'cn' => $primaryDomain,
                'org' => $admin['organation'] ?? '',
                'ou' => 'IT Department',
                'city' => $admin['city'] ?? '',
                'state' => $admin['state'] ?? '',
                'country' => $admin['country'] ?? '',
                'email' => $admin['email'] ?? '',
            ];
            
            logModuleCall('nicsrs_ssl', 'auto_csr_params', $csrParams, '');
            
            if (!empty($primaryDomain)) {
                try {
                    $generated = CertificateFunc::generateCSR($csrParams);
                    $data['csr'] = $generated['csr'];
                    $data['privateKey'] = $generated['privateKey'];
                } catch (Exception $e) {
                    logModuleCall('nicsrs_ssl', 'generateCSR_error', $csrParams, $e->getMessage());
                    $data['csr'] = '';
                    $data['privateKey'] = '';
                }
            } else {
                $data['csr'] = '';
                $data['privateKey'] = '';
            }
        }
        
        // Other fields
        $data['server'] = $postData['server'] ?? 'other';
        $data['renewOrNot'] = $postData['renewOrNot'] ?? 'purchase';
        
        // Log final parsed data
        logModuleCall('nicsrs_ssl', 'parseApplyFormData_result', [], [
            'domainCount' => count($data['domainInfo']),
            'hasAdmin' => !empty($data['Administrator']['firstName']),
            'hasCsr' => !empty($data['csr']),
            'primaryDomain' => $primaryDomain,
        ]);
        
        return $data;
    }

    /**
     * Validate apply form data
     */
    private static function validateApplyData(array $data, array $params): array
    {
        $errors = [];

        // CSR required
        if (empty($data['csr'])) {
            $errors['csr'] = 'CSR is required. Please check domain and contact information.';
        }

        // At least one domain required
        if (empty($data['domainInfo'])) {
            $errors['domain'] = 'At least one domain is required';
        } else {
            foreach ($data['domainInfo'] as $i => $domain) {
                if (empty($domain['domainName'])) {
                    $errors["domain_{$i}"] = 'Domain name is required';
                } elseif (!CertificateFunc::validateDomain($domain['domainName'])) {
                    $errors["domain_{$i}"] = "Invalid domain format: {$domain['domainName']}";
                }
                
                if (empty($domain['dcvMethod'])) {
                    $errors["dcv_{$i}"] = 'DCV method is required';
                }
            }
        }

        // Validate admin contact
        if (empty($data['Administrator']['firstName'])) {
            $errors['adminFirstName'] = 'First name is required';
        }
        if (empty($data['Administrator']['lastName'])) {
            $errors['adminLastName'] = 'Last name is required';
        }
        if (empty($data['Administrator']['email'])) {
            $errors['adminEmail'] = 'Email is required';
        } elseif (!filter_var($data['Administrator']['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['adminEmail'] = 'Invalid email format';
        }

        return $errors;
    }

    /**
     * Build API request data
     */
    private static function buildApiRequest(array $formData, array $params): array
    {
        $request = [
            'csr' => $formData['csr'],
            'domainInfo' => $formData['domainInfo'],
            'server' => $formData['server'] ?? 'other',
        ];

        if (!empty($formData['Administrator'])) {
            $request['Administrator'] = $formData['Administrator'];
            $request['tech'] = $formData['tech'] ?? $formData['Administrator'];
            $request['finance'] = $formData['finance'] ?? $formData['Administrator'];
        }

        if (!empty($formData['organizationInfo'])) {
            $request['organizationInfo'] = $formData['organizationInfo'];
        }

        if ($formData['renewOrNot'] === 'renew') {
            $request['isRenew'] = true;
        }

        return $request;
    }
}