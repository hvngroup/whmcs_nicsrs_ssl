# NicSRS SSL Module - Technical Architecture

## Overview

The NicSRS SSL Module follows a Model-View-Controller (MVC) architecture pattern adapted for WHMCS provisioning module requirements.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         WHMCS Core                               │
│  ┌───────────────┐  ┌───────────────┐  ┌───────────────────────┐│
│  │ Admin Area    │  │ Client Area   │  │ Cron/Automation       ││
│  └───────┬───────┘  └───────┬───────┘  └───────────┬───────────┘│
└──────────┼──────────────────┼──────────────────────┼────────────┘
           │                  │                      │
           ▼                  ▼                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    nicsrs_ssl.php (Entry Point)                  │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Module Functions:                                            ││
│  │ - nicsrs_ssl_MetaData()                                     ││
│  │ - nicsrs_ssl_ConfigOptions()                                ││
│  │ - nicsrs_ssl_CreateAccount()                                ││
│  │ - nicsrs_ssl_TerminateAccount()                             ││
│  │ - nicsrs_ssl_ClientArea()                                   ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────┬───────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Dispatcher Layer                          │
│  ┌─────────────────────┐    ┌─────────────────────────────────┐ │
│  │ PageDispatcher      │    │ ActionDispatcher                │ │
│  │ - Route page views  │    │ - Route AJAX/form actions       │ │
│  └──────────┬──────────┘    └──────────────┬──────────────────┘ │
└─────────────┼───────────────────────────────┼───────────────────┘
              │                               │
              ▼                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                       Controller Layer                           │
│  ┌─────────────────────┐    ┌─────────────────────────────────┐ │
│  │ PageController      │    │ ActionController                │ │
│  │ - index()           │    │ - submitApply()                 │ │
│  │ - Renders templates │    │ - downCert()                    │ │
│  │                     │    │ - batchUpdateDCV()              │ │
│  │                     │    │ - submitReplace()               │ │
│  └──────────┬──────────┘    └──────────────┬──────────────────┘ │
└─────────────┼───────────────────────────────┼───────────────────┘
              │                               │
              ▼                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Service Layer                             │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────────────────┐ │
│  │ nicsrsAPI    │ │ nicsrsSSLSql │ │ nicsrsFunc               │ │
│  │ - API calls  │ │ - DB queries │ │ - Utility functions      │ │
│  └──────┬───────┘ └──────┬───────┘ └──────────────────────────┘ │
│  ┌──────┴───────┐ ┌──────┴───────┐ ┌──────────────────────────┐ │
│  │ nicsrsResp   │ │ nicsrsTempl  │ │ Constants                │ │
│  │ - Responses  │ │ - Templates  │ │ - Status codes           │ │
│  └──────────────┘ └──────────────┘ └──────────────────────────┘ │
└─────────────────────────────┬───────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              ▼                               ▼
┌─────────────────────────┐   ┌─────────────────────────────────┐
│     NicSRS API          │   │      WHMCS Database             │
│  portal.nicsrs.com/ssl  │   │  - tblhosting                   │
│  - /validate            │   │  - tblproducts                  │
│  - /place               │   │  - tblclients                   │
│  - /collect             │   │  - nicsrs_sslorders             │
│  - /updateDCV           │   │                                 │
└─────────────────────────┘   └─────────────────────────────────┘
```

## Component Details

### Entry Point (nicsrs_ssl.php)

The main module file registers WHMCS hooks and exports required functions:

```php
// Required WHMCS Module Functions
function nicsrs_ssl_MetaData()      // Module information
function nicsrs_ssl_ConfigOptions() // Admin configuration fields
function nicsrs_ssl_CreateAccount() // Service provisioning
function nicsrs_ssl_TerminateAccount() // Service termination
function nicsrs_ssl_ClientArea()    // Client area rendering
```

### Dispatcher Layer

Dispatchers route incoming requests to appropriate controller methods.

**PageDispatcher** handles page rendering:
```
Client Area Request
    └── ?step=index → PageController::index()
```

**ActionDispatcher** handles AJAX/form submissions:
```
Form Submission
    └── ?step=submitApply → ActionController::submitApply()
    └── ?step=downCert → ActionController::downCert()
