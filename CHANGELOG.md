# Changelog

> **Project:** NicSRS SSL Management System for WHMCS  
> **Author:** [HVN GROUP](https://hvn.vn)  
> **Format:** Dựa trên [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) và [Semantic Versioning](https://semver.org/)

---

## Admin Addon Module (`nicsrs_ssl_admin`)

---

### [1.3.1] — 2025-06-XX

#### Fixed
- Currency settings migration trong `upgrade()` — kiểm tra `$exists` trước khi INSERT tránh lỗi duplicate key khi upgrade nhiều lần
- Sync error count tracking: reset đúng về 0 khi sync thành công

#### Changed
- `NICSRS_ADMIN_VERSION` cập nhật từ `1.3.0` → `1.3.1`

---

### [1.3.0] — 2025-05-XX

#### Added
- **ReportController** — Module báo cáo hoàn chỉnh với 3 loại:
  - Profit Report: doanh thu, chi phí, lợi nhuận per-order (USD + VND)
  - Product Performance: hiệu suất per-product với completion rate, renewal rate
  - Revenue by Brand: doanh thu per-vendor với market share %
- **ReportService** — Service layer cho data aggregation báo cáo
- **CurrencyHelper** — Helper class cho chuyển đổi USD ↔ VND:
  - `getUsdVndRate()`, `setUsdVndRate()`, `convertUsdToVnd()`
  - `formatVnd()`, `formatUsd()`
  - `updateRateFromApi()` — lấy tỷ giá từ API bên ngoài
  - `setDisplayMode()` — chế độ hiển thị: `usd` / `vnd` / `both`
- Chart.js visualization cho tất cả báo cáo: bar chart, line chart, pie chart, doughnut chart
- CSV export cho 3 loại báo cáo (base64-encoded download)
- Reports navigation tab trong admin interface
- Vietnamese language file (`lang/vietnamese.php`) — ~120 translation keys
- Currency settings trong `mod_nicsrs_settings`: `usd_vnd_rate`, `currency_display`, `rate_last_updated`
- Reports Index page với quick stats cards (doanh thu tháng, đơn hàng, chứng chỉ active)

#### Changed
- `upgrade()` function: thêm migration block cho v1.3.0 — tự động thêm currency settings
- Settings page: thêm Currency Settings panel
- Dashboard: cải thiện hiển thị thống kê

---

### [1.2.1] — 2025-04-XX

#### Added
- Auto-Sync improvements:
  - Expiry checking cho active certificates (không chỉ pending)
  - `sync_batch_size` setting có thể cấu hình (10–200)
  - `sync_error_count` tracking với threshold alert (≥ 3 errors)
  - `last_status_sync`, `last_product_sync` timestamps
- `AdminAreaHeaderOutput` hook: hiển thị warning banner khi sync lỗi liên tục
- `AddonActivation` hook: tự động tạo sync settings khi module được kích hoạt

#### Changed
- `upgrade()` function: thêm migration block cho v1.2.1 — thêm sync settings mới
- SyncService: phân biệt xử lý pending certificates (status update) và active certificates (expiry check)

#### Fixed
- `SyncService::syncCertificateStatus()`: xử lý đúng cả `status` và `certStatus` fields từ API response
- Completion date logic: sử dụng `beginDate` từ API hoặc current date khi status chuyển complete

---

### [1.2.0] — 2025-03-XX

#### Added
- **SyncService** — Auto-sync engine hoàn chỉnh:
  - Certificate status sync via WHMCS cron (`DailyCronJob`, `AfterCronJob` hooks)
  - Product catalog sync từ 10 vendors (500ms delay between vendors)
  - Price change detection + email notification với bảng so sánh
  - Configurable intervals: status sync (1–24h), product sync (1–168h)
  - Manual sync trigger từ Settings UI
  - Error tracking + admin notification khi ≥ 3 consecutive errors
- **NotificationService** — HTML email notifications via WHMCS Local API:
  - Certificate issuance notification (green header, cert details)
  - Expiry warning notification (urgency levels: 🚨 ≤7d, ⚠️ other)
  - Sync error alert notification (red header, error list, settings link)
  - Price change notification (comparison table with arrows)
  - `checkAndSendExpiryWarnings()` — full scan for cron
- **ActivityLogger** — Audit logging service:
  - `log()`, `logOrderAction()`, `logSettingsChange()`
  - `getLogsForEntity()` — filtered by entity type/id
- `hooks.php` — WHMCS hook integration:
  - `DailyCronJob` + `AfterCronJob` → `nicsrs_ssl_admin_run_sync()`
  - `AdminAreaHeaderOutput` → sync error warning banner
  - `ClientAreaPage` → reserved for future
  - `ServiceRenewal` → reserved for future auto-renewal
- `cron.php` — Standalone cron endpoint
- Sync Status display (AJAX) trong Settings: last sync, next sync, pending count
- Manual sync buttons: Sync Certificate Status, Sync Products, Check Expiring
- Sync log display (last 5 entries, JS-populated)

#### Changed
- **Email system**: Chuyển hoàn toàn từ PHP `mail()` sang WHMCS Local API `SendAdminEmail` — fix critical issue với email delivery
- Email format: Plain text → HTML templates với inline CSS, gradient headers, responsive design, HVN GROUP branding
- Settings page: thêm Auto-Sync Settings panel và sync status display

#### Fixed
- AJAX request routing trong Settings: sử dụng `modulelink` variable đúng cách thay vì hardcoded URL
- Email notifications: HTML formatting thay vì plain text

---

### [1.1.0] — 2025-02-XX

#### Added
- **ImportController** — Certificate import/link module:
  - `lookupCertificate()` — tra cứu cert từ NicSRS API bằng Certificate ID
  - `importCertificate()` — nhập cert không liên kết service (userid=0, serviceid=0)
  - `linkExistingService()` — nhập và liên kết với WHMCS service (validates servertype)
  - `bulkImport()` — nhập hàng loạt nhiều cert IDs (skip duplicates, report per-cert errors)
- Import template (`templates/import.php`): lookup form, import options, bulk textarea, recently imported list
- Import navigation tab
- Order detail improvements: thêm DCV information panel, activity log per order

#### Changed
- OrderController: thêm detail view (`render('order')` khi có `$_GET['id']`)
- Navigation: 5 tabs → 6 tabs (thêm Import)

---

### [1.0.0] — 2025-01-XX — Initial Release

#### Added
- Module entry point: `config()`, `activate()`, `deactivate()`, `output()`, `upgrade()`
- Database schema: 3 custom tables (`mod_nicsrs_products`, `mod_nicsrs_activity_log`, `mod_nicsrs_settings`)
- Auto-created on activation với 15+ default settings
- **BaseController** (abstract): template rendering, JSON responses, settings access, pagination, sanitization
- **DashboardController**: 4 stats cards, Chart.js charts (doughnut + bar), recent orders (10), expiring certs (20), API status alert
- **ProductController**: product list with search/filter (vendor, type, linked), product sync (all vendors or specific), pagination
- **OrderController**: order list with search/filter (status, domain, client), order detail view, status refresh, cancel, revoke
- **SettingsController**: notification settings, display settings, API configuration, activity log viewer, clear/export logs
- **ActivityController**: paginated activity log table
- **ViewHelper**: `e()`, `formatDate()`, `truncate()`, `statusBadge()`, `validationBadge()`, `formatPrice()`, `formatCurrency()`, `formatBillingCycle()`
- **NicsrsApiService** (instance-based): all NicSRS API endpoints, `testConnection()`
- Navigation: 5-tab layout (Dashboard, Products, Orders, Settings, Activity)
- English language file (`lang/english.php`): ~120 translation keys
- Admin CSS (`assets/css/admin.css`): Bootstrap 3 compatible styling
- Admin JS (`assets/js/admin.js`): toast notifications, utility functions
- SPL autoloader for `NicsrsAdmin\` namespace

---

## Server Provision Module (`nicsrs_ssl`)

---

### [2.1.0] — 2025-05-XX

#### Added
- **Multi-step `applycert.tpl`**: Visual progress bar (4 steps), section guidance text per step
- **CSR auto-generation**: Client-side option với `generateCSR` action (OpenSSL)
- **Draft save/resume**: `saveDraft` action + `isDraft` flag + Draft status card
- **DCV email optgroup**: Dynamic email loading per domain trong DCV dropdown
- **`reissue.tpl`**: Same multi-step structure as applycert, pre-filled with existing data, reissue reason selector (6 options)
- **`complete.tpl`** enhancements:
  - Download PEM/PKCS12/JKS formats
  - Password modal cho PKCS12 and JKS với copy button
  - Certificate + CA bundle copy-to-clipboard
  - Certificate info display (dates, vendor ID, domain list)
- **`cancelled.tpl`**: Timeline/history view, validity dates display, renew option
- Modern CSS (`assets/css/ssl-manager.css`): Ant Design inspired, responsive, CSS variables, progress indicators, form sections, status cards, modals
- `ssl-manager.js`: Form interactions, domain handlers, CSR handlers, DCV email options, form data restore
- **`getDcvEmails`** action (v2.0.1): Get DCV email options for domain via API

#### Changed
- `applycert.tpl`: Basic form → multi-step guided interface
- `reissue.tpl`: Simple form → full reissue flow with reason tracking
- Template variables: `$client` (applycert) vs `$clientsdetails` (reissue) — maintained for Smarty compat
- `TemplateHelper::getBaseVars()`: thêm `WEB_ROOT`, `countries`, `supportOptions`

---

### [2.0.1] — 2025-03-XX

#### Fixed
- **POST data handling**: Improved handling of old module data format (`{"data": {...}}` vs `data[key]=value`) — không merge vào `$_POST` để tránh conflicts
- **AJAX routing**: Sửa AJAX request routing để hoạt động đúng với WHMCS admin module URL structure
- **`getDcvEmails`** action: Thêm action mới cho việc load DCV email options per domain

#### Changed
- Logging: thêm chi tiết hơn cho AJAX requests (step, action, POST_keys, has_data)
- Error responses: standardized JSON format

---

### [2.0.0] — 2025-02-XX — Complete Rewrite

#### Added
- **Dispatcher pattern**:
  - `PageDispatcher`: Status-based page routing + `validateServiceOwnership()`
  - `ActionDispatcher`: Action routing với 25+ aliases cho backward compatibility + public actions exemption
- **`PageController`**: Status-based routing via `normalizeStatus()` (15+ status variants → 10 canonical):
  - `renderApplyCert()`, `renderPending()`, `renderComplete()`, `renderCancelled()`
  - `checkVendorMigration()` — detect certs from other providers in `tblsslorders`
  - `getCertConfig()` — dynamic product resolution from DB
- **`ActionController`**: Full AJAX handler set:
  - `submitApply()` — validate + build API request + place order
  - `saveDraft()` — merge form data + preserve existing configdata
  - `refreshStatus()` — API /collect + update order
  - `downCert()` — multi-format download (PEM/PKCS12/JKS/key)
  - `batchUpdateDCV()`, `resendDCVEmail()`
  - `cancelOrder()`, `revoke()`, `submitReissue()`, `renew()`
  - `generateCSR()`, `decodeCsr()`
- **`ApiService`** (modern): Static methods, 4-level token fallback chain:
  1. Product-level `configoption2`
  2. Service → Product lookup via DB
  3. Admin Addon `tbladdonmodules`
  4. `mod_nicsrs_settings` fallback
- **`OrderRepository`**: CRUD wrapper with `ensureTableExists()`, Capsule ORM
- **`CertificateFunc`**: Dynamic name↔code mapping from `mod_nicsrs_products` DB (replaces hardcoded), bidirectional cache
- **`ResponseFormatter`**: Standardized JSON responses (`success()`, `error()`, `json()`)
- **`TemplateHelper`**: Template factory by status, `getBaseVars()` for common variables
- **`DcvHelper`**: DCV method label + available methods utilities
- **Vendor migration flow**:
  - `hasActiveVendorCert()` in `CreateAccount` — checks `tblsslorders`
  - `buildVendorMigrationWarning()` in admin service tab
  - `AdminAllowNewCert` button + handler với migration flags
  - `migrated.tpl` template for client area
  - `originalfromOthers` / `isRenew` flags sent to API
- WHMCS module functions: `ConfigOptions` (dynamic dropdown from cache), `AdminServicesTabFields` (order info + migration warning), `AdminCustomButtonArray` (4 buttons), `ClientAreaCustomButtonArray` (status-based buttons)
- Step-to-action mapping: 25+ mappings including old module names (`applyssl`, `cancleOrder`, `downcert`, `replacessl`)

#### Changed
- Architecture: Monolithic → Dispatcher + Controller + Service layers
- API client: Single class → `ApiService` (modern) + `nicsrsAPI` (legacy compatibility)
- SQL operations: Mixed raw SQL → Capsule ORM via `OrderRepository`
- Certificate dropdown: Hardcoded list → Dynamic from `mod_nicsrs_products` cache
- Token management: Single source → 4-level priority-based fallback chain

#### Deprecated
- `nicsrsAPI` class — kept for backward compatibility, use `ApiService` for new code
- `nicsrsSSLSql` class — kept for legacy data access, use `OrderRepository` for new code
- `nicsrsTemplate` class — replaced by `TemplateHelper`

---

### [1.1] — 2024-XX-XX

#### Added
- sslTrus certificate support (DV/OV/EV, Wildcard, Multi-Domain)
- Improved multi-domain handling
- Enhanced DCV batch update functionality (`batchUpdateDCV`)
- IP address SSL support for select certificates

#### Changed
- DCV method selection: thêm HTTPS_CSR_HASH option
- Multi-domain form: improved add/remove domain UX

---

### [1.0] — 2024-XX-XX — Initial Release

#### Added
- NicSRS API integration: 15 endpoints (validate, place, collect, cancel, revoke, reissue, renew, replace, DCVemail, updateDCV, batchUpdateDCV, validatefile, validatedns, country, removeMdcDomain)
- Certificate lifecycle management: order → configure → validate → issue → download → reissue → renew → revoke
- Multi-vendor support: Sectigo, DigiCert, GlobalSign, GeoTrust, Symantec, Entrust, BaiduTrust, sslTrus
- DCV methods: EMAIL, HTTP_CSR_HASH, HTTPS_CSR_HASH, CNAME_CSR_HASH
- Certificate download: Apache/Nginx (PEM), IIS (PKCS12), Tomcat (JKS)
- Multi-language: English, Chinese (Traditional), Chinese (Simplified)
- Client area templates: applycert, complete, message, replace, error
- Database: `nicsrs_sslorders` table with JSON configdata
- WHMCS module functions: CreateAccount, SuspendAccount, TerminateAccount, ClientArea, ConfigOptions

---

## Migration Notes

### Upgrading Admin Addon from 1.2.x → 1.3.x
- `upgrade()` tự động thêm currency settings (`usd_vnd_rate`, `currency_display`, `rate_last_updated`)
- Không cần manual migration
- Reports tab tự động xuất hiện sau upgrade

### Upgrading Admin Addon from 1.1.x → 1.2.x
- `upgrade()` tự động thêm sync settings (`sync_batch_size`, `last_status_sync`, `last_product_sync`, `sync_error_count`)
- Cron hooks tự động đăng ký qua `hooks.php`
- Kiểm tra WHMCS cron đang chạy để auto-sync hoạt động

### Upgrading Server Module from 1.x → 2.0
- **No breaking changes** cho existing data — `nicsrs_sslorders` schema không thay đổi
- Legacy API client (`nicsrsAPI`) vẫn hoạt động song song với `ApiService`
- Old step names (`applyssl`, `cancleOrder`, etc.) vẫn được support qua alias mapping
- `configoption2` token fallback vẫn là Priority 1

### Data Preservation
- Module deactivation **không xóa** database tables
- Tất cả orders, products, settings, activity logs được giữ nguyên
- Re-activation sẽ detect existing tables và skip creation

---

**© HVN GROUP** — [hvn.vn](https://hvn.vn)