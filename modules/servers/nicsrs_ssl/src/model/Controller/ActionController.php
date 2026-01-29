<?php
/**
 * NicSRS SSL Module - Action Controller
 * Handles AJAX actions for SSL certificate management
 * 
 * FIXED v2.0.1:
 * - Fixed getCertificateByCode -> getCertAttributes
 * - Fixed isRenew/originalfromOthers consistency
 * - Enhanced decodeCsr with more fields
 * - Added DCV email generation
 * 
 * @package    nicsrs_ssl
 * @version    2.0.1
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
     * FIXED: Changed getCertificateByCode to getCertAttributes
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

            // FIXED: Use getCertAttributes instead of getCertificateByCode
            $certCode = $order->certtype ?? $params['configoption1'] ?? '';
            $cert = CertificateFunc::getCertAttributes($certCode);
            
            // If cert is null, create minimal config
            if (empty($cert)) {
                $cert = [
                    'code' => $certCode,
                    'name' => $certCode,
                    'sslValidationType' => 'dv',
                    'isMultiDomain' => false,
                    'maxDomains' => 1,
                ];
            }
            
            // Build API request
            $apiRequest = self::buildApiRequest($formData, $cert, $params);

            // Call API
            $apiResponse = ApiService::place($params, $apiRequest);
            $placeParsed = ApiService::parseResponse($apiResponse);

            if (!$placeParsed['success']) {
                return ResponseFormatter::error($placeParsed['message']);
            }

            // FIXED: Store isRenew as originalfromOthers for compatibility
            $isRenew = $formData['originalfromOthers'] ?? $formData['isRenew'] ?? '0';

            // Build configdata
            $configdata = [
                'csr' => $formData['csr'] ?? '',
                'privateKey' => $formData['privateKey'] ?? '',
                'domainInfo' => $formData['domainInfo'] ?? [],
                'server' => $formData['server'] ?? 'other',
                'originalfromOthers' => $isRenew,
                'isRenew' => $isRenew, // Store both for compatibility
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
     * Save draft - FIXED: Properly handles isRenew field
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
            
            if (empty($formData)) {
                return ResponseFormatter::error('No data to save');
            }

            // Get existing config to merge
            $existingConfig = json_decode($order->configdata, true) ?: [];
            
            // FIXED: Handle isRenew/originalfromOthers consistently
            $isRenew = $formData['originalfromOthers'] ?? $formData['isRenew'] ?? 
                       $existingConfig['originalfromOthers'] ?? $existingConfig['isRenew'] ?? '0';

            // Build configdata - merge with existing
            $configdata = array_merge($existingConfig, [
                'csr' => $formData['csr'] ?? ($existingConfig['csr'] ?? ''),
                'privateKey' => $formData['privateKey'] ?? ($existingConfig['privateKey'] ?? ''),
                'domainInfo' => $formData['domainInfo'] ?? ($existingConfig['domainInfo'] ?? []),
                'server' => $formData['server'] ?? ($existingConfig['server'] ?? 'other'),
                'originalfromOthers' => $isRenew,
                'isRenew' => $isRenew, // Store both for compatibility
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
                'isRenew' => $configdata['isRenew'],
                'originalfromOthers' => $configdata['originalfromOthers'],
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
                    'isRenew' => $configdata['isRenew'],
                ]);
            }
            
            return ResponseFormatter::error('Failed to save draft');

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'saveDraft_ERROR', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // CSR Actions - ENHANCED
    // ==========================================

    /**
     * Decode CSR - ENHANCED with more fields
     */
    public static function decodeCsr(array $params): array
    {
        try {
            // Get CSR from POST (sent directly, not in data wrapper)
            $csr = $_POST['csr'] ?? '';
            
            if (empty($csr)) {
                return ResponseFormatter::error('CSR is required');
            }
            
            $csr = trim($csr);
            
            // Validate CSR format
            if (strpos($csr, '-----BEGIN CERTIFICATE REQUEST-----') === false) {
                return ResponseFormatter::error('Invalid CSR format');
            }
            
            // Decode CSR using OpenSSL
            $csrResource = openssl_csr_get_subject($csr);
            
            if (!$csrResource) {
                return ResponseFormatter::error('Failed to decode CSR: ' . openssl_error_string());
            }
            
            // Get public key info for additional details
            $pubKeyResource = openssl_csr_get_public_key($csr);
            $pubKeyDetails = $pubKeyResource ? openssl_pkey_get_details($pubKeyResource) : [];
            
            // ENHANCED: Build comprehensive response
            $result = [
                // Standard fields (backward compatible)
                'CN' => $csrResource['CN'] ?? '',
                'O' => $csrResource['O'] ?? '',
                'OU' => $csrResource['OU'] ?? '',
                'L' => $csrResource['L'] ?? '',
                'ST' => $csrResource['ST'] ?? '',
                'C' => $csrResource['C'] ?? '',
                'emailAddress' => $csrResource['emailAddress'] ?? '',
                
                // NEW: Additional fields for enhanced display
                'commonName' => $csrResource['CN'] ?? '',
                'organization' => $csrResource['O'] ?? '',
                'organizationalUnit' => $csrResource['OU'] ?? '',
                'locality' => $csrResource['L'] ?? '',
                'state' => $csrResource['ST'] ?? '',
                'country' => $csrResource['C'] ?? '',
                'email' => $csrResource['emailAddress'] ?? '',
                
                // NEW: Key information
                'keySize' => $pubKeyDetails['bits'] ?? 0,
                'keyType' => self::getKeyTypeName($pubKeyDetails['type'] ?? 0),
                
                // NEW: Domain extraction
                'domain' => $csrResource['CN'] ?? '',
                'isWildcard' => strpos($csrResource['CN'] ?? '', '*.') === 0,
                
                // NEW: Validation status
                'valid' => true,
            ];
            
            return ResponseFormatter::success('CSR decoded successfully', $result);
            
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'decodeCsr', $_POST, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Generate CSR and Private Key
     */
    public static function generateCSR(array $params): array
    {
        try {
            $data = self::getPostData('data');
            
            if (empty($data)) {
                return ResponseFormatter::error('Data is required');
            }
            
            // Build DN (Distinguished Name)
            $dn = [];
            
            // Common Name (required)
            if (!empty($data['domain'])) {
                $dn['commonName'] = $data['domain'];
            } elseif (!empty($data['cn'])) {
                $dn['commonName'] = $data['cn'];
            } else {
                return ResponseFormatter::error('Domain/Common Name is required');
            }
            
            // Organization
            if (!empty($data['organization'])) {
                $dn['organizationName'] = $data['organization'];
            }
            
            // Country
            if (!empty($data['country'])) {
                $dn['countryName'] = $data['country'];
            }
            
            // State
            if (!empty($data['state'])) {
                $dn['stateOrProvinceName'] = $data['state'];
            }
            
            // City
            if (!empty($data['city'])) {
                $dn['localityName'] = $data['city'];
            }
            
            // Email
            if (!empty($data['email'])) {
                $dn['emailAddress'] = $data['email'];
            }
            
            // Generate private key
            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            
            if (!$privateKey) {
                return ResponseFormatter::error('Failed to generate private key: ' . openssl_error_string());
            }
            
            // Generate CSR
            $csr = openssl_csr_new($dn, $privateKey, [
                'digest_alg' => 'sha256',
            ]);
            
            if (!$csr) {
                return ResponseFormatter::error('Failed to generate CSR: ' . openssl_error_string());
            }
            
            // Export to PEM format
            openssl_csr_export($csr, $csrPem);
            openssl_pkey_export($privateKey, $privateKeyPem);
            
            return ResponseFormatter::success('CSR generated successfully', [
                'csr' => $csrPem,
                'privateKey' => $privateKeyPem,
                'dn' => $dn,
            ]);
            
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'generateCSR', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Get key type name from OpenSSL constant
     */
    private static function getKeyTypeName(int $type): string
    {
        $types = [
            OPENSSL_KEYTYPE_RSA => 'RSA',
            OPENSSL_KEYTYPE_DSA => 'DSA',
            OPENSSL_KEYTYPE_DH => 'DH',
            OPENSSL_KEYTYPE_EC => 'EC',
        ];
        
        return $types[$type] ?? 'Unknown';
    }

    // ==========================================
    // DCV Actions - ENHANCED
    // ==========================================

    /**
     * Get DCV email options for domain
     * NEW: Helper method for generating email dropdown options
     */
    public static function getDcvEmails(array $params): array
    {
        try {
            $domain = $_POST['domain'] ?? '';
            
            if (empty($domain)) {
                return ResponseFormatter::error('Domain is required');
            }
            
            // Remove wildcard prefix if present
            $cleanDomain = preg_replace('/^\*\./', '', $domain);
            
            // Generate standard validation email addresses
            $emails = self::generateDcvEmails($cleanDomain);
            
            return ResponseFormatter::success('DCV emails generated', [
                'emails' => $emails,
                'domain' => $cleanDomain,
            ]);
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Generate standard DCV email addresses for a domain
     * 
     * @param string $domain Domain name
     * @return array List of validation emails
     */
    public static function generateDcvEmails(string $domain): array
    {
        // Standard DCV email prefixes (as per CA/Browser Forum)
        $prefixes = ['admin', 'administrator', 'webmaster', 'hostmaster', 'postmaster'];
        
        $emails = [];
        foreach ($prefixes as $prefix) {
            $emails[] = $prefix . '@' . $domain;
        }
        
        // If subdomain, also add parent domain emails
        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            $parentDomain = implode('.', array_slice($parts, 1));
            foreach ($prefixes as $prefix) {
                $emails[] = $prefix . '@' . $parentDomain;
            }
        }
        
        return array_unique($emails);
    }

    /**
     * Batch update DCV method
     */
    public static function batchUpdateDCV(array $params): array
    {
        try {
            $data = self::getPostData('data');
            
            if (empty($data) || empty($data['domains'])) {
                return ResponseFormatter::error('Domains data is required');
            }
            
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found or not submitted');
            }
            
            // Build DCV info for API
            $dcvInfo = [];
            foreach ($data['domains'] as $domainData) {
                $item = [
                    'domainName' => $domainData['domainName'] ?? '',
                    'dcvMethod' => $domainData['dcvMethod'] ?? 'CNAME_CSR_HASH',
                ];
                
                // If email method, add email
                if (strtoupper($item['dcvMethod']) === 'EMAIL' && !empty($domainData['dcvEmail'])) {
                    $item['dcvEmail'] = $domainData['dcvEmail'];
                }
                
                $dcvInfo[] = $item;
            }
            
            // Call API to update DCV
            $apiResponse = ApiService::batchUpdateDCV($params, $order->remoteid, $dcvInfo);
            $parsed = ApiService::parseResponse($apiResponse);
            
            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }
            
            // Update local configdata
            $configdata = json_decode($order->configdata, true) ?: [];
            $configdata['domainInfo'] = array_map(function($domain) use ($dcvInfo) {
                foreach ($dcvInfo as $dcv) {
                    if ($dcv['domainName'] === $domain['domainName']) {
                        $domain['dcvMethod'] = $dcv['dcvMethod'];
                        if (isset($dcv['dcvEmail'])) {
                            $domain['dcvEmail'] = $dcv['dcvEmail'];
                        }
                        break;
                    }
                }
                return $domain;
            }, $configdata['domainInfo'] ?? []);
            
            OrderRepository::updateConfigData($order->id, $configdata);
            
            return ResponseFormatter::success('DCV method updated successfully');
            
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
            $domain = $_POST['domain'] ?? '';
            $email = $_POST['email'] ?? '';
            
            if (empty($domain)) {
                return ResponseFormatter::error('Domain is required');
            }
            
            // If no email provided, get from configdata or generate default
            if (empty($email)) {
                $order = OrderRepository::getByServiceId($params['serviceid']);
                if ($order) {
                    $configdata = json_decode($order->configdata, true) ?: [];
                    $domainInfo = $configdata['domainInfo'] ?? [];
                    foreach ($domainInfo as $d) {
                        if (($d['domainName'] ?? '') === $domain && !empty($d['dcvEmail'])) {
                            $email = $d['dcvEmail'];
                            break;
                        }
                    }
                }
                // Fallback to admin@domain
                if (empty($email)) {
                    $email = 'admin@' . $domain;
                }
            }
            
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found or not submitted');
            }
            
            // Call API with all 4 required parameters
            $apiResponse = ApiService::resendDCVEmail($params, $order->remoteid, $domain, $email);
            $parsed = ApiService::parseResponse($apiResponse);
            
            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }
            
            return ResponseFormatter::success('DCV email sent successfully');
            
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'resendDCVEmail', $params, $e->getMessage());
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

            $apiResponse = ApiService::collect($params, $order->remoteid);
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
            logModuleCall('nicsrs_ssl', 'downCert', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Order Management Actions
    // ==========================================

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
            
            if (empty($order->remoteid)) {
                return ResponseFormatter::error('Order not submitted yet');
            }

            $apiResponse = ApiService::cancel($params, $order->remoteid);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_CANCELLED,
            ]);

            return ResponseFormatter::success('Order cancelled successfully');

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'cancelOrder', $params, $e->getMessage());
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
            
            if (!$order || empty($order->remoteid)) {
                return ResponseFormatter::error('Order not found or not submitted');
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

            return ResponseFormatter::success('Certificate revoked successfully');

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'revoke', $params, $e->getMessage());
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

            $formData = self::getPostData('data');
            
            if (empty($formData)) {
                return ResponseFormatter::error('Missing form data');
            }

            // FIXED: Use getCertAttributes
            $certCode = $order->certtype ?? $params['configoption1'] ?? '';
            $cert = CertificateFunc::getCertAttributes($certCode);
            
            if (empty($cert)) {
                $cert = ['code' => $certCode, 'name' => $certCode];
            }

            $apiRequest = self::buildApiRequest($formData, $cert, $params);
            $apiRequest['certId'] = $order->remoteid;

            $certId = $apiRequest['certId'];
            unset($apiRequest['certId']);
            $apiResponse = ApiService::reissue($params, $certId, $apiRequest);
            $parsed = ApiService::parseResponse($apiResponse);

            if (!$parsed['success']) {
                return ResponseFormatter::error($parsed['message']);
            }

            $configdata = json_decode($order->configdata, true) ?: [];
            $configdata['csr'] = $formData['csr'] ?? '';
            $configdata['privateKey'] = $formData['privateKey'] ?? '';
            $configdata['domainInfo'] = $formData['domainInfo'] ?? [];
            $configdata['replaceTimes'] = ($configdata['replaceTimes'] ?? 0) + 1;
            $configdata['applyReturn'] = (array) ($parsed['data'] ?? []);
            $configdata['lastRefresh'] = date('Y-m-d H:i:s');

            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_PENDING,
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('Reissue request submitted successfully');

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

            $configdata = json_decode($order->configdata, true) ?: [];
            
            // Mark for renewal
            $configdata['originalfromOthers'] = '1';
            $configdata['isRenew'] = '1';
            $configdata['renewFrom'] = $order->remoteid;
            
            // Reset status for new application
            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_AWAITING,
                'remoteid' => '',
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('Ready for renewal. Please submit new certificate request.');

        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'renew', $params, $e->getMessage());
            return ResponseFormatter::error($e->getMessage());
        }
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    /**
     * Get POST data using OLD MODULE pattern
     * Handles both array and string JSON inputs
     */
    private static function getPostData(string $key): array
    {
        if (!isset($_POST[$key])) {
            return [];
        }
        
        $data = $_POST[$key];
        
        // If already an array (from PHP's form handling)
        if (is_array($data)) {
            return $data;
        }
        
        // If JSON string
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        
        return [];
    }

    /**
     * Validate form data
     */
    private static function validateFormData(array $formData, bool $isDraft = false): array
    {
        $errors = [];

        // Domain validation
        $domains = $formData['domainInfo'] ?? [];
        if (empty($domains) && !$isDraft) {
            $errors['domainInfo'] = 'At least one domain is required';
        }

        // CSR validation (only for submit, not draft)
        if (!$isDraft && empty($formData['csr'])) {
            $errors['csr'] = 'CSR is required';
        }

        // Administrator contact validation
        $admin = $formData['Administrator'] ?? [];
        $requiredAdminFields = ['firstName', 'lastName', 'email'];
        foreach ($requiredAdminFields as $field) {
            if (empty($admin[$field]) && !$isDraft) {
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
        
        // Process domain info for API
        $processedDomains = [];
        foreach ($domainInfos as $domain) {
            $item = [
                'domainName' => $domain['domainName'] ?? '',
                'dcvMethod' => $domain['dcvMethod'] ?? 'CNAME_CSR_HASH',
            ];
            
            // Check if dcvMethod is an email
            $dcvMethod = $item['dcvMethod'];
            if (filter_var($dcvMethod, FILTER_VALIDATE_EMAIL)) {
                $item['dcvMethod'] = 'EMAIL';
                $item['dcvEmail'] = $dcvMethod;
            } elseif (!empty($domain['dcvEmail'])) {
                $item['dcvEmail'] = $domain['dcvEmail'];
            }
            
            $processedDomains[] = $item;
        }
        
        $request = [
            'productCode' => $cert['code'] ?? $params['configoption1'] ?? '',
            'period' => self::getPeriodFromBillingCycle($params),
            'csr' => $formData['csr'] ?? '',
            'domainInfo' => $processedDomains,
            'adminContact' => $formData['Administrator'] ?? [],
            'techContact' => $formData['tech'] ?? $formData['Administrator'] ?? [],
        ];

        if (!empty($formData['organizationInfo'])) {
            $request['organization'] = $formData['organizationInfo'];
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
            'quarterly' => 1,
            'semi-annually' => 1,
            'annually' => 1,
            'biennially' => 2,
            'triennially' => 3,
        ];
        
        return $cycleMap[$cycle] ?? 1;
    }
}