<?php
/**
 * NicSRS SSL Module - API Service
 * Handles all communication with NicSRS API
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace nicsrsSSL;

use WHMCS\Database\Capsule;
use Exception;

class ApiService
{
    /**
     * API endpoints mapping
     */
    private static $endpoints = [
        'validate'      => '/validate',
        'place'         => '/place',
        'collect'       => '/collect',
        'cancel'        => '/cancel',
        'revoke'        => '/revoke',
        'reissue'       => '/reissue',
        'renew'         => '/renew',
        'replace'       => '/replace',
        'updateDCV'     => '/updateDCV',
        'batchUpdateDCV'=> '/batchUpdateDCV',
        'DCVemail'      => '/DCVemail',
        'removeMdc'     => '/removeMdcDomain',
        'productList'   => '/productList',
    ];

    /**
     * Get API token with fallback to Admin Addon settings
     * 
     * @param array $params Module parameters
     * @return string API token
     * @throws Exception If no API token configured
     */
    public static function getApiToken(array $params = []): string
    {
        // 1. Check product-level API token first (configoption2)
        if (!empty($params['configoption2'])) {
            return trim($params['configoption2']);
        }

        // 2. Try to get from product config via service
        if (!empty($params['serviceid'])) {
            $service = Capsule::table('tblhosting')
                ->where('id', $params['serviceid'])
                ->first();

            if ($service) {
                $product = Capsule::table('tblproducts')
                    ->where('id', $service->packageid)
                    ->first();

                if ($product && !empty($product->configoption2)) {
                    return trim($product->configoption2);
                }
            }
        }

        // 3. Fallback to Admin Addon shared settings
        $setting = Capsule::table('mod_nicsrs_settings')
            ->where('setting_key', 'api_token')
            ->first();

        if ($setting && !empty($setting->setting_value)) {
            return trim($setting->setting_value);
        }

        throw new Exception('API token not configured. Please configure in product settings or Admin Addon.');
    }

    /**
     * Make API call to NicSRS
     * 
     * @param string $endpoint Endpoint name
     * @param array $data Request data
     * @return object|null Response object
     * @throws Exception On API error
     */
    public static function call(string $endpoint, array $data)
    {
        $url = self::getEndpointUrl($endpoint);
        
        if (empty($url)) {
            throw new Exception("Unknown API endpoint: {$endpoint}");
        }

        // Log the request (without sensitive data)
        $logData = $data;
        if (isset($logData['api_token'])) {
            $logData['api_token'] = substr($logData['api_token'], 0, 8) . '***';
        }
        
        logModuleCall('nicsrs_ssl', $endpoint, $logData, '');

        try {
            $response = self::curlRequest($url, $data);
            
            // Log the response
            logModuleCall('nicsrs_ssl', $endpoint . '_response', $logData, $response);

            return $response;
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', $endpoint . '_error', $logData, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get full endpoint URL
     */
    private static function getEndpointUrl(string $endpoint): string
    {
        if (!isset(self::$endpoints[$endpoint])) {
            return '';
        }

        return NICSRS_API_URL . self::$endpoints[$endpoint];
    }

    /**
     * Execute cURL request
     */
    private static function curlRequest(string $url, array $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: {$httpCode}");
        }

        $decoded = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }

        return $decoded;
    }

    // ==========================================
    // Convenience Methods for Common Operations
    // ==========================================

    /**
     * Validate certificate request
     */
    public static function validate(array $params, array $requestData): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('validate', array_merge($requestData, [
            'api_token' => $apiToken,
        ]));
    }

    /**
     * Place certificate order
     */
    public static function place(array $params, array $requestData): object
    {
        $apiToken = self::getApiToken($params);

        $data = [
            'api_token' => $apiToken,
            'params' => json_encode($requestData),
        ];

        return self::call('place', $data);
    }

    /**
     * Collect certificate status and details
     */
    public static function collect(array $params, string $certId): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('collect', [
            'api_token' => $apiToken,
            'certId' => $certId,
        ]);
    }

    /**
     * Cancel certificate order
     */
    public static function cancel(array $params, string $certId, string $reason = ''): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('cancel', [
            'api_token' => $apiToken,
            'certId' => $certId,
            'reason' => $reason ?: 'Customer requested cancellation',
        ]);
    }

    /**
     * Revoke certificate
     */
    public static function revoke(array $params, string $certId, string $reason = ''): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('revoke', [
            'api_token' => $apiToken,
            'certId' => $certId,
            'reason' => $reason ?: 'Customer requested revocation',
        ]);
    }

    /**
     * Reissue certificate
     */
    public static function reissue(array $params, string $certId, array $requestData = []): object
    {
        $apiToken = self::getApiToken($params);

        $data = [
            'api_token' => $apiToken,
            'certId' => $certId,
        ];

        if (!empty($requestData)) {
            $data['params'] = json_encode($requestData);
        }

        return self::call('reissue', $data);
    }

    /**
     * Renew certificate
     */
    public static function renew(array $params, string $certId): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('renew', [
            'api_token' => $apiToken,
            'certId' => $certId,
        ]);
    }

    /**
     * Update DCV method for domains
     */
    public static function batchUpdateDCV(array $params, string $certId, array $domainInfo): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('batchUpdateDCV', [
            'api_token' => $apiToken,
            'certId' => $certId,
            'domainInfo' => json_encode($domainInfo),
        ]);
    }

    /**
     * Resend DCV email
     */
    public static function resendDCVEmail(array $params, string $certId, string $domain, string $email): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('DCVemail', [
            'api_token' => $apiToken,
            'certId' => $certId,
            'domainName' => $domain,
            'dcvEmail' => $email,
        ]);
    }

    /**
     * Remove multi-domain entry
     */
    public static function removeMdcDomain(array $params, string $certId, string $domainName): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('removeMdc', [
            'api_token' => $apiToken,
            'certId' => $certId,
            'domainName' => $domainName,
        ]);
    }

    /**
     * Get product list from API
     */
    public static function getProductList(array $params): object
    {
        $apiToken = self::getApiToken($params);

        return self::call('productList', [
            'api_token' => $apiToken,
        ]);
    }

    /**
     * Test API connection
     */
    public static function testConnection(array $params): bool
    {
        try {
            $result = self::getProductList($params);
            return isset($result->code) && $result->code == 1;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Parse API response and check for errors
     */
    public static function parseResponse(object $response): array
    {
        $code = $response->code ?? -2;
        $message = $response->msg ?? 'Unknown error';
        $status = $response->status ?? $response->certStatus ?? '';
        $data = $response->data ?? null;

        $success = in_array($code, [API_CODE_SUCCESS, API_CODE_PROCESSING]);

        return [
            'success' => $success,
            'code' => $code,
            'message' => $message,
            'status' => $status,
            'data' => $data,
            'isProcessing' => $code === API_CODE_PROCESSING,
        ];
    }
}