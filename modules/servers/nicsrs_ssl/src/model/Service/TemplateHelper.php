<?php
/**
 * NicSRS SSL Module - Template Helper
 * Helper functions for template rendering
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace nicsrsSSL;

class TemplateHelper
{
    /**
     * Get base template variables
     */
    public static function getBaseVars(array $params): array
    {
        // Get language
        $language = CertificateFunc::loadLanguage(
            CertificateFunc::getClientLanguage($params['userid'] ?? 0)
        );

        // Get WHMCS system URL
        $systemUrl = '';
        try {
            $systemUrl = \WHMCS\Config\Setting::getValue('SystemURL');
        } catch (\Exception $e) {
            $systemUrl = '';
        }

        return [
            '_LANG' => $language,
            '_LANG_JSON' => json_encode($language, JSON_UNESCAPED_UNICODE),
            'WEB_ROOT' => rtrim($systemUrl, '/'),
            'MODULE_PATH' => 'modules/servers/nicsrs_ssl',
            'serviceid' => $params['serviceid'] ?? 0,
            'userid' => $params['userid'] ?? 0,
            'moduleVersion' => NICSRS_SSL_VERSION,
        ];
    }

    /**
     * Render apply certificate page
     */
    public static function applyCert(array $params, object $order, array $cert): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];

        // Load country list
        $countryFile = NICSRS_SSL_PATH . 'src/config/country.json';
        $countries = file_exists($countryFile) 
            ? json_decode(file_get_contents($countryFile), true) 
            : [];

        // Pre-fill client info
        $client = self::getClientInfo($params['userid']);

        return [
            'templatefile' => 'applycert',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configdata' => $configdata,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'sslType' => $cert['sslType'] ?? 'website_ssl',
                'sslValidationType' => $cert['sslValidationType'] ?? 'dv',
                'isMultiDomain' => $cert['isMultiDomain'] ?? false,
                'isWildcard' => $cert['isWildcard'] ?? false,
                'maxDomains' => $cert['maxDomains'] ?? 1,
                'requiresOrganization' => in_array($cert['sslValidationType'] ?? 'dv', ['ov', 'ev']),
                'supportOptions' => [
                    'supportNormal' => $cert['supportNormal'] ?? true,
                    'supportIp' => $cert['supportIp'] ?? false,
                    'supportWild' => $cert['supportWild'] ?? false,
                    'supportHttps' => $cert['supportHttps'] ?? true,
                ],
                'dcvMethods' => DCV_METHODS,
                'serverTypes' => SERVER_TYPES,
                'countries' => $countries,
                'client' => $client,
                'status' => $order->status,
                'isRenewal' => $configdata['isRenewal'] ?? false,
                'previousCertId' => $configdata['previousCertId'] ?? '',
            ]),
        ];
    }

    /**
     * Render pending/message page
     */
    public static function pending(array $params, object $order, array $cert, array $collectData = []): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];
        $applyReturn = $configdata['applyReturn'] ?? [];
        $domainInfo = $configdata['domainInfo'] ?? [];

        // Format DCV status
        $dcvList = $applyReturn['dcvList'] ?? $collectData['dcvList'] ?? [];
        $dcvStatus = ResponseFormatter::formatDCVStatus($domainInfo, $dcvList);

        return [
            'templatefile' => 'pending',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configdata' => $configdata,
                'applyReturn' => $applyReturn,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'sslType' => $cert['sslType'] ?? 'website_ssl',
                'status' => $order->status,
                'remoteid' => $order->remoteid,
                'dcvStatus' => $dcvStatus,
                'dcvMethods' => DCV_METHODS,
                'applyTime' => $applyReturn['applyTime'] ?? '',
                'collectData' => $collectData,
                'lastRefresh' => $configdata['lastRefresh'] ?? '',
            ]),
        ];
    }

    /**
     * Render complete page
     */
    public static function complete(array $params, object $order, array $cert, array $collectData = []): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];
        $applyReturn = $configdata['applyReturn'] ?? [];
        $domainInfo = $configdata['domainInfo'] ?? [];

        // Calculate expiry info
        $endDate = $applyReturn['endDate'] ?? '';
        $daysLeft = CertificateFunc::getDaysUntilExpiry($endDate);
        $isExpiringSoon = $daysLeft !== null && $daysLeft <= 30;

        // Format DCV status
        $dcvList = $applyReturn['dcvList'] ?? [];
        $dcvStatus = ResponseFormatter::formatDCVStatus($domainInfo, $dcvList);

        // Check if certificate can be downloaded
        $canDownload = !empty($applyReturn['certificate']);

        return [
            'templatefile' => 'complete',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configdata' => $configdata,
                'applyReturn' => $applyReturn,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'sslType' => $cert['sslType'] ?? 'website_ssl',
                'sslValidationType' => $cert['sslValidationType'] ?? 'dv',
                'status' => $order->status,
                'remoteid' => $order->remoteid,
                'beginDate' => CertificateFunc::formatDate($applyReturn['beginDate'] ?? ''),
                'endDate' => CertificateFunc::formatDate($endDate),
                'daysLeft' => $daysLeft,
                'isExpiringSoon' => $isExpiringSoon,
                'dcvStatus' => $dcvStatus,
                'canDownload' => $canDownload,
                'canReissue' => true,
                'canRenew' => true,
                'downloadFormats' => DOWNLOAD_FORMATS,
                'collectData' => $collectData,
                'lastRefresh' => $configdata['lastRefresh'] ?? '',
            ]),
        ];
    }

    /**
     * Render manage page
     */
    public static function manage(array $params, object $order, array $cert): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];
        $applyReturn = $configdata['applyReturn'] ?? [];
        $domainInfo = $configdata['domainInfo'] ?? [];

        // Determine available actions
        $actions = self::getAvailableActions($order->status);

        return [
            'templatefile' => 'manage',
            'vars' => array_merge($baseVars, [
                'order' => ResponseFormatter::formatOrderForDisplay($order),
                'configdata' => $configdata,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'status' => $order->status,
                'statusClass' => CertificateFunc::getStatusClass($order->status),
                'actions' => $actions,
                'downloadFormats' => DOWNLOAD_FORMATS,
            ]),
        ];
    }

    /**
     * Render reissue page
     */
    public static function reissue(array $params, object $order, array $cert): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];

        // Load country list
        $countryFile = NICSRS_SSL_PATH . 'src/config/country.json';
        $countries = file_exists($countryFile) 
            ? json_decode(file_get_contents($countryFile), true) 
            : [];

        // Pre-fill with existing data
        $existingData = [
            'domainInfo' => $configdata['domainInfo'] ?? [],
            'Administrator' => $configdata['Administrator'] ?? [],
            'organizationInfo' => $configdata['organizationInfo'] ?? [],
        ];

        return [
            'templatefile' => 'reissue',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configdata' => $configdata,
                'existingData' => $existingData,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'sslType' => $cert['sslType'] ?? 'website_ssl',
                'sslValidationType' => $cert['sslValidationType'] ?? 'dv',
                'isMultiDomain' => $cert['isMultiDomain'] ?? false,
                'maxDomains' => $cert['maxDomains'] ?? 1,
                'requiresOrganization' => in_array($cert['sslValidationType'] ?? 'dv', ['ov', 'ev']),
                'dcvMethods' => DCV_METHODS,
                'serverTypes' => SERVER_TYPES,
                'countries' => $countries,
                'status' => $order->status,
                'remoteid' => $order->remoteid,
            ]),
        ];
    }

    /**
     * Render error page
     */
    public static function error(array $params, string $message, string $title = 'Error'): array
    {
        $baseVars = self::getBaseVars($params);

        return [
            'templatefile' => 'error',
            'vars' => array_merge($baseVars, [
                'errorTitle' => $title,
                'errorMessage' => $message,
            ]),
        ];
    }

    /**
     * Get available actions based on order status
     */
    private static function getAvailableActions(string $status): array
    {
        $actions = [
            'refresh' => false,
            'download' => false,
            'reissue' => false,
            'renew' => false,
            'cancel' => false,
            'revoke' => false,
            'updateDCV' => false,
        ];

        switch ($status) {
            case 'Awaiting Configuration':
            case 'Draft':
                // No actions available
                break;
            case 'Pending':
            case 'Processing':
                $actions['refresh'] = true;
                $actions['updateDCV'] = true;
                $actions['cancel'] = true;
                break;
            case 'Complete':
            case 'Issued':
                $actions['refresh'] = true;
                $actions['download'] = true;
                $actions['reissue'] = true;
                $actions['renew'] = true;
                $actions['revoke'] = true;
                break;
            case 'Reissue':
                $actions['refresh'] = true;
                $actions['updateDCV'] = true;
                break;
            case 'Cancelled':
            case 'Revoked':
            case 'Expired':
                $actions['renew'] = true;
                break;
        }

        return $actions;
    }

    /**
     * Get client info for pre-filling forms
     */
    private static function getClientInfo(int $userId): array
    {
        if (!$userId) {
            return [];
        }

        try {
            $client = \WHMCS\Database\Capsule::table('tblclients')
                ->where('id', $userId)
                ->first();

            if (!$client) {
                return [];
            }

            return [
                'firstName' => $client->firstname ?? '',
                'lastName' => $client->lastname ?? '',
                'email' => $client->email ?? '',
                'phone' => $client->phonenumber ?? '',
                'companyName' => $client->companyname ?? '',
                'address' => $client->address1 ?? '',
                'city' => $client->city ?? '',
                'state' => $client->state ?? '',
                'postCode' => $client->postcode ?? '',
                'country' => $client->country ?? '',
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}