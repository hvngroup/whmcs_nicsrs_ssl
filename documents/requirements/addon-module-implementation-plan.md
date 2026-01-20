# NicSRS SSL Admin Addon Module v1.2.0 - Implementation Plan

## Project Overview

| Item | Details |
|------|---------|
| **Project** | NicSRS SSL Admin Addon Module |
| **Version** | 1.2.0 |
| **Type** | WHMCS Addon Module |
| **Author** | HVN GROUP |
| **Estimated Duration** | 2 weeks (50 hours) |
| **Start Date** | TBD |

---

## Scope Definition

### ✅ In Scope (v1.2.0)

| # | Feature | Priority |
|---|---------|----------|
| 1 | Shared API Token configuration (used by Server Module) | P0 |
| 2 | Full API response field mapping from collect_api.txt | P0 |
| 3 | Date format handling (full datetime) | P0 |
| 4 | DCV method name mapping (CNAME_CSR_HASH, etc.) | P0 |
| 5 | Pre-formatted certificate download (JKS, PKCS12) | P1 |
| 6 | Enhanced Order Detail view với new fields | P1 |
| 7 | Vendor tracking display (vendorId, vendorCertId) | P2 |
| 8 | Certificate expiry tracking với due_date | P1 |

### ❌ Out of Scope (Deferred to v1.3.0)

| Feature | Reason |
|---------|--------|
| Tools Section (CAA Checker, CSR Decoder) | Deferred per spec |
| Webhook Integration | Requires NicSRS support |
| Auto-Renewal Automation | Requires extensive testing |
| Bulk Operations | Nice-to-have |

---

## Current Issues Analysis

### Issues từ collect_api.txt Analysis

| # | Issue | Current Code | Actual API | Impact |
|---|-------|--------------|------------|--------|
| 1 | Date format | `Y-m-d` | `Y-m-d H:i:s` | Parse errors, display issues |
| 2 | DCV methods | `CNAME`, `HTTP` | `CNAME_CSR_HASH`, `HTTP_CSR_HASH` | Wrong method display |
| 3 | Status case | `complete` | `COMPLETE` | Status mismatch |
| 4 | Missing fields | - | `jks`, `pkcs12`, `vendorId`, `dueDate` | Features not available |
| 5 | certStatus | Not handled | Separate from status | Incomplete tracking |

---

## Technical Architecture

### Module Structure (Updated)

```
modules/addons/nicsrs_ssl_admin/
├── nicsrs_ssl_admin.php              # Entry point (updated config)
├── hooks.php                         # WHMCS hooks
│
├── lib/
│   ├── Config/
│   │   └── ApiConfig.php             # [NEW] Shared API configuration
│   │
│   ├── Controller/
│   │   ├── BaseController.php        # Base controller
│   │   ├── DashboardController.php   # Dashboard (updated)
│   │   ├── ProductController.php     # Products
│   │   ├── OrderController.php       # Orders (major update)
│   │   ├── SettingsController.php    # Settings (updated)
│   │   └── ImportController.php      # Import (updated)
│   │
│   ├── Service/
│   │   ├── NicsrsApiService.php      # API client (updated)
│   │   ├── ProductService.php        # Product logic
│   │   ├── OrderService.php          # Order logic (updated)
│   │   ├── CertificateService.php    # [NEW] Certificate operations
│   │   ├── SyncService.php           # [NEW] API sync service
│   │   └── ActivityLogger.php        # Audit logging
│   │
│   ├── DTO/
│   │   ├── CertificateData.php       # [NEW] Certificate DTO
│   │   ├── CollectResponse.php       # [NEW] Collect API response
│   │   └── DcvInfo.php               # [NEW] DCV information
│   │
│   └── Helper/
│       ├── ViewHelper.php            # Template helpers (updated)
│       ├── DateHelper.php            # [NEW] Date parsing
│       ├── DcvHelper.php             # [NEW] DCV method mapping
│       └── Pagination.php            # Pagination
│
├── templates/
│   ├── dashboard.php                 # Dashboard (updated)
│   ├── products/
│   │   └── list.php
│   ├── orders/
│   │   ├── list.php                  # Orders list (updated)
│   │   └── detail.php                # Order detail (major update)
│   ├── settings.php                  # Settings (updated)
│   └── partials/
│       ├── _certificate_download.php # [NEW] Download options
│       ├── _dcv_status.php           # [NEW] DCV display
│       └── _vendor_info.php          # [NEW] Vendor tracking
│
├── assets/
│   ├── css/
│   │   └── admin.css                 # Styles (updated)
│   └── js/
│       └── admin.js                  # JavaScript (updated)
│
└── lang/
    ├── english.php                   # English (updated)
    └── vietnamese.php                # Vietnamese (updated)
```

