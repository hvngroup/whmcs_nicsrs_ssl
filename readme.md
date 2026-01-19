# NicSRS SSL Certificate Module for WHMCS

A comprehensive WHMCS provisioning module that enables seamless SSL certificate ordering, management, and automation through the NicSRS SSL API.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.2-blue.svg)](https://php.net)
[![WHMCS Version](https://img.shields.io/badge/WHMCS-%3E%3D7.0-green.svg)](https://whmcs.com)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

## Overview

This module integrates NicSRS SSL certificate services with WHMCS, allowing hosting providers and resellers to offer SSL certificates from major Certificate Authorities (CAs) including:

- **Sectigo** (formerly Comodo)
- **DigiCert**
- **GlobalSign**
- **GeoTrust**
- **Symantec**
- **Entrust**
- **BaiduTrust**
- **sslTrus**

## Features

### Certificate Types Supported
- **Domain Validation (DV)** - Basic SSL certificates
- **Organization Validation (OV)** - Business validated certificates
- **Extended Validation (EV)** - Highest trust certificates with green bar
- **Wildcard SSL** - Secure unlimited subdomains
- **Multi-Domain (SAN/UCC)** - Secure multiple domains
- **Code Signing** - Sign software and applications
- **S/MIME** - Email encryption certificates

### Key Capabilities
- Automated certificate provisioning and renewal
- Multiple Domain Control Validation (DCV) methods: EMAIL, HTTP, DNS, HTTPS
- Certificate reissuance and replacement
- Certificate download in multiple formats (Apache, Nginx, IIS, Tomcat)
- Multi-language support (English, Chinese Simplified, Chinese Traditional)
- Real-time certificate status tracking
- CSR generation and validation

## Requirements

- PHP 7.2 or higher
- WHMCS 7.0 or higher
- cURL extension enabled
- OpenSSL extension enabled
- Valid NicSRS API Token

## Installation

### Step 1: Upload Module Files

Upload the `nicsrs_ssl` folder to your WHMCS installation:

```
/path/to/whmcs/modules/servers/nicsrs_ssl/
```

### Step 2: Set Permissions

Ensure proper file permissions:

```bash
chmod -R 755 /path/to/whmcs/modules/servers/nicsrs_ssl/
```

### Step 3: Configure Product in WHMCS

1. Navigate to **Setup → Products/Services → Products/Services**
2. Create a new product or edit an existing one
3. Go to the **Module Settings** tab
4. Select **nicsrs_ssl** as the Module Name
5. Configure the following options:
   - **Certificate Type**: Select the SSL certificate type
   - **NicSRS API Token**: Enter your API token from NicSRS portal

## Directory Structure

```
nicsrs_ssl/
├── nicsrs_ssl.php              # Main module file
├── lang/                       # Language files
│   ├── english.php
│   ├── chinese.php             # Traditional Chinese
│   └── chinese-cn.php          # Simplified Chinese
├── src/
│   ├── config/
│   │   ├── const.php           # Constants definition
│   │   └── country.json        # Country list for forms
│   └── model/
│       ├── Controller/
│       │   ├── PageController.php    # Page rendering logic
│       │   └── ActionController.php  # Certificate actions
│       ├── Dispatcher/
│       │   ├── PageDispatcher.php    # Page routing
│       │   └── ActionDispatcher.php  # Action routing
│       └── Service/
│           ├── nicsrsAPI.php         # API client
│           ├── nicsrsFunc.php        # Utility functions
│           ├── nicsrsResponse.php    # Response formatting
│           ├── nicsrsSSLSql.php      # Database operations
│           └── nicsrsTemplate.php    # Template rendering
└── view/
    ├── applycert.tpl           # Certificate application form
    ├── complete.tpl            # Completed certificate view
    ├── message.tpl             # Status messages
    ├── replace.tpl             # Certificate replacement form
    ├── error.tpl               # Error display
    └── home/                   # Static assets (CSS, JS)
```

## API Endpoints

The module communicates with the NicSRS API at `https://portal.nicsrs.com/ssl`:

| Endpoint | Description |
|----------|-------------|
| `/validate` | Validate certificate request |
| `/place` | Place certificate order |
| `/collect` | Collect certificate status/data |
| `/cancel` | Cancel certificate order |
| `/DCVemail` | Get DCV email options |
| `/updateDCV` | Update DCV method |
| `/batchUpdateDCV` | Batch update DCV methods |
| `/validatefile` | HTTP file validation |
| `/validatedns` | DNS validation |
| `/country` | Get country list |
| `/reissue` | Reissue certificate |
| `/revoke` | Revoke certificate |
| `/replace` | Replace certificate |
| `/renew` | Renew certificate |
| `/removeMdcDomain` | Remove multi-domain entry |

## Certificate Lifecycle

```
┌─────────────────┐
│ Awaiting Config │ ← Initial state after order
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│     Draft       │ ← User saves application
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│    Pending      │ ← Submitted to CA, awaiting validation
└────────┬────────┘
         │
    ┌────┴────┐
    ▼         ▼
┌────────┐ ┌──────────┐
│Complete│ │Cancelled │
└────┬───┘ └──────────┘
     │
     ▼
┌─────────────────┐
│   Reissued      │ ← Certificate replacement
└─────────────────┘
```

## Domain Validation Methods

| Method | Description | Requirements |
|--------|-------------|--------------|
| EMAIL | Email verification | Access to admin@, administrator@, hostmaster@, postmaster@, or webmaster@ |
| HTTP_CSR_HASH | HTTP file validation | Upload file to `/.well-known/pki-validation/` |
| HTTPS_CSR_HASH | HTTPS file validation | Upload file with valid HTTPS |
| CNAME_CSR_HASH | DNS CNAME validation | Add CNAME record to DNS |

## Database Schema

The module creates a `nicsrs_sslorders` table:

```sql
CREATE TABLE `nicsrs_sslorders` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `serviceid` int(10) NOT NULL,
  `addon_id` text NOT NULL,
  `remoteid` text NOT NULL,          -- NicSRS certificate ID
  `module` text NOT NULL,
  `certtype` text NOT NULL,
  `configdata` text NOT NULL,        -- JSON configuration data
  `provisiondate` date NOT NULL,
  `completiondate` datetime NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

## Supported Certificates

### sslTrus Certificates
- sslTrus DV / DV Wildcard / DV Multi Domain
- sslTrus OV / OV Wildcard / OV Multi Domain
- sslTrus EV / EV Multi Domain

### Sectigo (Comodo) Certificates
- Sectigo SSL / Wildcard / Multi Domain
- Sectigo OV SSL / OV Wildcard / OV Multi Domain
- Sectigo EV SSL / EV Multi Domain
- PositiveSSL (DV/OV/EV variants)
- PremiumSSL Wildcard
- Code Signing Certificates

### DigiCert Certificates
- DigiCert OV SSL / OV Wildcard / OV Multi Domain
- DigiCert EV SSL / EV Multi Domain
- DigiCert Code Signing

### GlobalSign Certificates
- GlobalSign DV SSL
- GlobalSign OV SSL / OV Wildcard
- GlobalSign EV SSL

### Other Supported CAs
- GeoTrust QuickSSL Premium
- Symantec Secure Site
- Entrust Standard/Advantage/Wildcard/EV
- BaiduTrust DV/OV/EV

## Troubleshooting

### Common Issues

**Certificate order stuck in pending:**
- Verify DCV method is correctly configured
- Check if validation file/DNS record is accessible
- Ensure email validation address is correct

**API Connection Failed:**
- Verify API token is valid
- Check server firewall allows outbound HTTPS
- Confirm cURL extension is enabled

**Language not displaying correctly:**
- Ensure language file exists in `lang/` directory
- Check WHMCS language settings match

### Debug Logging

Enable WHMCS module debug logging:
1. Go to **Utilities → Logs → Module Log**
2. Enable logging for the nicsrs_ssl module
3. Review logs for API request/response details

## Support

For technical support:
- **Portal**: [https://portal.nicsrs.com](https://portal.nicsrs.com)
- **Documentation**: [https://docs.nicsrs.com](https://docs.nicsrs.com)

## License

This module is proprietary software. Unauthorized distribution or modification is prohibited.

## Changelog

### Version 1.1
- Added support for sslTrus certificates
- Improved multi-domain handling
- Enhanced DCV batch update functionality
- Added IP address SSL support for select certificates

### Version 1.0
- Initial release
- Support for major CA certificates
- Multi-language support
- Full certificate lifecycle management

---

**Author**: HVN GROUP  
**Website**: [https://hvn.vn](https://hvn.vn)