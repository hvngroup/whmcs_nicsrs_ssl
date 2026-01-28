<?php
/**
 * NicSRS SSL Module - Page Controller
 * Handles page rendering based on certificate status
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace nicsrsSSL;

use Exception;

class PageController
{
    /**
     * Render page based on order status
     */
    public static function index(array $params): array
    {
        try {
            // Get or create order
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                // Create order if doesn't exist
                OrderRepository::create([
                    'userid' => $params['userid'],
                    'serviceid' => $params['serviceid'],
                    'module' => 'nicsrs_ssl',
                    'certtype' => $params['configoption1'] ?? '',
                    'status' => SSL_STATUS_AWAITING,
                ]);
                $order = OrderRepository::getByServiceId($params['serviceid']);
            }

            // Get certificate type configuration
            $cert = self::getCertConfig($params);

            // Route based on status
            switch ($order->status) {
                case SSL_STATUS_AWAITING:
                case SSL_STATUS_DRAFT:
                    return self::renderApplyCert($params, $order, $cert);

                case SSL_STATUS_PENDING:
                case SSL_STATUS_PROCESSING:
                    return self::renderPending($params, $order, $cert);

                case SSL_STATUS_COMPLETE:
                case SSL_STATUS_ISSUED:
                    return self::renderComplete($params, $order, $cert);

                case SSL_STATUS_REISSUE:
                    return self::renderPending($params, $order, $cert);

                case SSL_STATUS_CANCELLED:
                case SSL_STATUS_REVOKED:
                case SSL_STATUS_EXPIRED:
                    return self::renderCancelled($params, $order, $cert);

                default:
                    return self::renderApplyCert($params, $order, $cert);
            }
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'PageController::index', $params, $e->getMessage());
            return TemplateHelper::error($params, $e->getMessage());
        }
    }

    /**
     * Render apply certificate page
     */
    public static function renderApplyCert(array $params, object $order, array $cert): array
    {
        return TemplateHelper::applyCert($params, $order, $cert);
    }

    /**
     * Render pending page with DCV status
     */
    public static function renderPending(array $params, object $order, array $cert): array
    {
        // Try to refresh status from API
        $collectData = [];
        
        if (!empty($order->remoteid)) {
            try {
                $response = ApiService::collect($params, $order->remoteid);
                $parsed = ApiService::parseResponse($response);
                
                if ($parsed['success'] && $parsed['data']) {
                    $collectData = (array) $parsed['data'];
                    
                    // Update local data
                    $configdata = json_decode($order->configdata, true) ?: [];
                    $configdata['applyReturn'] = array_merge(
                        $configdata['applyReturn'] ?? [],
                        $collectData
                    );
                    $configdata['lastRefresh'] = date('Y-m-d H:i:s');
                    
                    // Check if status changed to complete
                    $apiStatus = strtoupper($parsed['status'] ?? '');
                    if (in_array($apiStatus, ['COMPLETE', 'ISSUED'])) {
                        OrderRepository::update($order->id, [
                            'status' => SSL_STATUS_COMPLETE,
                            'configdata' => json_encode($configdata),
                            'completiondate' => date('Y-m-d H:i:s'),
                        ]);
                        $order->status = SSL_STATUS_COMPLETE;
                        return self::renderComplete($params, $order, $cert, $collectData);
                    }
                    
                    OrderRepository::updateConfigData($order->id, $configdata);
                }
            } catch (Exception $e) {
                logModuleCall('nicsrs_ssl', 'renderPending::collect', $params, $e->getMessage());
            }
        }

        return TemplateHelper::pending($params, $order, $cert, $collectData);
    }

    /**
     * Render complete page with certificate info
     */
    public static function renderComplete(array $params, object $order, array $cert, array $collectData = []): array
    {
        // Refresh if no collect data and we have remote ID
        if (empty($collectData) && !empty($order->remoteid)) {
            try {
                $response = ApiService::collect($params, $order->remoteid);
                $parsed = ApiService::parseResponse($response);
                
                if ($parsed['success'] && $parsed['data']) {
                    $collectData = (array) $parsed['data'];
                    
                    // Update local data
                    $configdata = json_decode($order->configdata, true) ?: [];
                    $configdata['applyReturn'] = array_merge(
                        $configdata['applyReturn'] ?? [],
                        $collectData
                    );
                    $configdata['lastRefresh'] = date('Y-m-d H:i:s');
                    OrderRepository::updateConfigData($order->id, $configdata);
                    
                    // Reload order
                    $order = OrderRepository::getById($order->id);
                }
            } catch (Exception $e) {
                logModuleCall('nicsrs_ssl', 'renderComplete::collect', $params, $e->getMessage());
            }
        }

        return TemplateHelper::complete($params, $order, $cert, $collectData);
    }

    /**
     * Render cancelled/revoked page
     */
    public static function renderCancelled(array $params, object $order, array $cert): array
    {
        $baseVars = TemplateHelper::getBaseVars($params);
        $configdata = json_decode($order->configdata, true) ?: [];

        return [
            'templatefile' => 'cancelled',
            'vars' => array_merge($baseVars, [
                'order' => $order,
                'configdata' => $configdata,
                'cert' => $cert,
                'productCode' => $cert['name'] ?? '',
                'status' => $order->status,
                'statusClass' => CertificateFunc::getStatusClass($order->status),
                'canRenew' => true,
            ]),
        ];
    }

    /**
     * Render manage page
     */
    public static function manage(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return TemplateHelper::error($params, 'Order not found');
            }

            $cert = self::getCertConfig($params);

            return TemplateHelper::manage($params, $order, $cert);
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'PageController::manage', $params, $e->getMessage());
            return TemplateHelper::error($params, $e->getMessage());
        }
    }

    /**
     * Render reissue page
     */
    public static function reissue(array $params): array
    {
        try {
            $order = OrderRepository::getByServiceId($params['serviceid']);
            
            if (!$order) {
                return TemplateHelper::error($params, 'Order not found');
            }

            // Check if reissue is allowed
            if (!in_array($order->status, [SSL_STATUS_COMPLETE, SSL_STATUS_ISSUED])) {
                return TemplateHelper::error(
                    $params, 
                    'Certificate must be issued before it can be reissued'
                );
            }

            $cert = self::getCertConfig($params);

            return TemplateHelper::reissue($params, $order, $cert);
        } catch (Exception $e) {
            logModuleCall('nicsrs_ssl', 'PageController::reissue', $params, $e->getMessage());
            return TemplateHelper::error($params, $e->getMessage());
        }
    }

    /**
     * Get certificate configuration from product
     */
    private static function getCertConfig(array $params): array
    {
        $certIdentifier = $params['configoption1'] ?? '';
        
        // Normalize to code (handles both name and code input)
        $certCode = CertificateFunc::normalizeToCode($certIdentifier);
        
        if ($certCode) {
            $cert = CertificateFunc::getCertAttributes($certCode);
            if ($cert) {
                $cert['code'] = $certCode;
                return $cert;
            }
        }

        // Fallback: Try to get from database by name
        if ($certIdentifier) {
            try {
                $product = \WHMCS\Database\Capsule::table('mod_nicsrs_products')
                    ->where('product_name', $certIdentifier)
                    ->orWhere('product_code', $certIdentifier)
                    ->first();

                if ($product) {
                    return [
                        'code' => $product->product_code,
                        'name' => $product->product_name,
                        'vendor' => $product->vendor ?? 'Unknown',
                        'sslType' => 'website_ssl',
                        'sslValidationType' => $product->validation_type ?? 'dv',
                        'isMultiDomain' => (bool) ($product->support_san ?? false),
                        'isWildcard' => (bool) ($product->support_wildcard ?? false),
                        'supportNormal' => true,
                        'supportIp' => false,
                        'supportWild' => (bool) ($product->support_wildcard ?? false),
                        'supportHttps' => true,
                        'maxDomains' => (int) ($product->max_domains ?? 1),
                    ];
                }
            } catch (\Exception $e) {
                // Continue to default
            }
        }

        // Default configuration
        return [
            'code' => 'unknown',
            'name' => $certIdentifier ?: 'SSL Certificate',
            'vendor' => 'Unknown',
            'sslType' => 'website_ssl',
            'sslValidationType' => 'dv',
            'isMultiDomain' => false,
            'isWildcard' => false,
            'supportNormal' => true,
            'supportIp' => false,
            'supportWild' => false,
            'supportHttps' => true,
            'maxDomains' => 1,
        ];
    }
}