---

## Database Integration

### Using Extended nicsrs_sslorders Table

Addon Module sẽ sử dụng các columns mới từ Server Module migration:

| Column | Usage in Addon |
|--------|----------------|
| `vendor_id` | Display in order detail |
| `vendor_cert_id` | Display in order detail |
| `cert_status` | Filter, display |
| `begin_date` | Display, export |
| `end_date` | Expiry tracking, filter |
| `due_date` | Renewal reminders |
| `last_sync` | Show sync status |
| `certificate` | Download, display |
| `ca_certificate` | Download |
| `private_key` | Download |
| `jks_data` | Download JKS |
| `pkcs12_data` | Download PKCS12 |
| `jks_password` | Display with download |
| `pkcs12_password` | Display with download |
| `dcv_file_name` | DCV display |
| `dcv_file_content` | DCV display |
| `dcv_dns_host` | DCV display |
| `dcv_dns_value` | DCV display |
| `dcv_dns_type` | DCV display |

### Settings Table Update

```sql
-- Add API token to settings if not exists
INSERT INTO `mod_nicsrs_settings` (`setting_key`, `setting_value`, `setting_type`) 
VALUES ('api_token', '', 'password')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
```

---

## API Response Mapping

### Actual Response from /collect (collect_api.txt)

```php
[
    'code' => 1,
    'status' => 'COMPLETE',
    'certStatus' => 'COMPLETE',
    'data' => [
        'beginDate' => '2026-01-19 08:00:00',
        'endDate' => '2027-02-20 07:59:59',
        'dueDate' => '2028-01-19 00:00:00',
        'certificate' => '-----BEGIN CERTIFICATE-----...',
        'caCertificate' => '-----BEGIN CERTIFICATE-----...',
        'rsaPrivateKey' => '',
        'jks' => 'base64_jks_data',
        'pkcs12' => 'base64_p12_data',
        'jksPass' => 'U0DHfMAlZXk7DHQP',
        'pkcsPass' => 'MojZVyvbiMO65dC9',
        'vendorId' => '2771240592',
        'vendorCertId' => '39831817562',
        'DCVfileName' => '148ECC23D64F50CDCCA4F0DAF72A9A4B.txt',
        'DCVfileContent' => 'hash_content...',
        'DCVdnsHost' => '_148ecc23d64f50cdcca4f0daf72a9a4b',
        'DCVdnsValue' => 'cname.sectigo.com',
        'DCVdnsType' => 'CNAME',
        'dcvList' => [
            [
                'dcvMethod' => 'CNAME_CSR_HASH',
                'is_verify' => 'verified',
                'domainName' => 'example.com'
            ]
        ]
    ]
]
```

### DCV Method Mapping

```php
// lib/Helper/DcvHelper.php
class DcvHelper
{
    public const METHOD_MAP = [
        'CNAME_CSR_HASH' => [
            'name' => 'DNS CNAME',
            'type' => 'dns',
            'icon' => 'fa-globe',
        ],
        'HTTP_CSR_HASH' => [
            'name' => 'HTTP File',
            'type' => 'http',
            'icon' => 'fa-file',
        ],
        'DNS_CSR_HASH' => [
            'name' => 'DNS TXT',
            'type' => 'dns',
            'icon' => 'fa-server',
        ],
        'EMAIL' => [
            'name' => 'Email',
            'type' => 'email',
            'icon' => 'fa-envelope',
        ],
    ];
    
    public static function getDisplayName(string $method): string
    {
        return self::METHOD_MAP[$method]['name'] ?? $method;
    }
    
    public static function getType(string $method): string
    {
        return self::METHOD_MAP[$method]['type'] ?? 'unknown';
    }
}
```