```

### Controller Layer

**PageController** responsibilities:
- Check product/order status
- Load certificate configuration
- Select appropriate template
- Pass data to template engine

**ActionController** responsibilities:
- Validate input data
- Call external APIs
- Update database records
- Return JSON responses

### Service Layer

| Class | Purpose |
|-------|---------|
| `nicsrsAPI` | External API communication |
| `nicsrsSSLSql` | Database operations via Capsule ORM |
| `nicsrsFunc` | Utility functions (CSR decode, zip, etc.) |
| `nicsrsResponse` | Standardized response formatting |
| `nicsrsTemplate` | Template configuration helpers |

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
   ├── Loads certificate attributes
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
   ├── Updates database via nicsrsSSLSql
   └── Returns JSON response
```

### Certificate Download Flow

```
1. User clicks "Download Certificate"
   │
   ▼
2. ActionDispatcher routes to ActionController::downCert()
   │
   ▼
3. ActionController:
   ├── Verifies user permissions
   ├── Calls nicsrsAPI::collect()
   ├── Calls nicsrsFunc::zipCert()
   │   ├── Creates temp directory
   │   ├── Writes certificate files
   │   │   ├── Apache format (.crt, .ca-bundle)
   │   │   ├── Nginx format (.pem)
   │   │   ├── IIS format (.p12)
   │   │   └── Tomcat format (.jks)
   │   ├── Creates ZIP archive
   │   └── Returns base64 encoded content
   └── Returns JSON with download data
```

## Database Schema

### nicsrs_sslorders Table

```sql
CREATE TABLE `nicsrs_sslorders` (
    `id` INT(10) AUTO_INCREMENT PRIMARY KEY,
    `userid` INT(10) NOT NULL,           -- WHMCS user ID
    `serviceid` INT(10) NOT NULL,        -- WHMCS service ID
    `addon_id` TEXT,                     -- Addon service ID (if applicable)
    `remoteid` TEXT,                     -- NicSRS certificate ID
    `module` TEXT,                       -- Module name (nicsrs_ssl)
    `certtype` TEXT,                     -- Certificate product code
    `configdata` TEXT,                   -- JSON configuration data
    `provisiondate` DATE,                -- Order date
    `completiondate` DATETIME,           -- Certificate issue date
    `status` TEXT                        -- Order status
);
```

### configdata JSON Structure

```json
{
    "csr": "-----BEGIN CERTIFICATE REQUEST-----...",
    "domainInfo": [
        {
            "domainName": "example.com",
            "dcvMethod": "EMAIL",
            "dcvEmail": "admin@example.com",
            "isVerified": true
        }
    ],
    "Administrator": {
        "firstName": "John",
        "lastName": "Doe",
        "email": "admin@example.com",
        "phone": "+1234567890"
    },
    "organizationInfo": {
        "organizationName": "Example Inc"
    },
    "applyReturn": {
        "certId": "12345",
        "vendorId": "CA-ORDER-123",
        "certificate": "-----BEGIN CERTIFICATE-----...",
        "caCertificate": "-----BEGIN CERTIFICATE-----...",
        "beginDate": "2024-01-01",
        "endDate": "2025-01-01"
    },
    "replaceTimes": 0,
    "originalDomains": ["example.com"]
}
```

## Order Status State Machine

```
                    ┌─────────────────────┐
                    │ Awaiting Config     │ ← Order created
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │ Draft               │ ← User saves form
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │ Pending             │ ← Submitted to CA
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              ▼                ▼                ▼
    ┌─────────────────┐ ┌─────────────┐ ┌─────────────┐
    │ Complete        │ │ Cancelled   │ │ Expired     │
    └────────┬────────┘ └─────────────┘ └─────────────┘
             │
             ▼
    ┌─────────────────┐
    │ Reissued        │ ← User requests reissue
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │ Pending         │ ← Reissue submitted
    └─────────────────┘
```

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
                                    │ API Token (server)│
                                    │ $params['config'] │
                                    └───────────────────┘
```

### Input Validation Points

1. **Client-side**: JavaScript validation for UX
2. **Dispatcher**: Basic request validation
3. **Controller**: Business logic validation
4. **Service**: API/Database input sanitization

## Performance Considerations

### Caching Strategy

- Country list is loaded from static JSON file
- Certificate attributes are defined in code (no DB lookup)
- Language files are loaded once per request

### Optimization Tips

- Use batch DCV update for multi-domain certificates
- Minimize API calls by storing certificate data locally
- Use WHMCS cron for status polling instead of real-time checks

---

**Author**: HVN GROUP  
**Website**: [https://hvn.vn](https://hvn.vn)