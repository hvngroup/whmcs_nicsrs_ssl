# NicSRS SSL Admin Module v1.2.0 - Implementation Plan

## Project Overview

| Item | Details |
|------|---------|
| **Project** | NicSRS SSL Admin Addon Module |
| **Version** | 1.2.0 |
| **Type** | WHMCS Addon Module |
| **Author** | HVN GROUP |
| **Estimated Duration** | 4 weeks |
| **Start Date** | TBD |

---

## Scope Definition

### ✅ In Scope (v1.2.0)

| # | Feature | Priority |
|---|---------|----------|
| 1 | Dashboard Overview với Statistics | P0 |
| 2 | Product List Management (API sync) | P0 |
| 3 | SSL Orders Management (CRUD) | P0 |
| 4 | Order Detail View | P0 |
| 5 | Certificate Actions (Cancel, Revoke, Reissue, Renew) | P1 |
| 6 | Settings Page | P1 |
| 7 | Activity Logging | P2 |
| 8 | Ant Design UI Framework | P0 |

### ❌ Out of Scope (Deferred to v1.3.0)

| Feature | Reason |
|---------|--------|
| Tools Section (CAA Checker, CSR Decoder) | Complexity, defer for polish |
| Webhook Integration | Requires NicSRS support |
| Auto-Renewal Automation | Requires extensive testing |
| Bulk Operations | Nice-to-have, not critical |
| Export Functions | Can add later easily |

---

## Technical Architecture

### Module Structure

```
modules/addons/nicsrs_ssl_admin/
├── nicsrs_ssl_admin.php          # Entry point, WHMCS hooks
├── hooks.php                     # Admin area hooks
├── lib/
│   ├── Controller/
│   │   ├── BaseController.php    # Base controller class
│   │   ├── DashboardController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   └── SettingsController.php
│   ├── Service/
│   │   ├── NicsrsApiService.php  # API communication
│   │   ├── ProductService.php    # Product business logic
│   │   ├── OrderService.php      # Order business logic
│   │   └── ActivityLogger.php    # Audit logging
│   └── Helper/
│       ├── ViewHelper.php        # Template helpers
│       └── Pagination.php        # Pagination utility
├── templates/
│   ├── layout.tpl                # Main layout wrapper
│   ├── dashboard.tpl
│   ├── products/
│   │   └── list.tpl
│   ├── orders/
│   │   ├── list.tpl
│   │   └── detail.tpl
│   └── settings.tpl
├── assets/
│   ├── css/
│   │   └── admin.css             # Ant Design styles
│   └── js/
│       └── admin.js              # Main JavaScript
└── lang/
    ├── english.php
    └── vietnamese.php
```

### Database Schema

```sql
-- Table 1: Products Cache
CREATE TABLE mod_nicsrs_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(100) UNIQUE,
    product_name VARCHAR(255),
    vendor VARCHAR(50),
    validation_type ENUM('dv','ov','ev'),
    support_wildcard TINYINT(1) DEFAULT 0,
    support_san TINYINT(1) DEFAULT 0,
    max_domains INT DEFAULT 1,
    max_years INT DEFAULT 1,
    price_data JSON,                    -- Store full pricing
    last_sync DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table 2: Activity Log  
CREATE TABLE mod_nicsrs_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50),            -- 'order', 'product', 'settings'
    entity_id INT,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);

-- Table 3: Module Settings
CREATE TABLE mod_nicsrs_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string',
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Development Phases

### Phase 1: Foundation (Week 1)
**Goal**: Setup module structure, database, base UI

| Task | Description | Est. Hours |
|------|-------------|------------|
| 1.1 | Create addon module skeleton | 2h |
| 1.2 | Implement activation/deactivation hooks | 2h |
| 1.3 | Create database tables (migration) | 3h |
| 1.4 | Setup base controller architecture | 4h |
| 1.5 | Create layout template với navigation | 4h |
| 1.6 | Implement Ant Design CSS framework | 6h |
| 1.7 | Setup JavaScript foundation | 3h |
| **Total** | | **24h** |

**Deliverables**:
- [ ] Module loads in WHMCS without errors
- [ ] Database tables created on activation
- [ ] Navigation menu functional
- [ ] Base CSS/JS assets loading

---

### Phase 2: Dashboard & Products (Week 2)
**Goal**: Dashboard statistics, Product list với API sync

| Task | Description | Est. Hours |
|------|-------------|------------|
| 2.1 | Create NicsrsApiService class | 4h |
| 2.2 | Implement `/productList` API integration | 4h |
| 2.3 | Build ProductService for data handling | 3h |
| 2.4 | Create Products list template | 4h |
| 2.5 | Add product filtering (vendor, type) | 3h |
| 2.6 | Build DashboardController | 3h |
| 2.7 | Create statistics queries | 3h |
| 2.8 | Build Dashboard template với cards | 4h |
| 2.9 | Add recent orders widget | 2h |
| **Total** | | **30h** |

**Deliverables**:
- [ ] Dashboard shows real statistics
- [ ] Products synced from NicSRS API
- [ ] Product list với filters working
- [ ] Statistics cards displaying correctly

**API Endpoints Used**:
```
POST /ssl/productList
  - Request: { api_token, vendor }
  - Response: { code, data: [products] }
