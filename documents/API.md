# NicSRS SSL Module - API Documentation

## Overview

This document describes the NicSRS API integration used by the WHMCS SSL module. All documentation is based on **actual API response data** captured from the NicSRS production environment.

> **Last Updated**: January 2026  
> **API Version**: Production  
> **Author**: HVN GROUP

---

## NicSRS External API

### Base URL

```
https://portal.nicsrs.com/ssl
```

### Authentication

All API requests require an `api_token` parameter:

```php
$data = [
    'api_token' => 'your_api_token_here',
    // ... other parameters
];
```

---

## API Endpoints

### 1. Product List

**Endpoint:** `POST /productList`

Retrieves available SSL certificate products.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API authentication token |
| vendor | string | No | Filter by vendor (e.g., "Sectigo") |

**Response:**
```json
{
    "code": 1,
    "data": {
        "products": [
            {
                "productCode": "sectigo_dv_ssl",
                "productName": "Sectigo PositiveSSL",
                "vendor": "Sectigo",
                "type": "DV",
                "maxDomains": 1,
                "validityPeriods": [1, 2, 3]
            }
        ]
    }
}
```

---

### 2. Validate Certificate Request

**Endpoint:** `POST /validate`

Validates certificate request data before placing an order.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API authentication token |
| productCode | string | Yes | Certificate product code |
| csr | string | Yes | Certificate Signing Request (PEM format) |
| domainInfo | json | Yes | Domain and DCV information array |

**Request Example:**
```php
[
    'api_token' => $token,
    'productCode' => 'sectigo_dv_ssl',
    'csr' => '-----BEGIN CERTIFICATE REQUEST-----...',
    'domainInfo' => json_encode([
        [
            'domainName' => 'example.com',
            'dcvMethod' => 'CNAME_CSR_HASH'
        ]
    ])
]
```

**Response:**
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "valid": true
    }
}
```

---

### 3. Place Certificate Order

**Endpoint:** `POST /place`

Places a new certificate order.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| productCode | string | Yes | Certificate product code |
| csr | string | Yes | CSR content (PEM format) |
| years | int | Yes | Validity period (1, 2, or 3) |
| domainInfo | json | Yes | Domain validation info |
| Administrator | json | OV/EV | Admin contact details |
| organizationInfo | json | OV/EV | Organization details |
| finance | json | OV/EV | Finance contact (optional) |
| tech | json | OV/EV | Technical contact (optional) |

**domainInfo Structure:**
```json
[
    {
        "domainName": "example.com",
        "dcvMethod": "CNAME_CSR_HASH",
        "dcvEmail": ""
    },
    {
        "domainName": "www.example.com",
        "dcvMethod": "EMAIL",
        "dcvEmail": "admin@example.com"
    }
]
```

**Response:**
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "certId": "12345678",
        "vendorId": "2771240592",
        "DCVfileName": "148ECC23D64F50CDCCA4F0DAF72A9A4B.txt",
        "DCVfileContent": "hash_content_here",
        "DCVdnsHost": "_148ecc23d64f50cdcca4f0daf72a9a4b",
        "DCVdnsValue": "cname_value.sectigo.com",
        "DCVdnsType": "CNAME"
    }
}
```

---

### 4. Collect Certificate Status (CRITICAL)

**Endpoint:** `POST /collect`

Retrieves certificate status and details. This is the most important endpoint for certificate lifecycle management.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID from place order |

**Actual Response (from collect_api.txt):**

