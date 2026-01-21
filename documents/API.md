# NicSRS SSL Module - API Documentation

## Overview

This document describes the internal API structure and external NicSRS API integration used by the module. All data in this document is based on **actual API responses** from the NicSRS portal.

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

### 1. Validate Certificate Request

**Endpoint:** `POST /validate`

Validates certificate request data before placing an order.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API authentication token |
| productCode | string | Yes | Certificate product code |
| csr | string | Yes | Certificate Signing Request |
| domainInfo | json | Yes | Domain and DCV information |

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

### 2. Place Certificate Order

**Endpoint:** `POST /place`

Places a new certificate order.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| productCode | string | Yes | Certificate product code |
| csr | string | Yes | CSR content |
| domainInfo | json | Yes | Domain validation info |
| Administrator | json | OV/EV | Admin contact details |
| tech | json | OV/EV | Technical contact details |
| finance | json | OV/EV | Finance contact details |
| organizationInfo | json | OV/EV | Organization details |
| server | string | Yes | Server type (e.g., "other") |

**Response:**
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "certId": "260113vghhracpaw",
        "vendorId": "1433958846",
        "DCVfileName": "fileauth.txt",
        "DCVfileContent": "_3525cdeanma96j6bosy7zdq8jld7tly",
        "DCVdnsHost": "_dnsauth",
        "DCVdnsValue": "_3525cdeanma96j6bosy7zdq8jld7tly",
        "DCVdnsType": "TXT",
        "applyTime": "2026-01-13 09:45:48"
    }
}
```

---

### 3. Collect Certificate Status

**Endpoint:** `POST /collect`

Retrieves certificate status and details.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID from place order |

**Actual Response (from collect_api.txt):**
```json
{
    "code": 1,
    "status": "COMPLETE",
    "certStatus": "COMPLETE",
    "data": {
        "certPath": "",
        "beginDate": "2026-01-19 08:00:00",
        "endDate": "2027-02-20 07:59:59",
        "dueDate": "2028-01-19 00:00:00",
        "certificate": "-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----",
        "caCertificate": "-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----",
        "rsaPrivateKey": "",
        "jks": "base64_encoded_jks_data...",
        "pkcs12": "base64_encoded_pkcs12_data...",
        "jksPass": "U0DHfMAlZXk7DHQP",
        "pkcsPass": "MojZVyvbiMO65dC9",
        "vendorId": "2771240592",
        "vendorCertId": "39831817562",
        "DCVfileName": "148ECC23D64F50CDCCA4F0DAF72A9A4B.txt",
        "DCVfileContent": "f7b3b82e12a4b76d0532898e04fde790c5325e133b17373ff31c17d9f84393b7\nsectigo.com",
        "DCVfilePath": "http://example.com/.well-known/pki-validation/148ECC23D64F50CDCCA4F0DAF72A9A4B.txt",
        "DCVdnsHost": "_148ecc23d64f50cdcca4f0daf72a9a4b",
        "DCVdnsValue": "f7b3b82e12a4b76d0532898e04fde790.c5325e133b17373ff31c17d9f84393b7.sectigo.com",
        "DCVdnsType": "CNAME",
        "application": {
            "status": "done"
        },
        "dcv": {
            "status": "done"
        },
        "issued": {
            "status": "done"
        },
        "dcvList": [
            {
                "dcvEmail": "",
                "dcvMethod": "CNAME_CSR_HASH",
                "is_verify": "verified",
                "domainName": "example.com"
            }
        ],
        "applyParams": {
            "csr": "-----BEGIN CERTIFICATE REQUEST-----\n...\n-----END CERTIFICATE REQUEST-----",
            "Administrator": {...},
            "tech": {...},
            "finance": {...},
            "organizationInfo": {...},
            "server": "other"
        }
    }
}
```

---

### 4. Cancel Certificate

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

---

### 5. Revoke Certificate

**Endpoint:** `POST /revoke`

Revokes an issued certificate.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| reason | string | Yes | Revocation reason |

---

### 6. Reissue Certificate

**Endpoint:** `POST /reissue`

Reissues an existing certificate with new CSR.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| csr | string | Yes | New CSR |
| domainInfo | json | Yes | Domain validation info |

---

### 7. Renew Certificate

**Endpoint:** `POST /renew`

Renews an expiring certificate.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |

---

### 8. Update DCV Method

**Endpoint:** `POST /updateDCV`

Updates the domain control validation method.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| domainName | string | Yes | Domain name |
| dcvMethod | string | Yes | New DCV method |
| dcvEmail | string | Conditional | Email for EMAIL method |

---

### 9. Get DCV Email List

**Endpoint:** `POST /DCVemail`

Gets available DCV email addresses for a domain.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| domainName | string | Yes | Domain name |

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

### 10. Product List

**Endpoint:** `POST /productList`

Gets available products with pricing.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |

---

## Response Codes

| Code | Status | Description |
|------|--------|-------------|
| 1 | Success | Operation completed successfully |
| 2 | Processing | Certificate being issued, retry later |
| 0 | Error | Operation failed |
| -1 | Validation Error | Parameter validation failed |
| -2 | Unknown Error | Unknown server error |
| -3 | Product Error | Product/price error |
| -4 | Insufficient Credit | Account credit insufficient |
| -6 | CA Error | CA request failed |
| 400 | Permission Denied | Invalid API token |

---

## DCV Methods

**Important:** The API returns DCV methods with `_CSR_HASH` suffix.

| API Value | Display Name | Type | Description |
|-----------|--------------|------|-------------|
| `CNAME_CSR_HASH` | DNS CNAME | DNS | CNAME record validation |
| `HTTP_CSR_HASH` | HTTP File | HTTP | File-based validation |
| `DNS_CSR_HASH` | DNS TXT | DNS | TXT record validation |
| `EMAIL` | Email | Email | Email-based validation |

---

## Date/Time Formats

**Important:** The API returns dates in **full datetime format**.

| Field | Format | Example |
|-------|--------|---------|
| beginDate | `Y-m-d H:i:s` | `2026-01-19 08:00:00` |
| endDate | `Y-m-d H:i:s` | `2027-02-20 07:59:59` |
| dueDate | `Y-m-d H:i:s` | `2028-01-19 00:00:00` |
| applyTime | `Y-m-d H:i:s` | `2026-01-13 09:45:48` |

---

## Pre-formatted Certificate Data

The API provides pre-formatted certificate files:

| Field | Format | Description |
|-------|--------|-------------|
| `certificate` | PEM | Server certificate |
| `caCertificate` | PEM | CA bundle/intermediate certificates |
| `rsaPrivateKey` | PEM | Private key (if generated by CA) |
| `jks` | Base64 | Java KeyStore file |
| `pkcs12` | Base64 | PKCS#12 (.p12/.pfx) file |
| `jksPass` | String | Password for JKS file |
| `pkcsPass` | String | Password for PKCS12 file |

---

## Vendor Tracking Fields

| Field | Description |
|-------|-------------|
| `vendorId` | CA vendor order ID (e.g., Sectigo order ID) |
| `vendorCertId` | CA vendor certificate ID |
| `certId` | NicSRS internal certificate ID |

---

## DCV Validation Fields

| Field | Description |
|-------|-------------|
| `DCVfileName` | HTTP validation filename |
| `DCVfileContent` | HTTP validation file content |
| `DCVfilePath` | Full URL path for HTTP validation |
| `DCVdnsHost` | DNS record hostname |
| `DCVdnsValue` | DNS record value |
| `DCVdnsType` | DNS record type (CNAME/TXT) |

---

## Process Status Tracking

The API provides status tracking for each process stage:

```json
{
    "application": { "status": "done" },
    "dcv": { "status": "done" },
    "issued": { "status": "done" }
}
```

Possible status values: `pending`, `processing`, `done`, `failed`

---

## Contact Information Structure

### Administrator/Tech/Finance Contact
```json
{
    "firstName": "John",
    "lastName": "Doe",
    "email": "admin@example.com",
    "mobile": "+1234567890",
    "job": "IT Manager",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "postCode": "10001",
    "country": "US",
    "organation": "Example Company Inc."
}
```

### Organization Info (OV/EV Certificates)
```json
{
    "organizationName": "Example Company Inc.",
    "organizationAddress": "123 Main St",
    "organizationCity": "New York",
    "organizationCountry": "US",
    "organizationState": "NY",
    "organizationPostCode": "10001",
    "organizationMobile": "+1234567890"
}
```

---

## Domain Info Structure

```json
{
    "domainInfo": [
        {
            "domainName": "example.com",
            "dcvMethod": "CNAME_CSR_HASH",
            "dcvEmail": "",
            "isVerified": true,
            "is_verify": "verified"
        }
    ]
}
```

**Note:** The field `is_verify` uses string value `"verified"` or empty string `""`.

---

## Error Handling

All API calls should be wrapped in try-catch blocks:

```php
try {
    $result = $apiService->collect($certId);
    
    if ($result['code'] != 1 && $result['code'] != 2) {
        throw new \Exception($result['msg'] ?? 'API Error');
    }
    
    // Process response
} catch (Exception $e) {
    logModuleCall('nicsrs_ssl', 'collect', $certId, $e->getMessage());
    return ['error' => $e->getMessage()];
}
```

---

## configdata Storage Structure

The module stores certificate data in the `configdata` JSON field of `nicsrs_sslorders` table:

```json
{
    "server": "other",
    "csr": "-----BEGIN CERTIFICATE REQUEST-----\n...",
    "privateKey": "-----BEGIN PRIVATE KEY-----\n...",
    "domainInfo": [
        {
            "domainName": "example.com",
            "dcvMethod": "CNAME_CSR_HASH",
            "dcvEmail": "",
            "isVerified": true,
            "is_verify": "verified"
        }
    ],
    "Administrator": {
        "firstName": "John",
        "lastName": "Doe",
        "email": "admin@example.com",
        "mobile": "+1234567890",
        "job": "IT Manager",
        "address": "123 Main St",
        "city": "New York",
        "state": "NY",
        "postCode": "10001",
        "country": "US",
        "organation": "Example Company Inc."
    },
    "organizationInfo": {...},
    "originalfromOthers": "0",
    "applyReturn": {
        "certId": "260113vghhracpaw",
        "vendorId": "1433958846",
        "vendorCertId": "39831817562",
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
        "certificate": "-----BEGIN CERTIFICATE-----\n...",
        "caCertificate": "-----BEGIN CERTIFICATE-----\n...",
        "certPath": "",
        "privateKey": "-----BEGIN PRIVATE KEY-----\n...",
        "jks": "base64_encoded_jks...",
        "pkcs12": "base64_encoded_pkcs12...",
        "jksPass": "random_password",
        "pkcsPass": "random_password",
        "application": { "status": "done" },
        "dcv": { "status": "done" },
        "issued": { "status": "done" },
        "dcvList": [...]
    },
    "applyParams": {
        "csr": "...",
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

## WHMCS Module Functions

### nicsrs_ssl_MetaData()
Returns module metadata for WHMCS.

### nicsrs_ssl_ConfigOptions()
Defines module configuration options.

### nicsrs_ssl_CreateAccount()
Called when service is provisioned.

### nicsrs_ssl_TerminateAccount()
Called when service is terminated.

### nicsrs_ssl_ClientArea()
Renders the client area interface.

---

## Action Controller Methods

| Method | Description |
|--------|-------------|
| `submitApply()` | Submits new certificate application |
| `decodeCsr()` | Decodes and validates CSR |
| `submitReplace()` | Submits replacement request |
| `downCert()` | Downloads certificate files |
| `batchUpdateDCV()` | Batch updates DCV methods |
| `cancelOrder()` | Cancels certificate order |
| `refreshStatus()` | Refreshes certificate status from API |

---

**Author:** HVN GROUP  
**Website:** https://hvn.vn  
**Last Updated:** January 2026