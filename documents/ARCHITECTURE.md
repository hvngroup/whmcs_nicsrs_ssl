# NicSRS SSL Module - Architecture Documentation

## Overview

This document describes the architecture of the NicSRS SSL Module for WHMCS, including both the Server Module (client-facing) and Admin Addon Module (admin-facing).

> **Last Updated**: January 2026  
> **Version**: 1.2.0  
> **Author**: HVN GROUP

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         WHMCS Platform                              │
├──────────────────────────────┬──────────────────────────────────────┤
│      Client Area             │          Admin Area                  │
│  ┌────────────────────────┐  │  ┌────────────────────────────────┐  │
│  │   Server Module        │  │  │     Admin Addon Module         │  │
│  │   (nicsrs_ssl)         │  │  │     (nicsrs_ssl_admin)         │  │
│  │                        │  │  │                                │  │
│  │  - Certificate Apply   │  │  │  - Dashboard                   │  │
│  │  - DCV Management      │  │  │  - Order Management            │  │
│  │  - Status View         │  │  │  - Product Sync                │  │
│  │  - Download Cert       │  │  │  - Import/Link Certs           │  │
│  └───────────┬────────────┘  │  │  - Settings                    │  │
│              │               │  │  - Activity Logs               │  │
│              │               │  └───────────┬────────────────────┘  │
├──────────────┼───────────────┴──────────────┼────────────────────────┤
│              │       Shared API Token       │                        │
│              │  ┌───────────────────────────┼─────┐                  │
│              └──┤  Priority 1: mod_nicsrs_settings.api_token         │
│                 │  Priority 2: tbladdonmodules.api_token (fallback)  │
│                 │  Priority 3: configoption2 (legacy)                │
│                 └────────────────────┬──────────────────────────────┘│
└──────────────────────────────────────┼───────────────────────────────┘
                                       │
                                       ▼
                    ┌──────────────────────────────────┐
                    │         NicSRS API               │
                    │   https://portal.nicsrs.com/ssl  │
                    └──────────────────────────────────┘
```

---

## Module Structure

### Server Module (nicsrs_ssl)

```
modules/servers/nicsrs_ssl/
├── nicsrs_ssl.php              # Main entry point & WHMCS callbacks
├── hooks.php                   # WHMCS hooks
│
├── lang/
│   ├── english.php
│   ├── vietnamese.php
│   └── chinese.php
│
├── src/
│   ├── Config/
│   │   ├── ApiConfig.php       # Shared API key management
│   │   ├── Constants.php       # Status, DCV method constants
│   │   └── CertificateTypes.php
│   │
│   ├── model/
│   │   ├── Controller/
│   │   │   ├── PageController.php     # Page rendering
│   │   │   └── ActionController.php   # Form actions
│   │   │
│   │   └── Service/
│   │       ├── nicsrsAPI.php          # API client
│   │       ├── nicsrsFunc.php         # Helper functions
│   │       ├── nicsrsSSLSql.php       # Database operations
│   │       ├── nicsrsResponse.php     # Response formatting
│   │       └── nicsrsTemplate.php     # Template helpers
│   │
│   └── Helper/
│       ├── CsrHelper.php       # CSR parsing
│       ├── DateHelper.php      # Date format handling
│       └── DcvHelper.php       # DCV method mapping
│
└── view/
    ├── applycert.tpl           # Certificate application form
    ├── message.tpl             # Pending status display
    └── complete.tpl            # Completed certificate display
```

### Admin Addon Module (nicsrs_ssl_admin)

```
modules/addons/nicsrs_ssl_admin/
├── nicsrs_ssl_admin.php        # Entry point (config, activate, output)
├── hooks.php                   # WHMCS hooks
│
├── lib/
│   ├── Config/
│   │   └── ApiConfig.php       # Shared API configuration
│   │
│   ├── Controller/
│   │   ├── BaseController.php       # Base controller
│   │   ├── DashboardController.php  # Dashboard
│   │   ├── ProductController.php    # Products
│   │   ├── OrderController.php      # Orders
│   │   ├── ImportController.php     # Import/Link
│   │   └── SettingsController.php   # Settings
│   │
│   ├── Service/
│   │   ├── NicsrsApiService.php     # API client
│   │   └── ActivityLogger.php       # Activity logging
│   │
│   ├── Helper/
│   │   ├── ViewHelper.php           # View utilities
│   │   ├── DateHelper.php           # Date handling
│   │   └── DcvHelper.php            # DCV method mapping
│   │
│   └── DTO/
│       └── CertificateData.php      # API response DTO
│
├── templates/
│   ├── dashboard.php
│   ├── orders.php
│   ├── order_detail.php
│   ├── products.php
│   ├── import.php
│   └── settings.php
│
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
│
└── lang/
    ├── english.php
    └── vietnamese.php
