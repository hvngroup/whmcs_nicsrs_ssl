# NicSRS SSL Module - API Documentation

## Overview

This document describes the internal API structure and external NicSRS API integration used by the module.

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

### API Endpoints

#### 1. Validate Certificate Request

**Endpoint:** `POST /validate`

Validates certificate request data before placing an order.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | Your API authentication token |
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

#### 2. Place Certificate Order

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
| organizationInfo | json | OV/EV | Organization details |

**Response:**
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "certId": "12345678",
        "orderId": "ORD-xxxxx"
    }
}
```

#### 3. Collect Certificate Status

**Endpoint:** `POST /collect`

Retrieves certificate status and details.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID from place order |

**Response:**
```json
{
    "code": 1,
    "status": "COMPLETE",
    "data": {
        "certificate": "-----BEGIN CERTIFICATE-----...",
        "caCertificate": "-----BEGIN CERTIFICATE-----...",
        "privateKey": "-----BEGIN PRIVATE KEY-----...",
        "beginDate": "2024-01-01",
        "endDate": "2025-01-01",
        "dcvList": [
            {
                "domainName": "example.com",
                "dcvMethod": "EMAIL",
                "is_verify": "verified"
            }
        ]
    }
}
```

#### 4. Cancel Certificate

**Endpoint:** `POST /cancel`

Cancels a pending certificate order.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |

#### 5. Update DCV Method

**Endpoint:** `POST /updateDCV`

Updates the domain control validation method for a domain.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| domainName | string | Yes | Domain to update |
| dcvMethod | string | Yes | New DCV method |
| dcvEmail | string | Conditional | Email if dcvMethod is EMAIL |

#### 6. Batch Update DCV

**Endpoint:** `POST /batchUpdateDCV`

Updates DCV methods for multiple domains at once.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| domainInfo | json | Yes | Array of domain DCV updates |

#### 7. Reissue Certificate

**Endpoint:** `POST /reissue`

Reissues an existing certificate with new CSR.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| csr | string | Yes | New CSR |
| domainInfo | json | Yes | Domain information |

#### 8. Revoke Certificate

**Endpoint:** `POST /revoke`

Revokes an issued certificate.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| api_token | string | Yes | API token |
| certId | string | Yes | Certificate ID |
| reason | string | No | Revocation reason |

#### 9. Replace Certificate

**Endpoint:** `POST /replace`

Replaces a certificate (similar to reissue).

#### 10. Renew Certificate

**Endpoint:** `POST /renew`

Renews an expiring certificate.

#### 11. Get Country List

**Endpoint:** `POST /country`

Returns list of countries for forms.

#### 12. Get DCV Emails

**Endpoint:** `POST /DCVemail`

Returns available DCV email addresses for a domain.

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

## Internal Module API

### WHMCS Module Functions

#### nicsrs_ssl_MetaData()

Returns module metadata for WHMCS.

```php
function nicsrs_ssl_MetaData() {
    return [
        'DisplayName' => 'nicsrs_ssl',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
        'DefaultNonSSLPort' => '80',
        'DefaultSSLPort' => '443',
    ];
}
```

#### nicsrs_ssl_ConfigOptions()

Defines module configuration options.

```php
function nicsrs_ssl_ConfigOptions() {
    return [
        'cert_type' => [
            'FriendlyName' => 'Certificate Type',
            'Type' => 'dropdown',
            'Options' => nicsrsFunc::getCertAttributes(null, 'name'),
        ],
        'nicsrs_api_token' => [
            'FriendlyName' => 'nicsrs API Token',
            'Type' => 'password',
            'size' => '32',
        ],
    ];
}
```

#### nicsrs_ssl_CreateAccount()

Called when service is provisioned.

#### nicsrs_ssl_TerminateAccount()

Called when service is terminated.

#### nicsrs_ssl_ClientArea()

Renders the client area interface.

### Action Controller Methods

Located in `src/model/Controller/ActionController.php`:

| Method | Description |
|--------|-------------|
| `applyReplace()` | Initiates certificate replacement |
| `decodeCsr()` | Decodes and validates CSR |
| `replacedraft()` | Saves replacement draft |
| `submitReplace()` | Submits replacement request |
| `submitApply()` | Submits new certificate application |
| `downCert()` | Downloads certificate files |
| `batchUpdateDCV()` | Batch updates DCV methods |
| `cancelOrder()` | Cancels certificate order |

### Response Codes

The API uses standardized response codes:

| Code | Status | Description |
|------|--------|-------------|
| 1 | Success | Operation completed successfully |
| 0 | Error | Operation failed |

### Error Handling

All API calls are wrapped in try-catch blocks:

```php
try {
    $result = nicsrsAPI::call('endpoint', $data);
} catch (Exception $e) {
    return nicsrsResponse::error($e->getMessage());
}
```

---

## Data Structures

### Domain Info Structure

```json
{
    "domainInfo": [
        {
            "domainName": "example.com",
            "dcvMethod": "EMAIL",
            "dcvEmail": "admin@example.com"
        },
        {
            "domainName": "www.example.com",
            "dcvMethod": "HTTP_CSR_HASH"
        }
    ]
}
```

### Administrator Contact Structure

```json
{
    "Administrator": {
        "firstName": "John",
        "lastName": "Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "title": "IT Manager",
        "organizationName": "Example Inc",
        "address": "123 Main St",
        "city": "New York",
        "state": "NY",
        "postCode": "10001",
        "country": "US"
    }
}
```

### Organization Info Structure (OV/EV Only)

```json
{
    "organizationInfo": {
        "organizationName": "Example Corporation",
        "organizationAddress": "123 Business Ave",
        "organizationCity": "San Francisco",
        "organizationState": "CA",
        "organizationPostCode": "94102",
        "organizationCountry": "US",
        "organizationMobile": "+1234567890",
        "idType": "BUSINESS_LICENSE",
        "organizationCode": "123456789"
    }
}
```

---

## Certificate Product Codes

### sslTrus Products
| Code | Name | Type |
|------|------|------|
| ssltrus-dv-ssl | sslTrus DV | DV |
| ssltrus-wildcard-dv | sslTrus DV Wildcard | DV |
| ssltrus-multi-domain-dv | sslTrus DV Multi Domain | DV |
| ssltrus-ov-ssl | sslTrus OV | OV |
| ssltrus-wildcard-ov | sslTrus OV Wildcard | OV |
| ssltrus-multi-domain-ov | sslTrus OV Multi Domain | OV |
| ssltrus-ev-ssl | sslTrus EV | EV |
| ssltrus-multi-domain-ev | sslTrus EV Multi Domain | EV |

### Sectigo Products
| Code | Name | Type |
|------|------|------|
| comodo-ssl | Sectigo SSL | DV |
| comodo-wildcard-ssl-certificate | Sectigo Wildcard | DV |
| sectigo-multi-domain | Sectigo Multi Domain | DV |
| sectigo-ov | Sectigo OV SSL | OV |
| sectigo-ov-wildcard | Sectigo OV Wildcard | OV |
| sectigo-ov-multi-domain | Sectigo OV Multi Domain | OV |
| sectigo-ev | Sectigo EV SSL | EV |
| sectigo-ev-multi-domain | Sectigo EV Multi Domain | EV |

---

**Author**: HVN GROUP  
**Support**: [https://hvn.vn](https://hvn.vn)