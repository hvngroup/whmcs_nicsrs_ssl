# NicSRS SSL Server Provision Module - Upgrade Plan v2.0

## Project Overview

| Item | Details |
|------|---------|
| **Project** | NicSRS SSL Server Provision Module Upgrade |
| **Version** | 2.0.0 |
| **Type** | WHMCS Server Provisioning Module |
| **Author** | HVN GROUP |
| **Estimated Duration** | 3 weeks |
| **Start Date** | TBD |
| **Reference** | NicSRS SSL Admin Addon Module v1.2.1 |

---

## 1. Executive Summary

### Current State Analysis

The existing Server Provision Module (`modules/servers/nicsrs_ssl/`) is functional but outdated with the following limitations:

1. **API Configuration**: Each product requires its own API token (`configoption2`)
2. **Limited Client Actions**: Only basic certificate application and status viewing
3. **Outdated UI**: Uses basic Smarty templates without modern styling
4. **Code Duplication**: Has its own API service separate from the Addon Module
5. **Missing Features**: No manage, reissue, renew, revoke actions in client area

### Upgrade Goals

1. **API Centralization**: Use shared API token from Addon Module (`tbladdonmodules`)
2. **Enhanced Client Actions**: Add manage, reissue, renew buttons
3. **UI Modernization**: Match Addon Module's Ant Design-inspired styling
4. **Code Reuse**: Leverage Addon Module's services where possible
5. **Backward Compatibility**: Maintain existing structure for current customers

---

## 2. Directory Structure

### Current Structure (Preserved)
```
modules/servers/nicsrs_ssl/
├── nicsrs_ssl.php                    # Main entry point [MODIFY]
├── hooks.php                         # WHMCS hooks [NEW]
│
├── lang/
│   ├── english.php                   # [MODIFY - add new strings]
│   ├── vietnamese.php                # [MODIFY - add new strings]
│   ├── chinese.php                   
│   └── chinese-cn.php                
│
├── src/
│   ├── config/
│   │   ├── const.php                 # Constants [MODIFY - add new constants]
│   │   └── country.json              
│   │
│   └── model/
│       ├── Controller/
│       │   ├── PageController.php    # Page rendering [MODIFY]
│       │   └── ActionController.php  # Certificate actions [MODIFY]
│       │
│       ├── Dispatcher/
│       │   ├── PageDispatcher.php    # [KEEP]
│       │   └── ActionDispatcher.php  # [MODIFY - add new actions]
│       │
│       └── Service/
│           ├── nicsrsAPI.php         # API client [MODIFY - use shared token]
│           ├── nicsrsFunc.php        # [MODIFY - add helper functions]
│           ├── nicsrsResponse.php    # [KEEP]
│           ├── nicsrsSSLSql.php      # [MODIFY]
│           └── nicsrsTemplate.php    # [KEEP]
│
└── view/
    ├── applycert.tpl                 # [MODIFY - UI enhancement]
    ├── complete.tpl                  # [MODIFY - add action buttons]
    ├── message.tpl                   
    ├── pending.tpl                   # [MODIFY - UI enhancement]
    ├── manage.tpl                    # [NEW - certificate management]
    ├── reissue.tpl                   # [NEW - reissue form]
    ├── replace.tpl                   # [MODIFY]
    ├── error.tpl                     
    └── home/
        ├── css/
        │   ├── style.css             # [KEEP]
        │   └── nicsrs-modern.css     # [NEW - modern styling]
        └── js/
            ├── jquery.min.js         
            └── nicsrs-client.js      # [NEW - enhanced JS]
```

---

## 3. Key Technical Changes

### 3.1 API Token Centralization

**Current Implementation** (`nicsrsAPI.php`):
```php
// API token is passed per product via $params['configoption2']
$collect_data = array(
    'api_token' => $params['configoption2'],
    'certId' => $certId
);
```

**New Implementation** - Priority: Get from Addon → Fallback to Product Config:
```php
class nicsrsAPI {
    /**
     * Get API token with priority:
     * 1. Addon Module settings (tbladdonmodules)
     * 2. Product config (configoption2) - backward compatibility
     */
    public static function getApiToken($params = []) {
        // Priority 1: Try Addon Module
        $addonToken = self::getAddonApiToken();
        if (!empty($addonToken)) {
            return $addonToken;
        }
        
        // Priority 2: Fallback to product config (backward compatible)
        if (!empty($params['configoption2'])) {
            return $params['configoption2'];
        }
        
        return null;
    }
    
    private static function getAddonApiToken() {
        try {
            $result = Capsule::table('tbladdonmodules')
                ->where('module', 'nicsrs_ssl_admin')
                ->where('setting', 'api_token')
                ->first();
            
            return $result ? $result->value : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
```

### 3.2 ConfigOptions Update