```

---

## Database Schema

### nicsrs_sslorders Table (Updated for v1.2.0)

```sql
CREATE TABLE `nicsrs_sslorders` (
    -- Primary Keys
    `id` INT(10) AUTO_INCREMENT PRIMARY KEY,
    `userid` INT(10) NOT NULL,
    `serviceid` INT(10) NOT NULL,
    `addon_id` TEXT,
    
    -- NicSRS Reference
    `remoteid` TEXT,                          -- NicSRS certId
    `vendor_id` VARCHAR(50),                  -- Sectigo vendor ID
    `vendor_cert_id` VARCHAR(50),             -- Vendor certificate ID
    
    -- Product Info
    `module` TEXT,                            -- 'nicsrs_ssl'
    `certtype` TEXT,                          -- Product code
    
    -- Configuration
    `configdata` TEXT,                        -- JSON (domainInfo, applyReturn, etc.)
    
    -- Dates
    `provisiondate` DATE,                     -- Order date
    `completiondate` DATETIME,                -- Certificate issue datetime
    `begin_date` DATETIME,                    -- Certificate validity start
    `end_date` DATETIME,                      -- Certificate validity end
    `due_date` DATE,                          -- Renewal due date
    `last_sync` DATETIME,                     -- Last API sync time
    
    -- Status
    `status` TEXT,                            -- Order status (lowercase)
    `cert_status` VARCHAR(20),                -- Certificate status
    
    -- Certificate Data
    `certificate` MEDIUMTEXT,                 -- PEM certificate
    `ca_certificate` MEDIUMTEXT,              -- CA bundle
    `private_key` MEDIUMTEXT,                 -- Private key (if stored)
    
    -- Pre-formatted Certificates
    `jks_data` MEDIUMTEXT,                    -- Base64 JKS file
    `pkcs12_data` MEDIUMTEXT,                 -- Base64 PKCS12 file
    `jks_password` VARCHAR(100),              -- JKS password
    `pkcs12_password` VARCHAR(100),           -- PKCS12 password
    
    -- DCV Information
    `dcv_file_name` VARCHAR(255),             -- HTTP validation filename
    `dcv_file_content` TEXT,                  -- HTTP file content
    `dcv_file_path` VARCHAR(500),             -- Full validation URL
    `dcv_dns_host` VARCHAR(255),              -- DNS record host
    `dcv_dns_value` VARCHAR(500),             -- DNS record value
    `dcv_dns_type` VARCHAR(20),               -- DNS type (CNAME/TXT)
    
    -- Indexes
    INDEX `idx_userid` (`userid`),
    INDEX `idx_serviceid` (`serviceid`),
    INDEX `idx_status` (`status`(20)),
    INDEX `idx_end_date` (`end_date`)
);
```

### mod_nicsrs_settings Table

```sql
CREATE TABLE `mod_nicsrs_settings` (
    `id` INT(10) AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` VARCHAR(50) DEFAULT 'text',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default settings
INSERT INTO `mod_nicsrs_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('api_token', '', 'password'),
('items_per_page', '25', 'number'),
('auto_sync_enabled', '1', 'boolean'),
('sync_interval_hours', '6', 'number'),
('expiry_warning_days', '30', 'number');
```

### mod_nicsrs_activity_log Table

```sql
CREATE TABLE `mod_nicsrs_activity_log` (
    `id` INT(10) AUTO_INCREMENT PRIMARY KEY,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50),
    `entity_id` INT(10),
    `old_value` TEXT,
    `new_value` TEXT,
    `admin_id` INT(10),
    `admin_username` VARCHAR(100),
    `ip_address` VARCHAR(45),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
);
```

---

## configdata JSON Structure (Updated)

The `configdata` field stores detailed order information as JSON:

```json
{
    "csr": "-----BEGIN CERTIFICATE REQUEST-----...",
    "domainInfo": [
        {
            "domainName": "example.com",
            "dcvMethod": "CNAME_CSR_HASH",
            "dcvEmail": "",
            "isVerified": true,
            "is_verify": "verified"
        },
        {
            "domainName": "www.example.com",
            "dcvMethod": "EMAIL",
            "dcvEmail": "admin@example.com",
            "isVerified": true,
            "is_verify": "verified"
        }
    ],
    "Administrator": {
        "firstName": "John",
        "lastName": "Doe",
        "email": "admin@example.com",
        "phone": "+84123456789"
    },
    "organizationInfo": {
        "organizationName": "Example Inc",
        "country": "VN",
        "province": "Ho Chi Minh",
        "city": "Ho Chi Minh",
        "address": "123 Example Street",
        "postalCode": "70000",
        "phone": "+84123456789"
    },
    "applyReturn": {
        "certId": "12345678",
        "vendorId": "2771240592",
        "vendorCertId": "39831817562",
        "beginDate": "2026-01-19 08:00:00",
        "endDate": "2027-02-20 07:59:59",
        "dueDate": "2028-01-19 00:00:00",
        "certificate": "-----BEGIN CERTIFICATE-----...",
        "caCertificate": "-----BEGIN CERTIFICATE-----...",
        "DCVfileName": "148ECC23D64F50CDCCA4F0DAF72A9A4B.txt",
        "DCVfileContent": "hash_content...",
        "DCVdnsHost": "_148ecc23d64f50cdcca4f0daf72a9a4b",
        "DCVdnsValue": "cname_value.sectigo.com",
        "DCVdnsType": "CNAME",
        "applyTime": "2026-01-15 10:30:00"
    },
    "replaceTimes": 0,
    "originalDomains": ["example.com", "www.example.com"],
    "lastRefresh": "2026-01-20 14:30:00",
    "importedAt": null,
    "importedBy": null
}
```

---

## Order Status State Machine

```
                    ┌─────────────────────┐
                    │ Awaiting Config     │ ← Order created in WHMCS
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │ Draft               │ ← User saves form (optional)
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │ Pending             │ ← Submitted to CA (NicSRS)
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              ▼                ▼                ▼
    ┌─────────────────┐ ┌─────────────┐ ┌─────────────┐
    │ Complete        │ │ Cancelled   │ │ Expired     │
    │ (issued)        │ │ (cancelled) │ │ (expired)   │
    └────────┬────────┘ └─────────────┘ └─────────────┘
             │
    ┌────────┴────────┐
    ▼                 ▼
