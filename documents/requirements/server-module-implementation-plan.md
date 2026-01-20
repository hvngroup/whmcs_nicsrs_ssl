# NicSRS SSL Server Module v1.2.0 - Implementation Plan

## Project Overview

| Item | Details |
|------|---------|
| **Project** | NicSRS SSL Server Provisioning Module |
| **Version** | 1.2.0 |
| **Type** | WHMCS Server Provisioning Module |
| **Author** | HVN GROUP |
| **Estimated Duration** | 3 weeks (70 hours) |
| **Start Date** | TBD |

---

## Scope Definition

### ✅ In Scope (v1.2.0)

| # | Feature | Priority |
|---|---------|----------|
| 1 | Shared API Key từ Addon Module settings | P0 |
| 2 | Database schema mở rộng (consolidated) | P0 |
| 3 | Full API response field mapping | P0 |
| 4 | Pre-formatted certificate support (JKS, PKCS12) | P1 |
| 5 | Professional directory structure với DTO | P1 |
| 6 | Enhanced DCV management | P1 |
| 7 | Date format handling (datetime) | P0 |
| 8 | Vendor tracking (vendorId, vendorCertId) | P2 |

### ❌ Out of Scope (Deferred to v1.3.0)

| Feature | Reason |
|---------|--------|
| Auto-renewal automation | Requires extensive testing |
| Webhook integration | Requires NicSRS support |
| Multi-language email templates | Nice-to-have |
| Certificate monitoring dashboard | Complexity |

---

## Technical Architecture

### Shared API Key Design

```
┌─────────────────────────────────────────────────────────────┐
│                    WHMCS Admin Panel                         │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Addons → NicSRS SSL Admin → Settings                   ││
│  │  ┌─────────────────────────────────────────────────────┐││
│  │  │  API Token: [************************]  [Test]      │││
│  │  │  (Shared across ALL SSL products)                   │││
│  │  └─────────────────────────────────────────────────────┘││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     ApiConfig Class                          │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Priority 1: mod_nicsrs_settings.api_token              ││
│  │  Priority 2: tbladdonmodules.api_token (fallback)       ││
│  │  Priority 3: configoption2 (legacy compatibility)       ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    NicSRS API                                │
│                https://portal.nicsrs.com/ssl                 │
└─────────────────────────────────────────────────────────────┘
```

### Module Structure

```
modules/servers/nicsrs_ssl/
├── nicsrs_ssl.php                    # Main entry point
├── hooks.php                         # WHMCS hooks
│
├── lang/
│   ├── english.php
│   ├── vietnamese.php
│   └── chinese.php
│
├── src/
│   ├── Config/
│   │   ├── ApiConfig.php             # Shared API key management
│   │   ├── Constants.php             # Status, DCV method constants
│   │   └── CertificateTypes.php      # Product type definitions
│   │
│   ├── Api/
│   │   ├── NicsrsClient.php          # HTTP client wrapper
│   │   ├── ApiResponse.php           # Response wrapper
│   │   └── DTO/
│   │       ├── CertificateData.php   # Certificate response DTO
│   │       ├── DcvInfo.php           # DCV validation DTO
│   │       └── CollectResponse.php   # Collect API response DTO
│   │
│   ├── Controller/
│   │   ├── PageController.php        # Page rendering
│   │   ├── ActionController.php      # Form actions
│   │   └── AjaxController.php        # AJAX handlers
│   │
│   ├── Service/
│   │   ├── CertificateService.php    # Certificate operations
│   │   ├── DcvService.php            # DCV operations
│   │   ├── DownloadService.php       # Certificate download/export
│   │   └── SyncService.php           # API sync service
│   │
│   ├── Repository/
│   │   └── OrderRepository.php       # Database operations
│   │
│   ├── Helper/
│   │   ├── CsrHelper.php             # CSR parsing
│   │   ├── DateHelper.php            # Date format handling
│   │   └── ZipHelper.php             # Certificate ZIP creation
│   │
│   └── Exception/
│       ├── ApiException.php          # API errors
│       └── ConfigException.php       # Configuration errors
│
├── resources/
│   ├── data/
│   │   └── countries.json
│   └── views/
│       ├── pages/
│       │   ├── apply.tpl
│       │   ├── pending.tpl
│       │   ├── complete.tpl
│       │   └── reissue.tpl
│       └── partials/
│           ├── _dcv_form.tpl
│           ├── _domain_list.tpl
│           └── _download_options.tpl
│
└── assets/
    ├── css/
    │   └── nicsrs.css
    └── js/
        └── nicsrs.js
```