---

## Key Components Implementation

### 1. Shared ApiConfig Class

```php
<?php
// lib/Config/ApiConfig.php
namespace NicsrsAdmin\Config;

use WHMCS\Database\Capsule;

class ApiConfig
{
    private static ?string $cachedToken = null;
    
    /**
     * Get API token from addon settings
     */
    public static function getApiToken(): string
    {
        if (self::$cachedToken !== null) {
            return self::$cachedToken;
        }
        
        // Try mod_nicsrs_settings first
        $token = self::getFromSettings();
        if ($token) {
            self::$cachedToken = $token;
            return $token;
        }
        
        // Try tbladdonmodules
        $token = self::getFromModuleConfig();
        if ($token) {
            self::$cachedToken = $token;
            return $token;
        }
        
        throw new \Exception('API Token not configured');
    }
    
    /**
     * Save API token to settings
     */
    public static function saveApiToken(string $token): bool
    {
        try {
            Capsule::table('mod_nicsrs_settings')
                ->updateOrInsert(
                    ['setting_key' => 'api_token'],
                    ['setting_value' => $token, 'setting_type' => 'password']
                );
            
            self::$cachedToken = $token;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private static function getFromSettings(): ?string
    {
        try {
            $setting = Capsule::table('mod_nicsrs_settings')
                ->where('setting_key', 'api_token')
                ->first();
            return $setting->setting_value ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private static function getFromModuleConfig(): ?string
    {
        try {
            $config = Capsule::table('tbladdonmodules')
                ->where('module', 'nicsrs_ssl_admin')
                ->where('setting', 'api_token')
                ->first();
            return $config->value ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public static function clearCache(): void
    {
        self::$cachedToken = null;
    }
}
```

### 2. DateHelper Class

```php
<?php
// lib/Helper/DateHelper.php
namespace NicsrsAdmin\Helper;

class DateHelper
{
    /**
     * Parse API datetime to standard format
     * API returns: "2026-01-19 08:00:00"
     */
    public static function parseDateTime(?string $value): ?string
    {
        if (empty($value)) return null;
        
        $timestamp = strtotime($value);
        if ($timestamp === false) return null;
        
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    /**
     * Parse API date to date only
     */
    public static function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        
        $timestamp = strtotime($value);
        if ($timestamp === false) return null;
        
        return date('Y-m-d', $timestamp);
    }
    
    /**
     * Format for display
     */
    public static function formatDisplay(?string $datetime, string $format = 'M d, Y'): string
    {
        if (empty($datetime)) return '-';
        
        $timestamp = strtotime($datetime);
        if ($timestamp === false) return '-';
        
        return date($format, $timestamp);
    }
    
    /**
     * Format with time for display
     */
    public static function formatDisplayWithTime(?string $datetime): string
    {
        return self::formatDisplay($datetime, 'M d, Y H:i');
    }
    
    /**
     * Check if date is valid (not 0000-00-00)
     */
    public static function isValidDate(?string $date): bool
    {
        if (empty($date)) return false;
        if (strpos($date, '0000-00-00') !== false) return false;
        
        $timestamp = strtotime($date);
        return $timestamp !== false && $timestamp > 0;
    }
    
    /**
     * Get days until expiry
     */
    public static function getDaysUntilExpiry(?string $endDate): ?int
    {
        if (!self::isValidDate($endDate)) return null;
        
        $now = new \DateTime();
        $expiry = new \DateTime($endDate);
        $diff = $now->diff($expiry);
        
        return $diff->invert ? -$diff->days : $diff->days;
    }
}
```

### 3. CertificateData DTO

