# Auto-Sync Feature Implementation Plan

## NicSRS SSL Admin Module - v1.2.1

**Document Version:** 1.0  
**Created:** 2025-01-21  
**Author:** HVN GROUP  
**Status:** Planning

---

## 1. Executive Summary

### Current State
The Auto-Sync Settings UI exists in the Settings page, but the backend functionality is **NOT implemented**. The module currently only supports manual refresh of certificate status.

### Goal
Implement automatic synchronization of:
1. **Certificate Status** - Sync pending/processing certificates with NicSRS API
2. **Product Catalog** - Sync product list and pricing from NicSRS API

### Scope
- Create `SyncService` class for sync logic
- Create `hooks.php` for WHMCS cron integration
- Add database tracking for last sync timestamps
- Implement email notifications for status changes

---

## 2. Technical Architecture

### 2.1 System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    WHMCS Daily Cron Job                          │
│              (runs every 5-15 minutes via cron.php)              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                 hooks.php - DailyCronJob Hook                    │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 1. Load settings from mod_nicsrs_settings               │   │
│  │ 2. Check if auto_sync_status = enabled                  │   │
│  │ 3. Check if interval elapsed since last_sync            │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              ▼                               ▼
┌─────────────────────────┐     ┌─────────────────────────┐
│   Status Sync Service   │     │   Product Sync Service  │
│   ──────────────────    │     │   ────────────────────  │
│   • Query pending certs │     │   • Call /productList   │
│   • Call /collect API   │     │   • Update prices       │
│   • Update DB records   │     │   • Update mod_nicsrs_  │
│   • Send notifications  │     │     products table      │
│   • Log activity        │     │   • Log activity        │
└─────────────────────────┘     └─────────────────────────┘
```

### 2.2 File Structure

```
modules/addons/nicsrs_ssl_admin/
├── hooks.php                          # NEW - WHMCS hooks
├── lib/
│   └── Service/
│       ├── SyncService.php            # NEW - Main sync logic
│       └── NotificationService.php    # NEW - Email notifications
└── nicsrs_ssl_admin.php               # UPDATE - Register hooks
```

---

## 3. Database Schema Updates

### 3.1 New Settings Keys

Add to `mod_nicsrs_settings` table:

| setting_key | setting_value | setting_type | Description |
|-------------|---------------|--------------|-------------|
| `last_status_sync` | datetime | datetime | Last status sync timestamp |
| `last_product_sync` | datetime | datetime | Last product sync timestamp |
| `sync_batch_size` | 50 | integer | Certificates per batch |
| `sync_error_count` | 0 | integer | Consecutive error count |

### 3.2 Activity Log Enhancement

Ensure `mod_nicsrs_activity_log` captures:
- `action`: 'auto_sync_status', 'auto_sync_products'
- `entity_type`: 'cron'
- `details`: JSON with sync results

---

## 4. Implementation Details

### 4.1 hooks.php

```php
<?php
/**
 * NicSRS SSL Admin - WHMCS Hooks
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('Access Denied');
}

/**
 * Daily Cron Job Hook
 * Triggers automatic sync based on configured intervals
 */