---

## Database Schema

### Extended Table: nicsrs_sslorders

```sql
-- Migration: Add new columns to existing table
ALTER TABLE `nicsrs_sslorders`
    -- Vendor tracking
    ADD COLUMN `vendor_id` VARCHAR(50) NULL AFTER `remoteid`,
    ADD COLUMN `vendor_cert_id` VARCHAR(50) NULL AFTER `vendor_id`,
    
    -- Certificate status (separate from order status)
    ADD COLUMN `cert_status` VARCHAR(20) DEFAULT 'pending' AFTER `status`,
    
    -- Important dates (full datetime from API)
    ADD COLUMN `due_date` DATE NULL AFTER `completiondate`,
    ADD COLUMN `begin_date` DATETIME NULL AFTER `due_date`,
    ADD COLUMN `end_date` DATETIME NULL AFTER `begin_date`,
    ADD COLUMN `last_sync` DATETIME NULL AFTER `end_date`,
    
    -- Certificate data (direct storage for easier access)
    ADD COLUMN `certificate` MEDIUMTEXT NULL,
    ADD COLUMN `ca_certificate` MEDIUMTEXT NULL,
    ADD COLUMN `private_key` MEDIUMTEXT NULL,
    
    -- Pre-formatted certificates from API
    ADD COLUMN `jks_data` MEDIUMTEXT NULL,
    ADD COLUMN `pkcs12_data` MEDIUMTEXT NULL,
    ADD COLUMN `jks_password` VARCHAR(100) NULL,
    ADD COLUMN `pkcs12_password` VARCHAR(100) NULL,
    
    -- DCV information (primary domain)
    ADD COLUMN `dcv_file_name` VARCHAR(255) NULL,
    ADD COLUMN `dcv_file_content` TEXT NULL,
    ADD COLUMN `dcv_file_path` VARCHAR(500) NULL,
    ADD COLUMN `dcv_dns_host` VARCHAR(255) NULL,
    ADD COLUMN `dcv_dns_value` VARCHAR(500) NULL,
    ADD COLUMN `dcv_dns_type` VARCHAR(20) NULL,
    
    -- Indexes
    ADD INDEX `idx_vendor_id` (`vendor_id`),
    ADD INDEX `idx_cert_status` (`cert_status`),
    ADD INDEX `idx_due_date` (`due_date`),
    ADD INDEX `idx_end_date` (`end_date`);
```