```php
<?php
// lib/DTO/CertificateData.php
namespace NicsrsAdmin\DTO;

use NicsrsAdmin\Helper\DateHelper;

class CertificateData
{
    public int $code;
    public string $status;
    public ?string $certStatus = null;
    
    // Certificate files
    public ?string $certificate = null;
    public ?string $caCertificate = null;
    public ?string $privateKey = null;
    
    // Pre-formatted certificates
    public ?string $jks = null;
    public ?string $pkcs12 = null;
    public ?string $jksPassword = null;
    public ?string $pkcsPassword = null;
    
    // Dates
    public ?string $beginDate = null;
    public ?string $endDate = null;
    public ?string $dueDate = null;
    
    // Vendor info
    public ?string $vendorId = null;
    public ?string $vendorCertId = null;
    
    // DCV info
    public ?string $dcvFileName = null;
    public ?string $dcvFileContent = null;
    public ?string $dcvFilePath = null;
    public ?string $dcvDnsHost = null;
    public ?string $dcvDnsValue = null;
    public ?string $dcvDnsType = null;
    public array $dcvList = [];
    
    /**
     * Create from API response
     */
    public static function fromApiResponse(array $response): self
    {
        $dto = new self();
        
        $dto->code = (int) ($response['code'] ?? 0);
        $dto->status = strtolower($response['status'] ?? 'pending');
        $dto->certStatus = strtolower($response['certStatus'] ?? $dto->status);
        
        $data = $response['data'] ?? [];
        
        // Certificate files
        $dto->certificate = $data['certificate'] ?? null;
        $dto->caCertificate = $data['caCertificate'] ?? null;
        $dto->privateKey = $data['rsaPrivateKey'] ?? null;
        
        // Pre-formatted
        $dto->jks = $data['jks'] ?? null;
        $dto->pkcs12 = $data['pkcs12'] ?? null;
        $dto->jksPassword = $data['jksPass'] ?? null;
        $dto->pkcsPassword = $data['pkcsPass'] ?? null;
        
        // Dates (parse full datetime)
        $dto->beginDate = DateHelper::parseDateTime($data['beginDate'] ?? null);
        $dto->endDate = DateHelper::parseDateTime($data['endDate'] ?? null);
        $dto->dueDate = DateHelper::parseDate($data['dueDate'] ?? null);
        
        // Vendor
        $dto->vendorId = $data['vendorId'] ?? null;
        $dto->vendorCertId = $data['vendorCertId'] ?? null;
        
        // DCV
        $dto->dcvFileName = $data['DCVfileName'] ?? null;
        $dto->dcvFileContent = $data['DCVfileContent'] ?? null;
        $dto->dcvFilePath = $data['DCVfilePath'] ?? null;
        $dto->dcvDnsHost = $data['DCVdnsHost'] ?? null;
        $dto->dcvDnsValue = $data['DCVdnsValue'] ?? null;
        $dto->dcvDnsType = $data['DCVdnsType'] ?? null;
        $dto->dcvList = $data['dcvList'] ?? [];
        
        return $dto;
    }
    
    /**
     * Convert to database columns array
     */
    public function toDbArray(): array
    {
        return array_filter([
            'cert_status' => $this->certStatus,
            'vendor_id' => $this->vendorId,
            'vendor_cert_id' => $this->vendorCertId,
            'begin_date' => $this->beginDate,
            'end_date' => $this->endDate,
            'due_date' => $this->dueDate,
            'certificate' => $this->certificate,
            'ca_certificate' => $this->caCertificate,
            'private_key' => $this->privateKey,
            'jks_data' => $this->jks,
            'pkcs12_data' => $this->pkcs12,
            'jks_password' => $this->jksPassword,
            'pkcs12_password' => $this->pkcsPassword,
            'dcv_file_name' => $this->dcvFileName,
            'dcv_file_content' => $this->dcvFileContent,
            'dcv_file_path' => $this->dcvFilePath,
            'dcv_dns_host' => $this->dcvDnsHost,
            'dcv_dns_value' => $this->dcvDnsValue,
            'dcv_dns_type' => $this->dcvDnsType,
            'last_sync' => date('Y-m-d H:i:s'),
        ], fn($v) => $v !== null);
    }
    
    /**
     * Check if certificate is available
     */
    public function hasCertificate(): bool
    {
        return !empty($this->certificate);
    }
    
    /**
     * Check if JKS is available
     */
    public function hasJks(): bool
    {
        return !empty($this->jks);
    }
    
    /**
     * Check if PKCS12 is available
     */
    public function hasPkcs12(): bool
    {
        return !empty($this->pkcs12);
    }
}
```

### 4. Updated OrderController - refreshStatus