```

---

### Phase 3: Orders Management (Week 3)
**Goal**: Full orders CRUD, detail view, certificate actions

| Task | Description | Est. Hours |
|------|-------------|------------|
| 3.1 | Build OrderService class | 4h |
| 3.2 | Create orders list query với joins | 3h |
| 3.3 | Implement search & filter logic | 4h |
| 3.4 | Build orders list template | 5h |
| 3.5 | Add pagination component | 2h |
| 3.6 | Create order detail view | 5h |
| 3.7 | Implement `/collect` API for status refresh | 3h |
| 3.8 | Build Cancel action (`/cancel`) | 3h |
| 3.9 | Build Revoke action (`/revoke`) | 3h |
| 3.10 | Build Reissue action (`/reissue`) | 3h |
| 3.11 | Build Renew action (`/renew`) | 3h |
| 3.12 | Add confirmation modals | 2h |
| **Total** | | **40h** |

**Deliverables**:
- [ ] Orders list với pagination
- [ ] Search by domain/client working
- [ ] Filter by status working
- [ ] Order detail shows all info
- [ ] Certificate actions functional
- [ ] Confirmation dialogs for destructive actions

**API Endpoints Used**:
```
POST /ssl/collect    - Get certificate status & details
POST /ssl/cancel     - Cancel SSL subscription
POST /ssl/revoke     - Revoke issued certificate  
POST /ssl/reissue    - Reissue certificate
POST /ssl/renew      - Renew certificate
```

---

### Phase 4: Settings & Polish (Week 4)
**Goal**: Settings page, activity logging, testing, documentation

| Task | Description | Est. Hours |
|------|-------------|------------|
| 4.1 | Build SettingsController | 2h |
| 4.2 | Create settings form template | 3h |
| 4.3 | Implement settings save/load | 2h |
| 4.4 | Build ActivityLogger service | 3h |
| 4.5 | Add logging to all actions | 3h |
| 4.6 | Create activity log viewer | 3h |
| 4.7 | UI polish & responsive fixes | 4h |
| 4.8 | Error handling improvements | 3h |
| 4.9 | Add Vietnamese language file | 2h |
| 4.10 | Testing - Unit tests | 4h |
| 4.11 | Testing - Integration tests | 4h |
| 4.12 | Documentation update | 3h |
| 4.13 | Bug fixes buffer | 4h |
| **Total** | | **40h** |

**Deliverables**:
- [ ] Settings page fully functional
- [ ] All admin actions logged
- [ ] Activity log viewable
- [ ] Vietnamese translation complete
- [ ] All features tested
- [ ] Documentation updated

---

## Task Checklist

### Module Setup
- [ ] Create `nicsrs_ssl_admin.php` entry file
- [ ] Implement `_config()` function
- [ ] Implement `_activate()` function  
- [ ] Implement `_deactivate()` function
- [ ] Implement `_upgrade()` function
- [ ] Implement `_output()` main function

### Database
- [ ] Create `mod_nicsrs_products` table
- [ ] Create `mod_nicsrs_activity_log` table
- [ ] Create `mod_nicsrs_settings` table
- [ ] Add indexes for performance

### Controllers
- [ ] BaseController với common methods
- [ ] DashboardController
- [ ] ProductController
- [ ] OrderController
- [ ] SettingsController

### Services
- [ ] NicsrsApiService (API wrapper)
- [ ] ProductService
- [ ] OrderService
- [ ] ActivityLogger

### Templates
- [ ] layout.tpl (main wrapper)
- [ ] dashboard.tpl
- [ ] products/list.tpl
- [ ] orders/list.tpl
- [ ] orders/detail.tpl
- [ ] settings.tpl

### Assets
- [ ] admin.css (Ant Design styles)
- [ ] admin.js (interactions)

### Languages
- [ ] english.php
- [ ] vietnamese.php

---

## API Integration Details

### 1. Product List API

**Endpoint**: `POST /ssl/productList`

**Request**:
```php
[
    'api_token' => $token,
    'vendor' => 'Sectigo' // or DigiCert, GlobalSign, etc.
]
```

**Response**:
```json
{
    "code": 1,
    "data": [
        {
            "code": "sectigo-ov",
            "productName": "Sectigo OV SSL",
            "supportWildcard": "N",
            "supportSan": "Y", 
            "validationType": "ov",
            "maxDomain": 5,
            "maxYear": 2,
            "price": {
                "basePrice": { "price012": 59.00, "price024": 99.00 },
                "sanPrice": { "price012": 15.00, "price024": 25.00 }
            }
        }
    ]
}
```

**Sync Strategy**:
- Sync all vendors on module activation
- Manual refresh button in admin
- Optional: Auto-sync every 24h via cron

---

### 2. Collect Certificate API

**Endpoint**: `POST /ssl/collect`

**Request**:
```php
[
    'api_token' => $token,
    'certId' => '12345'
]
```

**Response**:
```json
{
    "code": 1,
    "status": "COMPLETE",
    "data": {
        "beginDate": "2025-01-15",
        "endDate": "2026-01-15",
        "certificate": "-----BEGIN...",
        "dcvList": [
            { "domainName": "example.com", "is_verify": "verified" }
        ]
    }
}
```

---

### 3. Cancel API

**Endpoint**: `POST /ssl/cancel`

**Request**:
```php
[
    'api_token' => $token,
    'certId' => '12345',
    'reason' => 'Customer requested cancellation'
]
```

**Business Rules**:
- Only pending/processing orders can be cancelled
- Issued certs can be cancelled within 30 days

---

### 4. Response Codes

| Code | Meaning | Action |
|------|---------|--------|
| 1 | Success | Process response |
| 2 | In progress | Show "please wait" |
| -1 | Validation error | Show error details |
| -2 | Unknown error | Log & show generic error |
| -3 | Product error | Check product config |
| -4 | Insufficient credit | Alert admin |
| -6 | CA error | Contact NicSRS support |
| 400 | Permission denied | Check API token |

---

## UI Components Specification

### 1. Statistics Card
```
┌─────────────────────────┐
│  📦  156                │  <- Icon + Value
│  Total Orders           │  <- Label
│  ▲ 12% from last month  │  <- Trend (optional)
└─────────────────────────┘
```

### 2. Data Table
```
┌─────────────────────────────────────────────────────┐
│ [Search___________] [Status ▼] [Filter]   [Refresh] │  <- Toolbar
├─────────────────────────────────────────────────────┤
│ ☐ │ ID  │ Domain    │ Status  │ Created │ Actions  │  <- Header
├───┼─────┼───────────┼─────────┼─────────┼──────────┤
│ ☐ │ 156 │ test.com  │ ●Active │ Jan 15  │ [⋮]      │  <- Row
├───┼─────┼───────────┼─────────┼─────────┼──────────┤
│ Showing 1-25 of 156          [< 1 2 3 4 5 ... >]   │  <- Pagination
└─────────────────────────────────────────────────────┘
```

### 3. Status Badges
| Status | Style |
|--------|-------|
| Awaiting | `background: #f5f5f5; color: #8c8c8c` |
| Pending | `background: #fffbe6; color: #d48806` |
| Complete | `background: #f6ffed; color: #52c41a` |
| Cancelled | `background: #fff2f0; color: #ff4d4f` |