**Current** (`nicsrs_ssl.php`):
```php
function nicsrs_ssl_ConfigOptions() {
    return array(
        'cert_type' => array(
            'FriendlyName' => 'Certificate Type',
            'Type' => 'dropdown',
            'Options' => nicsrsFunc::getCertAttributes(null, 'name'),
        ),
        'nicsrs_api_token' => array(
            'FriendlyName' => 'nicsrs API Token',
            'Type' => 'password',
            'size' => '32',
            'Description' => 'Enter Your nicsrs API Token',
        ),
    );
}
```

**New Implementation**:
```php
function nicsrs_ssl_ConfigOptions() {
    // Check if Addon Module has API token configured
    $addonTokenConfigured = nicsrsAPI::getAddonApiToken() ? true : false;
    
    return array(
        'cert_type' => array(
            'FriendlyName' => 'Certificate Type',
            'Type' => 'dropdown',
            'Options' => nicsrsFunc::getCertTypeOptions(), // Enhanced method
            'Description' => 'Select SSL certificate type from NicSRS',
        ),
        'use_addon_api' => array(
            'FriendlyName' => 'API Token Source',
            'Type' => 'yesno',
            'Default' => 'yes',
            'Description' => $addonTokenConfigured 
                ? '✓ Use API token from NicSRS SSL Admin Addon (Recommended)' 
                : '⚠ Addon not configured. Please setup NicSRS SSL Admin first.',
        ),
        'nicsrs_api_token' => array(
            'FriendlyName' => 'API Token (Override)',
            'Type' => 'password',
            'size' => '32',
            'Description' => 'Only needed if not using Addon API token',
        ),
        'auto_activate' => array(
            'FriendlyName' => 'Auto Activate',
            'Type' => 'yesno',
            'Default' => 'no',
            'Description' => 'Automatically create certificate order on service activation',
        ),
    );
}
```

### 3.3 Client Area Custom Buttons

**New Function** (`nicsrs_ssl.php`):
```php
/**
 * Client area custom button array
 * Adds Manage, Reissue actions to client service details
 */
function nicsrs_ssl_ClientAreaCustomButtonArray() {
    return array(
        'Manage Certificate' => 'manageCertificate',
        'Reissue Certificate' => 'reissueCertificate',
        'View Certificate' => 'viewCertificate',
    );
}

/**
 * Allowed custom functions for client area
 */
function nicsrs_ssl_ClientAreaAllowedFunctions() {
    return array(
        'manageCertificate',
        'reissueCertificate', 
        'viewCertificate',
        'downloadCertificate',
        'refreshStatus',
    );
}
```

### 3.4 Admin Custom Buttons

**New Function** (`nicsrs_ssl.php`):
```php
/**
 * Admin area custom button array
 */
function nicsrs_ssl_AdminCustomButtonArray() {
    return array(
        'Refresh Status' => 'refreshStatus',
        'Cancel Certificate' => 'cancelCertificate',
        'Revoke Certificate' => 'revokeCertificate',
        'View in Admin' => 'viewInAdmin',
    );
}

/**
 * View certificate in Admin Addon Module
 */
function nicsrs_ssl_viewInAdmin(array $params) {
    $orderId = nicsrsSSLSql::GetOrderIdByServiceId($params['serviceid']);
    if ($orderId) {
        header('Location: addonmodules.php?module=nicsrs_ssl_admin&action=order&id=' . $orderId);
        exit;
    }
    return 'Order not found';
}
```

---

## 4. New/Modified Files Detail

### 4.1 `nicsrs_ssl.php` - Main Module File

**New Functions to Add:**

