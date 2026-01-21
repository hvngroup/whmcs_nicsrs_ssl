# NicSRS SSL Module - Technical Architecture

## Overview

The NicSRS SSL Module follows a Model-View-Controller (MVC) architecture pattern adapted for WHMCS provisioning module requirements. This document reflects the **actual implementation** based on real API responses and configdata structure.

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                           WHMCS Core                                 │
│  ┌───────────────┐  ┌───────────────┐  ┌───────────────────────────┐│
│  │ Admin Area    │  │ Client Area   │  │ Cron/Automation           ││
│  └───────┬───────┘  └───────┬───────┘  └───────────┬───────────────┘│
└──────────┼──────────────────┼──────────────────────┼────────────────┘
           │                  │                      │
           ▼                  ▼                      ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Module Entry Points                               │
│  ┌─────────────────────────────────────────────────────────────────┐│
│  │ modules/servers/nicsrs_ssl/nicsrs_ssl.php (Server Module)       ││
│  │ modules/addons/nicsrs_ssl_admin/nicsrs_ssl_admin.php (Addon)    ││
│  └─────────────────────────────────────────────────────────────────┘│
└─────────────────────────────┬───────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Dispatcher Layer                              │
│  ┌─────────────────────────┐    ┌─────────────────────────────────┐ │
│  │ PageDispatcher          │    │ ActionDispatcher                │ │
│  │ - Route page views      │    │ - Route AJAX/form actions       │ │
│  │ - Template selection    │    │ - JSON responses                │ │
│  └──────────┬──────────────┘    └──────────────┬──────────────────┘ │
└─────────────┼───────────────────────────────────┼───────────────────┘
              │                                   │
              ▼                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                       Controller Layer                               │
│  ┌─────────────────────────┐    ┌─────────────────────────────────┐ │
│  │ PageController          │    │ ActionController                │ │
│  │ - index()               │    │ - submitApply()                 │ │
│  │ - complete()            │    │ - downCert()                    │ │
│  │ - Renders templates     │    │ - batchUpdateDCV()              │ │
│  │                         │    │ - refreshStatus()               │ │
│  │                         │    │ - cancelOrder()                 │ │
│  └──────────┬──────────────┘    └──────────────┬──────────────────┘ │
└─────────────┼───────────────────────────────────┼───────────────────┘
              │                                   │
              ▼                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Service Layer                                 │
│  ┌──────────────┐ ┌──────────────┐ ┌────────────────────────────┐   │
│  │ nicsrsAPI    │ │ nicsrsSSLSql │ │ nicsrsFunc                 │   │
│  │ - API calls  │ │ - DB queries │ │ - CSR decode               │   │
│  │ - collect()  │ │ - CRUD ops   │ │ - Certificate zip          │   │
│  │ - place()    │ │              │ │ - Helper functions         │   │
│  └──────┬───────┘ └──────┬───────┘ └────────────────────────────┘   │
│  ┌──────┴───────┐ ┌──────┴───────┐ ┌────────────────────────────┐   │
│  │ nicsrsResp   │ │ nicsrsTempl  │ │ Constants & Config         │   │
│  │ - Responses  │ │ - Templates  │ │ - DCV methods              │   │
│  │              │ │              │ │ - Status codes             │   │
│  └──────────────┘ └──────────────┘ └────────────────────────────┘   │
└─────────────────────────────┬───────────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              ▼                               ▼
┌─────────────────────────────┐   ┌───────────────────────────────────┐
│     NicSRS API              │   │      WHMCS Database               │
│  portal.nicsrs.com/ssl      │   │  - tblhosting                     │
│  - /validate                │   │  - tblproducts                    │
│  - /place                   │   │  - tblclients                     │
│  - /collect                 │   │  - nicsrs_sslorders               │
│  - /updateDCV               │   │  - mod_nicsrs_settings            │
│  - /cancel                  │   │  - mod_nicsrs_products            │
│  - /revoke                  │   │  - mod_nicsrs_activity_log        │
│  - /reissue                 │   │                                   │
│  - /renew                   │   │                                   │
└─────────────────────────────┘   └───────────────────────────────────┘
```

---

## Module Directory Structure

### Server Module (Client-Facing)
```
modules/servers/nicsrs_ssl/
├── nicsrs_ssl.php                    # Main entry point
├── hooks.php                         # WHMCS hooks
│
├── lang/
│   ├── english.php
│   ├── vietnamese.php
│   ├── chinese.php                   # Traditional Chinese
│   └── chinese-cn.php                # Simplified Chinese
│
├── src/
│   ├── config/
│   │   ├── const.php                 # Constants (DCV methods, status)
│   │   └── country.json              # Country list for forms
│   │
│   └── model/
│       ├── Controller/
│       │   ├── PageController.php    # Page rendering logic
│       │   └── ActionController.php  # Certificate actions
│       │
│       ├── Dispatcher/
│       │   ├── PageDispatcher.php    # Page routing
│       │   └── ActionDispatcher.php  # Action routing
│       │
│       └── Service/
│           ├── nicsrsAPI.php         # API client
│           ├── nicsrsFunc.php        # Utility functions
│           ├── nicsrsResponse.php    # Response formatting
│           ├── nicsrsSSLSql.php      # Database operations
│           └── nicsrsTemplate.php    # Template helpers
│
└── view/
    ├── applycert.tpl                 # Certificate application form
    ├── complete.tpl                  # Completed certificate view
    ├── message.tpl                   # Status messages
    ├── replace.tpl                   # Certificate replacement
    ├── replace1.tpl                  # Replacement step 1
    ├── error.tpl                     # Error display
    └── home/                         # Static assets (CSS, JS)