add_hook('DailyCronJob', 1, function ($vars) {
    try {
        // Check if module is active
        $moduleActive = Capsule::table('tbladdonmodules')
            ->where('module', 'nicsrs_ssl_admin')
            ->where('setting', 'status')
            ->value('value');
        
        if ($moduleActive !== 'Active') {
            return;
        }

        // Load SyncService
        require_once __DIR__ . '/lib/Service/SyncService.php';
        
        $syncService = new \NicsrsAdmin\Service\SyncService();
        $syncService->runScheduledSync();
        
    } catch (\Exception $e) {
        logModuleCall(
            'nicsrs_ssl_admin',
            'DailyCronJob',
            [],
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
});

/**
 * Hourly Cron Job Hook (Alternative - more frequent)
 * Use this for more granular control
 */
add_hook('AfterCronJob', 1, function ($vars) {
    // Optional: Run sync checks more frequently
    // Uncomment if needed for faster sync intervals
    /*
    try {
        require_once __DIR__ . '/lib/Service/SyncService.php';
        $syncService = new \NicsrsAdmin\Service\SyncService();
        $syncService->runScheduledSync();
    } catch (\Exception $e) {
        // Log error silently
    }
    */
});
```

### 4.2 SyncService.php

```php
<?php
/**
 * Sync Service
 * Handles automatic synchronization of certificates and products
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Service;

use WHMCS\Database\Capsule;

class SyncService
{
    /** @var array Module settings */
    private $settings;
    
    /** @var NicsrsApiService API service */
    private $apiService;
    
    /** @var ActivityLogger Logger */
    private $logger;
    
    /** @var int Batch size for processing */
    private $batchSize = 50;
    
    /** @var array Statuses to sync */
    private const SYNCABLE_STATUSES = [
        'pending',
        'processing',
        'awaiting_issuance',
        'awaiting',
        'draft',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadSettings();
        $this->initializeServices();
    }

    /**
     * Load module settings
     */
    private function loadSettings(): void
    {
        $rows = Capsule::table('mod_nicsrs_settings')->get();
        $this->settings = [];
        
        foreach ($rows as $row) {
            $this->settings[$row->setting_key] = $row->setting_value;
        }
        
        $this->batchSize = (int) ($this->settings['sync_batch_size'] ?? 50);
    }

    /**
     * Initialize required services
     */
    private function initializeServices(): void
    {
        require_once __DIR__ . '/NicsrsApiService.php';
        require_once __DIR__ . '/ActivityLogger.php';
        require_once __DIR__ . '/../Config/ApiConfig.php';
        
        $apiToken = \NicsrsAdmin\Config\ApiConfig::getApiToken();
        
        if (!empty($apiToken)) {
            $this->apiService = new NicsrsApiService($apiToken);
        }
        
        $this->logger = new ActivityLogger();
    }

    /**
     * Run scheduled sync based on settings
     * 
     * @return array Results summary
     */
    public function runScheduledSync(): array
    {
        $results = [
            'status_sync' => null,
            'product_sync' => null,
        ];

        // Check if auto-sync is enabled
        if (empty($this->settings['auto_sync_status'])) {
            return $results;
        }

        // Check API availability
        if (!$this->apiService) {
            $this->logError('API token not configured');
            return $results;
        }

        // Run Status Sync
        if ($this->shouldRunStatusSync()) {
            $results['status_sync'] = $this->syncCertificateStatuses();
        }

        // Run Product Sync
        if ($this->shouldRunProductSync()) {
            $results['product_sync'] = $this->syncProducts();
        }

        return $results;
    }

    /**
     * Check if status sync should run
     * 
     * @return bool
     */
    private function shouldRunStatusSync(): bool
    {
        $intervalHours = (int) ($this->settings['sync_interval_hours'] ?? 6);
        $lastSync = $this->settings['last_status_sync'] ?? null;
        
        if (empty($lastSync)) {
            return true;
        }
        
        $lastSyncTime = strtotime($lastSync);
        $nextSyncTime = $lastSyncTime + ($intervalHours * 3600);
        
        return time() >= $nextSyncTime;
    }

    /**
     * Check if product sync should run
     * 
     * @return bool
     */
    private function shouldRunProductSync(): bool
    {
        $intervalHours = (int) ($this->settings['product_sync_hours'] ?? 24);
        $lastSync = $this->settings['last_product_sync'] ?? null;
        
        if (empty($lastSync)) {
            return true;
        }
        
        $lastSyncTime = strtotime($lastSync);
        $nextSyncTime = $lastSyncTime + ($intervalHours * 3600);
        
        return time() >= $nextSyncTime;
    }

    /**
     * Sync certificate statuses
     * 
     * @return array Sync results
     */
    public function syncCertificateStatuses(): array
    {
        $startTime = microtime(true);
        $results = [
            'total' => 0,
            'updated' => 0,
            'completed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        try {
            // Get certificates needing sync
            $certificates = $this->getCertificatesToSync();
            $results['total'] = count($certificates);

            foreach ($certificates as $cert) {
                try {
                    $syncResult = $this->syncSingleCertificate($cert);
                    
                    if ($syncResult['success']) {
                        $results['updated']++;
                        
                        if ($syncResult['status_changed'] && 
                            $syncResult['new_status'] === 'complete') {
                            $results['completed']++;
                            
                            // Send notification if enabled
                            $this->sendCompletionNotification($cert, $syncResult);
                        }
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Order #{$cert->id}: " . $e->getMessage();
                }
            }

            // Update last sync timestamp
            $this->updateSetting('last_status_sync', date('Y-m-d H:i:s'));
            $this->updateSetting('sync_error_count', 0);

            // Log activity
            $duration = round(microtime(true) - $startTime, 2);
            $this->logger->log('auto_sync_status', 'cron', null, null, json_encode([
                'results' => $results,
                'duration' => $duration,
            ]));

        } catch (\Exception $e) {
            $this->incrementErrorCount();
            $this->logError('Status sync failed: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Get certificates that need syncing
     * 
     * @return array Certificate records
     */
    private function getCertificatesToSync(): array
    {
        return Capsule::table('nicsrs_sslorders')
            ->whereIn('status', self::SYNCABLE_STATUSES)
            ->whereNotNull('remoteid')
            ->where('remoteid', '!=', '')
            ->orderBy('id', 'asc')
            ->limit($this->batchSize)
            ->get()
            ->toArray();
    }

    /**
     * Sync a single certificate
     * 
     * @param object $cert Certificate record
     * @return array Sync result
     */
    private function syncSingleCertificate(object $cert): array
    {
        $certId = $cert->remoteid;
        $oldStatus = $cert->status;
        
        // Call NicSRS API
        $apiResult = $this->apiService->collect($certId);
        
        if (!isset($apiResult['code']) || $apiResult['code'] != 1) {
            throw new \Exception($apiResult['msg'] ?? 'API error');
        }
        
        // Determine new status
        $newStatus = strtolower($apiResult['status'] ?? $apiResult['certStatus'] ?? $oldStatus);
        
        // Parse config data
        $configData = json_decode($cert->configdata, true) ?: [];
        
        // Update applyReturn with API data
        if (isset($apiResult['data'])) {
            $data = $apiResult['data'];
            $configData['applyReturn'] = array_merge(
                $configData['applyReturn'] ?? [],
                [
                    'certId' => $certId,
                    'beginDate' => $data['beginDate'] ?? null,
                    'endDate' => $data['endDate'] ?? null,
                    'dueDate' => $data['dueDate'] ?? null,
                    'certificate' => $data['certificate'] ?? null,
                    'caCertificate' => $data['caCertificate'] ?? null,
                    'vendorId' => $data['vendorId'] ?? null,
                    'vendorCertId' => $data['vendorCertId'] ?? null,
                ]
            );
            
            // Update DCV list
            if (!empty($data['dcvList'])) {
                $configData['dcvList'] = [];
                foreach ($data['dcvList'] as $dcv) {
                    $configData['dcvList'][] = [
                        'domainName' => $dcv['domainName'] ?? '',
                        'dcvMethod' => $dcv['dcvMethod'] ?? 'EMAIL',
                        'dcvEmail' => $dcv['dcvEmail'] ?? '',
                        'isVerified' => ($dcv['is_verify'] ?? '') === 'verified',
                        'is_verify' => $dcv['is_verify'] ?? '',
                    ];
                }
            }
        }
        
        $configData['lastAutoSync'] = date('Y-m-d H:i:s');
        
        // Build update data
        $updateData = [
            'status' => $newStatus,
            'configdata' => json_encode($configData),
        ];
        
        // Set completiondate when status changes to complete
        if ($newStatus === 'complete' && $oldStatus !== 'complete') {
            $completionDate = $configData['applyReturn']['beginDate'] ?? date('Y-m-d H:i:s');
            // Handle datetime format (API returns 'Y-m-d H:i:s')
            if (strlen($completionDate) > 10) {
                $completionDate = substr($completionDate, 0, 10);
            }
            $updateData['completiondate'] = $completionDate;
        }
        
        // Update database
        Capsule::table('nicsrs_sslorders')
            ->where('id', $cert->id)
            ->update($updateData);
        
        return [
            'success' => true,
            'status_changed' => $newStatus !== $oldStatus,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ];
    }

    /**
     * Sync products from NicSRS API
     * 
     * @return array Sync results
     */
    public function syncProducts(): array
    {
        $startTime = microtime(true);
        $results = [
            'vendors' => [],
            'total_products' => 0,
            'errors' => [],
        ];

        $vendors = ['sectigo', 'digicert', 'globalsign', 'entrust'];

        foreach ($vendors as $vendor) {
            try {
                $apiResult = $this->apiService->getProductList($vendor);
                
                if (isset($apiResult['code']) && $apiResult['code'] == 1) {
                    $products = $apiResult['data'] ?? [];
                    $count = $this->saveProducts($products, $vendor);
                    
                    $results['vendors'][$vendor] = $count;
                    $results['total_products'] += $count;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "{$vendor}: " . $e->getMessage();
            }
        }

        // Update last sync timestamp
        $this->updateSetting('last_product_sync', date('Y-m-d H:i:s'));

        // Log activity
        $duration = round(microtime(true) - $startTime, 2);
        $this->logger->log('auto_sync_products', 'cron', null, null, json_encode([
            'results' => $results,
            'duration' => $duration,
        ]));

        return $results;
    }

    /**
     * Save products to database
     * 
     * @param array $products Product list from API
     * @param string $vendor Vendor name
     * @return int Number of products saved
     */
    private function saveProducts(array $products, string $vendor): int
    {
        $count = 0;
        
        foreach ($products as $product) {
            $productCode = $product['product_code'] ?? $product['productCode'] ?? null;
            
            if (!$productCode) {
                continue;
            }
            
            $data = [
                'product_code' => $productCode,
                'product_name' => $product['product_name'] ?? $product['productName'] ?? $productCode,
                'vendor' => $vendor,
                'product_type' => $product['product_type'] ?? $product['productType'] ?? 'DV',
                'validation_type' => $product['validation_type'] ?? 'DV',
                'price_1year' => $product['price_1year'] ?? $product['price1Year'] ?? null,
                'price_2year' => $product['price_2year'] ?? $product['price2Year'] ?? null,
                'price_3year' => $product['price_3year'] ?? $product['price3Year'] ?? null,
                'is_wildcard' => $product['is_wildcard'] ?? 0,
                'is_multidomain' => $product['is_multidomain'] ?? 0,
                'max_domains' => $product['max_domains'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Upsert
            $existing = Capsule::table('mod_nicsrs_products')
                ->where('product_code', $productCode)
                ->where('vendor', $vendor)
                ->first();
            
            if ($existing) {
                Capsule::table('mod_nicsrs_products')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                Capsule::table('mod_nicsrs_products')->insert($data);
            }
            
            $count++;
        }
        
        return $count;
    }

    /**
     * Send completion notification email
     * 
     * @param object $cert Certificate record
     * @param array $syncResult Sync result data
     */
    private function sendCompletionNotification(object $cert, array $syncResult): void
    {
        if (empty($this->settings['email_on_issuance'])) {
            return;
        }

        try {
            require_once __DIR__ . '/NotificationService.php';
            
            $notifier = new NotificationService();
            $notifier->sendCertificateIssuedNotification($cert);
            
        } catch (\Exception $e) {
            $this->logError('Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Update a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     */
    private function updateSetting(string $key, $value): void
    {
        Capsule::table('mod_nicsrs_settings')
            ->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => $value, 'setting_type' => 'string']
            );
        
        $this->settings[$key] = $value;
    }

    /**
     * Increment error count
     */
    private function incrementErrorCount(): void
    {
        $count = (int) ($this->settings['sync_error_count'] ?? 0);
        $this->updateSetting('sync_error_count', $count + 1);
    }

    /**
     * Log error message
     * 
     * @param string $message Error message
     */
    private function logError(string $message): void
    {
        logModuleCall(
            'nicsrs_ssl_admin',
            'SyncService',
            [],
            $message,
            'ERROR'
        );
    }
}
```

### 4.3 NotificationService.php

```php
<?php
/**
 * Notification Service
 * Handles email notifications for certificate events
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Service;

use WHMCS\Database\Capsule;

class NotificationService
{
    /** @var array Module settings */
    private $settings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load module settings
     */
    private function loadSettings(): void
    {
        $rows = Capsule::table('mod_nicsrs_settings')->get();
        $this->settings = [];
        
        foreach ($rows as $row) {
            $this->settings[$row->setting_key] = $row->setting_value;
        }
    }

    /**
     * Send certificate issued notification
     * 
     * @param object $cert Certificate record
     */
    public function sendCertificateIssuedNotification(object $cert): void
    {
        $configData = json_decode($cert->configdata, true) ?: [];
        $domain = $configData['domainInfo'][0]['domainName'] ?? 'Unknown';
        
        // Get admin email
        $adminEmail = $this->settings['admin_email'] ?? '';
        if (empty($adminEmail)) {
            // Fallback to WHMCS admin email
            $adminEmail = Capsule::table('tbladmins')
                ->where('disabled', 0)
                ->orderBy('id', 'asc')
                ->value('email');
        }
        
        if (empty($adminEmail)) {
            return;
        }
        
        // Get client info
        $client = null;
        if ($cert->userid) {
            $client = Capsule::table('tblclients')
                ->where('id', $cert->userid)
                ->first();
        }
        
        $clientName = $client 
            ? trim($client->firstname . ' ' . $client->lastname) 
            : 'Unknown';
        
        // Prepare email
        $subject = "[NicSRS SSL] Certificate Issued - {$domain}";
        
        $body = "
            <h2>SSL Certificate Issued</h2>
            <p>A certificate has been successfully issued:</p>
            <table>
                <tr><td><strong>Order ID:</strong></td><td>#{$cert->id}</td></tr>
                <tr><td><strong>Domain:</strong></td><td>{$domain}</td></tr>
                <tr><td><strong>Client:</strong></td><td>{$clientName}</td></tr>
                <tr><td><strong>Certificate ID:</strong></td><td>{$cert->remoteid}</td></tr>
                <tr><td><strong>Status:</strong></td><td>Complete</td></tr>
            </table>
            <p>
                <a href='" . \App::getSystemURL() . "admin/addonmodules.php?module=nicsrs_ssl_admin&action=order_detail&id={$cert->id}'>
                    View Order Details
                </a>
            </p>
        ";
        
        // Send using WHMCS mail function
        $this->sendMail($adminEmail, $subject, $body);
    }

    /**
     * Send expiry warning notification
     * 
     * @param object $cert Certificate record
     * @param int $daysUntilExpiry Days until certificate expires
     */
    public function sendExpiryWarningNotification(object $cert, int $daysUntilExpiry): void
    {
        if (empty($this->settings['email_on_expiry'])) {
            return;
        }
        
        $configData = json_decode($cert->configdata, true) ?: [];
        $domain = $configData['domainInfo'][0]['domainName'] ?? 'Unknown';
        $expiryDate = $configData['applyReturn']['endDate'] ?? 'Unknown';
        
        $adminEmail = $this->settings['admin_email'] ?? '';
        if (empty($adminEmail)) {
            $adminEmail = Capsule::table('tbladmins')
                ->where('disabled', 0)
                ->orderBy('id', 'asc')
                ->value('email');
        }
        
        if (empty($adminEmail)) {
            return;
        }
        
        $subject = "[NicSRS SSL] Certificate Expiring Soon - {$domain}";
        
        $body = "
            <h2>SSL Certificate Expiry Warning</h2>
            <p>The following certificate will expire in <strong>{$daysUntilExpiry} days</strong>:</p>
            <table>
                <tr><td><strong>Domain:</strong></td><td>{$domain}</td></tr>
                <tr><td><strong>Expiry Date:</strong></td><td>{$expiryDate}</td></tr>
                <tr><td><strong>Order ID:</strong></td><td>#{$cert->id}</td></tr>
                <tr><td><strong>Certificate ID:</strong></td><td>{$cert->remoteid}</td></tr>
            </table>
            <p>Please renew the certificate before it expires.</p>
        ";
        
        $this->sendMail($adminEmail, $subject, $body);
    }

    /**
     * Send email using WHMCS mail function
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     */
    private function sendMail(string $to, string $subject, string $body): void
    {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->getSystemEmail(),
        ];
        
        mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Get WHMCS system email
     * 
     * @return string System email address
     */
    private function getSystemEmail(): string
    {
        return Capsule::table('tblconfiguration')
            ->where('setting', 'SystemEmailsFromEmail')
            ->value('value') ?? 'noreply@example.com';
    }
}
```

---

## 5. Module Registration Update

### 5.1 Update nicsrs_ssl_admin.php

Add hooks registration in the config function:

```php
/**
 * Module configuration
 */
function nicsrs_ssl_admin_config()
{
    return [
        'name' => 'NicSRS SSL Admin',
        'description' => 'Admin module for managing NicSRS SSL certificates',
        'version' => '1.2.1',
        'author' => 'HVN GROUP',
        'language' => 'english',
        'fields' => [],
    ];
}

// Register hooks
if (file_exists(__DIR__ . '/hooks.php')) {
    require_once __DIR__ . '/hooks.php';
}
```

---

## 6. Settings UI Enhancement

### 6.1 Add Last Sync Display

Update `templates/settings.php` to show last sync times:

```php
<!-- Auto-Sync Status Display -->
<div class="alert alert-info">
    <strong>Last Status Sync:</strong> 
    <?php echo $settings['last_status_sync'] ?? 'Never'; ?>
    <br>
    <strong>Last Product Sync:</strong> 
    <?php echo $settings['last_product_sync'] ?? 'Never'; ?>
    <br>
    <strong>Next Scheduled:</strong>
    <?php 
    $interval = (int)($settings['sync_interval_hours'] ?? 6);
    $lastSync = $settings['last_status_sync'] ?? null;
    if ($lastSync) {
        $next = date('Y-m-d H:i:s', strtotime($lastSync) + ($interval * 3600));
        echo $next;
    } else {
        echo 'On next cron run';
    }
    ?>
</div>

<!-- Manual Sync Buttons -->
<div class="form-group">
    <button type="button" class="btn btn-primary" onclick="runManualSync('status')">
        <i class="fa fa-refresh"></i> Sync Status Now
    </button>
    <button type="button" class="btn btn-default" onclick="runManualSync('products')">
        <i class="fa fa-refresh"></i> Sync Products Now
    </button>
</div>
```

---

## 7. Testing Plan

### 7.1 Unit Tests

| Test Case | Expected Result |
|-----------|-----------------|
| `shouldRunStatusSync()` returns true when never synced | Pass |
| `shouldRunStatusSync()` returns false within interval | Pass |
| `shouldRunProductSync()` with 24h interval | Pass |
| `syncSingleCertificate()` updates status | Pass |
| `sendCompletionNotification()` sends email | Pass |

### 7.2 Integration Tests

| Test Case | Steps | Expected |
|-----------|-------|----------|
| Cron triggers sync | Run WHMCS cron | SyncService executes |
| Status updates | Add pending cert, run sync | Status changes to complete |
| Product sync | Run product sync | Products updated in DB |
| Email notification | Complete a cert | Admin receives email |

### 7.3 Manual Testing Checklist

- [ ] Enable auto-sync in settings
- [ ] Set interval to 1 hour
- [ ] Create pending certificate order
- [ ] Wait for cron or trigger manually
- [ ] Verify status updated
- [ ] Check activity log
- [ ] Verify email received (if enabled)
- [ ] Test with disabled setting
- [ ] Test with invalid API key

---

## 8. Implementation Timeline

| Phase | Task | Hours | Status |
|-------|------|-------|--------|
| **1** | Create `hooks.php` | 2h | TODO |
| **2** | Create `SyncService.php` | 6h | TODO |
| **3** | Create `NotificationService.php` | 3h | TODO |
| **4** | Update `nicsrs_ssl_admin.php` | 1h | TODO |
| **5** | Update Settings UI | 2h | TODO |
| **6** | Testing & Debug | 4h | TODO |
| **7** | Documentation | 2h | TODO |
| | **Total** | **20h** | |

---

## 9. Risk Assessment

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| API rate limiting | Medium | Medium | Batch processing, backoff |
| Cron not running | High | Low | Manual sync button |
| Memory issues | Medium | Low | Batch size limit |
| Email delivery | Low | Medium | WHMCS mail queue |

---

## 10. Rollback Plan

If issues occur:
1. Disable auto-sync in settings
2. Remove `hooks.php` include from main file
3. Restore previous version
4. Clear `last_status_sync` and `last_product_sync`

---

## 11. Future Enhancements

### v1.3.0
- Webhook support for real-time updates
- Retry mechanism with exponential backoff
- Sync queue for large datasets
- Dashboard sync status widget

### v1.4.0
- Auto-renewal trigger
- Client notifications
- Slack/Discord webhooks

---

**Document Version:** 1.0  
**Last Updated:** 2025-01-21  
**Author:** HVN GROUP