```php
/**
 * Module version information
 */
function nicsrs_ssl_MetaData() {
    return array(
        'DisplayName' => 'NicSRS SSL',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
        'DefaultNonSSLPort' => '80',
        'DefaultSSLPort' => '443',
        'ServiceSingleSignOnLabel' => 'Manage SSL Certificate',
        'AdminSingleSignOnLabel' => 'View in NicSRS Admin',
        'DocURL' => 'https://docs.hvn.vn/whmcs/nicsrs-ssl/',
    );
}

/**
 * Suspend account - Cancel pending certificate
 */
function nicsrs_ssl_SuspendAccount(array $params) {
    try {
        $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        if (!$order || empty($order->remoteid)) {
            return 'success'; // No certificate to suspend
        }
        
        // Log suspension
        logModuleCall('nicsrs_ssl', 'SuspendAccount', $params, 'Service suspended');
        return 'success';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Unsuspend account
 */
function nicsrs_ssl_UnsuspendAccount(array $params) {
    return 'success';
}

/**
 * Terminate account - Optionally revoke certificate
 */
function nicsrs_ssl_TerminateAccount(array $params) {
    try {
        $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        if (!$order || empty($order->remoteid)) {
            return 'success';
        }
        
        // Update order status
        nicsrsSSLSql::UpdateOrderStatus($order->id, 'terminated');
        
        logModuleCall('nicsrs_ssl', 'TerminateAccount', $params, 'Service terminated');
        return 'success';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Renew account - Trigger certificate renewal
 */
function nicsrs_ssl_Renew(array $params) {
    try {
        $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
        if (!$order || empty($order->remoteid)) {
            return 'No certificate to renew';
        }
        
        $apiToken = nicsrsAPI::getApiToken($params);
        $renewResult = nicsrsAPI::call('renew', [
            'api_token' => $apiToken,
            'certId' => $order->remoteid,
        ]);
        
        if ($renewResult->code == 1) {
            logModuleCall('nicsrs_ssl', 'Renew', $params, 'Certificate renewed');
            return 'success';
        }
        
        return $renewResult->msg ?? 'Renewal failed';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Admin services tab fields
 */
function nicsrs_ssl_AdminServicesTabFields(array $params) {
    $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
    
    $fields = [];
    
    if ($order) {
        $configData = json_decode($order->configdata, true) ?: [];
        
        $fields['Certificate ID'] = $order->remoteid ?: '<em>Not issued</em>';
        $fields['Status'] = ucfirst($order->status);
        $fields['Domain'] = !empty($configData['domainInfo'][0]['domainName']) 
            ? $configData['domainInfo'][0]['domainName'] 
            : 'N/A';
        
        if (!empty($configData['applyReturn']['endDate'])) {
            $fields['Expires'] = $configData['applyReturn']['endDate'];
        }
        
        // Link to Admin Addon
        $fields['Admin Panel'] = '<a href="addonmodules.php?module=nicsrs_ssl_admin&action=order&id=' 
            . $order->id . '" target="_blank" class="btn btn-info btn-sm">'
            . '<i class="fa fa-external-link"></i> View in Admin</a>';
    }
    
    return $fields;
}
```

### 4.2 `ActionController.php` - Enhanced Actions

**New Methods:**

```php
/**
 * Manage certificate - Show management options
 */
public function manageCertificate(array $params) {
    $order = $this->getOrder($params['serviceid']);
    if (!$order || $order->status !== 'complete') {
        return nicsrsResponse::error('Certificate not available for management');
    }
    
    $configData = json_decode($order->configdata, true);
    
    return nicsrsResponse::success([
        'order_id' => $order->id,
        'cert_id' => $order->remoteid,
        'status' => $order->status,
        'domain' => $configData['domainInfo'][0]['domainName'] ?? '',
        'expires' => $configData['applyReturn']['endDate'] ?? '',
        'actions' => ['reissue', 'download', 'view'],
    ]);
}

/**
 * Reissue certificate
 */
public function reissueCertificate(array $params) {
    try {
        $order = $this->getOrder($params['serviceid']);
        if (!$order || $order->status !== 'complete') {
            return nicsrsResponse::error('Certificate cannot be reissued');
        }
        
        $data = $this->checkData('data');
        $apiToken = nicsrsAPI::getApiToken($params);
        
        $reissueData = [
            'api_token' => $apiToken,
            'certId' => $order->remoteid,
            'csr' => $data['csr'],
            'domainInfo' => json_encode($data['domainInfo']),
        ];
        
        $result = nicsrsAPI::call('reissue', $reissueData);
        
        if ($result->code == 1) {
            // Update configdata with reissue info
            $this->updateConfigData($order, 'reissue', $result->data);
            nicsrsSSLSql::UpdateOrderStatus($order->id, 'pending');
            
            return nicsrsResponse::success([
                'message' => 'Certificate reissue initiated',
                'new_cert_id' => $result->data->certId ?? $order->remoteid,
            ]);
        }
        
        return nicsrsResponse::error($result->msg ?? 'Reissue failed');
        
    } catch (\Exception $e) {
        return nicsrsResponse::error($e->getMessage());
    }
}

/**
 * Revoke certificate
 */
public function revokeCertificate(array $params) {
    try {
        $order = $this->getOrder($params['serviceid']);
        if (!$order || $order->status !== 'complete') {
            return nicsrsResponse::error('Certificate cannot be revoked');
        }
        
        $data = $this->checkData('data');
        $apiToken = nicsrsAPI::getApiToken($params);
        
        $result = nicsrsAPI::call('revoke', [
            'api_token' => $apiToken,
            'certId' => $order->remoteid,
            'reason' => $data['reason'] ?? 'User requested revocation',
        ]);
        
        if ($result->code == 1) {
            nicsrsSSLSql::UpdateOrderStatus($order->id, 'revoked');
            return nicsrsResponse::success(['message' => 'Certificate revoked']);
        }
        
        return nicsrsResponse::error($result->msg ?? 'Revocation failed');
        
    } catch (\Exception $e) {
        return nicsrsResponse::error($e->getMessage());
    }
}
```