### Final Table Structure

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Primary key |
| `userid` | INT | WHMCS user ID |
| `serviceid` | INT | WHMCS service ID |
| `addon_id` | TEXT | Addon service ID |
| `remoteid` | TEXT | NicSRS certId |
| `vendor_id` | VARCHAR(50) | **NEW** - Sectigo vendor ID |
| `vendor_cert_id` | VARCHAR(50) | **NEW** - Vendor certificate ID |
| `module` | TEXT | Module name |
| `certtype` | TEXT | Product code |
| `configdata` | TEXT | JSON (domainInfo, applyReturn, dcvList) |
| `provisiondate` | DATE | Order date |
| `completiondate` | DATETIME | Issue date |
| `due_date` | DATE | **NEW** - Renewal due date |
| `begin_date` | DATETIME | **NEW** - Certificate start |
| `end_date` | DATETIME | **NEW** - Certificate expiry |
| `last_sync` | DATETIME | **NEW** - Last API sync |
| `status` | TEXT | Order status |
| `cert_status` | VARCHAR(20) | **NEW** - Certificate status |
| `certificate` | MEDIUMTEXT | **NEW** - PEM certificate |
| `ca_certificate` | MEDIUMTEXT | **NEW** - CA bundle |
| `private_key` | MEDIUMTEXT | **NEW** - Private key |
| `jks_data` | MEDIUMTEXT | **NEW** - Base64 JKS file |
| `pkcs12_data` | MEDIUMTEXT | **NEW** - Base64 PKCS12 file |
| `jks_password` | VARCHAR(100) | **NEW** - JKS password |
| `pkcs12_password` | VARCHAR(100) | **NEW** - PKCS12 password |
| `dcv_file_name` | VARCHAR(255) | **NEW** - HTTP validation filename |
| `dcv_file_content` | TEXT | **NEW** - HTTP file content |
| `dcv_file_path` | VARCHAR(500) | **NEW** - Full validation URL |
| `dcv_dns_host` | VARCHAR(255) | **NEW** - DNS record host |
| `dcv_dns_value` | VARCHAR(500) | **NEW** - DNS record value |
| `dcv_dns_type` | VARCHAR(20) | **NEW** - DNS type (CNAME) |

---

## API Response Mapping

### Actual API Response from `/collect` (collect_api.txt)

```php
[
    'code' => 1,
    'status' => 'COMPLETE',
    'certStatus' => 'COMPLETE',
    'data' => [
        'beginDate' => '2026-01-19 08:00:00',    // Full datetime
        'endDate' => '2027-02-20 07:59:59',
        'dueDate' => '2028-01-19 00:00:00',
        'certificate' => '-----BEGIN CERTIFICATE-----...',
        'caCertificate' => '-----BEGIN CERTIFICATE-----...',
        'rsaPrivateKey' => '',
        'jks' => 'base64_encoded_jks',
        'pkcs12' => 'base64_encoded_p12',
        'jksPass' => 'U0DHfMAlZXk7DHQP',
        'pkcsPass' => 'MojZVyvbiMO65dC9',
        'vendorId' => '2771240592',
        'vendorCertId' => '39831817562',
        'DCVfileName' => '148ECC23D64F50CDCCA4F0DAF72A9A4B.txt',
        'DCVfileContent' => 'hash_content...',
        'DCVfilePath' => 'http://example.com/.well-known/pki-validation/...',
        'DCVdnsHost' => '_148ecc23d64f50cdcca4f0daf72a9a4b',
        'DCVdnsValue' => 'cname_value.sectigo.com',
        'DCVdnsType' => 'CNAME',
        'dcvList' => [
            [
                'dcvEmail' => '',
                'dcvMethod' => 'CNAME_CSR_HASH',  // Note: Not 'CNAME'
                'is_verify' => 'verified',
                'domainName' => 'example.com'
            ]
        ]
    ]
]
```

### Field Mapping Table

| API Field | DB Column | Transform |
|-----------|-----------|-----------|
| `status` | `status` | lowercase |
| `certStatus` | `cert_status` | lowercase |
| `vendorId` | `vendor_id` | direct |
| `vendorCertId` | `vendor_cert_id` | direct |
| `beginDate` | `begin_date` | parse datetime |
| `endDate` | `end_date` | parse datetime |
| `dueDate` | `due_date` | parse date only |
| `certificate` | `certificate` | direct |
| `caCertificate` | `ca_certificate` | direct |
| `rsaPrivateKey` | `private_key` | direct |
| `jks` | `jks_data` | direct (base64) |
| `pkcs12` | `pkcs12_data` | direct (base64) |
| `jksPass` | `jks_password` | direct |
| `pkcsPass` | `pkcs12_password` | direct |
| `DCVfileName` | `dcv_file_name` | direct |
| `DCVfileContent` | `dcv_file_content` | direct |
| `DCVfilePath` | `dcv_file_path` | direct |
| `DCVdnsHost` | `dcv_dns_host` | direct |
| `DCVdnsValue` | `dcv_dns_value` | direct |
| `DCVdnsType` | `dcv_dns_type` | direct |
| `dcvList` | `configdata` | JSON encode |