```

### Admin Addon Module
```
modules/addons/nicsrs_ssl_admin/
├── nicsrs_ssl_admin.php              # Entry point, WHMCS hooks
├── hooks.php                         # Admin area hooks
│
├── lib/
│   ├── Config/
│   │   └── ApiConfig.php             # Shared API configuration
│   │
│   ├── Controller/
│   │   ├── BaseController.php        # Base controller class
│   │   ├── DashboardController.php   # Dashboard stats & charts
│   │   ├── ProductController.php     # Product management
│   │   ├── OrderController.php       # Order management
│   │   └── SettingsController.php    # Module settings
│   │
│   ├── Service/
│   │   ├── NicsrsApiService.php      # API communication
│   │   ├── ProductService.php        # Product business logic
│   │   ├── OrderService.php          # Order business logic
│   │   └── ActivityLogger.php        # Audit logging
│   │
│   └── Helper/
│       ├── ViewHelper.php            # Template helpers
│       └── Pagination.php            # Pagination utility
│
├── templates/
│   ├── layout.tpl                    # Main layout wrapper
│   ├── dashboard.tpl                 # Dashboard view
│   ├── products/
│   │   └── list.tpl                  # Products list
│   ├── orders/
│   │   ├── list.tpl                  # Orders list
│   │   └── detail.tpl                # Order detail view
│   └── settings.tpl                  # Settings page
│
├── assets/
│   ├── css/
│   │   └── admin.css                 # Ant Design-inspired styles
│   └── js/
│       └── admin.js                  # Main JavaScript
│
└── lang/
    ├── english.php
    └── vietnamese.php
```

---

## Database Schema

### Main Orders Table: `nicsrs_sslorders`

```sql
CREATE TABLE `nicsrs_sslorders` (
    `id` INT(10) AUTO_INCREMENT PRIMARY KEY,
    `userid` INT(10) NOT NULL,              -- WHMCS user ID
    `serviceid` INT(10) NOT NULL,           -- WHMCS service ID
    `addon_id` TEXT,                        -- Addon service ID
    `remoteid` TEXT,                        -- NicSRS certId
    `module` TEXT,                          -- Module name (nicsrs_ssl)
    `certtype` TEXT,                        -- Certificate product code
    `configdata` TEXT,                      -- JSON configuration (see below)
    `provisiondate` DATE,                   -- Order creation date
    `completiondate` DATETIME,              -- Certificate issue date
    `status` TEXT,                          -- Order status
    INDEX `idx_userid` (`userid`),
    INDEX `idx_serviceid` (`serviceid`),
    INDEX `idx_status` (`status`(20))
);
```

### Addon Module Tables

```sql
-- Products cache table
CREATE TABLE `mod_nicsrs_products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_code` VARCHAR(100) UNIQUE NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `vendor` VARCHAR(50),
    `validation_type` ENUM('dv','ov','ev') DEFAULT 'dv',
    `price_1year` DECIMAL(10,2),
    `price_2year` DECIMAL(10,2),
    `max_domains` INT DEFAULT 1,
    `wildcard` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `synced_at` DATETIME,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Activity log table