### 4.3 `PageController.php` - Enhanced Views

**Modified `index` Method:**

```php
public function index(array $params) {
    $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
    
    // Determine which template to show
    if (empty($order) || empty($order->remoteid)) {
        // No order yet - show application form
        return $this->showApplyCert($params);
    }
    
    $status = strtolower($order->status);
    
    switch ($status) {
        case 'complete':
        case 'issued':
            return $this->showComplete($params, $order);
            
        case 'pending':
        case 'processing':
            return $this->showPending($params, $order);
            
        case 'awaiting':
        case 'draft':
            return $this->showApplyCert($params, $order);
            
        case 'cancelled':
        case 'revoked':
        case 'expired':
            return $this->showExpired($params, $order);
            
        default:
            return $this->showApplyCert($params, $order);
    }
}

/**
 * Show completed certificate with management options
 */
private function showComplete(array $params, $order) {
    $configData = json_decode($order->configdata, true) ?: [];
    
    // Get certificate details from API if needed
    $certDetails = $this->getCertificateDetails($params, $order);
    
    return [
        'tabOverviewReplacementTemplate' => 'view/complete.tpl',
        'templateVariables' => [
            'order' => $order,
            'certId' => $order->remoteid,
            'configData' => $configData,
            'certDetails' => $certDetails,
            'domain' => $configData['domainInfo'][0]['domainName'] ?? '',
            'validFrom' => $configData['applyReturn']['beginDate'] ?? '',
            'validTo' => $configData['applyReturn']['endDate'] ?? '',
            'canReissue' => true,
            'canDownload' => !empty($configData['applyReturn']['certificate']),
            'serviceId' => $params['serviceid'],
            '_LANG' => $this->loadLanguage($params),
        ],
    ];
}

/**
 * Show pending certificate with DCV status
 */
private function showPending(array $params, $order) {
    $configData = json_decode($order->configdata, true) ?: [];
    
    // Get latest status from API
    $this->refreshOrderStatus($params, $order);
    
    // Reload order after refresh
    $order = nicsrsSSLSql::GetSSLProduct($params['serviceid']);
    $configData = json_decode($order->configdata, true) ?: [];
    
    return [
        'tabOverviewReplacementTemplate' => 'view/pending.tpl',
        'templateVariables' => [
            'order' => $order,
            'certId' => $order->remoteid,
            'configData' => $configData,
            'domainInfo' => $configData['domainInfo'] ?? [],
            'dcvInfo' => $configData['applyReturn'] ?? [],
            'serviceId' => $params['serviceid'],
            '_LANG' => $this->loadLanguage($params),
        ],
    ];
}
```

---

## 5. New Templates

### 5.1 `view/manage.tpl` - Certificate Management

```smarty
<div class="nicsrs-manage-container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-certificate"></i> {$_LANG.manage_certificate}
            </h3>
        </div>
        <div class="panel-body">
            <!-- Certificate Info -->
            <div class="cert-info">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">{$_LANG.certificate_id}</th>
                        <td><code>{$certId}</code></td>
                    </tr>
                    <tr>
                        <th>{$_LANG.domain}</th>
                        <td><strong>{$domain}</strong></td>
                    </tr>
                    <tr>
                        <th>{$_LANG.status}</th>
                        <td>
                            <span class="label label-success">
                                <i class="fa fa-check-circle"></i> {$_LANG.status_complete}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{$_LANG.valid_period}</th>
                        <td>{$validFrom} - {$validTo}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="cert-actions">
                <h4>{$_LANG.available_actions}</h4>
                <div class="btn-group-vertical btn-block">
                    {if $canDownload}
                    <button type="button" class="btn btn-success btn-lg" 
                            onclick="nicsrsDownloadCert({$serviceId})">
                        <i class="fa fa-download"></i> {$_LANG.download_certificate}
                    </button>
                    {/if}
                    
                    {if $canReissue}
                    <button type="button" class="btn btn-warning btn-lg"
                            onclick="nicsrsShowReissue({$serviceId})">
                        <i class="fa fa-refresh"></i> {$_LANG.reissue_certificate}
                    </button>
                    {/if}
                    
                    <button type="button" class="btn btn-info btn-lg"
                            onclick="nicsrsRefreshStatus({$serviceId})">
                        <i class="fa fa-sync"></i> {$_LANG.refresh_status}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 5.2 Enhanced `view/complete.tpl`

```smarty
{* Modern complete certificate view *}
<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/nicsrs-modern.css">