### DCV Method Mapping

| API Value | Display Name | Type |
|-----------|--------------|------|
| `CNAME_CSR_HASH` | DNS CNAME | DNS |
| `HTTP_CSR_HASH` | HTTP File | HTTP |
| `DNS_CSR_HASH` | DNS TXT | DNS |
| `EMAIL` | Email | Email |

---

## Key Components Implementation

### 1. ApiConfig Class

```php
<?php
// src/Config/ApiConfig.php
namespace NicsrsSsl\Config;

use WHMCS\Database\Capsule;

class ApiConfig
{
    private static ?string $cachedToken = null;
    
    /**
     * Get API token (shared from Addon Module)
     * Priority: Addon settings → Module config → Product config (legacy)
     */
    public static function getApiToken(array $params = []): string
    {
        if (self::$cachedToken !== null) {
            return self::$cachedToken;
        }
        
        // Priority 1: Addon settings table
        $token = self::getFromAddonSettings();
        if ($token) {
            self::$cachedToken = $token;
            return $token;
        }
        
        // Priority 2: Addon module config
        $token = self::getFromModuleConfig();
        if ($token) {
            self::$cachedToken = $token;
            return $token;
        }
        
        // Priority 3: Legacy product config
        if (!empty($params['configoption2'])) {
            return $params['configoption2'];
        }
        
        throw new \Exception('API Token not configured. Please set in Addon Module settings.');
    }
    
    private static function getFromAddonSettings(): ?string
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
    
    public static function getApiUrl(): string
    {
        return 'https://portal.nicsrs.com/ssl';
    }
    
    public static function clearCache(): void
    {
        self::$cachedToken = null;
    }
}
```

### 2. CertificateData DTO

```php
<?php
// src/Api/DTO/CertificateData.php
namespace NicsrsSsl\Api\DTO;

class CertificateData
{
    public ?string $certificate = null;
    public ?string $caCertificate = null;
    public ?string $privateKey = null;
    public ?string $jks = null;
    public ?string $pkcs12 = null;
    public ?string $jksPassword = null;
    public ?string $pkcsPassword = null;
    public ?string $beginDate = null;
    public ?string $endDate = null;
    public ?string $dueDate = null;
    public ?string $vendorId = null;
    public ?string $vendorCertId = null;
    public ?string $dcvFileName = null;
    public ?string $dcvFileContent = null;
    public ?string $dcvFilePath = null;
    public ?string $dcvDnsHost = null;
    public ?string $dcvDnsValue = null;
    public ?string $dcvDnsType = null;
    public array $dcvList = [];
    
    public static function fromApiResponse(array $data): self
    {
        $dto = new self();
        
        $dto->certificate = $data['certificate'] ?? null;
        $dto->caCertificate = $data['caCertificate'] ?? null;
        $dto->privateKey = $data['rsaPrivateKey'] ?? null;
        $dto->jks = $data['jks'] ?? null;
        $dto->pkcs12 = $data['pkcs12'] ?? null;
        $dto->jksPassword = $data['jksPass'] ?? null;
        $dto->pkcsPassword = $data['pkcsPass'] ?? null;
        $dto->vendorId = $data['vendorId'] ?? null;
        $dto->vendorCertId = $data['vendorCertId'] ?? null;
        $dto->dcvFileName = $data['DCVfileName'] ?? null;
        $dto->dcvFileContent = $data['DCVfileContent'] ?? null;
        $dto->dcvFilePath = $data['DCVfilePath'] ?? null;
        $dto->dcvDnsHost = $data['DCVdnsHost'] ?? null;
        $dto->dcvDnsValue = $data['DCVdnsValue'] ?? null;
        $dto->dcvDnsType = $data['DCVdnsType'] ?? null;
        $dto->dcvList = $data['dcvList'] ?? [];
        
        // Parse dates (API returns full datetime)
        $dto->beginDate = self::parseDateTime($data['beginDate'] ?? null);
        $dto->endDate = self::parseDateTime($data['endDate'] ?? null);
        $dto->dueDate = self::parseDate($data['dueDate'] ?? null);
        
        return $dto;
    }
    
    private static function parseDateTime(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
    
    private static function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
    }
    
    /**
     * Convert to database update array
     */
    public function toDbArray(): array
    {
        return [
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
        ];
    }
}
```

