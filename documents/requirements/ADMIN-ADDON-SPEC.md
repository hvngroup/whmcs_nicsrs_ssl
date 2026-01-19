# NicSRS SSL Admin Addon Module - Version 1.2.0 Specification

## Overview

The Admin Addon Module provides a comprehensive management interface for WHMCS administrators to manage SSL certificates, view products, monitor orders, and perform certificate operations directly from the WHMCS admin panel.

## Design System

### UI Framework: Ant Design Style

The admin interface follows **Ant Design** principles:
- Clean, minimalist aesthetic with consistent spacing
- Color-coded status indicators
- Card-based layouts with subtle shadows
- Responsive tables with inline actions
- Modal dialogs for confirmations
- Toast notifications (message feedback)
- Form validation with inline error messages

### Color Palette

```css
/* Primary Colors */
--primary-color: #1890ff;      /* Primary Blue */
--success-color: #52c41a;      /* Green - Complete */
--warning-color: #faad14;      /* Orange - Pending */
--error-color: #ff4d4f;        /* Red - Cancelled/Error */
--info-color: #1890ff;         /* Blue - Info */

/* Neutral Colors */
--heading-color: #262626;
--text-color: #595959;
--text-secondary: #8c8c8c;
--border-color: #d9d9d9;
--background-color: #f5f5f5;
--component-bg: #ffffff;

/* Status Badge Colors */
--badge-complete: #52c41a;
--badge-pending: #faad14;
--badge-cancelled: #ff4d4f;
--badge-draft: #8c8c8c;
--badge-reissue: #722ed1;
```

---

## Module Structure

```
nicsrs_ssl/
├── nicsrs_ssl.php                    # Main provisioning module
├── addon/
│   └── nicsrs_ssl_admin/
│       ├── nicsrs_ssl_admin.php      # Addon entry point
│       ├── hooks.php                 # WHMCS hooks
│       ├── lib/
│       │   ├── Admin/
│       │   │   ├── AdminController.php
│       │   │   ├── DashboardController.php
│       │   │   ├── ProductController.php
│       │   │   ├── OrderController.php
│       │   │   └── ToolsController.php
│       │   └── Api/
│       │       └── NicsrsAdminApi.php
│       ├── templates/
│       │   ├── dashboard.tpl
│       │   ├── products.tpl
│       │   ├── orders.tpl
│       │   ├── order-detail.tpl
│       │   ├── tools.tpl
│       │   └── settings.tpl
│       ├── assets/
│       │   ├── css/
│       │   │   └── admin-antd.css
│       │   └── js/
│       │       ├── admin-main.js
│       │       └── charts.js
│       └── lang/
│           ├── english.php
│           └── chinese.php
```

---

## Features Specification

### 1. Dashboard Overview

**Purpose**: Provide quick insights into SSL certificate business.

**Components**:

```
┌────────────────────────────────────────────────────────────────────┐
│                        NicSRS SSL Dashboard                         │
├────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌────────────┐ │
│  │   Total      │ │   Pending    │ │   Issued     │ │   Expiring │ │
│  │   Orders     │ │   Orders     │ │   Certs      │ │   Soon     │ │
│  │    156       │ │     12       │ │     134      │ │     8      │ │
│  │   ▲ 12%      │ │   ▼ 3%       │ │   ▲ 8%       │ │            │ │
│  └──────────────┘ └──────────────┘ └──────────────┘ └────────────┘ │
│                                                                     │
│  ┌─────────────────────────────┐  ┌─────────────────────────────┐  │
│  │   Certificate Status Pie    │  │   Monthly Orders Chart      │  │
│  │         [CHART]             │  │         [CHART]             │  │
│  └─────────────────────────────┘  └─────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                    Recent Orders Table                        │  │
│  │  ID  │ Domain      │ Product    │ Status   │ Created        │  │
│  │  156 │ example.com │ PositiveSSL│ Complete │ 2025-01-15     │  │
│  │  155 │ test.com    │ Sectigo OV │ Pending  │ 2025-01-14     │  │
│  └──────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────┘
```