<div class="nicsrs-certificate-complete">
    <!-- Success Banner -->
    <div class="alert alert-success">
        <i class="fa fa-check-circle fa-2x pull-left"></i>
        <div>
            <strong>{$_LANG.certificate_issued}</strong><br>
            {$_LANG.certificate_ready_message}
        </div>
    </div>
    
    <!-- Certificate Details Card -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-shield"></i> {$_LANG.certificate_details}
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="dl-horizontal">
                        <dt>{$_LANG.domain}:</dt>
                        <dd><strong>{$domain}</strong></dd>
                        
                        <dt>{$_LANG.certificate_id}:</dt>
                        <dd><code>{$certId}</code></dd>
                        
                        <dt>{$_LANG.issued_date}:</dt>
                        <dd>{$validFrom}</dd>
                        
                        <dt>{$_LANG.expires_date}:</dt>
                        <dd>{$validTo}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="dl-horizontal">
                        <dt>{$_LANG.validation_type}:</dt>
                        <dd><span class="label label-info">{$validationType|upper}</span></dd>
                        
                        <dt>{$_LANG.vendor}:</dt>
                        <dd>{$vendor}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-bolt"></i> {$_LANG.quick_actions}
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <button type="button" class="btn btn-success btn-block btn-lg"
                            onclick="downloadCertificate({$serviceId})">
                        <i class="fa fa-download"></i><br>
                        {$_LANG.download_cert}
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-warning btn-block btn-lg"
                            onclick="showReissueModal()">
                        <i class="fa fa-refresh"></i><br>
                        {$_LANG.reissue}
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-info btn-block btn-lg"
                            onclick="refreshStatus({$serviceId})">
                        <i class="fa fa-sync"></i><br>
                        {$_LANG.refresh}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{* Include Reissue Modal *}
{include file="$template_path/modals/reissue.tpl"}

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/nicsrs-client.js"></script>
```

---

## 6. CSS Modernization

### 6.1 `view/home/css/nicsrs-modern.css`

```css
/* NicSRS SSL Modern Styling - Matching Addon Module */

:root {
    --nicsrs-primary: #1890ff;
    --nicsrs-success: #52c41a;
    --nicsrs-warning: #faad14;
    --nicsrs-danger: #ff4d4f;
    --nicsrs-info: #13c2c2;
    --nicsrs-border: #d9d9d9;
    --nicsrs-bg: #f5f5f5;
}

