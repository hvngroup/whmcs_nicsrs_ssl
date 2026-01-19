# Changelog

All notable changes to the NicSRS SSL WHMCS Module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Webhook support for certificate status updates
- Automated renewal reminders
- ACME protocol support

---

## [1.2.0] - 2025-XX-XX

### Added

#### Admin Area Management (Addon Module)
- **Dashboard Overview**: Real-time statistics with charts showing certificate status distribution, monthly orders, revenue tracking
- **Product List Management**: Fetch and display all available NicSRS products with pricing from API `/productList`
- **SSL Orders Management**: Comprehensive table view with search, filter, pagination
- **Certificate Operations**: Direct admin actions (Cancel, Revoke, Reissue, Renew) with confirmation dialogs
- **CAA Record Checker**: Built-in tool to verify CAA records for domains via `/caaCheck` API
- **Account Balance Display**: Show reseller credit balance from NicSRS account
- **Activity Logs**: Track all admin operations with timestamps

#### New API Integrations
- `GET /productList` - Fetch available products with pricing by vendor
- `POST /caaCheck` - Check CAA records for domain validation
- `POST /cancel` - Cancel SSL subscription with reason
- `POST /revoke` - Revoke issued certificates
- `POST /renew` - Renew expiring certificates
- `POST /reissue` - Reissue certificates with new CSR
- `GET /getCertByRefId` - Lookup certificate by reference ID

#### UI Improvements
- Ant Design-inspired admin interface with modern components
- Responsive data tables with sorting and filtering
- Status badges with color-coded indicators
- Modal dialogs for confirmations and forms
- Toast notifications for operation feedback
- Dark/Light theme support

#### Additional Features
- **Bulk Operations**: Select multiple certificates for batch status update
- **Export Functions**: Export certificate list to CSV/Excel
- **Email Notifications**: Configurable admin alerts for certificate events
- **API Token Validation**: Test API connection from admin panel
- **Product Price Calculator**: Calculate certificate prices with SAN additions

### Changed
- Refactored API client to support all new endpoints
- Improved error handling with detailed messages
- Enhanced database schema for order tracking
- Updated language files with new admin strings

### Security
- Admin action audit logging
- Role-based access control for addon module
- API token encryption in database

---

## [1.1.0] - 2024-XX-XX

### Added
- **sslTrus Certificate Support**: Full support for sslTrus DV, OV, EV certificates including wildcard and multi-domain variants
- **IP Address SSL Support**: Select certificates now support IP address validation
- **Batch DCV Update**: New API endpoint for updating multiple domain DCV methods simultaneously
- **Certificate Replacement Flow**: Improved certificate reissuance workflow with draft saving
- **Entrust Certificates**: Added support for Entrust Standard, Advantage, Wildcard, and EV certificates
- **BaiduTrust Certificates**: Added support for BaiduTrust DV, OV, EV certificates

### Changed
- Improved domain validation regex for better wildcard handling
- Enhanced error messages with more specific details
- Updated API client with better timeout handling
- Refactored certificate download to support multiple formats (Apache, Nginx, IIS, Tomcat)

### Fixed
- Fixed CSR validation for certain edge cases
- Resolved multi-domain certificate SAN count calculation
- Fixed language detection for certain WHMCS configurations
- Corrected file permissions on certificate download

### Security
- Added input sanitization for all form submissions
- Improved session validation for client area requests
- Enhanced API token handling

---

## [1.0.0] - 2023-XX-XX

### Added
- **Initial Release**
- WHMCS provisioning module integration
- Support for major Certificate Authorities:
  - Sectigo (Comodo)
  - DigiCert
  - GlobalSign
  - GeoTrust
  - Symantec
- Certificate types:
  - Domain Validation (DV)
  - Organization Validation (OV)
  - Extended Validation (EV)
  - Wildcard SSL
  - Multi-Domain (SAN/UCC)
  - Code Signing
  - S/MIME Email Certificates
- Domain Control Validation (DCV) methods:
  - Email validation
  - HTTP file validation
  - HTTPS file validation
  - DNS CNAME validation
- Multi-language support:
  - English
  - Chinese (Simplified)
  - Chinese (Traditional)
- Certificate lifecycle management:
  - New certificate application
  - Certificate reissuance
  - Certificate revocation
  - Status tracking
- Certificate download in multiple server formats:
  - Apache (.crt, .ca-bundle)
  - Nginx (.pem)
  - IIS (.p12)
  - Tomcat (.jks)
- Client area interface with:
  - Certificate application form
  - Domain validation status
  - Certificate download
  - Certificate reissue
- Admin configuration:
  - Certificate type selection
  - API token configuration

### Technical
- MVC architecture with Controller/Dispatcher pattern
- Namespace-based PHP organization
- Smarty template engine integration
- WHMCS Capsule database ORM usage
- RESTful API client implementation

---

## Migration Notes

### From 1.0.x to 1.1.x

No database migrations required. The module is backward compatible.

**Recommended Steps:**
1. Backup your current module folder
2. Upload new module files (overwrite existing)
3. Clear WHMCS cache
4. Test with a new order

### Database Schema Changes

No schema changes between versions. The `nicsrs_sslorders` table remains unchanged.

---

## Deprecation Notices

### Version 1.1.0
- `nicsrsFunc::loadLanguage()` is deprecated. Internal language loading should use the new method.

---

## Known Issues

### Current
- Certificate auto-renewal requires manual intervention
- Some Cyrillic domain names may not validate correctly

### Resolved in 1.1.0
- ~~Multi-domain wildcard certificates showing incorrect domain count~~
- ~~Chinese Traditional language file encoding issues~~

---

**Author**: HVN GROUP  
**Website**: [https://hvn.vn](https://hvn.vn)