### 3. DownloadService

```php
<?php
// src/Service/DownloadService.php
namespace NicsrsSsl\Service;

use NicsrsSsl\Repository\OrderRepository;
use NicsrsSsl\Exception\CertificateException;

class DownloadService
{
    private OrderRepository $orderRepo;
    
    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Download JKS format (from API pre-formatted data)
     */
    public function downloadJks(int $orderId): array
    {
        $order = $this->orderRepo->find($orderId);
        
        if (empty($order->jks_data)) {
            throw new CertificateException('JKS format not available');
        }
        
        return [
            'content' => base64_decode($order->jks_data),
            'filename' => $this->getDomain($order) . '.jks',
            'password' => $order->jks_password,
            'mime' => 'application/octet-stream',
        ];
    }
    
    /**
     * Download PKCS12 format (from API pre-formatted data)
     */
    public function downloadPkcs12(int $orderId): array
    {
        $order = $this->orderRepo->find($orderId);
        
        if (empty($order->pkcs12_data)) {
            throw new CertificateException('PKCS12 format not available');
        }
        
        return [
            'content' => base64_decode($order->pkcs12_data),
            'filename' => $this->getDomain($order) . '.p12',
            'password' => $order->pkcs12_password,
            'mime' => 'application/x-pkcs12',
        ];
    }
    
    /**
     * Download all formats as ZIP
     */
    public function downloadZip(int $orderId): array
    {
        $order = $this->orderRepo->find($orderId);
        $domain = $this->getDomain($order);
        
        // Create ZIP with all available formats
        $zip = new \ZipArchive();
        $tmpFile = tempnam(sys_get_temp_dir(), 'cert_');
        $zip->open($tmpFile, \ZipArchive::CREATE);
        
        // PEM format
        if (!empty($order->certificate)) {
            $zip->addFromString("{$domain}.crt", $order->certificate);
        }
        if (!empty($order->ca_certificate)) {
            $zip->addFromString("{$domain}.ca-bundle", $order->ca_certificate);
        }
        if (!empty($order->private_key)) {
            $zip->addFromString("{$domain}.key", $order->private_key);
        }
        
        // JKS format
        if (!empty($order->jks_data)) {
            $zip->addFromString("{$domain}.jks", base64_decode($order->jks_data));
            $zip->addFromString("jks_password.txt", "Password: {$order->jks_password}");
        }
        
        // PKCS12 format
        if (!empty($order->pkcs12_data)) {
            $zip->addFromString("{$domain}.p12", base64_decode($order->pkcs12_data));
            $zip->addFromString("p12_password.txt", "Password: {$order->pkcs12_password}");
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
    
    private function getDomain($order): string
    {
        $config = json_decode($order->configdata, true);
        return $config['domainInfo'][0]['domainName'] ?? 'certificate';
    }
}
```

---

## Development Phases

### Phase 1: Foundation (Week 1) - 20 hours

| Task | Description | Hours |
|------|-------------|-------|
| 1.1 | Create migration script for database schema | 3h |
| 1.2 | Implement ApiConfig class (shared API key) | 3h |
| 1.3 | Create DTO classes (CertificateData, DcvInfo) | 4h |
| 1.4 | Refactor NicsrsClient with new structure | 4h |
| 1.5 | Update OrderRepository for new columns | 3h |
| 1.6 | Update Addon Module to store API token | 3h |

