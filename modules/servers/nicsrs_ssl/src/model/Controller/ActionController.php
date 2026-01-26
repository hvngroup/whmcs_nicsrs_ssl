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
            $formData = self::parseApplyFormData();

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
                'applyReturn' => (array) ($placeParsed['data'] ?? []),
                'applyParams' => $apiRequest,
                'lastRefresh' => date('Y-m-d H:i:s'),
            ];

            // Add contact info for OV/EV
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

            $formData = self::parseApplyFormData();

            $configdata = json_decode($order->configdata, true) ?: [];
            $configdata = array_merge($configdata, [
                'draft' => $formData,
                'draftSavedAt' => date('Y-m-d H:i:s'),
            ]);

            OrderRepository::update($order->id, [
                'status' => SSL_STATUS_DRAFT,
                'configdata' => json_encode($configdata),
            ]);

            return ResponseFormatter::success('Draft saved successfully');

        } catch (Exception $e) {
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

            $response = ApiService::collect($params, $order->remoteid);
            $parsed = ApiService::parseResponse($response);

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

            $response = ApiService::batchUpdateDCV($params, $order->remoteid, $domainInfo);
            $parsed = ApiService::parseResponse($response);

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

            $response = ApiService::resendDCVEmail($params, $order->remoteid, $domain, $email);
            $parsed = ApiService::parseResponse($response);

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
                $response = ApiService::cancel($params, $order->remoteid, $reason);
                $parsed = ApiService::parseResponse($response);

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

            $response = ApiService::revoke($params, $order->remoteid, $reason);
            $parsed = ApiService::parseResponse($response);

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

            $formData = self::parseApplyFormData();

            // Build reissue request
            $reissueRequest = [];
            
            if (!empty($formData['csr'])) {
                $reissueRequest['csr'] = $formData['csr'];
            }
            
            if (!empty($formData['domainInfo'])) {
                $reissueRequest['domainInfo'] = $formData['domainInfo'];
            }

            $response = ApiService::reissue($params, $order->remoteid, $reissueRequest);
            $parsed = ApiService::parseResponse($response);

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

            $response = ApiService::renew($params, $order->remoteid);
            $parsed = ApiService::parseResponse($response);

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
     * Parse form data from POST
     */
    private static function parseApplyFormData(): array
    {
        $data = [];

        // CSR
        $csrMode = $_POST['csrMode'] ?? 'auto';
        
        if ($csrMode === 'manual' && !empty($_POST['csr'])) {
            $data['csr'] = trim($_POST['csr']);
        } else {
            // Auto-generate CSR
            $csrParams = [
                'cn' => $_POST['commonName'] ?? $_POST['domain'] ?? '',
                'org' => $_POST['organization'] ?? '',
                'ou' => $_POST['organizationalUnit'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'country' => $_POST['country'] ?? '',
                'email' => $_POST['email'] ?? '',
            ];
            
            $generated = CertificateFunc::generateCSR($csrParams);
            $data['csr'] = $generated['csr'];
            $data['privateKey'] = $generated['privateKey'];
        }

        // Domain Info
        $data['domainInfo'] = [];
        
        if (isset($_POST['domainInfo']) && is_array($_POST['domainInfo'])) {
            $data['domainInfo'] = $_POST['domainInfo'];
        } else {
            // Parse from individual fields
            $domains = $_POST['domainName'] ?? [];
            $dcvMethods = $_POST['dcvMethod'] ?? [];
            $dcvEmails = $_POST['dcvEmail'] ?? [];

            if (!is_array($domains)) {
                $domains = [$domains];
                $dcvMethods = [$dcvMethods];
                $dcvEmails = [$dcvEmails ?? ''];
            }

            foreach ($domains as $i => $domain) {
                if (!empty($domain)) {
                    $data['domainInfo'][] = [
                        'domainName' => trim($domain),
                        'dcvMethod' => $dcvMethods[$i] ?? 'HTTP_CSR_HASH',
                        'dcvEmail' => $dcvEmails[$i] ?? '',
                    ];
                }
            }
        }

        // Server type
        $data['server'] = $_POST['server'] ?? 'other';

        // Administrator contact (for OV/EV)
        if (!empty($_POST['adminFirstName'])) {
            $data['Administrator'] = [
                'firstName' => $_POST['adminFirstName'] ?? '',
                'lastName' => $_POST['adminLastName'] ?? '',
                'email' => $_POST['adminEmail'] ?? '',
                'mobile' => $_POST['adminPhone'] ?? '',
                'job' => $_POST['adminTitle'] ?? '',
                'organation' => $_POST['adminOrganizationName'] ?? '',
            ];
        }

        // Technical contact (copy from admin if not provided)
        $data['tech'] = $data['Administrator'] ?? [];

        // Organization info (for OV/EV)
        if (!empty($_POST['orgName'])) {
            $data['organizationInfo'] = [
                'name' => $_POST['orgName'] ?? '',
                'country' => $_POST['orgCountry'] ?? '',
                'state' => $_POST['orgState'] ?? '',
                'city' => $_POST['orgCity'] ?? '',
                'address' => $_POST['orgAddress'] ?? '',
                'postCode' => $_POST['orgPostCode'] ?? '',
                'phone' => $_POST['orgPhone'] ?? '',
                'division' => $_POST['orgDivision'] ?? '',
            ];
        }

        // Is renewal
        $data['isRenew'] = $_POST['isRenew'] ?? '0';

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
            $errors['csr'] = 'CSR is required';
        }

        // At least one domain required
        if (empty($data['domainInfo'])) {
            $errors['domain'] = 'At least one domain is required';
        } else {
            foreach ($data['domainInfo'] as $i => $domain) {
                if (empty($domain['domainName'])) {
                    $errors["domain_{$i}"] = 'Domain name is required';
                } elseif (!CertificateFunc::validateDomain($domain['domainName'])) {
                    $errors["domain_{$i}"] = 'Invalid domain format';
                }
                
                if (empty($domain['dcvMethod'])) {
                    $errors["dcv_{$i}"] = 'DCV method is required';
                }
            }
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
            $request['finance'] = $formData['Administrator'];
        }

        if (!empty($formData['organizationInfo'])) {
            $request['organizationInfo'] = $formData['organizationInfo'];
        }

        if ($formData['isRenew'] === '1') {
            $request['isRenew'] = true;
        }

        return $request;
    }
}