```php
<?php
// lib/Controller/OrderController.php (partial)

use NicsrsAdmin\DTO\CertificateData;
use NicsrsAdmin\Helper\DateHelper;
use NicsrsAdmin\Helper\DcvHelper;

/**
 * Refresh order status from API
 */
private function refreshStatus(int $orderId): string
{
    try {
        $order = Capsule::table('nicsrs_sslorders')->find($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found');
        }
        
        if (empty($order->remoteid)) {
            throw new \Exception('No remote certificate ID');
        }

        $result = $this->apiService->collect($order->remoteid);
        
        if ($result['code'] != 1 && $result['code'] != 2) {
            throw new \Exception($result['msg'] ?? 'API error');
        }
        
        // Parse response using DTO
        $certData = CertificateData::fromApiResponse($result);
        
        // Build update data from DTO
        $updateData = $certData->toDbArray();
        $updateData['status'] = $certData->status;
        
        // Handle provisiondate
        if (!DateHelper::isValidDate($order->provisiondate)) {
            $updateData['provisiondate'] = date('Y-m-d');
        }
        
        // Handle completiondate for complete status
        if ($certData->status === 'complete') {
            if (!DateHelper::isValidDate($order->completiondate)) {
                $updateData['completiondate'] = $certData->beginDate ?? date('Y-m-d H:i:s');
            }
        }
        
        // Update configdata with dcvList
        $configData = json_decode($order->configdata, true) ?: [];
        
        if (!empty($certData->dcvList)) {
            $configData['domainInfo'] = [];
            foreach ($certData->dcvList as $dcv) {
                $configData['domainInfo'][] = [
                    'domainName' => $dcv['domainName'] ?? '',
                    'dcvMethod' => $dcv['dcvMethod'] ?? '',
                    'dcvMethodDisplay' => DcvHelper::getDisplayName($dcv['dcvMethod'] ?? ''),
                    'dcvEmail' => $dcv['dcvEmail'] ?? '',
                    'isVerified' => ($dcv['is_verify'] ?? '') === 'verified',
                ];
            }
        }
        
        $configData['lastRefresh'] = date('Y-m-d H:i:s');
        $updateData['configdata'] = json_encode($configData);
        
        // Update database
        Capsule::table('nicsrs_sslorders')
            ->where('id', $orderId)
            ->update($updateData);

        // Log activity
        $this->logger->log('refresh_status', 'order', $orderId, $order->status, $certData->status);

        return $this->jsonSuccess('Status refreshed', [
            'status' => $certData->status,
            'cert_status' => $certData->certStatus,
            'begin_date' => DateHelper::formatDisplay($certData->beginDate),
            'end_date' => DateHelper::formatDisplay($certData->endDate),
            'has_certificate' => $certData->hasCertificate(),
            'has_jks' => $certData->hasJks(),
            'has_pkcs12' => $certData->hasPkcs12(),
        ]);

    } catch (\Exception $e) {
        return $this->jsonError('Refresh failed: ' . $e->getMessage());
    }
}
```

### 5. CertificateService - Downloads

