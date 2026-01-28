<?php
/**
 * NicSRS SSL Module - Template Helper
 * Helper functions for template rendering
 * 
 * FIXED: Properly handles countries array from JSON
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace nicsrsSSL;

use WHMCS\Database\Capsule;

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
     * Get client info for pre-filling forms
     */
    public static function getClientInfo(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        try {
            $client = Capsule::table('tblclients')
                ->where('id', $userId)
                ->first();

            if (!$client) {
                return [];
            }

            return [
                'firstname' => $client->firstname ?? '',
                'lastname' => $client->lastname ?? '',
                'email' => $client->email ?? '',
                'phonenumber' => $client->phonenumber ?? '',
                'companyname' => $client->companyname ?? '',
                'address1' => $client->address1 ?? '',
                'address2' => $client->address2 ?? '',
                'city' => $client->city ?? '',
                'state' => $client->state ?? '',
                'postcode' => $client->postcode ?? '',
                'country' => $client->country ?? 'VN',
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Load countries from JSON file
     * Returns array of objects with 'code' and 'name' properties
     */
    public static function getCountries(): array
    {
        $countryFile = NICSRS_SSL_PATH . 'src/config/country.json';
        
        if (!file_exists($countryFile)) {
            // Return basic fallback
            return [
                ['code' => 'VN', 'name' => 'Vietnam'],
                ['code' => 'US', 'name' => 'United States'],
                ['code' => 'GB', 'name' => 'United Kingdom'],
                ['code' => 'SG', 'name' => 'Singapore'],
                ['code' => 'JP', 'name' => 'Japan'],
                ['code' => 'AU', 'name' => 'Australia'],
                ['code' => 'DE', 'name' => 'Germany'],
                ['code' => 'FR', 'name' => 'France'],
                ['code' => 'CN', 'name' => 'China'],
                ['code' => 'KR', 'name' => 'South Korea'],
            ];
        }
        
        $content = file_get_contents($countryFile);
        $countries = json_decode($content, true);
        
        if (!is_array($countries)) {
            return [];
        }
        
        // Ensure each country has 'code' and 'name'
        $result = [];
        foreach ($countries as $country) {
            if (isset($country['code']) && isset($country['name'])) {
                $result[] = [
                    'code' => $country['code'],
                    'name' => $country['name'],
                ];
            }
        }
        
        return $result;
    }

    /**
     * Render apply certificate page
     */
    public static function applyCert(array $params, object $order, array $cert): array
    {
        $baseVars = self::getBaseVars($params);
        
        // Decode configdata properly
        $configdata = [];
        if (!empty($order->configdata)) {
            $configdata = json_decode($order->configdata, true);
            if (!is_array($configdata)) {
                $configdata = [];
            }
        }

        // DEBUG: Log what we're passing
        logModuleCall('nicsrs_ssl', 'TemplateHelper_applyCert', [
            'order_id' => $order->id ?? 'N/A',
            'configdata_raw_length' => strlen($order->configdata ?? ''),
            'domainCount' => count($configdata['domainInfo'] ?? []),
            'hasAdmin' => !empty($configdata['Administrator']),
        ], 'Building template vars');

        // Load countries
        $countries = self::getCountries();

        // Pre-fill client info
        $client = self::getClientInfo($params['userid'] ?? 0);

        return [
            'templatefile' => 'applycert',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configData' => $configdata,
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
                'countries' => $countries,
                'client' => $client,
            ]),
        ];
    }

    /**
     * Render pending/message page (waiting for validation)
     */
    public static function message(array $params, object $order, array $cert): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];
        $applyReturn = $configdata['applyReturn'] ?? [];

        return [
            'templatefile' => 'message',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configData' => $configdata,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'sslValidationType' => $cert['sslValidationType'] ?? 'dv',
                'domainInfo' => $configdata['domainInfo'] ?? [],
                'applyReturn' => $applyReturn,
                'certId' => $order->remoteid ?? '',
                'vendorId' => $applyReturn['vendorId'] ?? '',
                // DCV Info
                'dcvFileName' => $applyReturn['DCVfileName'] ?? '',
                'dcvFileContent' => $applyReturn['DCVfileContent'] ?? '',
                'dcvDnsHost' => $applyReturn['DCVdnsHost'] ?? '',
                'dcvDnsValue' => $applyReturn['DCVdnsValue'] ?? '',
                'dcvDnsType' => $applyReturn['DCVdnsType'] ?? '',
            ]),
        ];
    }

    /**
     * Render complete page (certificate issued)
     */
    public static function complete(array $params, object $order, array $cert): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];
        $applyReturn = $configdata['applyReturn'] ?? [];

        // Check if certificate content is available
        $hasCertificate = !empty($applyReturn['certificate']);
        $hasPrivateKey = !empty($configdata['privateKey']);

        return [
            'templatefile' => 'complete',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configData' => $configdata,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'sslValidationType' => $cert['sslValidationType'] ?? 'dv',
                'status' => $order->status,
                'certId' => $order->remoteid ?? '',
                'vendorId' => $applyReturn['vendorId'] ?? '',
                'domainInfo' => $configdata['domainInfo'] ?? [],
                // Certificate data
                'hasCertificate' => $hasCertificate,
                'hasPrivateKey' => $hasPrivateKey,
                'certificate' => $applyReturn['certificate'] ?? '',
                'caCertificate' => $applyReturn['caCertificate'] ?? '',
                'beginDate' => $applyReturn['beginDate'] ?? '',
                'endDate' => $applyReturn['endDate'] ?? '',
                // Download options
                'canDownload' => $hasCertificate,
                'canDownloadKey' => $hasPrivateKey,
            ]),
        ];
    }

    /**
     * Render cancelled/revoked page
     */
    public static function cancelled(array $params, object $order, array $cert): array
    {
        $baseVars = self::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];

        return [
            'templatefile' => 'cancelled',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configData' => $configdata,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'status' => $order->status,
                'certId' => $order->remoteid ?? '',
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

        // Load countries
        $countries = self::getCountries();

        // Get original domains
        $originalDomains = [];
        if (!empty($configdata['domainInfo'])) {
            foreach ($configdata['domainInfo'] as $domain) {
                $originalDomains[] = $domain['domainName'] ?? '';
            }
        }

        return [
            'templatefile' => 'reissue',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configData' => $configdata,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'sslType' => $cert['sslType'] ?? 'website_ssl',
                'sslValidationType' => $cert['sslValidationType'] ?? 'dv',
                'isMultiDomain' => $cert['isMultiDomain'] ?? false,
                'maxDomains' => $cert['maxDomains'] ?? 1,
                'requiresOrganization' => in_array($cert['sslValidationType'] ?? 'dv', ['ov', 'ev']),
                'supportOptions' => [
                    'supportNormal' => $cert['supportNormal'] ?? true,
                    'supportIp' => $cert['supportIp'] ?? false,
                    'supportWild' => $cert['supportWild'] ?? false,
                    'supportHttps' => $cert['supportHttps'] ?? true,
                ],
                'countries' => $countries,
                // Original domains for reference
                'originalDomains' => $originalDomains,
                'replaceTimes' => $configdata['replaceTimes'] ?? 0,
            ]),
        ];
    }

    /**
     * Render error page
     */
    public static function error(array $params, string $message): array
    {
        $baseVars = self::getBaseVars($params);

        return [
            'templatefile' => 'error',
            'vars' => array_merge($baseVars, [
                'errorMessage' => $message,
            ]),
        ];
    }

    /**
     * Determine which template to render based on order status
     */
    public static function getTemplateForStatus(array $params, object $order, array $cert): array
    {
        $status = strtolower($order->status ?? '');

        // DEBUG
        logModuleCall('nicsrs_ssl', 'getTemplateForStatus', [
            'status' => $status,
            'order_id' => $order->id ?? 'N/A',
        ], 'Determining template');

        switch ($status) {
            case SSL_STATUS_AWAITING:
            case SSL_STATUS_DRAFT:
            case 'awaiting configuration':
            case 'draft':
                return self::applyCert($params, $order, $cert);

            case SSL_STATUS_PENDING:
            case 'pending':
            case 'processing':
                return self::message($params, $order, $cert);

            case SSL_STATUS_COMPLETE:
            case 'complete':
            case 'active':
            case 'issued':
                return self::complete($params, $order, $cert);

            case SSL_STATUS_REISSUE:
            case 'reissue':
                return self::reissue($params, $order, $cert);

            case SSL_STATUS_CANCELLED:
            case SSL_STATUS_REVOKED:
            case SSL_STATUS_EXPIRED:
            case 'cancelled':
            case 'canceled':
            case 'revoked':
            case 'expired':
                return self::cancelled($params, $order, $cert);

            default:
                // Check if it's a reissue scenario
                $configdata = json_decode($order->configdata, true) ?: [];
                if (!empty($configdata['replaceTimes'])) {
                    return self::reissue($params, $order, $cert);
                }
                // Default to apply cert for unknown status
                return self::applyCert($params, $order, $cert);
        }
    }
}