CREATE TABLE `mod_nicsrs_activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,     -- 'order', 'product', 'settings'
    `entity_id` INT,
    `action` VARCHAR(50) NOT NULL,          -- 'refresh', 'cancel', 'revoke'
    `details` TEXT,                         -- JSON details
    `ip_address` VARCHAR(45),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created` (`created_at`)
);

-- Settings table
CREATE TABLE `mod_nicsrs_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `setting_type` VARCHAR(50) DEFAULT 'text',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## configdata JSON Structure

The `configdata` field stores complete certificate data as JSON. Based on **actual production data**:

```json
{
    "server": "other",
    "csr": "-----BEGIN CERTIFICATE REQUEST-----\n...\n-----END CERTIFICATE REQUEST-----\n",
    "privateKey": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
    
    "domainInfo": [
        {
            "domainName": "mspace.com.vn",
            "dcvMethod": "CNAME_CSR_HASH",
            "dcvEmail": "",
            "isVerified": true,
            "is_verify": "verified"
        }
    ],
    
    "Administrator": {
        "organation": "COMPANY NAME",
        "job": "admin",
        "firstName": "John",
        "lastName": "Doe",
        "email": "admin@example.com",
        "mobile": "0123456789",
        "country": "VN",
        "address": "123 Main St",
        "city": "Hanoi",
        "state": "Hanoi",
        "postCode": "10000"
    },
    
    "organizationInfo": {
        "organizationName": "",
        "organizationAddress": "",
        "organizationCity": "",
        "organizationCountry": "",
        "organizationPostCode": "",
        "organizationMobile": ""
    },
    
    "originalfromOthers": "0",
    
    "applyReturn": {
        "certId": "260113vghhracpaw",
        "vendorId": "1433958846",
        "vendorCertId": "1438517035",
        
        "DCVfileName": "fileauth.txt",
        "DCVfileContent": "_3525cdeanma96j6bosy7zdq8jld7tly",
        "DCVdnsHost": "_dnsauth",
        "DCVdnsValue": "_3525cdeanma96j6bosy7zdq8jld7tly",
        "DCVdnsType": "TXT",
        "DCVfilePath": "http://example.com/.well-known/pki-validation/fileauth.txt",
        
        "applyTime": "2026-01-13 09:45:48",
        "beginDate": "2026-01-13 08:00:00",
        "endDate": "2027-01-13 07:59:59",
        "dueDate": "2027-01-13 00:00:00",
        
        "certificate": "-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----",
        "caCertificate": "-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----",
        "certPath": "",
        "privateKey": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
        
        "jks": "base64_encoded_jks_data...",
        "pkcs12": "base64_encoded_pkcs12_data...",
        "jksPass": "3DUy2fQB9zy5SSIW",
        "pkcsPass": "bFPZbEqmIC5CrAG1",
        
        "application": { "status": "done" },
        "dcv": { "status": "done" },
        "issued": { "status": "done" },
        
        "dcvList": [
            {
                "dcvEmail": "",
                "dcvMethod": "CNAME_CSR_HASH",
                "is_verify": "verified",
                "domainName": "mspace.com.vn"
            }
        ]
    },
    
    "applyParams": {
        "csr": "-----BEGIN CERTIFICATE REQUEST-----\n...",
        "Administrator": {...},
        "tech": {...},
        "finance": {...},
        "organizationInfo": {...},
        "server": "other"
    },
    
    "lastRefresh": "2026-01-19 13:28:14"
}
```

---

## DCV Method Constants

**Important:** API returns methods with `_CSR_HASH` suffix.

```php
// src/config/const.php
const DCV_METHODS = [
    'CNAME_CSR_HASH' => [
        'name' => 'DNS CNAME',
        'type' => 'dns',
        'description' => 'Add a CNAME record to your DNS'
    ],
    'HTTP_CSR_HASH' => [
        'name' => 'HTTP File',
        'type' => 'http',
        'description' => 'Upload a file to your web server'
    ],
    'DNS_CSR_HASH' => [
        'name' => 'DNS TXT',
        'type' => 'dns',
        'description' => 'Add a TXT record to your DNS'
    ],
    'EMAIL' => [
        'name' => 'Email',
        'type' => 'email',
        'description' => 'Verify via email'
    ]
];
```

---

## Order Status State Machine

```
                    ┌─────────────────────┐
                    │ Awaiting Config     │ ← Order created in WHMCS
                    └──────────┬──────────┘
                               │ User submits form
                    ┌──────────▼──────────┐
                    │ Draft               │ ← User saves incomplete form
                    └──────────┬──────────┘
                               │ User clicks Submit
                    ┌──────────▼──────────┐
                    │ Pending             │ ← Submitted to NicSRS API
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              │ DCV Completed  │                │ User cancels
              ▼                │                ▼
    ┌─────────────────┐       │      ┌─────────────────┐
    │ Complete        │       │      │ Cancelled       │
    │ (Issued)        │       │      └─────────────────┘
    └────────┬────────┘       │
             │                │
    ┌────────┴────────┐       │
    │                 │       │
    ▼                 ▼       ▼