┌──────────┐  ┌──────────────┐
│ Reissued │  │ Renewed      │
└────┬─────┘  └──────┬───────┘
     │               │
     ▼               ▼
┌─────────────────────────┐
│ Pending                 │ ← Back to pending state
└─────────────────────────┘
```

---

## Request Flow Examples

### Certificate Application Flow

```
1. User clicks "Configure SSL" in Client Area
   │
   ▼
2. WHMCS calls nicsrs_ssl_ClientArea()
   │
   ▼
3. PageDispatcher routes to PageController::index()
   │
   ▼
4. PageController:
   ├── Checks order status (Awaiting Configuration)
   ├── Loads certificate type attributes
   ├── Returns applycert.tpl template
   │
   ▼
5. User fills form and submits
   │
   ▼
6. ActionDispatcher routes to ActionController::submitApply()
   │
   ▼
7. ActionController:
   ├── Validates input data
   ├── Calls nicsrsAPI::validate()
   ├── Calls nicsrsAPI::place()
   ├── Stores applyReturn data (certId, vendorId, DCV info)
   ├── Updates database via nicsrsSSLSql
   └── Returns JSON response
   │
   ▼
8. Certificate enters "Pending" status
   │
   ▼
9. User performs DCV (Email/DNS/HTTP)
   │
   ▼
10. Periodic refresh via collect API
    │
    ▼
11. When status == COMPLETE:
    ├── Store certificate data
    ├── Update status to "complete"
    └── Enable certificate download