```php
<?php
// lib/Service/CertificateService.php
namespace NicsrsAdmin\Service;

use WHMCS\Database\Capsule;

class CertificateService
{
    /**
     * Download certificate in specified format
     */
    public function download(int $orderId, string $format): array
    {
        $order = Capsule::table('nicsrs_sslorders')->find($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found');
        }
        
        $domain = $this->getPrimaryDomain($order);
        
        switch ($format) {
            case 'pem':
                return $this->downloadPem($order, $domain);
            case 'jks':
                return $this->downloadJks($order, $domain);
            case 'pkcs12':
            case 'p12':
                return $this->downloadPkcs12($order, $domain);
            case 'zip':
                return $this->downloadZip($order, $domain);
            default:
                throw new \Exception('Invalid format');
        }
    }
    
    private function downloadPem($order, string $domain): array
    {
        if (empty($order->certificate)) {
            throw new \Exception('Certificate not available');
        }
        
        $content = $order->certificate;
        if (!empty($order->ca_certificate)) {
            $content .= "\n" . $order->ca_certificate;
        }
        
        return [
            'content' => base64_encode($content),
            'filename' => "{$domain}.pem",
            'mime' => 'application/x-pem-file',
        ];
    }
    
    private function downloadJks($order, string $domain): array
    {
        if (empty($order->jks_data)) {
            throw new \Exception('JKS format not available');
        }
        
        return [
            'content' => $order->jks_data, // Already base64
            'filename' => "{$domain}.jks",
            'mime' => 'application/octet-stream',
            'password' => $order->jks_password,
        ];
    }
    
    private function downloadPkcs12($order, string $domain): array
    {
        if (empty($order->pkcs12_data)) {
            throw new \Exception('PKCS12 format not available');
        }
        
        return [
            'content' => $order->pkcs12_data, // Already base64
            'filename' => "{$domain}.p12",
            'mime' => 'application/x-pkcs12',
            'password' => $order->pkcs12_password,
        ];
    }
    
    private function downloadZip($order, string $domain): array
    {
        $zip = new \ZipArchive();
        $tmpFile = tempnam(sys_get_temp_dir(), 'cert_');
        $zip->open($tmpFile, \ZipArchive::CREATE);
        
        // Add available formats
        if (!empty($order->certificate)) {
            $zip->addFromString("{$domain}.crt", $order->certificate);
        }
        if (!empty($order->ca_certificate)) {
            $zip->addFromString("{$domain}.ca-bundle", $order->ca_certificate);
        }
        if (!empty($order->private_key)) {
            $zip->addFromString("{$domain}.key", $order->private_key);
        }
        if (!empty($order->jks_data)) {
            $zip->addFromString("{$domain}.jks", base64_decode($order->jks_data));
        }
        if (!empty($order->pkcs12_data)) {
            $zip->addFromString("{$domain}.p12", base64_decode($order->pkcs12_data));
        }
        
        // Add password file
        $passwords = [];
        if (!empty($order->jks_password)) {
            $passwords[] = "JKS Password: {$order->jks_password}";
        }
        if (!empty($order->pkcs12_password)) {
            $passwords[] = "PKCS12 Password: {$order->pkcs12_password}";
        }
        if (!empty($passwords)) {
            $zip->addFromString("passwords.txt", implode("\n", $passwords));
        }
        
        $zip->close();
        
        $content = file_get_contents($tmpFile);
        unlink($tmpFile);
        
        return [
            'content' => base64_encode($content),
            'filename' => "{$domain}_certificates.zip",
            'mime' => 'application/zip',
        ];
    }
    
    private function getPrimaryDomain($order): string
    {
        $config = json_decode($order->configdata, true);
        return $config['domainInfo'][0]['domainName'] ?? 'certificate';
    }
}
```

---

## Updated Templates

### Order Detail Template (Partial)