┌──────────┐   ┌──────────┐  ┌──────────┐
│ Revoked  │   │ Expired  │  │ Reissued │
└──────────┘   └──────────┘  └────┬─────┘
                                  │
                                  ▼
                           ┌──────────┐
                           │ Pending  │ ← Reissue submitted
                           └──────────┘
```

---

## Request Flow: Certificate Application

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
   ├── Loads certificate attributes from product config
   ├── Returns applycert.tpl template with form
   │
   ▼
5. User fills form (CSR, domain info, contacts) and submits
   │
   ▼
6. AJAX request to ActionController::submitApply()
   │
   ▼
7. ActionController:
   ├── Validates input data (CSR, domains, contacts)
   ├── Calls nicsrsAPI::validate() to pre-validate
   ├── Calls nicsrsAPI::place() to submit order
   ├── Stores response in configdata.applyReturn
   ├── Updates order status to "Pending"
   └── Returns JSON response with certId
   │
   ▼
8. User sees pending status with DCV instructions
```

---

## Request Flow: Status Refresh (Collect API)

```
1. User/Admin clicks "Refresh Status"
   │
   ▼
2. AJAX request to ActionController::refreshStatus()
   │
   ▼
3. ActionController:
   ├── Gets certId from order.remoteid
   ├── Calls nicsrsAPI::collect(certId)
   │
   ▼
4. API returns full certificate data:
   {
     code: 1,
     status: "COMPLETE",
     certStatus: "COMPLETE", 
     data: {
       beginDate: "2026-01-19 08:00:00",   // Full datetime
       endDate: "2027-02-20 07:59:59",
       certificate: "-----BEGIN...",
       caCertificate: "-----BEGIN...",
       jks: "base64...",
       pkcs12: "base64...",
       jksPass: "password",
       pkcsPass: "password",
       vendorId: "2771240592",
       vendorCertId: "39831817562",
       dcvList: [{
         dcvMethod: "CNAME_CSR_HASH",      // Note: with _CSR_HASH
         is_verify: "verified",
         domainName: "example.com"
       }]
     }
   }
   │
   ▼
5. ActionController:
   ├── Merges response into configdata.applyReturn
   ├── Updates configdata.lastRefresh timestamp
   ├── Updates order status if changed
   ├── Saves to database
   └── Returns JSON response
```

---

## Request Flow: Certificate Download

```
1. User clicks "Download Certificate"
   │
   ▼
2. AJAX request to ActionController::downCert()
   │
   ▼
3. ActionController:
   ├── Verifies user permissions
   ├── Gets certificate data from configdata
   │
   ▼
4. nicsrsFunc::zipCert() creates archive:
   │
   ├── Apache format (.crt, .ca-bundle, .key)
   ├── Nginx format (.pem combined)
   ├── IIS format (.p12 from pkcs12 data)
   ├── Tomcat format (.jks from jks data)
   └── Passwords.txt (jksPass, pkcsPass)
   │
   ▼
5. Returns base64 encoded ZIP to browser
```