**Statistics Cards**:
| Metric | Description | Icon |
|--------|-------------|------|
| Total Orders | All-time SSL orders | 📦 |
| Pending Orders | Orders awaiting validation | ⏳ |
| Issued Certificates | Successfully issued certs | ✅ |
| Expiring Soon | Certificates expiring in 30 days | ⚠️ |
| Account Balance | NicSRS credit balance | 💰 |

---

### 2. Product List Management

**Purpose**: Display available NicSRS products with real-time pricing.

**API Endpoint**: `POST /ssl/productList`

**Request**:
```json
{
    "api_token": "your_token",
    "vendor": "Sectigo"
}
```

**UI Layout**:

```
┌────────────────────────────────────────────────────────────────────┐
│  Product List                                     [Refresh] [Export]│
├────────────────────────────────────────────────────────────────────┤
│  Vendor Filter: [All ▼] [Sectigo] [DigiCert] [GlobalSign] [...]    │
│  Type Filter:   [All ▼] [DV] [OV] [EV] [Wildcard] [Multi-Domain]   │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌────────────────────────────────────────────────────────────────┐│
│  │ Product        │ Type │ Wildcard │ SAN │ Max │ 1Y Price │ 2Y   ││
│  ├────────────────┼──────┼──────────┼─────┼─────┼──────────┼──────┤│
│  │ PositiveSSL    │  DV  │    No    │ No  │  1  │  $9.00   │$16.00││
│  │ Sectigo OV     │  OV  │    No    │ Yes │  5  │  $59.00  │$99.00││
│  │ Sectigo EV     │  EV  │    No    │ Yes │  3  │  $129.00 │$229  ││
│  │ Wildcard DV    │  DV  │   Yes    │ No  │  1  │  $79.00  │$139  ││
│  └────────────────────────────────────────────────────────────────┘│
│                                                                     │
│  Pagination: [< Prev] [1] [2] [3] [Next >]       Showing 1-20 of 85│
└────────────────────────────────────────────────────────────────────┘
```

**Table Columns**:
| Column | Description |
|--------|-------------|
| Product Code | Internal product identifier |
| Product Name | Display name |
| Validation Type | DV/OV/EV badge |
| Wildcard Support | Yes/No |
| SAN Support | Yes/No |
| Max Domains | Maximum domain count |
| Price (1Y) | One-year pricing |
| Price (2Y) | Two-year pricing |
| SAN Price | Additional SAN pricing |
| Actions | Create WHMCS Product |

---

### 3. SSL Orders Management

**Purpose**: View and manage all SSL certificate orders.

**UI Layout**:

```
┌────────────────────────────────────────────────────────────────────┐
│  SSL Orders                                    [+ New Order] [Export]│
├────────────────────────────────────────────────────────────────────┤
│  Search: [_______________] Status: [All ▼]  Date: [From] - [To]    │
├────────────────────────────────────────────────────────────────────┤
│  ☐ │ ID  │ Client    │ Domain       │ Product   │ Status  │Actions │
│  ──┼─────┼───────────┼──────────────┼───────────┼─────────┼────────│
│  ☐ │ 156 │ John Doe  │ example.com  │ Positive  │●Complete│[⋮ Menu]│
│  ☐ │ 155 │ Jane Smith│ *.test.com   │ Wildcard  │●Pending │[⋮ Menu]│
│  ☐ │ 154 │ Bob Wilson│ shop.com     │ EV SSL    │●Draft   │[⋮ Menu]│
├────────────────────────────────────────────────────────────────────┤
│  Bulk Actions: [Select Action ▼] [Apply]                            │
│  Options: Cancel Selected | Resend Validation | Export Selected     │
└────────────────────────────────────────────────────────────────────┘
```

