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
            // DEBUG: Log raw POST data
            logModuleCall('nicsrs_ssl', 'saveDraft_RAW_POST', [
                'POST' => $_POST,
                'params' => $params,
            ], 'Checking raw input');
            
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            // Parse form data
            $formData = self::parseApplyFormData($params);
            
            // DEBUG: Log parsed form data
            logModuleCall('nicsrs_ssl', 'saveDraft_PARSED', [
                'formData' => $formData,
                'domainCount' => count($formData['domainInfo'] ?? []),
                'hasAdmin' => !empty($formData['Administrator']),
            ], 'Parsed form data');
            
            // Get existing configdata
            $existingConfig = [];
            if (!empty($order->configdata)) {
                $existingConfig = json_decode($order->configdata, true) ?: [];
            }
            
            // FIXED: Merge properly - ensure all fields are saved
            $configdata = [
                // Preserve existing important fields
                'applyReturn' => $existingConfig['applyReturn'] ?? [],
                'privateKey' => $formData['privateKey'] ?: ($existingConfig['privateKey'] ?? ''),
                
                // Save new form data
                'csr' => $formData['csr'] ?? '',
                'domainInfo' => $formData['domainInfo'] ?? [],
                'Administrator' => $formData['Administrator'] ?? [],
                'tech' => $formData['tech'] ?? $formData['Administrator'] ?? [],
                'finance' => $formData['finance'] ?? $formData['Administrator'] ?? [],
                'organizationInfo' => $formData['organizationInfo'] ?? [],
                'server' => $formData['server'] ?? 'other',
                'originalfromOthers' => $formData['originalfromOthers'] ?? '0',
                
                // Metadata
                'lastSaved' => date('Y-m-d H:i:s'),
                'isDraft' => true,
            ];
            
            // DEBUG: Log what will be saved
            logModuleCall('nicsrs_ssl', 'saveDraft_TO_SAVE', [
                'configdata' => $configdata,
                'json' => json_encode($configdata),
            ], 'Data to save');
            
            // Update database
            $result = OrderRepository::update($order->id, [
                'status' => SSL_STATUS_DRAFT,
                'configdata' => json_encode($configdata),
            ]);

            // DEBUG: Verify save
            $verifyOrder = OrderRepository::getByServiceId($params['serviceid']);
            logModuleCall('nicsrs_ssl', 'saveDraft_VERIFY', [
                'saved' => $result,
                'newConfigdata' => $verifyOrder->configdata ?? 'NULL',
            ], 'Verify save result');

            if ($result) {
                return ResponseFormatter::success('Draft saved successfully', [
                    'savedAt' => $configdata['lastSaved'],
                    'hasCsr' => !empty($configdata['csr']),
                    'domainCount' => count($configdata['domainInfo']),
                ]);
            }
            
            return ResponseFormatter::error('Failed to save draft');

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'saveDraft_ERROR', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Validate form data before submission
     * @param array $data Parsed form data
     * @param bool $isDraft If true, only validate essential fields
     */
    private static function validateFormData(array $data, bool $isDraft = false): array
    {
        $errors = [];

        // For draft, we allow partial data
        if ($isDraft) {
            return $errors; // No validation for draft
        }

        // CSR is required for submission
        if (empty($data['csr'])) {
            $errors['csr'] = 'CSR is required. Please check domain and contact information.';
        } elseif (strpos($data['csr'], '-----BEGIN CERTIFICATE REQUEST-----') === false) {
            $errors['csr'] = 'Invalid CSR format. CSR must begin with -----BEGIN CERTIFICATE REQUEST-----';
        }

        // At least one domain required
        if (empty($data['domainInfo'])) {
            $errors['domain'] = 'At least one domain is required';
        } else {
            foreach ($data['domainInfo'] as $i => $domain) {
                if (empty($domain['domainName'])) {
                    $errors["domain_{$i}"] = 'Domain name is required';
                }
                if (empty($domain['dcvMethod'])) {
                    $errors["dcv_{$i}"] = 'DCV method is required';
                }
                // If EMAIL method, dcvEmail is required
                if ($domain['dcvMethod'] === 'EMAIL' && empty($domain['dcvEmail'])) {
                    $errors["dcvEmail_{$i}"] = 'Email address is required for email validation';
                }
            }
        }

        // Validate admin contact
        $admin = $data['Administrator'] ?? [];
        if (empty($admin['firstName'])) {
            $errors['adminFirstName'] = 'First name is required';
        }
        if (empty($admin['lastName'])) {
            $errors['adminLastName'] = 'Last name is required';
        }
        if (empty($admin['email'])) {
            $errors['adminEmail'] = 'Email is required';
        } elseif (!filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['adminEmail'] = 'Invalid email format';
        }

        return $errors;
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
    private static function parseApplyFormData(array $params): array
    {
        $data = [
            'csr' => '',
            'privateKey' => '',
            'server' => 'other',
            'domainInfo' => [],
            'Administrator' => [],
            'tech' => [],
            'finance' => [],
            'organizationInfo' => [],
            'originalfromOthers' => '0',
            'renewOrNot' => 'new',
        ];
        
        // Start with POST data
        $postData = $_POST;
        
        // DEBUG: Log initial POST
        logModuleCall('nicsrs_ssl', 'parseApplyFormData_START', [
            'POST_keys' => array_keys($_POST),
            'has_data_key' => isset($_POST['data']),
        ], 'Starting parse');
        
        // Check for JSON-encoded 'data' parameter (from JavaScript)
        if (!empty($_POST['data'])) {
            $jsonData = $_POST['data'];
            
            // Handle URL-encoded JSON
            if (strpos($jsonData, '%7B') !== false || strpos($jsonData, '%22') !== false) {
                $jsonData = urldecode($jsonData);
            }
            
            // DEBUG: Log JSON before parse
            logModuleCall('nicsrs_ssl', 'parseApplyFormData_JSON', [
                'raw_length' => strlen($jsonData),
                'raw_preview' => substr($jsonData, 0, 500),
            ], 'JSON data received');
            
            $decoded = json_decode($jsonData, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // IMPORTANT: Merge decoded data into postData
                $postData = array_merge($postData, $decoded);
                
                logModuleCall('nicsrs_ssl', 'parseApplyFormData_DECODED', [
                    'decoded_keys' => array_keys($decoded),
                    'domainInfo_count' => count($decoded['domainInfo'] ?? []),
                    'has_Administrator' => isset($decoded['Administrator']),
                ], 'Decoded JSON');
            } else {
                logModuleCall('nicsrs_ssl', 'parseApplyFormData_JSON_ERROR', [
                    'error' => json_last_error_msg(),
                    'raw' => substr($jsonData, 0, 200),
                ], 'JSON decode failed');
            }
        }
        
        // ==========================================
        // Parse Domain Info
        // ==========================================
        if (!empty($postData['domainInfo']) && is_array($postData['domainInfo'])) {
            foreach ($postData['domainInfo'] as $domain) {
                if (!empty($domain['domainName'])) {
                    $dcvMethod = $domain['dcvMethod'] ?? 'CNAME_CSR_HASH';
                    $dcvEmail = $domain['dcvEmail'] ?? '';
                    
                    // Handle email-based DCV
                    if (filter_var($dcvMethod, FILTER_VALIDATE_EMAIL)) {
                        $dcvEmail = $dcvMethod;
                        $dcvMethod = 'EMAIL';
                    }
                    
                    $data['domainInfo'][] = [
                        'domainName' => trim($domain['domainName']),
                        'dcvMethod' => $dcvMethod,
                        'dcvEmail' => $dcvEmail,
                    ];
                }
            }
        }
        
        // ==========================================
        // Parse Administrator/Contact Info
        // ==========================================
        if (!empty($postData['Administrator']) && is_array($postData['Administrator'])) {
            $admin = $postData['Administrator'];
            $data['Administrator'] = [
                'firstName' => trim($admin['firstName'] ?? ''),
                'lastName' => trim($admin['lastName'] ?? ''),
                'email' => trim($admin['email'] ?? ''),
                'mobile' => trim($admin['mobile'] ?? $admin['phone'] ?? ''),
                'job' => trim($admin['job'] ?? 'IT Manager'),
                'organation' => trim($admin['organation'] ?? $admin['organization'] ?? ''),
                'country' => trim($admin['country'] ?? ''),
                'address' => trim($admin['address'] ?? ''),
                'city' => trim($admin['city'] ?? ''),
                'state' => trim($admin['state'] ?? $admin['province'] ?? ''),
                'postCode' => trim($admin['postCode'] ?? $admin['postalCode'] ?? ''),
            ];
            
            // Copy to tech and finance
            $data['tech'] = $data['Administrator'];
            $data['finance'] = $data['Administrator'];
        }
        
        // ==========================================
        // Parse Organization Info
        // ==========================================
        if (!empty($postData['organizationInfo']) && is_array($postData['organizationInfo'])) {
            $org = $postData['organizationInfo'];
            $data['organizationInfo'] = [
                'organizationName' => trim($org['organizationName'] ?? ''),
                'organizationAddress' => trim($org['organizationAddress'] ?? ''),
                'organizationCity' => trim($org['organizationCity'] ?? ''),
                'organizationCountry' => trim($org['organizationCountry'] ?? ''),
                'organizationState' => trim($org['organizationState'] ?? ''),
                'organizationPostCode' => trim($org['organizationPostCode'] ?? $org['organizationPostalCode'] ?? ''),
                'organizationMobile' => trim($org['organizationMobile'] ?? ''),
            ];
        }
        
        // ==========================================
        // Parse CSR and other fields
        // ==========================================
        $data['csr'] = trim($postData['csr'] ?? '');
        $data['privateKey'] = trim($postData['privateKey'] ?? '');
        $data['server'] = $postData['server'] ?? 'other';
        $data['originalfromOthers'] = $postData['originalfromOthers'] ?? '0';
        $data['renewOrNot'] = $postData['renewOrNot'] ?? 'purchase';
        
        // DEBUG: Log final result
        logModuleCall('nicsrs_ssl', 'parseApplyFormData_RESULT', [
            'domainCount' => count($data['domainInfo']),
            'hasAdmin' => !empty($data['Administrator']['firstName']),
            'hasCsr' => !empty($data['csr']),
        ], 'Final parsed data');
        
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