**Deliverables:**
- [ ] Database migration script ready
- [ ] ApiConfig working with fallback
- [ ] DTO classes implemented
- [ ] Unit tests for ApiConfig

---

### Phase 2: Core Services (Week 2) - 25 hours

| Task | Description | Hours |
|------|-------------|-------|
| 2.1 | Implement SyncService (API data sync) | 5h |
| 2.2 | Update CertificateService with new fields | 4h |
| 2.3 | Implement DownloadService (JKS, PKCS12, ZIP) | 5h |
| 2.4 | Update DcvService with proper method mapping | 4h |
| 2.5 | Refactor ActionController | 4h |
| 2.6 | Update PageController | 3h |

**Deliverables:**
- [ ] Full API response sync working
- [ ] Pre-formatted cert downloads working
- [ ] DCV method mapping correct
- [ ] All services using shared API key

---

### Phase 3: UI & Testing (Week 3) - 25 hours

| Task | Description | Hours |
|------|-------------|-------|
| 3.1 | Update complete.tpl (new download options) | 4h |
| 3.2 | Update pending.tpl (DCV display) | 3h |
| 3.3 | Update JavaScript handlers | 4h |
| 3.4 | Integration testing | 6h |
| 3.5 | Migration testing & data validation | 4h |
| 3.6 | Documentation update | 4h |

**Deliverables:**
- [ ] UI shows all certificate formats
- [ ] Download buttons for JKS/PKCS12
- [ ] All tests passing
- [ ] Documentation complete

---

## Migration Script

```php
<?php
// migrations/v1.2.0_schema_update.php

use WHMCS\Database\Capsule;

function nicsrs_ssl_migrate_v120(): array
{
    $results = ['success' => true, 'messages' => []];
    $table = 'nicsrs_sslorders';
    
    try {
        // Backup first
        Capsule::statement("CREATE TABLE IF NOT EXISTS {$table}_backup_v120 AS SELECT * FROM {$table}");
        $results['messages'][] = "Backup created: {$table}_backup_v120";
        
        // Add new columns
        $columns = [
            'vendor_id' => "VARCHAR(50) NULL AFTER `remoteid`",
            'vendor_cert_id' => "VARCHAR(50) NULL AFTER `vendor_id`",
            'cert_status' => "VARCHAR(20) DEFAULT 'pending' AFTER `status`",
            'due_date' => "DATE NULL AFTER `completiondate`",
            'begin_date' => "DATETIME NULL AFTER `due_date`",
            'end_date' => "DATETIME NULL AFTER `begin_date`",
            'last_sync' => "DATETIME NULL AFTER `end_date`",
            'certificate' => "MEDIUMTEXT NULL",
            'ca_certificate' => "MEDIUMTEXT NULL",
            'private_key' => "MEDIUMTEXT NULL",
            'jks_data' => "MEDIUMTEXT NULL",
            'pkcs12_data' => "MEDIUMTEXT NULL",
            'jks_password' => "VARCHAR(100) NULL",
            'pkcs12_password' => "VARCHAR(100) NULL",
            'dcv_file_name' => "VARCHAR(255) NULL",
            'dcv_file_content' => "TEXT NULL",
            'dcv_file_path' => "VARCHAR(500) NULL",
            'dcv_dns_host' => "VARCHAR(255) NULL",
            'dcv_dns_value' => "VARCHAR(500) NULL",
            'dcv_dns_type' => "VARCHAR(20) NULL",
        ];
        
        foreach ($columns as $col => $def) {
            if (!Capsule::schema()->hasColumn($table, $col)) {
                Capsule::statement("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}");
                $results['messages'][] = "Added: {$col}";
            }
        }
        
        // Add indexes
        $indexes = ['idx_vendor_id' => 'vendor_id', 'idx_cert_status' => 'cert_status', 
                    'idx_due_date' => 'due_date', 'idx_end_date' => 'end_date'];
        
        foreach ($indexes as $name => $col) {
            try {
                Capsule::statement("CREATE INDEX `{$name}` ON `{$table}` (`{$col}`)");
                $results['messages'][] = "Index added: {$name}";
            } catch (\Exception $e) { /* Index exists */ }
        }
        
        // Migrate existing data from configdata
        $orders = Capsule::table($table)->whereNotNull('configdata')->get();
        $migrated = 0;
        
        foreach ($orders as $order) {
            $config = json_decode($order->configdata, true);
            if (empty($config['applyReturn'])) continue;
            
            $ar = $config['applyReturn'];
            $update = [];
            
            if (!empty($ar['certificate']) && empty($order->certificate)) {
                $update['certificate'] = $ar['certificate'];
            }
            if (!empty($ar['caCertificate']) && empty($order->ca_certificate)) {
                $update['ca_certificate'] = $ar['caCertificate'];
            }
            if (!empty($ar['beginDate']) && empty($order->begin_date)) {
                $update['begin_date'] = date('Y-m-d H:i:s', strtotime($ar['beginDate']));
            }
            if (!empty($ar['endDate']) && empty($order->end_date)) {
                $update['end_date'] = date('Y-m-d H:i:s', strtotime($ar['endDate']));
            }
            
            if (!empty($update)) {
                Capsule::table($table)->where('id', $order->id)->update($update);
                $migrated++;
            }
        }
        
        $results['messages'][] = "Migrated {$migrated} orders";
        
    } catch (\Exception $e) {
        $results['success'] = false;
        $results['messages'][] = "Error: " . $e->getMessage();
    }
    
    return $results;
}
```