**Order Status Badges**:
| Status | Color | Badge |
|--------|-------|-------|
| Awaiting Configuration | Gray | ⚪ |
| Draft | Gray | ⚪ |
| Pending | Orange | 🟠 |
| Complete | Green | 🟢 |
| Cancelled | Red | 🔴 |
| Reissued | Purple | 🟣 |

**Action Menu** (dropdown per row):
- 👁️ View Details
- ✏️ Edit Order
- 🔄 Refresh Status
- 📧 Resend Validation
- 📥 Download Certificate
- 🔁 Reissue Certificate
- ♻️ Renew Certificate
- 🚫 Cancel Order
- ⛔ Revoke Certificate

---

### 4. Order Detail View

**Purpose**: Comprehensive view of single order with all operations.

**Layout**:

```
┌────────────────────────────────────────────────────────────────────┐
│  Order #156 - example.com                    [Back to List]         │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────┐  ┌─────────────────────────────────┐  │
│  │ Order Information       │  │ Certificate Status              │  │
│  │ ───────────────────────│  │ ─────────────────────────────── │  │
│  │ Order ID: 156          │  │ Status: ● Complete              │  │
│  │ Remote ID: NICSRS-12345│  │ Issued: 2025-01-15              │  │
│  │ Product: PositiveSSL   │  │ Expires: 2026-01-15             │  │
│  │ Created: 2025-01-10    │  │ Days Left: 361 days             │  │
│  │ Client: John Doe       │  │                                 │  │
│  └─────────────────────────┘  └─────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Domain Validation Status                                      │  │
│  │ ────────────────────────────────────────────────────────────│  │
│  │ Domain        │ Method │ Status    │ Actions                 │  │
│  │ example.com   │ EMAIL  │ ✓Verified │ [Resend]               │  │
│  │ www.example.  │ DNS    │ ⏳Pending │ [Resend] [Change DCV]  │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Quick Actions                                                 │  │
│  │ [📥 Download Cert] [🔁 Reissue] [♻️ Renew] [🚫 Cancel]      │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Activity Log                                                  │  │
│  │ 2025-01-15 10:30 - Certificate issued successfully           │  │
│  │ 2025-01-14 15:20 - DCV verification completed                │  │
│  │ 2025-01-10 09:00 - Order created by admin                    │  │
│  └──────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────┘
```

---

### 5. Tools Section

> **⚠️ DEFERRED TO v1.3.0**: Tools Section sẽ được phát triển trong phiên bản tiếp theo.
> 
> Planned tools:
> - CAA Record Checker
> - CSR Decoder  
> - API Connection Test
> - Certificate Expiry Report

---

### 6. Settings Page

**Purpose**: Configure addon module settings.

**Sections**:

```
┌────────────────────────────────────────────────────────────────────┐
│  NicSRS SSL Admin Settings                                          │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  API Configuration                                                  │
│  ─────────────────────────────────────────────────────────────────│
│  API Token:      [••••••••••••••••••••] [Show/Hide] [Test]         │
│  API Endpoint:   [https://portal.nicsrs.com/ssl]                   │
│                                                                     │
│  Notification Settings                                              │
│  ─────────────────────────────────────────────────────────────────│
│  ☑ Email admin on certificate issuance                              │
│  ☑ Email admin on certificate expiry (30 days before)               │
│  ☑ Email client on DCV pending                                      │
│  Admin Email:    [admin@example.com_________________]               │
│                                                                     │
│  Display Settings                                                   │
│  ─────────────────────────────────────────────────────────────────│
│  Items per page: [25 ▼]                                             │
│  Date format:    [Y-m-d ▼]                                          │
│  Theme:          [Light ▼] [Dark]                                   │
│                                                                     │
│  Auto-Sync Settings                                                 │
│  ─────────────────────────────────────────────────────────────────│
│  ☑ Auto-sync certificate status every [6] hours                     │
│  ☑ Auto-sync product prices every [24] hours                        │
│                                                                     │
│  [Save Settings]                                                    │
└────────────────────────────────────────────────────────────────────┘
```