```

### Certificate Download Flow

```
1. User clicks "Download Certificate"
   │
   ▼
2. ActionController::downCert() called
   │
   ▼
3. Fetch latest data from collect API
   │
   ▼
4. Check for pre-formatted certificates:
   ├── jks available? → Include JKS file
   ├── pkcs12 available? → Include PKCS12 file
   │
   ▼
5. Create ZIP archive with:
   ├── Apache format (.crt, .ca-bundle)
   ├── Nginx format (.pem combined)
   ├── IIS/PKCS12 format (.p12)
   ├── Tomcat/JKS format (.jks)
   └── passwords.txt (if jks/pkcs12)
   │
   ▼
6. Return base64 encoded ZIP
```

---

## API Response Data Transfer Object (DTO)

### CertificateData DTO

```php
<?php
namespace NicsrsAdmin\DTO;

class CertificateData
{
    // Response metadata
    public int $code;
    public string $status;           // lowercase
    public ?string $certStatus = null;
    
    // Certificate files
    public ?string $certificate = null;
    public ?string $caCertificate = null;
    public ?string $privateKey = null;
    
    // Pre-formatted certificates (NEW in v1.2.0)
    public ?string $jks = null;
    public ?string $pkcs12 = null;
    public ?string $jksPassword = null;
    public ?string $pkcsPassword = null;
    
    // Dates (FULL DATETIME format)
    public ?string $beginDate = null;     // Y-m-d H:i:s
    public ?string $endDate = null;       // Y-m-d H:i:s
    public ?string $dueDate = null;       // Y-m-d H:i:s
    
    // Vendor tracking (NEW in v1.2.0)
    public ?string $vendorId = null;
    public ?string $vendorCertId = null;
    
    // DCV information
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
        
        // Metadata
        $dto->code = (int) ($response['code'] ?? 0);
        $dto->status = strtolower($response['status'] ?? 'pending');
        $dto->certStatus = strtolower($response['certStatus'] ?? $dto->status);
        
        $data = $response['data'] ?? [];
        
        // Certificate files
        $dto->certificate = $data['certificate'] ?? null;
        $dto->caCertificate = $data['caCertificate'] ?? null;
        $dto->privateKey = $data['rsaPrivateKey'] ?? null;
        
        // Pre-formatted (base64 encoded)
        $dto->jks = $data['jks'] ?? null;
        $dto->pkcs12 = $data['pkcs12'] ?? null;
        $dto->jksPassword = $data['jksPass'] ?? null;
        $dto->pkcsPassword = $data['pkcsPass'] ?? null;
        
        // Dates (parse full datetime)
        $dto->beginDate = self::parseDateTime($data['beginDate'] ?? null);
        $dto->endDate = self::parseDateTime($data['endDate'] ?? null);
        $dto->dueDate = self::parseDateTime($data['dueDate'] ?? null);
        
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
    
    private static function parseDateTime(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
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
            'due_date' => $this->dueDate ? substr($this->dueDate, 0, 10) : null,
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
    
    public function hasCertificate(): bool
    {
        return !empty($this->certificate);
    }
    
    public function hasJks(): bool
    {
        return !empty($this->jks);
    }
    
    public function hasPkcs12(): bool
    {
        return !empty($this->pkcs12);
    }
}
```

---

## Helper Classes

### DateHelper

```php
<?php
namespace NicsrsAdmin\Helper;

class DateHelper
{
    /**
     * Parse full datetime from API
     */
    public static function parseDateTime(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
    
    /**
     * Parse date only
     */
    public static function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
    }
    
    /**
     * Check if date is valid (not 0000-00-00)
     */
    public static function isValidDate($date): bool
    {
        if (empty($date)) return false;
        if ($date === '0000-00-00' || $date === '0000-00-00 00:00:00') return false;
        return strtotime($date) !== false;
    }
    
    /**
     * Format for display
     */
    public static function formatDisplay(?string $date): string
    {
        if (!self::isValidDate($date)) return 'N/A';
        return date('M d, Y', strtotime($date));
    }
    
    /**
     * Format with time
     */
    public static function formatWithTime(?string $date): string
    {
        if (!self::isValidDate($date)) return 'N/A';
        return date('M d, Y H:i', strtotime($date));
    }
    