```php
<!-- templates/orders/detail.php -->

<!-- Vendor Information Section -->
<?php if (!empty($order->vendor_id) || !empty($order->vendor_cert_id)): ?>
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-building"></i> Vendor Information
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label>Vendor ID</label>
                <p class="form-control-static"><?= $order->vendor_id ?: '-' ?></p>
            </div>
            <div class="col-md-6">
                <label>Vendor Certificate ID</label>
                <p class="form-control-static"><?= $order->vendor_cert_id ?: '-' ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Certificate Dates Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-calendar"></i> Certificate Dates
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Issue Date</label>
                <p class="form-control-static">
                    <?= \NicsrsAdmin\Helper\DateHelper::formatDisplay($order->begin_date) ?>
                </p>
            </div>
            <div class="col-md-4">
                <label>Expiry Date</label>
                <p class="form-control-static">
                    <?= \NicsrsAdmin\Helper\DateHelper::formatDisplay($order->end_date) ?>
                    <?php 
                    $days = \NicsrsAdmin\Helper\DateHelper::getDaysUntilExpiry($order->end_date);
                    if ($days !== null):
                        $badgeClass = $days <= 30 ? 'danger' : ($days <= 60 ? 'warning' : 'success');
                    ?>
                    <span class="badge badge-<?= $badgeClass ?>"><?= $days ?> days</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4">
                <label>Renewal Due</label>
                <p class="form-control-static">
                    <?= \NicsrsAdmin\Helper\DateHelper::formatDisplay($order->due_date) ?>
                </p>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <small class="text-muted">
                    Last synced: <?= \NicsrsAdmin\Helper\DateHelper::formatDisplayWithTime($order->last_sync) ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Certificate Download Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-download"></i> Certificate Downloads
    </div>
    <div class="card-body">
        <div class="btn-group" role="group">
            <?php if (!empty($order->certificate)): ?>
            <button type="button" class="btn btn-outline-primary btn-download" 
                    data-format="pem" data-order="<?= $order->id ?>">
                <i class="fas fa-file-alt"></i> PEM
            </button>
            <?php endif; ?>
            
            <?php if (!empty($order->jks_data)): ?>
            <button type="button" class="btn btn-outline-primary btn-download" 
                    data-format="jks" data-order="<?= $order->id ?>">
                <i class="fas fa-file-archive"></i> JKS
            </button>
            <?php endif; ?>
            
            <?php if (!empty($order->pkcs12_data)): ?>
            <button type="button" class="btn btn-outline-primary btn-download" 
                    data-format="pkcs12" data-order="<?= $order->id ?>">
                <i class="fas fa-file-archive"></i> PKCS12
            </button>
            <?php endif; ?>
            
            <button type="button" class="btn btn-primary btn-download" 
                    data-format="zip" data-order="<?= $order->id ?>">
                <i class="fas fa-file-zipper"></i> Download All (ZIP)
            </button>
        </div>
        
        <?php if (!empty($order->jks_password) || !empty($order->pkcs12_password)): ?>
        <div class="mt-3">
            <small class="text-muted">
                <strong>Passwords:</strong><br>
                <?php if (!empty($order->jks_password)): ?>
                JKS: <code><?= htmlspecialchars($order->jks_password) ?></code><br>
                <?php endif; ?>
                <?php if (!empty($order->pkcs12_password)): ?>
                PKCS12: <code><?= htmlspecialchars($order->pkcs12_password) ?></code>
                <?php endif; ?>
            </small>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- DCV Status Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-check-circle"></i> Domain Validation
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Domain</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domainInfo as $domain): ?>
                <tr>
                    <td><?= htmlspecialchars($domain['domainName']) ?></td>
                    <td>
                        <i class="fas <?= \NicsrsAdmin\Helper\DcvHelper::getIcon($domain['dcvMethod']) ?>"></i>
                        <?= \NicsrsAdmin\Helper\DcvHelper::getDisplayName($domain['dcvMethod']) ?>
                    </td>
                    <td>
                        <?php if ($domain['isVerified']): ?>
                        <span class="badge badge-success">Verified</span>
                        <?php else: ?>
                        <span class="badge badge-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (!empty($order->dcv_dns_host)): ?>
        <div class="mt-3 p-3 bg-light rounded">
            <strong>DNS CNAME Record:</strong><br>
            <code>Host: <?= htmlspecialchars($order->dcv_dns_host) ?></code><br>
            <code>Value: <?= htmlspecialchars($order->dcv_dns_value) ?></code><br>
            <code>Type: <?= htmlspecialchars($order->dcv_dns_type) ?></code>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($order->dcv_file_name)): ?>
        <div class="mt-3 p-3 bg-light rounded">
            <strong>HTTP File Validation:</strong><br>
            <code>File: <?= htmlspecialchars($order->dcv_file_name) ?></code><br>
            <code>Path: <?= htmlspecialchars($order->dcv_file_path) ?></code><br>
            <code>Content: <?= htmlspecialchars($order->dcv_file_content) ?></code>
        </div>
        <?php endif; ?>
    </div>
</div>
```

---

## Development Phases

### Phase 1: Core Updates (Week 1) - 25 hours

| Task | Description | Hours |
|------|-------------|-------|
| 1.1 | Create ApiConfig class (shared API key) | 3h |
| 1.2 | Create DateHelper class | 2h |
| 1.3 | Create DcvHelper class | 2h |
| 1.4 | Create CertificateData DTO | 4h |
| 1.5 | Update NicsrsApiService | 3h |
| 1.6 | Update OrderController - refreshStatus | 4h |
| 1.7 | Create CertificateService - downloads | 4h |
| 1.8 | Update SettingsController - API token | 3h |

**Deliverables:**
- [ ] Shared API key working
- [ ] Full API response parsing
- [ ] Date handling correct
- [ ] DCV method mapping correct

---

### Phase 2: UI & Features (Week 2) - 25 hours