---

## Updated Product ConfigOptions

```php
// nicsrs_ssl.php
function nicsrs_ssl_ConfigOptions()
{
    return [
        'cert_type' => [
            'FriendlyName' => 'Certificate Type',
            'Type' => 'dropdown',
            'Options' => nicsrsFunc::getCertAttributes(null, 'name'),
            'Description' => 'Select SSL certificate type',
        ],
        // API Token removed - now uses Addon Module settings
        'auto_renewal_reminder' => [
            'FriendlyName' => 'Renewal Reminder',
            'Type' => 'yesno',
            'Description' => 'Send renewal reminder emails',
        ],
        'reminder_days' => [
            'FriendlyName' => 'Reminder Days Before',
            'Type' => 'dropdown',
            'Options' => '7,14,30,60',
            'Default' => '30',
            'Description' => 'Days before expiry to send reminder',
        ],
    ];
}
```

---

## Timeline Summary

| Week | Phase | Hours | Deliverables |
|------|-------|-------|--------------|
| 1 | Foundation | 20h | Schema, ApiConfig, DTOs |
| 2 | Core Services | 25h | Sync, Download, DCV |
| 3 | UI & Testing | 25h | Templates, Tests, Docs |
| **Total** | | **70h** | |

---

## Risk Assessment

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Data migration issues | High | Low | Backup table, rollback script |
| API key not found | Medium | Low | Fallback to legacy config |
| Existing orders break | High | Low | Backward compatible columns |
| Performance with large data | Medium | Medium | Indexes on key columns |

---

## Definition of Done

### Per Task
- [ ] Code complete
- [ ] Unit tests passing
- [ ] Integration tests passing
- [ ] Code reviewed
- [ ] Documentation updated

### For Release
- [ ] All phases complete
- [ ] Migration script tested
- [ ] Backward compatibility verified
- [ ] Performance acceptable
- [ ] CHANGELOG updated
- [ ] Version bumped to 1.2.0

---

## Approval Required

| Item | Status |
|------|--------|
| Database schema changes | ⬜ Pending |
| Shared API key approach | ⬜ Pending |
| Timeline (3 weeks) | ⬜ Pending |
| Ready to proceed | ⬜ Pending |

---

**Document Version:** 1.0  
**Created:** 2025-01-20  
**Author:** HVN GROUP