.nicsrs-certificate-complete,
.nicsrs-manage-container,
.nicsrs-pending-container {
    max-width: 900px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Status Labels */
.nicsrs-status {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.nicsrs-status-complete { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
.nicsrs-status-pending { background: #fffbe6; color: #d48806; border: 1px solid #ffe58f; }
.nicsrs-status-cancelled { background: #fff2f0; color: #ff4d4f; border: 1px solid #ffccc7; }

/* Action Buttons */
.nicsrs-btn {
    border-radius: 4px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s;
}

.nicsrs-btn-primary { background: var(--nicsrs-primary); border-color: var(--nicsrs-primary); }
.nicsrs-btn-success { background: var(--nicsrs-success); border-color: var(--nicsrs-success); }
.nicsrs-btn-warning { background: var(--nicsrs-warning); border-color: var(--nicsrs-warning); }

/* DCV Status Table */
.dcv-status-table .verified { color: var(--nicsrs-success); }
.dcv-status-table .pending { color: var(--nicsrs-warning); }
.dcv-status-table .failed { color: var(--nicsrs-danger); }

/* Certificate Info Cards */
.cert-info-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    padding: 20px;
    margin-bottom: 20px;
}

/* Download Format Selector */
.download-format-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
}

.download-format-item {
    padding: 15px;
    border: 2px solid var(--nicsrs-border);
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}

.download-format-item:hover,
.download-format-item.selected {
    border-color: var(--nicsrs-primary);
    background: #e6f7ff;
}
```

---

## 7. JavaScript Enhancements

### 7.1 `view/home/js/nicsrs-client.js`

```javascript
/**
 * NicSRS SSL Client Area JavaScript
 * Version 2.0
 */
(function() {
    'use strict';
    
    window.NicsrsSSL = {
        serviceId: null,
        baseUrl: '',
        
        init: function(options) {
            this.serviceId = options.serviceId;
            this.baseUrl = options.baseUrl;
            this.bindEvents();
        },
        
        /**
         * Download certificate in specified format
         */
        downloadCertificate: function(format) {
            var self = this;
            
            $.ajax({
                url: self.baseUrl,
                type: 'POST',
                data: {
                    step: 'downCert',
                    format: format || 'pem',
                    serviceid: self.serviceId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        // Trigger download
                        window.location.href = response.data.downloadUrl;
                    } else {
                        self.showError(response.msg || 'Download failed');
                    }
                },
                error: function() {
                    self.showError('Network error');
                }
            });
        },
        
        /**
         * Refresh certificate status
         */
        refreshStatus: function() {
            var self = this;
            var btn = $('#btnRefresh');
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Refreshing...');
            
            $.ajax({
                url: self.baseUrl,
                type: 'POST',
                data: {
                    step: 'refreshStatus',
                    serviceid: self.serviceId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        self.showSuccess('Status refreshed');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        self.showError(response.msg);
                        btn.prop('disabled', false).html('<i class="fa fa-sync"></i> Refresh');
                    }
                },
                error: function() {
                    self.showError('Network error');
                    btn.prop('disabled', false).html('<i class="fa fa-sync"></i> Refresh');
                }
            });
        },
        
        /**
         * Submit reissue request
         */
        submitReissue: function(formData) {
            var self = this;
            
            $.ajax({
                url: self.baseUrl,
                type: 'POST',
                data: $.extend({
                    step: 'reissueCertificate',
                    serviceid: self.serviceId
                }, formData),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        self.showSuccess('Reissue request submitted');
                        $('#reissueModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        self.showError(response.msg);
                    }
                },
                error: function() {
                    self.showError('Network error');
                }
            });
        },
        
        /**
         * Update DCV method
         */
        updateDcvMethod: function(domain, method, email) {
            var self = this;
            
            $.ajax({
                url: self.baseUrl,
                type: 'POST',
                data: {
                    step: 'batchUpdateDCV',
                    serviceid: self.serviceId,
                    data: JSON.stringify({
                        domainInfo: [{
                            domainName: domain,
                            dcvMethod: method,
                            dcvEmail: email || ''
                        }]
                    })
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        self.showSuccess('DCV method updated');
                        location.reload();
                    } else {
                        self.showError(response.msg);
                    }
                }
            });
        },
        
        // UI Helpers
        showSuccess: function(msg) {
            this.showAlert(msg, 'success');
        },
        
        showError: function(msg) {
            this.showAlert(msg, 'danger');
        },
        
        showAlert: function(msg, type) {
            var alert = $('<div class="alert alert-' + type + ' alert-dismissible">' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                msg + '</div>');
            $('.nicsrs-alert-container').html(alert);
            
            setTimeout(function() {
                alert.fadeOut();
            }, 5000);
        },
        
        bindEvents: function() {
            var self = this;
            
            // Download button
            $(document).on('click', '.btn-download-cert', function() {
                var format = $(this).data('format') || 'pem';
                self.downloadCertificate(format);
            });
            
            // Refresh button
            $(document).on('click', '#btnRefresh', function() {
                self.refreshStatus();
            });
            
            // Reissue form
            $(document).on('submit', '#reissueForm', function(e) {
                e.preventDefault();
                var formData = $(this).serializeArray();
                self.submitReissue(formData);
            });
        }
    };
})();
```

---

## 8. Language File Updates

### 8.1 `lang/english.php` (Additions)

```php
<?php
// New strings for v2.0

// Certificate Management
$_LANG['manage_certificate'] = 'Manage Certificate';
$_LANG['certificate_management'] = 'Certificate Management';
$_LANG['quick_actions'] = 'Quick Actions';
$_LANG['available_actions'] = 'Available Actions';

// Actions
$_LANG['reissue_certificate'] = 'Reissue Certificate';
$_LANG['view_certificate'] = 'View Certificate';
$_LANG['download_certificate'] = 'Download Certificate';
$_LANG['refresh_status'] = 'Refresh Status';
$_LANG['revoke_certificate'] = 'Revoke Certificate';

// Status Messages
$_LANG['certificate_issued'] = 'Certificate Issued Successfully';
$_LANG['certificate_ready_message'] = 'Your SSL certificate has been issued and is ready to use.';
$_LANG['reissue_initiated'] = 'Reissue request submitted successfully';
$_LANG['status_refreshed'] = 'Status has been refreshed';

// Reissue Form
$_LANG['reissue_reason'] = 'Reason for Reissue';
$_LANG['reissue_new_csr'] = 'New CSR (Certificate Signing Request)';
$_LANG['reissue_keep_domains'] = 'Keep existing domains';
$_LANG['reissue_warning'] = 'Warning: Reissuing will invalidate the current certificate.';

// Download Options
$_LANG['download_format'] = 'Download Format';
$_LANG['format_pem'] = 'PEM (Apache/Nginx)';
$_LANG['format_pfx'] = 'PFX/PKCS12 (IIS/Windows)';
$_LANG['format_jks'] = 'JKS (Java/Tomcat)';
$_LANG['format_der'] = 'DER (Binary)';

// Validation
$_LANG['dcv_pending'] = 'Domain Validation Pending';
$_LANG['dcv_verified'] = 'Verified';
$_LANG['dcv_instructions'] = 'Please complete domain validation using one of the methods below.';