| Task | Description | Hours |
|------|-------------|-------|
| 2.1 | Update order detail template | 5h |
| 2.2 | Add certificate download UI | 4h |
| 2.3 | Add vendor info display | 2h |
| 2.4 | Update orders list (new columns) | 3h |
| 2.5 | Update dashboard statistics | 3h |
| 2.6 | Update JavaScript handlers | 4h |
| 2.7 | Update language files | 2h |
| 2.8 | Testing & bug fixes | 2h |

**Deliverables:**
- [ ] Order detail shows all new fields
- [ ] Download buttons work (PEM, JKS, PKCS12, ZIP)
- [ ] DCV display correct
- [ ] All UI updated

---

## Settings Page Update

```php
// Module config fields (nicsrs_ssl_admin.php)
function nicsrs_ssl_admin_config()
{
    return [
        'name' => 'HVN - NicSRS SSL Admin',
        'description' => 'SSL certificate management for NicSRS resellers. API Token configured here is shared with Server Module.',
        'version' => '1.2.0',
        'author' => '<a href="https://hvn.vn" target="_blank">HVN GROUP</a>',
        'language' => 'english',
        'fields' => [
            'api_token' => [
                'FriendlyName' => 'NicSRS API Token',
                'Type' => 'password',
                'Size' => '64',
                'Description' => '<span class="text-success"><i class="fas fa-share-alt"></i> Shared with all SSL products</span>. Enter your API token from portal.nicsrs.com',
            ],
            'items_per_page' => [
                'FriendlyName' => 'Items Per Page',
                'Type' => 'dropdown',
                'Options' => '10,25,50,100',
                'Default' => '25',
            ],
            'auto_sync' => [
                'FriendlyName' => 'Auto Sync Status',
                'Type' => 'yesno',
                'Description' => 'Automatically sync certificate status on view',
            ],
        ],
    ];
}
```

---

## Timeline Summary

| Week | Phase | Hours | Deliverables |
|------|-------|-------|--------------|
| 1 | Core Updates | 25h | ApiConfig, DTOs, Services |
| 2 | UI & Features | 25h | Templates, Downloads, Testing |
| **Total** | | **50h** | |

---

## Dependencies

### Requires Server Module Migration First

Addon Module v1.2.0 phụ thuộc vào database schema từ Server Module migration:

```
1. Run Server Module migration (ALTER TABLE nicsrs_sslorders)
2. Then deploy Addon Module v1.2.0
```

Hoặc có thể chạy migration script independently trong Addon activation.

---

## File Changes Summary

| File | Change Type | Description |
|------|-------------|-------------|
| `nicsrs_ssl_admin.php` | Modified | Add API token to config |
| `lib/Config/ApiConfig.php` | New | Shared API key management |
| `lib/Helper/DateHelper.php` | New | Date parsing utilities |
| `lib/Helper/DcvHelper.php` | New | DCV method mapping |
| `lib/DTO/CertificateData.php` | New | API response DTO |
| `lib/Controller/OrderController.php` | Modified | Use DTO, new columns |
| `lib/Service/CertificateService.php` | New | Download operations |
| `lib/Service/NicsrsApiService.php` | Modified | Use ApiConfig |
| `templates/orders/detail.php` | Modified | New sections |
| `templates/orders/list.php` | Modified | New columns |
| `assets/js/admin.js` | Modified | Download handlers |
| `lang/english.php` | Modified | New strings |
| `lang/vietnamese.php` | Modified | New strings |

---

## Definition of Done

### Per Task
- [ ] Code complete
- [ ] Follows existing code style
- [ ] No PHP errors/warnings
- [ ] JavaScript works without console errors

### For Release
- [ ] All tasks complete
- [ ] Works with new database schema
- [ ] Backward compatible
- [ ] Language files updated
- [ ] Version bumped to 1.2.0

---

## Approval Required

| Item | Status |
|------|--------|
| Scope approved | ⬜ Pending |
| Timeline (2 weeks) | ⬜ Pending |
| Dependencies understood | ⬜ Pending |
| Ready to proceed | ⬜ Pending |

---

**Document Version:** 1.0  
**Created:** 2025-01-20  
**Author:** HVN GROUP