### 4. Action Buttons
- Primary: Blue (`#1890ff`)
- Success: Green (`#52c41a`) 
- Danger: Red (`#ff4d4f`)
- Default: Gray border

---

## Risk Assessment

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| API rate limiting | Medium | Low | Implement caching, batch requests |
| API schema changes | High | Low | Version check, graceful degradation |
| WHMCS version incompatibility | Medium | Medium | Test on 7.x and 8.x |
| Performance với large datasets | Medium | Medium | Pagination, lazy loading |
| Security vulnerabilities | High | Low | Input validation, CSRF tokens |

---

## Testing Plan

### Unit Tests
- [ ] NicsrsApiService methods
- [ ] ProductService CRUD
- [ ] OrderService queries
- [ ] Pagination helper

### Integration Tests  
- [ ] API connection test
- [ ] Product sync flow
- [ ] Order status refresh
- [ ] Certificate actions

### Manual Tests
- [ ] Full user flow walkthrough
- [ ] Responsive design check
- [ ] Error handling scenarios
- [ ] Permission checks

---

## Definition of Done

### For each feature:
- [ ] Code complete & reviewed
- [ ] Unit tests passing
- [ ] Integration tests passing
- [ ] UI matches design spec
- [ ] No console errors
- [ ] Works on WHMCS 7.x & 8.x
- [ ] Vietnamese translation added
- [ ] Documentation updated

### For release:
- [ ] All features complete
- [ ] Full regression test passed
- [ ] Performance acceptable (< 3s page load)
- [ ] Security review passed
- [ ] CHANGELOG updated
- [ ] Version number bumped

---

## Timeline Summary

| Week | Phase | Key Deliverables |
|------|-------|------------------|
| 1 | Foundation | Module skeleton, DB, base UI |
| 2 | Dashboard & Products | Statistics, product sync |
| 3 | Orders Management | CRUD, actions, detail view |
| 4 | Settings & Polish | Settings, logging, testing |

**Total Estimated Hours**: ~134h

---

## Next Steps (Post v1.2.0)

### v1.3.0 - Tools & Automation
- CAA Record Checker
- CSR Decoder
- API Connection Test
- Expiry Report
- Auto-sync cron job

### v1.4.0 - Advanced Features
- Webhook integration
- Auto-renewal
- Bulk operations
- Export to CSV/Excel
- Email notifications

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-19  
**Author**: HVN GROUP