// Errors
$_LANG['error_no_certificate'] = 'No certificate found for this service';
$_LANG['error_reissue_failed'] = 'Failed to reissue certificate';
$_LANG['error_download_failed'] = 'Failed to download certificate';
```

### 8.2 `lang/vietnamese.php` (Additions)

```php
<?php
// Chuỗi mới cho v2.0

// Quản lý chứng chỉ
$_LANG['manage_certificate'] = 'Quản lý chứng chỉ';
$_LANG['certificate_management'] = 'Quản lý chứng chỉ SSL';
$_LANG['quick_actions'] = 'Thao tác nhanh';
$_LANG['available_actions'] = 'Các thao tác có sẵn';

// Hành động
$_LANG['reissue_certificate'] = 'Cấp lại chứng chỉ';
$_LANG['view_certificate'] = 'Xem chứng chỉ';
$_LANG['download_certificate'] = 'Tải chứng chỉ';
$_LANG['refresh_status'] = 'Làm mới trạng thái';
$_LANG['revoke_certificate'] = 'Thu hồi chứng chỉ';

// Thông báo trạng thái
$_LANG['certificate_issued'] = 'Chứng chỉ đã được cấp thành công';
$_LANG['certificate_ready_message'] = 'Chứng chỉ SSL của bạn đã được cấp và sẵn sàng sử dụng.';
$_LANG['reissue_initiated'] = 'Yêu cầu cấp lại đã được gửi thành công';
$_LANG['status_refreshed'] = 'Trạng thái đã được làm mới';

// Form cấp lại
$_LANG['reissue_reason'] = 'Lý do cấp lại';
$_LANG['reissue_new_csr'] = 'CSR mới';
$_LANG['reissue_keep_domains'] = 'Giữ nguyên các tên miền hiện tại';
$_LANG['reissue_warning'] = 'Cảnh báo: Cấp lại sẽ vô hiệu hóa chứng chỉ hiện tại.';

// Tùy chọn tải xuống
$_LANG['download_format'] = 'Định dạng tải xuống';
$_LANG['format_pem'] = 'PEM (Apache/Nginx)';
$_LANG['format_pfx'] = 'PFX/PKCS12 (IIS/Windows)';
$_LANG['format_jks'] = 'JKS (Java/Tomcat)';
$_LANG['format_der'] = 'DER (Binary)';

// Xác thực
$_LANG['dcv_pending'] = 'Đang chờ xác thực tên miền';
$_LANG['dcv_verified'] = 'Đã xác thực';
$_LANG['dcv_instructions'] = 'Vui lòng hoàn thành xác thực tên miền bằng một trong các phương thức bên dưới.';

// Lỗi
$_LANG['error_no_certificate'] = 'Không tìm thấy chứng chỉ cho dịch vụ này';
$_LANG['error_reissue_failed'] = 'Không thể cấp lại chứng chỉ';
$_LANG['error_download_failed'] = 'Không thể tải chứng chỉ';
```

---

## 9. Hooks Implementation

### 9.1 `hooks.php` (New File)

```php
<?php
/**
 * NicSRS SSL Server Module Hooks
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

/**
 * Hook: Service renewal - Trigger certificate renewal check
 */
add_hook('ServiceRenewal', 1, function($vars) {
    $serviceId = $vars['serviceid'];
    
    try {
        // Check if this is an SSL product
        $service = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblhosting.id', $serviceId)
            ->first();
        
        if ($service && $service->servertype === 'nicsrs_ssl') {
            // Log the renewal
            logActivity("NicSRS SSL: Service #{$serviceId} renewed, certificate renewal may be needed");
        }
    } catch (\Exception $e) {
        logActivity("NicSRS SSL Hook Error: " . $e->getMessage());
    }
});

/**
 * Hook: Admin area - Add quick links
 */
add_hook('AdminServiceManagementLinks', 1, function($vars) {
    $serviceId = $vars['serviceid'];
    
    $order = Capsule::table('nicsrs_sslorders')
        ->where('serviceid', $serviceId)
        ->first();
    
    if ($order) {
        return [
            '<a href="addonmodules.php?module=nicsrs_ssl_admin&action=order&id=' . $order->id . '" target="_blank">'
            . '<i class="fa fa-shield"></i> View SSL Order</a>',
        ];
    }
    
    return [];
});

/**
 * Hook: Pre-service delete - Clean up SSL order
 */