```php
[
    'code' => 1,
    'status' => 'COMPLETE',                    // UPPERCASE
    'certStatus' => 'COMPLETE',                // Separate certificate status
    'data' => [
        // Certificate Dates (FULL DATETIME FORMAT)
        'beginDate' => '2026-01-19 08:00:00',  // Y-m-d H:i:s
        'endDate' => '2027-02-20 07:59:59',    // Y-m-d H:i:s
        'dueDate' => '2028-01-19 00:00:00',    // Renewal due date
        
        // Certificate Files
        'certificate' => '-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----',
        'caCertificate' => '-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----',
        'rsaPrivateKey' => '',                  // Usually empty (not stored)
        
        // Pre-formatted Certificates (NEW)
        'jks' => 'base64_encoded_jks_file',    // Java KeyStore format
        'pkcs12' => 'base64_encoded_p12_file', // PKCS#12 format
        'jksPass' => 'U0DHfMAlZXk7DHQP',       // JKS password
        'pkcsPass' => 'MojZVyvbiMO65dC9',      // PKCS12 password
        
        // Vendor Information
        'vendorId' => '2771240592',            // Sectigo vendor ID
        'vendorCertId' => '39831817562',       // Vendor certificate ID
        
        // Certificate Path (optional)
        'certPath' => '',
        
        // DCV Information (for HTTP/DNS validation)
        'DCVfileName' => '148ECC23D64F50CDCCA4F0DAF72A9A4B.txt',
        'DCVfileContent' => 'hash_content...',
        'DCVfilePath' => 'http://example.com/.well-known/pki-validation/...',
        'DCVdnsHost' => '_148ecc23d64f50cdcca4f0daf72a9a4b',
        'DCVdnsValue' => 'cname_value.sectigo.com',
        'DCVdnsType' => 'CNAME',
        
        // Process Status
        'application' => ['status' => 'done'],
        'dcv' => ['status' => 'done'],
        'issued' => ['status' => 'done'],
        
        // Domain Validation List (CRITICAL)
        'dcvList' => [
            [
                'domainName' => 'example.com',
                'dcvMethod' => 'CNAME_CSR_HASH',  // NOT 'CNAME'!
                'dcvEmail' => '',
                'is_verify' => 'verified'         // 'verified' or 'unverified'
            ]
        ],
        
        // Original CSR (for reissue)
        'applyParams' => [
            'csr' => '-----BEGIN CERTIFICATE REQUEST-----\n...'
        ]
    ]
]
```

---

### 5. Cancel Certificate

**Endpoint:** `POST /cancel`

Cancels a pending certificate order.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| reason | string | No | Cancellation reason |

**Response:**
```json
{
    "code": 1,
    "msg": "success"
}
```

**Business Rules:**
- Only pending/processing orders can be cancelled
- Issued certificates can be cancelled within 30 days

---

### 6. Revoke Certificate

**Endpoint:** `POST /revoke`

Revokes an issued certificate.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| reason | string | No | Revocation reason |

---

### 7. Reissue Certificate

**Endpoint:** `POST /reissue`

Reissues an existing certificate with new CSR.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Original certificate ID |
| csr | string | Yes | New CSR |
| domainInfo | json | Yes | Domain information |

---

### 8. Replace Certificate

**Endpoint:** `POST /replace`

Replaces a certificate (similar to reissue).

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Original certificate ID |
| params | json | Yes | JSON containing csr, domainInfo, organizationInfo |

**Response:**
```json
{
    "code": 1,
    "data": {
        "certId": "new_cert_id",
        "vendorId": "vendor_id",
        "DCVfileName": "filename.txt",
        "DCVfileContent": "content",
        "DCVdnsHost": "_hash",
        "DCVdnsValue": "value.sectigo.com",
        "DCVdnsType": "CNAME"
    }
}
```

---

### 9. Renew Certificate

**Endpoint:** `POST /renew`

Renews an expiring certificate.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| years | int | Yes | Renewal period |

---

### 10. Update DCV Method

**Endpoint:** `POST /updateDCV`

Updates the domain control validation method for a single domain.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| domainName | string | Yes | Domain to update |
| dcvMethod | string | Yes | New DCV method |
| dcvEmail | string | Conditional | Email if dcvMethod is EMAIL |

---

### 11. Batch Update DCV

**Endpoint:** `POST /batchUpdateDCV`

Updates DCV methods for multiple domains at once.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| domainInfo | json | Yes | Array of domain DCV updates |

**domainInfo Structure:**
```json
[
    {
        "domainName": "example.com",
        "dcvMethod": "CNAME_CSR_HASH",
        "dcvEmail": ""
    },
    {
        "domainName": "www.example.com",
        "dcvMethod": "EMAIL",
        "dcvEmail": "admin@example.com"
    }
]
```

---

### 12. Get DCV Emails

**Endpoint:** `POST /DCVemail`

Returns available DCV email addresses for a domain.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| domain | string | Yes | Domain name |

**Response:**
```json
{
    "code": 1,
    "data": {
        "emails": [
            "admin@example.com",
            "administrator@example.com",
            "hostmaster@example.com",
            "postmaster@example.com",
            "webmaster@example.com"
        ]
    }
}
```

---

### 13. Get Country List

**Endpoint:** `POST /country`

Returns list of countries for forms.

**Response:**
```json
{
    "code": 1,
    "data": {
        "countries": [
            {"code": "VN", "name": "Vietnam"},
            {"code": "US", "name": "United States"}
        ]
    }
}
```

---

## Critical Data Mappings

### DCV Method Mapping (IMPORTANT)

The API uses specific DCV method names that differ from common display names:

| API Value | Display Name | Validation Type | Description |
|-----------|--------------|-----------------|-------------|
| `CNAME_CSR_HASH` | DNS CNAME | DNS | CNAME record validation |
| `HTTP_CSR_HASH` | HTTP File | HTTP | File-based validation |
| `DNS_CSR_HASH` | DNS TXT | DNS | TXT record validation |
| `EMAIL` | Email | Email | Email-based validation |

**Code Example:**
```php
class DcvHelper
{
    public const METHODS = [
        'CNAME_CSR_HASH' => 'DNS CNAME',
        'HTTP_CSR_HASH' => 'HTTP File',
        'DNS_CSR_HASH' => 'DNS TXT',
        'EMAIL' => 'Email',
    ];
    
    public static function getDisplayName(string $method): string
    {
        return self::METHODS[$method] ?? $method;
    }
    
    public static function isDnsMethod(string $method): bool
    {
        return in_array($method, ['CNAME_CSR_HASH', 'DNS_CSR_HASH']);
    }
    
    public static function isHttpMethod(string $method): bool
    {
        return $method === 'HTTP_CSR_HASH';
    }
}
```

---

### Date Format Handling (IMPORTANT)

The API returns dates in **full datetime format**, not date-only:

| Field | API Format | Example |
|-------|------------|---------|
| beginDate | `Y-m-d H:i:s` | `2026-01-19 08:00:00` |
| endDate | `Y-m-d H:i:s` | `2027-02-20 07:59:59` |
| dueDate | `Y-m-d H:i:s` | `2028-01-19 00:00:00` |

**Code Example:**
```php
class DateHelper
{
    public static function parseDateTime(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
    
    public static function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
    }
    
    public static function formatDisplay(?string $date): string
    {
        if (empty($date)) return 'N/A';
        $ts = strtotime($date);
        return $ts ? date('M d, Y', $ts) : 'N/A';
    }
}
```

---

### Status Values (IMPORTANT)

The API returns status in **UPPERCASE**. Always convert to lowercase for storage:

| API Status | DB Storage | Description |
|------------|------------|-------------|
| `COMPLETE` | `complete` | Certificate issued |
| `PENDING` | `pending` | Awaiting validation |
| `CANCELLED` | `cancelled` | Order cancelled |
| `EXPIRED` | `expired` | Certificate expired |
| `PROCESSING` | `processing` | Being processed |

**Code Example:**
```php
$status = strtolower($response['status'] ?? 'pending');
$certStatus = strtolower($response['certStatus'] ?? $status);
```

---

## Response Codes

| Code | Status | Description | Action |
|------|--------|-------------|--------|
| 1 | Success | Operation completed | Process response |
| 2 | In Progress | Certificate being issued | Retry later |
| -1 | Validation Error | Parameter validation failed | Show error details |
| -2 | Unknown Error | Unexpected error | Log & show generic error |
| -3 | Product Error | Product/price issue | Check product config |
| -4 | Insufficient Credit | Account balance low | Alert admin |
| -6 | CA Error | Certificate Authority error | Contact NicSRS support |
| 400 | Permission Denied | Invalid API token | Check API token |

---

## API Field to Database Column Mapping

| API Field | DB Column | Transform |
|-----------|-----------|-----------|
| `status` | `status` | `strtolower()` |
| `certStatus` | `cert_status` | `strtolower()` |
| `vendorId` | `vendor_id` | direct |
| `vendorCertId` | `vendor_cert_id` | direct |
| `beginDate` | `begin_date` | parse datetime |
| `endDate` | `end_date` | parse datetime |
| `dueDate` | `due_date` | parse date |
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

---

## Error Handling

All API calls should be wrapped in try-catch blocks:

```php
try {
    $result = $apiService->collect($certId);
    
    if ($result['code'] == 1 || $result['code'] == 2) {
        // Success or in-progress
        return $this->processResponse($result);
    }
    
    // Handle error
    $errorMsg = $result['msg'] ?? NicsrsApiService::getResponseCodeDescription($result['code']);
    throw new \Exception($errorMsg);
    
} catch (\Exception $e) {
    logModuleCall('nicsrs_ssl', 'collect', $certId, $e->getMessage());
    return ['error' => $e->getMessage()];
}
```

---

## Changelog

### Version 1.2.0 (January 2026)
- Updated date format documentation (full datetime)
- Added DCV method mapping table (CNAME_CSR_HASH, HTTP_CSR_HASH, etc.)
- Added pre-formatted certificate fields (jks, pkcs12)
- Added vendor tracking fields (vendorId, vendorCertId)
- Added dueDate field for renewal tracking
- Updated collect response with actual API data structure