---

## API Client Implementation

```php
class nicsrsAPI {
    private const API_BASE = 'https://portal.nicsrs.com/ssl';
    
    public static function collect(string $certId): array
    {
        $response = self::request('/collect', [
            'certId' => $certId
        ]);
        
        // Response contains:
        // - code: 1 (success), 2 (processing)
        // - status: "COMPLETE", "PENDING", etc.
        // - certStatus: "COMPLETE", "PENDING", etc.
        // - data: certificate data with full datetime
        
        return $response;
    }
    
    private static function request(string $endpoint, array $data): array
    {
        // Add API token
        $data['api_token'] = self::getApiToken();
        
        $ch = curl_init(self::API_BASE . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Log for debugging
        logModuleCall('nicsrs_ssl', $endpoint, $data, $response);
        
        return json_decode($response, true);
    }
}
```

---

## Date/Time Handling

**Critical:** API returns full datetime format `Y-m-d H:i:s`.

```php
class DateHelper {
    /**
     * Parse datetime from API response
     * API format: "2026-01-19 08:00:00"
     */
    public static function parseDateTime(?string $value): ?string
    {
        if (empty($value)) return null;
        
        $ts = strtotime($value);
        if ($ts === false) return null;
        
        return date('Y-m-d H:i:s', $ts);
    }
    
    /**
     * Parse date only (for dueDate)
     * API format: "2028-01-19 00:00:00"
     */
    public static function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        
        $ts = strtotime($value);
        if ($ts === false) return null;
        
        return date('Y-m-d', $ts);
    }
    
    /**
     * Format for display
     */
    public static function formatDisplay(?string $datetime): string
    {
        if (empty($datetime)) return 'N/A';
        
        $ts = strtotime($datetime);
        return date('M j, Y H:i', $ts);
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
                                    │ API Token Source  │
                                    │ 1. mod_nicsrs_settings
                                    │ 2. tbladdonmodules│
                                    │ 3. configoption2  │
                                    └───────────────────┘
```

### Input Validation Points

1. **Client-side**: JavaScript validation for UX
2. **Dispatcher**: Basic request validation
3. **Controller**: Business logic validation
4. **API**: NicSRS server-side validation
5. **Database**: Prepared statements via Capsule ORM

---

## Shared API Token Configuration

```php
// lib/Config/ApiConfig.php
class ApiConfig {
    private static ?string $cachedToken = null;
    
    public static function getApiToken(): string
    {
        if (self::$cachedToken !== null) {
            return self::$cachedToken;
        }
        
        // Priority 1: Addon module settings table
        $token = self::getFromSettings();
        if ($token) {
            self::$cachedToken = $token;
            return $token;
        }
        
        // Priority 2: tbladdonmodules table
        $token = self::getFromModuleConfig();
        if ($token) {
            self::$cachedToken = $token;
            return $token;
        }
        
        throw new \Exception('API Token not configured');
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
}
```

---

## Error Handling Strategy

```php
// Standard error response format
class nicsrsResponse {
    public static function error(string $message): string
    {
        return json_encode([
            'status' => 0,
            'msg' => 'failed',
            'error' => $message
        ]);
    }
    
    public static function success(array $data = []): string
    {
        return json_encode([
            'status' => 1,
            'msg' => 'success',
            'data' => $data
        ]);
    }
    
    public static function apiError(string $message): string
    {
        return json_encode([
            'status' => 0,
            'msg' => 'API Error',
            'error' => $message
        ]);
    }
}
```

---

## Certificate Download Formats

| Format | File Extension | Use Case |
|--------|---------------|----------|
| Apache | .crt, .ca-bundle, .key | Apache HTTP Server |
| Nginx | .pem (combined) | Nginx |
| IIS | .p12 (from pkcs12) | Microsoft IIS |
| Tomcat | .jks (from jks) | Apache Tomcat |
| Combined | .zip | All formats in one download |

---

**Author:** HVN GROUP  
**Website:** https://hvn.vn  
**Last Updated:** January 2026