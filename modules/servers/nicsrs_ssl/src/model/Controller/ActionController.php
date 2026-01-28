<?php
/**
 * NicSRS SSL Module - Action Controller
 * Handles AJAX actions for SSL certificate management
 * 
 * FIXED: Uses OLD MODULE pattern for POST data handling
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace nicsrsSSL;

use Exception;
use WHMCS\Database\Capsule;

class ActionController
{
    // ==========================================
    // Certificate Application Actions
    // ==========================================

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

            // Check order status
            if (!in_array($order->status, [SSL_STATUS_AWAITING, SSL_STATUS_DRAFT])) {
                return ResponseFormatter::error('Invalid order status for application');
            }

            // Get form data using OLD MODULE pattern
            $formData = self::getPostData('data');
            
            if (empty($formData)) {
                return ResponseFormatter::error('Missing form data');
            }

            // Validate form data
            $errors = self::validateFormData($formData, false);
            if (!empty($errors)) {
                return ResponseFormatter::error('Validation failed: ' . implode(', ', $errors));
            }

            // Get certificate info
            $cert = CertificateFunc::getCertificateByCode($order->certtype ?? '');
            
            // Build API request
            $apiRequest = self::buildApiRequest($formData, $cert, $params);

            // Call API
            $apiResponse = ApiService::placeOrder($params, $apiRequest);
            $placeParsed = ApiService::parseResponse($apiResponse);

            if (!$placeParsed['success']) {
                return ResponseFormatter::error($placeParsed['message']);
            }

            // Build configdata
            $configdata = [
                'csr' => $formData['csr'] ?? '',
                'privateKey' => $formData['privateKey'] ?? '',
                'domainInfo' => $formData['domainInfo'] ?? [],
                'server' => $formData['server'] ?? 'other',
                'originalfromOthers' => $formData['originalfromOthers'] ?? '0',
                'Administrator' => $formData['Administrator'] ?? [],
                'tech' => $formData['tech'] ?? $formData['Administrator'] ?? [],
                'finance' => $formData['finance'] ?? $formData['Administrator'] ?? [],
                'organizationInfo' => $formData['organizationInfo'] ?? [],
                'applyReturn' => (array) ($placeParsed['data'] ?? []),
                'applyParams' => $apiRequest,
                'lastRefresh' => date('Y-m-d H:i:s'),
            ];

            // Get certId from response
            $certId = '';
            if (isset($placeParsed['data'])) {
                $certId = $placeParsed['data']->certId ?? '';
            }

            // Update main domain
            $mainDomain = $formData['domainInfo'][0]['domainName'] ?? '';
            if (!empty($mainDomain)) {
                Capsule::table('tblhosting')
                    ->where('id', $params['serviceid'])
                    ->update(['domain' => $mainDomain]);
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
     * Save draft - FIXED: Uses OLD MODULE pattern
     */
    public static function saveDraft(array $params): array
    {
        try {
            logModuleCall('nicsrs_ssl', 'saveDraft_RAW_POST', [
                'POST_keys' => array_keys($_POST),
                'has_data' => isset($_POST['data']),
                'data_type' => isset($_POST['data']) ? gettype($_POST['data']) : 'not set',
            ], 'Raw input check');
            
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return ResponseFormatter::error('Order not found');
            }

            // Get form data using OLD MODULE pattern
            $formData = self::getPostData('data');
            
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
            
            // Build configdata - merge existing with new
            $configdata = array_merge($existingConfig, [
                'csr' => $formData['csr'] ?? ($existingConfig['csr'] ?? ''),
                'privateKey' => $formData['privateKey'] ?? ($existingConfig['privateKey'] ?? ''),
                'domainInfo' => $formData['domainInfo'] ?? ($existingConfig['domainInfo'] ?? []),
                'server' => $formData['server'] ?? 'other',
                'originalfromOthers' => $formData['originalfromOthers'] ?? '0',
                'Administrator' => $formData['Administrator'] ?? ($existingConfig['Administrator'] ?? []),
                'tech' => $formData['tech'] ?? $formData['Administrator'] ?? ($existingConfig['tech'] ?? []),
                'finance' => $formData['finance'] ?? $formData['Administrator'] ?? ($existingConfig['finance'] ?? []),
                'organizationInfo' => $formData['organizationInfo'] ?? ($existingConfig['organizationInfo'] ?? []),
                'applyReturn' => $existingConfig['applyReturn'] ?? [],
                'lastSaved' => date('Y-m-d H:i:s'),
                'isDraft' => true,
            ]);
            
            logModuleCall('nicsrs_ssl', 'saveDraft_TO_SAVE', [
                'configdata_keys' => array_keys($configdata),
                'domainCount' => count($configdata['domainInfo']),
                'json_length' => strlen(json_encode($configdata)),
            ], 'Data to save');
            
            // Update database
            $result = OrderRepository::update($order->id, [
                'status' => SSL_STATUS_DRAFT,
                'configdata' => json_encode($configdata),
            ]);

            if ($result) {
                return ResponseFormatter::success('Draft saved successfully', [
                    'savedAt' => $configdata['lastSaved'],
                    'hasCsr' => !empty($configdata['csr']),
                    'domainCount' => count($configdata['domainInfo']),
                    'hasAdmin' => !empty($configdata['Administrator']),
                ]);
            }
            
            return ResponseFormatter::error('Failed to save draft');

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'saveDraft_ERROR', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Status & Refresh Actions
    // ==========================================

    /**
     * Refresh certificate status from API
     */
    public static function refreshStatus(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('No certificate to refresh');
            }

            $apiResponse = ApiService::getCertificate($params, $order->remoteid);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error('Failed to fetch certificate status');
            }

            $certData = $parsed['data'];
            $newStatus = $certData->status ?? '';
            
            $statusMap = [
                'pending' => SSL_STATUS_PENDING,
                'processing' => SSL_STATUS_PENDING,
                'issued' => SSL_STATUS_COMPLETE,
                'complete' => SSL_STATUS_COMPLETE,
                'active' => SSL_STATUS_COMPLETE,
                'cancelled' => SSL_STATUS_CANCELLED,
                'canceled' => SSL_STATUS_CANCELLED,
                'revoked' => SSL_STATUS_REVOKED,
                'expired' => SSL_STATUS_EXPIRED,
            ];
            
            $internalStatus = $statusMap[strtolower($newStatus)] ?? $order->status;
            
            $configdata = json_decode($order->configdata, true) ?: [];
            $configdata['lastRefresh'] = date('Y-m-d H:i:s');
            $configdata['apiStatus'] = $newStatus;
            
            if (in_array($internalStatus, [SSL_STATUS_COMPLETE])) {
                $configdata['applyReturn']['certificate'] = $certData->certificate ?? '';
                $configdata['applyReturn']['caCertificate'] = $certData->caCertificate ?? '';
                $configdata['applyReturn']['beginDate'] = $certData->beginDate ?? '';
                $configdata['applyReturn']['endDate'] = $certData->endDate ?? '';
            }
            
            if (!empty($certData->DCVInfo)) {
                $configdata['dcvInfo'] = (array) $certData->DCVInfo;
            }

            OrderRepository::update($order->id, [
                'status' => $internalStatus,
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('Status refreshed', [
                'status' => $internalStatus,
                'apiStatus' => $newStatus,
                'lastRefresh' => $configdata['lastRefresh'],
            ]);

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'refreshStatus', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Download Actions
    // ==========================================

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
            
            if (empty($configdata['applyReturn']['certificate'])) {
                return ResponseFormatter::error('Certificate not yet issued');
            }

            $format = $_POST['format'] ?? $_GET['format'] ?? 'pem';
            $domain = $configdata['domainInfo'][0]['domainName'] ?? 'certificate';
            $domain = str_replace(['*', '.'], ['wildcard', '_'], $domain);
            
            $certificate = $configdata['applyReturn']['certificate'];
            $caCertificate = $configdata['applyReturn']['caCertificate'] ?? '';
            $privateKey = $configdata['privateKey'] ?? '';

            switch (strtolower($format)) {
                case 'pem':
                case 'nginx':
                    $content = $certificate;
                    if (!empty($caCertificate)) {
                        $content .= "\n" . $caCertificate;
                    }
                    return ResponseFormatter::success('Download ready', [
                        'filename' => $domain . '.pem',
                        'content' => base64_encode($content),
                        'mime' => 'application/x-pem-file',
                    ]);
                    
                case 'crt':
                case 'apache':
                    return ResponseFormatter::success('Download ready', [
                        'filename' => $domain . '.crt',
                        'content' => base64_encode($certificate),
                        'mime' => 'application/x-x509-ca-cert',
                        'caBundle' => !empty($caCertificate) ? base64_encode($caCertificate) : null,
                        'caBundleFilename' => $domain . '.ca-bundle',
                    ]);
                    
                case 'key':
                    if (empty($privateKey)) {
                        return ResponseFormatter::error('Private key not available');
                    }
                    return ResponseFormatter::success('Download ready', [
                        'filename' => $domain . '.key',
                        'content' => base64_encode($privateKey),
                        'mime' => 'application/x-pem-file',
                    ]);
                    
                default:
                    return ResponseFormatter::error('Unknown format: ' . $format);
            }

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // DCV Actions
    // ==========================================

    public static function batchUpdateDCV(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found');
            }

            $dcvData = self::getPostData('data');
            
            if (empty($dcvData) || !is_array($dcvData)) {
                return ResponseFormatter::error('Invalid DCV data');
            }

            $apiResponse = ApiService::batchUpdateDCV($params, $order->remoteid, $dcvData);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            $configdata = json_decode($order->configdata, true) ?: [];
            $domainInfos = $configdata['domainInfo'] ?? [];
            
            foreach ($dcvData as $item) {
                $domainName = $item['domainName'] ?? '';
                $dcvMethod = $item['dcvMethod'] ?? '';
                
                foreach ($domainInfos as &$domain) {
                    if ($domain['domainName'] === $domainName) {
                        $domain['dcvMethod'] = $dcvMethod;
                        break;
                    }
                }
            }
            
            $configdata['domainInfo'] = $domainInfos;
            OrderRepository::update($order->id, [
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('DCV methods updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public static function resendDCVEmail(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found');
            }

            $domain = $_POST['domain'] ?? '';
            
            if (empty($domain)) {
                return ResponseFormatter::error('Domain is required');
            }

            $apiResponse = ApiService::resendDCVEmail($params, $order->remoteid, $domain);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            return ResponseFormatter::success('DCV email resent');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Order Management Actions
    // ==========================================

    public static function cancelOrder(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found');
            }

            if (!in_array($order->status, [SSL_STATUS_PENDING, SSL_STATUS_DRAFT])) {
                return ResponseFormatter::error('Only pending orders can be cancelled');
            }

            $reason = $_POST['reason'] ?? 'Customer requested cancellation';

            $apiResponse = ApiService::cancel($params, $order->remoteid, $reason);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_CANCELLED,
            ]);

            return ResponseFormatter::success('Order cancelled');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public static function revoke(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found');
            }

            if ($order->status !== SSL_STATUS_COMPLETE) {
                return ResponseFormatter::error('Only issued certificates can be revoked');
            }

            $reason = $_POST['reason'] ?? 'unspecified';

            $apiResponse = ApiService::revoke($params, $order->remoteid, $reason);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_REVOKED,
            ]);

            return ResponseFormatter::success('Certificate revoked');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Reissue/Replace Actions
    // ==========================================

    public static function submitReissue(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found');
            }

            if ($order->status !== SSL_STATUS_COMPLETE) {
                return ResponseFormatter::error('Only issued certificates can be reissued');
            }

            $formData = self::getPostData('data');
            
            if (empty($formData)) {
                return ResponseFormatter::error('Missing form data');
            }

            $requestData = [
                'csr' => $formData['csr'] ?? '',
                'domainInfo' => $formData['domainInfo'] ?? [],
                'organizationInfo' => $formData['organizationInfo'] ?? [],
            ];

            $apiResponse = ApiService::replace($params, $order->remoteid, $requestData);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

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
            
            $configdata['replaceTimes'] = ($configdata['replaceTimes'] ?? 0) + 1;
            $configdata['reissueReturn'] = (array) ($parsed['data'] ?? []);
            $configdata['lastRefresh'] = date('Y-m-d H:i:s');

            $newCertId = $parsed['data']->certId ?? $order->remoteid;

            OrderRepository::update($order->id, [
                'remoteid' => $newCertId,
                'status' => SSL_STATUS_PENDING,
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('Reissue request submitted', $parsed['data']);

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'submitReissue', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

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
    // CSR Tools
    // ==========================================

    public static function generateCSR(array $params): array
    {
        try {
            $data = self::getPostData('data');
            
            if (empty($data)) {
                return ResponseFormatter::error('Missing CSR data');
            }

            $domain = $data['domain'] ?? '';
            $organization = $data['organization'] ?? '';
            $country = $data['country'] ?? 'VN';
            $state = $data['state'] ?? '';
            $city = $data['city'] ?? '';
            $email = $data['email'] ?? '';

            if (empty($domain)) {
                return ResponseFormatter::error('Domain is required');
            }

            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            if (!$privateKey) {
                return ResponseFormatter::error('Failed to generate private key');
            }

            $dn = [
                'commonName' => $domain,
                'countryName' => $country,
            ];
            
            if (!empty($state)) $dn['stateOrProvinceName'] = $state;
            if (!empty($city)) $dn['localityName'] = $city;
            if (!empty($organization)) $dn['organizationName'] = $organization;
            if (!empty($email)) $dn['emailAddress'] = $email;

            $csr = openssl_csr_new($dn, $privateKey, ['digest_alg' => 'sha256']);

            if (!$csr) {
                return ResponseFormatter::error('Failed to generate CSR');
            }

            openssl_csr_export($csr, $csrOut);
            openssl_pkey_export($privateKey, $privateKeyOut);

            return ResponseFormatter::success('CSR generated', [
                'csr' => $csrOut,
                'privateKey' => $privateKeyOut,
            ]);

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public static function decodeCsr(array $params): array
    {
        try {
            $csr = $_POST['csr'] ?? '';
            
            if (empty($csr)) {
                return ResponseFormatter::error('CSR is required');
            }

            $decoded = openssl_csr_get_subject($csr);
            
            if (!$decoded) {
                return ResponseFormatter::error('Invalid CSR format');
            }

            return ResponseFormatter::success('CSR decoded', $decoded);

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Helper Methods - OLD MODULE PATTERN
    // ==========================================

    /**
     * Get POST data - SAME PATTERN AS OLD MODULE checkData()
     */
    private static function getPostData(string $key): array
    {
        logModuleCall('nicsrs_ssl', 'getPostData_START', [
            'key' => $key,
            'POST_keys' => array_keys($_POST),
            'has_key' => isset($_POST[$key]),
        ], 'Looking for POST data');

        if (!isset($_POST[$key])) {
            logModuleCall('nicsrs_ssl', 'getPostData_FALLBACK', [], 'Key not found, trying fallback');
            return self::parseApplyFormDataFallback();
        }

        $rawData = $_POST[$key];
        
        // Already an array (jQuery auto-serialized)
        if (is_array($rawData)) {
            logModuleCall('nicsrs_ssl', 'getPostData_ARRAY', [
                'keys' => array_keys($rawData),
            ], 'Data is already an array');
            return $rawData;
        }
        
        // JSON string
        if (is_string($rawData)) {
            $decoded = json_decode($rawData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                logModuleCall('nicsrs_ssl', 'getPostData_JSON', [], 'Data was JSON string');
                return $decoded;
            }
        }

        logModuleCall('nicsrs_ssl', 'getPostData_EMPTY', [
            'rawData_type' => gettype($rawData),
        ], 'Could not parse data');
        
        return [];
    }

    /**
     * Fallback parser for flat form fields
     */
    private static function parseApplyFormDataFallback(): array
    {
        $data = [
            'csr' => '',
            'privateKey' => '',
            'server' => 'other',
            'domainInfo' => [],
            'Administrator' => [],
            'organizationInfo' => [],
            'originalfromOthers' => '0',
        ];
        
        $data['csr'] = trim($_POST['csr'] ?? '');
        $data['privateKey'] = trim($_POST['privateKey'] ?? '');
        $data['server'] = $_POST['server'] ?? 'other';
        $data['originalfromOthers'] = $_POST['originalfromOthers'] ?? '0';
        
        // Parse domain info from indexed fields
        $domainIndex = 0;
        while (isset($_POST["domainInfo[$domainIndex][domainName]"]) || 
               isset($_POST["domainInfo[{$domainIndex}][domainName]"])) {
            $domainName = $_POST["domainInfo[$domainIndex][domainName]"] ?? 
                         $_POST["domainInfo[{$domainIndex}][domainName]"] ?? '';
            $dcvMethod = $_POST["domainInfo[$domainIndex][dcvMethod]"] ?? 
                        $_POST["domainInfo[{$domainIndex}][dcvMethod]"] ?? 'CNAME_CSR_HASH';
            $dcvEmail = $_POST["domainInfo[$domainIndex][dcvEmail]"] ?? 
                       $_POST["domainInfo[{$domainIndex}][dcvEmail]"] ?? '';
            
            if (!empty($domainName)) {
                $data['domainInfo'][] = [
                    'domainName' => $domainName,
                    'dcvMethod' => $dcvMethod,
                    'dcvEmail' => $dcvEmail,
                ];
            }
            $domainIndex++;
        }
        
        // Parse Administrator from indexed fields
        if (isset($_POST['Administrator']) && is_array($_POST['Administrator'])) {
            $data['Administrator'] = $_POST['Administrator'];
        }
        
        // Parse Organization info
        if (isset($_POST['organizationInfo']) && is_array($_POST['organizationInfo'])) {
            $data['organizationInfo'] = $_POST['organizationInfo'];
        }
        
        logModuleCall('nicsrs_ssl', 'parseApplyFormDataFallback', [
            'domainCount' => count($data['domainInfo']),
        ], 'Fallback result');
        
        return $data;
    }

    /**
     * Validate form data before submission
     */
    private static function validateFormData(array $data, bool $isDraft = false): array
    {
        $errors = [];

        if ($isDraft) {
            return $errors;
        }

        if (empty($data['csr'])) {
            $errors['csr'] = 'CSR is required';
        } elseif (strpos($data['csr'], '-----BEGIN CERTIFICATE REQUEST-----') === false) {
            $errors['csr'] = 'Invalid CSR format';
        }

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
            }
        }

        $admin = $data['Administrator'] ?? [];
        $requiredAdminFields = ['firstName', 'lastName', 'email'];
        foreach ($requiredAdminFields as $field) {
            if (empty($admin[$field])) {
                $errors["admin_{$field}"] = "Administrator {$field} is required";
            }
        }

        return $errors;
    }

    /**
     * Build API request from form data
     */
    private static function buildApiRequest(array $formData, array $cert, array $params): array
    {
        $domainInfos = $formData['domainInfo'] ?? [];
        
        $request = [
            'productCode' => $cert['code'] ?? $params['configoption1'] ?? '',
            'period' => self::getPeriodFromBillingCycle($params),
            'csr' => $formData['csr'],
            'dcvMethod' => $domainInfos[0]['dcvMethod'] ?? 'CNAME_CSR_HASH',
            'adminContact' => $formData['Administrator'],
            'techContact' => $formData['tech'] ?? $formData['Administrator'],
        ];

        if (!empty($formData['organizationInfo'])) {
            $request['organization'] = $formData['organizationInfo'];
        }

        if (count($domainInfos) > 1) {
            $sanDomains = [];
            for ($i = 1; $i < count($domainInfos); $i++) {
                $sanDomains[] = [
                    'domainName' => $domainInfos[$i]['domainName'],
                    'dcvMethod' => $domainInfos[$i]['dcvMethod'] ?? 'CNAME_CSR_HASH',
                ];
            }
            $request['sanDomains'] = $sanDomains;
        }

        return $request;
    }

    /**
     * Get period from billing cycle
     */
    private static function getPeriodFromBillingCycle(array $params): int
    {
        $cycle = strtolower($params['billingcycle'] ?? 'annually');
        
        $cycleMap = [
            'monthly' => 1,
            'quarterly' => 3,
            'semi-annually' => 6,
            'annually' => 12,
            'biennially' => 24,
            'triennially' => 36,
        ];
        
        return $cycleMap[$cycle] ?? 12;
    }
}