add_hook('PreServiceDelete', 1, function($vars) {
    $serviceId = $vars['serviceid'];
    
    try {
        // Update order status instead of deleting
        Capsule::table('nicsrs_sslorders')
            ->where('serviceid', $serviceId)
            ->update(['status' => 'service_deleted']);
        
    } catch (\Exception $e) {
        logActivity("NicSRS SSL: Error updating order on service delete - " . $e->getMessage());
    }
});
```

---

## 10. Integration Points with Addon Module

### 10.1 Shared API Configuration

```php
// In nicsrsAPI.php - Add method to use Addon's API service
class nicsrsAPI {
    
    /**
     * Get API service from Addon Module if available
     */
    public static function getAddonApiService() {
        $addonServicePath = dirname(dirname(dirname(__FILE__))) 
            . '/addons/nicsrs_ssl_admin/lib/Service/NicsrsApiService.php';
        
        if (file_exists($addonServicePath)) {
            require_once $addonServicePath;
            
            $token = self::getAddonApiToken();
            if ($token) {
                return new \NicsrsAdmin\Service\NicsrsApiService($token);
            }
        }
        
        return null;
    }
}
```

### 10.2 Product List from Addon

```php
// In nicsrsFunc.php - Get certificate types from Addon's product cache
public static function getCertTypeOptions() {
    try {
        // Try to get from Addon's product cache
        $products = Capsule::table('mod_nicsrs_products')
            ->where('is_active', 1)
            ->orderBy('vendor')
            ->orderBy('product_name')
            ->get();
        
        if ($products->count() > 0) {
            $options = [];
            foreach ($products as $product) {
                $options[$product->product_code] = $product->vendor . ' - ' . $product->product_name;
            }
            return implode(',', array_keys($options));
        }
    } catch (\Exception $e) {
        // Fallback to hardcoded list
    }
    
    // Fallback to original method
    return self::getCertAttributes(null, 'name');
}
```

---

## 11. Implementation Timeline

### Phase 1: Core Upgrades (Week 1)

| Task | Description | Est. Hours | Status |
|------|-------------|------------|--------|
| 1.1 | API token centralization | 4h | [ ] |
| 1.2 | ConfigOptions enhancement | 3h | [ ] |
| 1.3 | Add Client Area buttons | 4h | [ ] |
| 1.4 | Add Admin buttons | 3h | [ ] |
| 1.5 | Update ActionController | 6h | [ ] |
| 1.6 | Update PageController | 5h | [ ] |
| **Total** | | **25h** | |

### Phase 2: UI Modernization (Week 2)

| Task | Description | Est. Hours | Status |
|------|-------------|------------|--------|
| 2.1 | Create modern CSS | 4h | [ ] |
| 2.2 | Update complete.tpl | 4h | [ ] |
| 2.3 | Create manage.tpl | 4h | [ ] |
| 2.4 | Update pending.tpl | 3h | [ ] |
| 2.5 | Create reissue.tpl | 3h | [ ] |
| 2.6 | Create nicsrs-client.js | 5h | [ ] |
| 2.7 | Update language files | 2h | [ ] |
| **Total** | | **25h** | |

### Phase 3: Testing & Integration (Week 3)

| Task | Description | Est. Hours | Status |
|------|-------------|------------|--------|
| 3.1 | Unit testing all new functions | 6h | [ ] |
| 3.2 | Integration with Addon | 4h | [ ] |
| 3.3 | Backward compatibility testing | 4h | [ ] |
| 3.4 | Documentation update | 3h | [ ] |
| 3.5 | Bug fixes | 5h | [ ] |
| 3.6 | Final review | 2h | [ ] |
| **Total** | | **24h** | |

---

## 12. Backward Compatibility Checklist

- [ ] Existing orders continue to work
- [ ] Products without Addon still functional (fallback API token)
- [ ] No database schema changes required
- [ ] Original template variables preserved
- [ ] Language keys backward compatible
- [ ] API calls unchanged for existing flows

---

## 13. Testing Checklist

### Functional Tests
- [ ] New certificate application
- [ ] Certificate status refresh
- [ ] DCV method update
- [ ] Certificate download (all formats)
- [ ] Certificate reissue
- [ ] Admin panel integration
- [ ] API token from Addon works
- [ ] Fallback to product API token

### UI Tests
- [ ] All templates render correctly
- [ ] JavaScript functions work
- [ ] Mobile responsive
- [ ] Cross-browser (Chrome, Firefox, Safari)

### Integration Tests
- [ ] Addon Module communication
- [ ] WHMCS hooks fire correctly
- [ ] Activity logging works
- [ ] Error handling complete

---

## 14. Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Breaking existing customers | High | Keep all original functions, add new ones |
| API incompatibility | Medium | Test thoroughly with NicSRS API |
| Template conflicts | Low | Use unique CSS class prefixes |
| Performance impact | Low | Optimize API calls, use caching |

---

**Document Version**: 1.0  
**Created**: January 2026  
**Author**: HVN GROUP  
**Next Review**: After Phase 1 completion