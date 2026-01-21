<?php
/**
 * DCV Helper Class
 * Handles DCV method display names and icons
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Helper;

class DcvHelper
{
    /**
     * DCV Method mapping - API values to display info
     * API returns: CNAME_CSR_HASH, HTTP_CSR_HASH, DNS_CSR_HASH, EMAIL
     */
    private const METHOD_MAP = [
        'CNAME_CSR_HASH' => [
            'name' => 'DNS CNAME',
            'type' => 'dns',
            'icon' => 'fa-server',
            'color' => 'info',
            'description' => 'Add a CNAME record to your DNS'
        ],
        'HTTP_CSR_HASH' => [
            'name' => 'HTTP File',
            'type' => 'http',
            'icon' => 'fa-file-text-o',
            'color' => 'warning',
            'description' => 'Upload a validation file to your web server'
        ],
        'DNS_CSR_HASH' => [
            'name' => 'DNS TXT',
            'type' => 'dns',
            'icon' => 'fa-list-alt',
            'color' => 'info',
            'description' => 'Add a TXT record to your DNS'
        ],
        'EMAIL' => [
            'name' => 'Email',
            'type' => 'email',
            'icon' => 'fa-envelope',
            'color' => 'primary',
            'description' => 'Verify via approval email'
        ],
        // Legacy fallbacks
        'CNAME' => [
            'name' => 'DNS CNAME',
            'type' => 'dns',
            'icon' => 'fa-server',
            'color' => 'info',
            'description' => 'Add a CNAME record to your DNS'
        ],
        'HTTP' => [
            'name' => 'HTTP File',
            'type' => 'http',
            'icon' => 'fa-file-text-o',
            'color' => 'warning',
            'description' => 'Upload a validation file to your web server'
        ],
        'DNS' => [
            'name' => 'DNS TXT',
            'type' => 'dns',
            'icon' => 'fa-list-alt',
            'color' => 'info',
            'description' => 'Add a TXT record to your DNS'
        ],
    ];

    /**
     * Get display name for DCV method
     * 
     * @param string $method API method value (e.g., CNAME_CSR_HASH)
     * @return string Display name (e.g., DNS CNAME)
     */
    public static function getDisplayName(string $method): string
    {
        $method = strtoupper(trim($method));
        return self::METHOD_MAP[$method]['name'] ?? $method;
    }

    /**
     * Get icon class for DCV method
     * 
     * @param string $method API method value
     * @return string FontAwesome icon class
     */
    public static function getIcon(string $method): string
    {
        $method = strtoupper(trim($method));
        return self::METHOD_MAP[$method]['icon'] ?? 'fa-question-circle';
    }

    /**
     * Get color class for DCV method
     * 
     * @param string $method API method value
     * @return string Bootstrap color class (info, warning, primary, etc.)
     */
    public static function getColor(string $method): string
    {
        $method = strtoupper(trim($method));
        return self::METHOD_MAP[$method]['color'] ?? 'default';
    }

    /**
     * Get DCV method type
     * 
     * @param string $method API method value
     * @return string Type (dns, http, email)
     */
    public static function getType(string $method): string
    {
        $method = strtoupper(trim($method));
        return self::METHOD_MAP[$method]['type'] ?? 'unknown';
    }

    /**
     * Get description for DCV method
     * 
     * @param string $method API method value
     * @return string Description
     */
    public static function getDescription(string $method): string
    {
        $method = strtoupper(trim($method));
        return self::METHOD_MAP[$method]['description'] ?? 'Unknown validation method';
    }

    /**
     * Get full info array for DCV method
     * 
     * @param string $method API method value
     * @return array Full method info
     */
    public static function getMethodInfo(string $method): array
    {
        $method = strtoupper(trim($method));
        return self::METHOD_MAP[$method] ?? [
            'name' => $method,
            'type' => 'unknown',
            'icon' => 'fa-question-circle',
            'color' => 'default',
            'description' => 'Unknown validation method'
        ];
    }

    /**
     * Render DCV method badge HTML
     * 
     * @param string $method API method value
     * @return string HTML badge
     */
    public static function renderBadge(string $method): string
    {
        $info = self::getMethodInfo($method);
        return sprintf(
            '<span class="label label-%s" title="%s"><i class="fa %s"></i> %s</span>',
            htmlspecialchars($info['color']),
            htmlspecialchars($info['description']),
            htmlspecialchars($info['icon']),
            htmlspecialchars($info['name'])
        );
    }

    /**
     * Check if method requires DNS configuration
     * 
     * @param string $method API method value
     * @return bool
     */
    public static function isDnsMethod(string $method): bool
    {
        return self::getType($method) === 'dns';
    }

    /**
     * Check if method requires HTTP file upload
     * 
     * @param string $method API method value
     * @return bool
     */
    public static function isHttpMethod(string $method): bool
    {
        return self::getType($method) === 'http';
    }

    /**
     * Check if method is email-based
     * 
     * @param string $method API method value
     * @return bool
     */
    public static function isEmailMethod(string $method): bool
    {
        return self::getType($method) === 'email';
    }

    /**
     * Get all available DCV methods
     * 
     * @return array
     */
    public static function getAllMethods(): array
    {
        return [
            'CNAME_CSR_HASH' => self::METHOD_MAP['CNAME_CSR_HASH'],
            'HTTP_CSR_HASH' => self::METHOD_MAP['HTTP_CSR_HASH'],
            'DNS_CSR_HASH' => self::METHOD_MAP['DNS_CSR_HASH'],
            'EMAIL' => self::METHOD_MAP['EMAIL'],
        ];
    }

    /**
     * Normalize DCV method from various formats
     * 
     * @param string $method Input method (could be legacy format)
     * @return string Normalized method name
     */
    public static function normalize(string $method): string
    {
        $method = strtoupper(trim($method));
        
        // Map legacy formats to current
        $legacyMap = [
            'CNAME' => 'CNAME_CSR_HASH',
            'HTTP' => 'HTTP_CSR_HASH',
            'DNS' => 'DNS_CSR_HASH',
            'HTTPS' => 'HTTP_CSR_HASH',
            'FILE' => 'HTTP_CSR_HASH',
        ];
        
        return $legacyMap[$method] ?? $method;
    }
}