---

## API Endpoints Reference

### New Endpoints for Admin Module

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/ssl/productList` | POST | Get available products with pricing |
| `/ssl/caaCheck` | POST | Check CAA records for domain |
| `/ssl/cancel` | POST | Cancel SSL subscription |
| `/ssl/revoke` | POST | Revoke issued certificate |
| `/ssl/renew` | POST | Renew certificate |
| `/ssl/reissue` | POST | Reissue certificate |
| `/ssl/getCertByRefId` | POST | Get certificate by reference ID |

### Response Codes

| Code | Description |
|------|-------------|
| 1 | Success |
| 2 | Certificate being issued, retry later |
| -1 | Parameter validation failed |
| -2 | Unknown error |
| -3 | Product/price error |
| -4 | Insufficient credit |
| -6 | CA request failed |
| 400 | Permission denied |

---

## Database Schema Updates

### New Table: `nicsrs_ssl_products`

```sql
CREATE TABLE `nicsrs_ssl_products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_code` VARCHAR(100) NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `vendor` VARCHAR(50) NOT NULL,
    `validation_type` ENUM('dv', 'ov', 'ev') NOT NULL,
    `support_wildcard` TINYINT(1) DEFAULT 0,
    `support_san` TINYINT(1) DEFAULT 0,
    `max_domains` INT DEFAULT 1,
    `max_years` INT DEFAULT 1,
    `price_1y` DECIMAL(10,2),
    `price_2y` DECIMAL(10,2),
    `san_price_1y` DECIMAL(10,2),
    `san_price_2y` DECIMAL(10,2),
    `last_sync` DATETIME,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `product_code` (`product_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### New Table: `nicsrs_ssl_activity_log`

```sql
CREATE TABLE `nicsrs_ssl_activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `order_id` INT,
    `action` VARCHAR(50) NOT NULL,
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order` (`order_id`),
    INDEX `idx_admin` (`admin_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### New Table: `nicsrs_ssl_settings`

```sql
CREATE TABLE `nicsrs_ssl_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Suggested Additional Features

### Priority 1 (Essential)

1. **Auto-Renewal Automation**
   - Cron job to check expiring certificates
   - Auto-create renewal orders for clients
   - Email notifications before expiry

2. **Webhook Integration**
   - Receive real-time status updates from NicSRS
   - Automatic order status sync
   - Instant notification on certificate issuance

3. **Multi-Vendor Price Comparison**
   - Compare prices across vendors
   - Recommend best-value certificates
   - Margin calculator for resellers

### Priority 2 (Recommended)

4. **Client Self-Service Portal**
   - Certificate installation guides
   - CSR generator for clients
   - DCV troubleshooting wizard

5. **Reporting & Analytics**
   - Revenue reports by product/vendor
   - Certificate issuance trends
   - Client activity reports

6. **Bulk Order Import**
   - CSV import for multiple orders
   - API for third-party integrations
   - Automated provisioning

### Priority 3 (Nice to Have)

7. **Certificate Monitoring**
   - SSL health checks
   - Expiry monitoring
   - Configuration validation

8. **White-Label Support**
   - Custom branding options
   - Custom email templates
   - Branded client portal

9. **API Rate Limiting Dashboard**
   - Track API usage
   - Monitor rate limits
   - Usage analytics

---

## Implementation Timeline

| Phase | Features | Duration |
|-------|----------|----------|
| Phase 1 | Dashboard, Product List, Basic Orders | 2 weeks |
| Phase 2 | Order Management, Certificate Actions | 2 weeks |
| Phase 3 | Tools, Settings, Activity Logs | 1 week |
| Phase 4 | Testing, Documentation, Release | 1 week |

**Total Estimated Time**: 6 weeks

---

**Author**: HVN GROUP  
**Version**: 1.2.0  
**Website**: [https://hvn.vn](https://hvn.vn)