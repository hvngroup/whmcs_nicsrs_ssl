<?php
/**
 * NicSRS SSL Module - Certificate Functions
 * Utility functions for certificate operations
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace nicsrsSSL;

use Exception;
use ZipArchive;

class CertificateFunc
{
    /**
     * Get certificate type attributes
     */
    public static function getCertAttributes(?string $certType = null, ?string $key = null)
    {
        $types = CERT_TYPES;

        if ($certType === null) {
            return $types;
        }

        if (!isset($types[$certType])) {
            return null;
        }

        if ($key === null) {
            return $types[$certType];
        }

        return $types[$certType][$key] ?? null;
    }

    /**
     * Get certificate attributes dropdown for module config
     */
    public static function getCertAttributesDropdown(): string
    {
        $options = [];
        foreach (CERT_TYPES as $code => $cert) {
            $options[] = $cert['name'];
        }
        return implode(',', $options);
    }

    /**
     * Get certificate code by name
     */
    public static function getCertCodeByName(string $name): ?string
    {
        foreach (CERT_TYPES as $code => $cert) {
            if ($cert['name'] === $name) {
                return $code;
            }
        }
        return null;
    }

    /**
     * Generate CSR and Private Key
     */
    public static function generateCSR(array $data): array
    {
        $dn = [];

        // Common Name (required)
        if (!empty($data['cn'])) {
            $dn['commonName'] = $data['cn'];
        } elseif (!empty($data['commonName'])) {
            $dn['commonName'] = $data['commonName'];
        }

        // Organization
        if (!empty($data['org'])) {
            $dn['organizationName'] = $data['org'];
        } elseif (!empty($data['organization'])) {
            $dn['organizationName'] = $data['organization'];
        }

        // Organizational Unit
        if (!empty($data['ou'])) {
            $dn['organizationalUnitName'] = $data['ou'];
        } elseif (!empty($data['organizationalUnit'])) {
            $dn['organizationalUnitName'] = $data['organizationalUnit'];
        }

        // Locality (City)
        if (!empty($data['city'])) {
            $dn['localityName'] = $data['city'];
        } elseif (!empty($data['locality'])) {
            $dn['localityName'] = $data['locality'];
        }

        // State/Province
        if (!empty($data['state'])) {
            $dn['stateOrProvinceName'] = $data['state'];
        }

        // Country (2-letter code)
        if (!empty($data['country'])) {
            $dn['countryName'] = strtoupper(substr($data['country'], 0, 2));
        }

        // Email
        if (!empty($data['email'])) {
            $dn['emailAddress'] = $data['email'];
        }

        // Remove empty values
        $dn = array_filter($dn, function($v) {
            return !empty($v);
        });

        // Validate required field
        if (empty($dn['commonName'])) {
            throw new Exception('Common Name (domain) is required for CSR generation');
        }

        // Configuration
        $config = DEFAULT_CSR_CONFIG;

        // Generate private key
        $privateKey = openssl_pkey_new($config);
        if (!$privateKey) {
            throw new Exception('Failed to generate private key: ' . openssl_error_string());
        }

        // Generate CSR
        $csr = openssl_csr_new($dn, $privateKey, $config);
        if (!$csr) {
            throw new Exception('Failed to generate CSR: ' . openssl_error_string());
        }

        // Export CSR
        openssl_csr_export($csr, $csrOut);

        // Export private key
        openssl_pkey_export($privateKey, $privateKeyOut);

        return [
            'csr' => $csrOut,
            'privateKey' => $privateKeyOut,
            'dn' => $dn,
        ];
    }

    /**
     * Decode and validate CSR
     */
    public static function decodeCSR(string $csr): array
    {
        $csr = trim($csr);

        // Validate CSR format
        if (strpos($csr, '-----BEGIN CERTIFICATE REQUEST-----') === false &&
            strpos($csr, '-----BEGIN NEW CERTIFICATE REQUEST-----') === false) {
            throw new Exception('Invalid CSR format');
        }

        // Parse CSR
        $csrResource = openssl_csr_get_subject($csr, false);

        if (!$csrResource) {
            throw new Exception('Failed to parse CSR: ' . openssl_error_string());
        }

        // Get public key info
        $pubKeyResource = openssl_csr_get_public_key($csr);
        $pubKeyDetails = openssl_pkey_get_details($pubKeyResource);

        $result = [
            'valid' => true,
            'commonName' => $csrResource['CN'] ?? '',
            'organization' => $csrResource['O'] ?? '',
            'organizationalUnit' => $csrResource['OU'] ?? '',
            'locality' => $csrResource['L'] ?? '',
            'state' => $csrResource['ST'] ?? '',
            'country' => $csrResource['C'] ?? '',
            'email' => $csrResource['emailAddress'] ?? '',
            'keySize' => $pubKeyDetails['bits'] ?? 0,
            'keyType' => self::getKeyTypeName($pubKeyDetails['type'] ?? 0),
        ];

        // Extract domain from common name
        $result['domain'] = $result['commonName'];

        return $result;
    }

    /**
     * Get key type name
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

    /**
     * Create certificate download ZIP
     */
    public static function createCertificateZip(array $certData, string $format = 'all'): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'ssl_cert_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE) !== true) {
            throw new Exception('Failed to create ZIP file');
        }

        $domain = $certData['domain'] ?? 'certificate';
        $safeDomain = preg_replace('/[^a-zA-Z0-9.-]/', '_', $domain);

        // Get certificate data
        $certificate = $certData['certificate'] ?? '';
        $caCertificate = $certData['caCertificate'] ?? '';
        $privateKey = $certData['privateKey'] ?? '';
        $pkcs12 = $certData['pkcs12'] ?? '';
        $jks = $certData['jks'] ?? '';
        $pkcsPass = $certData['pkcsPass'] ?? '';
        $jksPass = $certData['jksPass'] ?? '';

        switch ($format) {
            case 'apache':
                // Apache format: .crt, .ca-bundle, .key
                if ($certificate) {
                    $zip->addFromString("{$safeDomain}.crt", $certificate);
                }
                if ($caCertificate) {
                    $zip->addFromString("{$safeDomain}.ca-bundle", $caCertificate);
                }
                if ($privateKey) {
                    $zip->addFromString("{$safeDomain}.key", $privateKey);
                }
                break;

            case 'nginx':
                // Nginx format: combined .pem
                $pem = $certificate . "\n" . $caCertificate;
                $zip->addFromString("{$safeDomain}.pem", $pem);
                if ($privateKey) {
                    $zip->addFromString("{$safeDomain}.key", $privateKey);
                }
                break;

            case 'iis':
                // IIS format: .p12/.pfx
                if ($pkcs12) {
                    $zip->addFromString("{$safeDomain}.p12", base64_decode($pkcs12));
                    if ($pkcsPass) {
                        $zip->addFromString("password.txt", "PKCS12 Password: {$pkcsPass}");
                    }
                }
                break;

            case 'tomcat':
                // Tomcat format: .jks
                if ($jks) {
                    $zip->addFromString("{$safeDomain}.jks", base64_decode($jks));
                    if ($jksPass) {
                        $zip->addFromString("password.txt", "JKS Password: {$jksPass}");
                    }
                }
                break;

            case 'all':
            default:
                // All formats
                // Apache/Standard
                if ($certificate) {
                    $zip->addFromString("apache/{$safeDomain}.crt", $certificate);
                }
                if ($caCertificate) {
                    $zip->addFromString("apache/{$safeDomain}.ca-bundle", $caCertificate);
                }
                if ($privateKey) {
                    $zip->addFromString("apache/{$safeDomain}.key", $privateKey);
                }

                // Nginx
                $pem = $certificate . "\n" . $caCertificate;
                $zip->addFromString("nginx/{$safeDomain}.pem", $pem);
                if ($privateKey) {
                    $zip->addFromString("nginx/{$safeDomain}.key", $privateKey);
                }

                // IIS
                if ($pkcs12) {
                    $zip->addFromString("iis/{$safeDomain}.p12", base64_decode($pkcs12));
                }

                // Tomcat
                if ($jks) {
                    $zip->addFromString("tomcat/{$safeDomain}.jks", base64_decode($jks));
                }

                // Passwords
                $passwords = [];
                if ($pkcsPass) {
                    $passwords[] = "PKCS12/IIS Password: {$pkcsPass}";
                }
                if ($jksPass) {
                    $passwords[] = "JKS/Tomcat Password: {$jksPass}";
                }
                if (!empty($passwords)) {
                    $zip->addFromString("passwords.txt", implode("\n", $passwords));
                }

                // Readme
                $readme = self::generateReadme($safeDomain);
                $zip->addFromString("README.txt", $readme);
                break;
        }

        $zip->close();

        // Read and return base64 encoded content
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return base64_encode($content);
    }

    /**
     * Generate README content for certificate ZIP
     */
    private static function generateReadme(string $domain): string
    {
        return <<<README
SSL Certificate Installation Guide
===================================

Domain: {$domain}
Generated: {date('Y-m-d H:i:s')}

This archive contains your SSL certificate in multiple formats.

APACHE/STANDARD FORMAT (apache/)
--------------------------------
- {$domain}.crt - Your SSL Certificate
- {$domain}.ca-bundle - CA Bundle (Intermediate Certificates)  
- {$domain}.key - Private Key

Installation:
1. Upload files to your server
2. Update Apache configuration with paths to these files
3. Restart Apache

NGINX FORMAT (nginx/)
---------------------
- {$domain}.pem - Combined certificate (cert + CA bundle)
- {$domain}.key - Private Key

Installation:
1. Upload files to your server
2. Update Nginx configuration with paths to these files
3. Restart Nginx

IIS FORMAT (iis/)
-----------------
- {$domain}.p12 - PKCS#12 file containing certificate and key

Installation:
1. Import .p12 file via IIS Manager or MMC
2. Enter password from passwords.txt when prompted
3. Bind certificate to your website

TOMCAT FORMAT (tomcat/)
-----------------------
- {$domain}.jks - Java KeyStore file

Installation:
1. Copy .jks file to your Tomcat conf directory
2. Update server.xml with keystore path and password
3. Restart Tomcat

IMPORTANT: Keep your private key secure and never share it!

Support: https://hvn.vn
README;
    }

    /**
     * Validate domain format
     */
    public static function validateDomain(string $domain): bool
    {
        // Remove wildcard prefix if present
        $domain = preg_replace('/^\*\./', '', $domain);

        // Check for valid domain format
        if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return true;
        }

        // Check for IP address (for IP-enabled certificates)
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return true;
        }

        return false;
    }

    /**
     * Check if domain is wildcard
     */
    public static function isWildcardDomain(string $domain): bool
    {
        return strpos($domain, '*.') === 0;
    }

    /**
     * Get base domain from wildcard
     */
    public static function getBaseDomain(string $domain): string
    {
        return preg_replace('/^\*\./', '', $domain);
    }

    /**
     * Get DCV email options for a domain
     */
    public static function getDCVEmailOptions(string $domain): array
    {
        $baseDomain = self::getBaseDomain($domain);

        $prefixes = ['admin', 'administrator', 'hostmaster', 'webmaster', 'postmaster'];
        $emails = [];

        foreach ($prefixes as $prefix) {
            $emails[] = "{$prefix}@{$baseDomain}";
        }

        return $emails;
    }

    /**
     * Format date for display
     */
    public static function formatDate(?string $date, string $format = 'Y-m-d'): string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return 'N/A';
        }

        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : 'N/A';
    }

    /**
     * Calculate days until expiry
     */
    public static function getDaysUntilExpiry(?string $expiryDate): ?int
    {
        if (empty($expiryDate)) {
            return null;
        }

        $expiry = strtotime($expiryDate);
        $now = time();

        if (!$expiry) {
            return null;
        }

        return (int) floor(($expiry - $now) / 86400);
    }

    /**
     * Get status badge class
     */
    public static function getStatusClass(string $status): string
    {
        return STATUS_CLASSES[$status] ?? 'default';
    }

    /**
     * Load language file
     */
    public static function loadLanguage(string $language = 'english'): array
    {
        $langPath = NICSRS_SSL_PATH . 'lang' . DS;
        $langFile = $langPath . strtolower($language) . '.php';

        // Fallback to English
        if (!file_exists($langFile)) {
            $langFile = $langPath . 'english.php';
        }

        if (!file_exists($langFile)) {
            return [];
        }

        $_LANG = [];
        include $langFile;

        return $_LANG;
    }

    /**
     * Get client's preferred language
     */
    public static function getClientLanguage(int $userId): string
    {
        try {
            $client = \WHMCS\Database\Capsule::table('tblclients')
                ->where('id', $userId)
                ->first();

            return $client->language ?? 'english';
        } catch (Exception $e) {
            return 'english';
        }
    }
}