    /**
     * Calculate days until expiry
     */
    public static function daysUntilExpiry(?string $endDate): int
    {
        if (!self::isValidDate($endDate)) return 0;
        $end = new \DateTime($endDate);
        $now = new \DateTime();
        $diff = $now->diff($end);
        return $diff->invert ? -$diff->days : $diff->days;
    }
}
```

### DcvHelper

```php
<?php
namespace NicsrsAdmin\Helper;

class DcvHelper
{
    /**
     * DCV method mapping (API value => Display name)
     */
    public const METHODS = [
        'CNAME_CSR_HASH' => 'DNS CNAME',
        'HTTP_CSR_HASH' => 'HTTP File',
        'DNS_CSR_HASH' => 'DNS TXT',
        'EMAIL' => 'Email',
    ];
    
    /**
     * DCV method types
     */
    public const TYPE_DNS = ['CNAME_CSR_HASH', 'DNS_CSR_HASH'];
    public const TYPE_HTTP = ['HTTP_CSR_HASH'];
    public const TYPE_EMAIL = ['EMAIL'];
    
    /**
     * Get display name for DCV method
     */
    public static function getDisplayName(string $method): string
    {
        return self::METHODS[strtoupper($method)] ?? $method;
    }
    
    /**
     * Get all methods as options
     */
    public static function getMethodOptions(): array
    {
        return self::METHODS;
    }
    
    /**
     * Check if DNS-based method
     */
    public static function isDnsMethod(string $method): bool
    {
        return in_array(strtoupper($method), self::TYPE_DNS);
    }
    
    /**
     * Check if HTTP-based method
     */
    public static function isHttpMethod(string $method): bool
    {
        return in_array(strtoupper($method), self::TYPE_HTTP);
    }
    
    /**
     * Check if Email-based method
     */
    public static function isEmailMethod(string $method): bool
    {
        return in_array(strtoupper($method), self::TYPE_EMAIL);
    }
    
    /**
     * Normalize method name from API
     */
    public static function normalizeMethod(string $method): string
    {
        $method = strtoupper(trim($method));
        return isset(self::METHODS[$method]) ? $method : 'EMAIL';
    }
}
```

---

## Security Architecture

### Authentication Flow

```
┌────────────┐     ┌─────────────┐     ┌──────────────┐
│  Client    │────▶│   WHMCS     │────▶│ nicsrs_ssl   │
│  Browser   │     │   Session   │     │   Module     │
└────────────┘     └─────────────┘     └──────┬───────┘
                                              │
                                    ┌─────────▼─────────┐
                                    │ Permission Check  │
                                    │ $_SESSION['uid']  │
                                    │ == order.userid   │
                                    └─────────┬─────────┘
                                              │
                                    ┌─────────▼─────────┐
                                    │ API Token (shared)│
                                    │ ApiConfig::get()  │
                                    └───────────────────┘
```

### Input Validation Points

1. **Client-side**: JavaScript validation for UX
2. **Dispatcher**: Basic request validation
3. **Controller**: Business logic validation
4. **API Service**: Final validation before API call

### API Token Priority

```php
// 1. Check mod_nicsrs_settings (highest priority)
// 2. Check tbladdonmodules (fallback)
// 3. Check configoption2 (legacy)

class ApiConfig
{
    public static function getApiToken(): ?string
    {
        // Priority 1: Addon settings
        $token = self::getFromSettings();
        if ($token) return $token;
        
        // Priority 2: Addon module config
        $token = self::getFromAddonConfig();
        if ($token) return $token;
        
        // Priority 3: Legacy configoption2
        return self::getFromLegacyConfig();
    }
}
```

---

## Changelog

### Version 1.2.0 (January 2026)
- Added CertificateData DTO for API response handling
- Updated database schema with new columns
- Added DCV method mapping (CNAME_CSR_HASH, HTTP_CSR_HASH, etc.)
- Added DateHelper for proper datetime parsing
- Added pre-formatted certificate support (JKS, PKCS12)
- Added vendor tracking (vendorId, vendorCertId)
- Added dueDate for renewal tracking
- Updated configdata JSON structure