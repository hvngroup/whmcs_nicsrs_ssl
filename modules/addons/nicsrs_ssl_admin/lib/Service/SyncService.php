<?php
/**
 * Sync Service
 * 
 * Handles automatic synchronization of SSL certificates and products
 * with the NicSRS API. Supports scheduled sync via WHMCS cron and
 * manual sync triggers from admin interface.
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 * @version    1.2.1
 */

namespace NicsrsAdmin\Service;

use WHMCS\Database\Capsule;

class SyncService
{
    /**
     * @var array Module settings cache
     */
    private $settings = [];
    
    /**
     * @var NicsrsApiService|null API service instance
     */
    private $apiService = null;
    
    /**
     * @var ActivityLogger|null Logger instance
     */
    private $logger = null;
    
    /**
     * @var int Batch size for processing certificates
     */
    private $batchSize = 50;
    
    /**
     * @var int Maximum API retries
     */
    private $maxRetries = 3;
    
    /**
     * @var array Certificate statuses that need syncing
     */
    private const SYNCABLE_STATUSES = [
        'pending',
        'processing',
        'awaiting_issuance',
        'awaiting',
        'draft',
        'awaiting configuration',
    ];
    
    /**
     * @var array Vendor list for product sync
     */
    private const VENDORS = [
        'Sectigo', 
        'Positive',
        'DigiCert', 
        'GlobalSign', 
        'GeoTrust', 
        'Thawte',
        'RapidSSL',
        'sslTrus', 
        'Entrust',
        'BaiduTrust',
    ];

    /**
     * Constructor
     * 
     * Initializes the sync service with settings and dependencies.
     */
    public function __construct()
    {
        $this->loadSettings();
        $this->initializeServices();
    }

    /**
     * Load module settings from database
     * 
     * @return void
     */
    private function loadSettings(): void
    {
        try {
            $rows = Capsule::table('mod_nicsrs_settings')->get();
            
            foreach ($rows as $row) {
                $this->settings[$row->setting_key] = $row->setting_value;
            }
            
            // Set batch size from settings
            $this->batchSize = (int) ($this->settings['sync_batch_size'] ?? 50);
            
            // Ensure minimum batch size
            if ($this->batchSize < 10) {
                $this->batchSize = 10;
            }
            
            // Ensure maximum batch size
            if ($this->batchSize > 200) {
                $this->batchSize = 200;
            }
            
        } catch (\Exception $e) {
            $this->logError('Failed to load settings: ' . $e->getMessage());
        }
    }

    /**
     * Initialize required services
     * 
     * @return void
     */
    private function initializeServices(): void
    {
        $basePath = __DIR__ . '/';
        
        // Load API Service
        if (file_exists($basePath . 'NicsrsApiService.php')) {
            require_once $basePath . 'NicsrsApiService.php';
        }
        
        // Load Activity Logger
        if (file_exists($basePath . 'ActivityLogger.php')) {
            require_once $basePath . 'ActivityLogger.php';
            $this->logger = new ActivityLogger();
        }
        
        // Load API Config
        $configPath = __DIR__ . '/../Config/ApiConfig.php';
        if (file_exists($configPath)) {
            require_once $configPath;
        }
        
        // Initialize API service with token
        $apiToken = $this->getApiToken();
        
        if (!empty($apiToken)) {
            $this->apiService = new NicsrsApiService($apiToken);
            $this->apiService->setTimeout(60); // Longer timeout for batch operations
        }
    }

    /**
     * Get API token from shared config or module settings
     * 
     * @return string|null
     */
    private function getApiToken(): ?string
    {
        // Try shared config first
        if (class_exists('\NicsrsAdmin\Config\ApiConfig')) {
            $token = \NicsrsAdmin\Config\ApiConfig::getApiToken();
            if (!empty($token)) {
                return $token;
            }
        }
        
        // Fallback to direct database query
        try {
            // Check server module config
            $serverConfig = Capsule::table('tblservers')
                ->where('type', 'nicsrs_ssl')
                ->where('disabled', 0)
                ->first();
            
            if ($serverConfig && !empty($serverConfig->password)) {
                return decrypt($serverConfig->password);
            }
            
            // Check addon module config  
            $addonConfig = Capsule::table('tbladdonmodules')
                ->where('module', 'nicsrs_ssl_admin')
                ->where('setting', 'api_token')
                ->value('value');
            
            if (!empty($addonConfig)) {
                return $addonConfig;
            }
            
        } catch (\Exception $e) {
            $this->logError('Failed to get API token: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Run scheduled sync based on settings
     * 
     * Main entry point for cron-triggered sync.
     * 
     * @return array Results summary
     */
    public function runScheduledSync(): array
    {
        $results = [
            'status_sync' => null,
            'product_sync' => null,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // Check if auto-sync is enabled
        if (empty($this->settings['auto_sync_status'])) {
            return $results;
        }

        // Check API availability
        if (!$this->apiService) {
            $this->logError('API service not available - token may be missing');
            $this->incrementErrorCount();
            return $results;
        }

        // Run Status Sync if due
        if ($this->shouldRunStatusSync()) {
            $results['status_sync'] = $this->syncCertificateStatuses();
        }

        // Run Product Sync if due
        if ($this->shouldRunProductSync()) {
            $results['product_sync'] = $this->syncProducts();
        }

        return $results;
    }

    /**
     * Check if status sync should run based on interval
     * 
     * @return bool
     */
    private function shouldRunStatusSync(): bool
    {
        $intervalHours = (int) ($this->settings['sync_interval_hours'] ?? 6);
        $lastSync = $this->settings['last_status_sync'] ?? null;
        
        // Always run if never synced
        if (empty($lastSync)) {
            return true;
        }
        
        $lastSyncTime = strtotime($lastSync);
        
        // Handle invalid timestamp
        if (!$lastSyncTime) {
            return true;
        }
        
        $nextSyncTime = $lastSyncTime + ($intervalHours * 3600);
        
        return time() >= $nextSyncTime;
    }

    /**
     * Check if product sync should run based on interval
     * 
     * @return bool
     */
    private function shouldRunProductSync(): bool
    {
        $intervalHours = (int) ($this->settings['product_sync_hours'] ?? 24);
        $lastSync = $this->settings['last_product_sync'] ?? null;
        
        // Always run if never synced
        if (empty($lastSync)) {
            return true;
        }
        
        $lastSyncTime = strtotime($lastSync);
        
        // Handle invalid timestamp
        if (!$lastSyncTime) {
            return true;
        }
        
        $nextSyncTime = $lastSyncTime + ($intervalHours * 3600);
        
        return time() >= $nextSyncTime;
    }

    /**
     * Sync certificate statuses from NicSRS API
     * 
     * Fetches pending certificates and updates their status
     * by calling the /collect API endpoint.
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
            'unchanged' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
            'completed_orders' => [],
        ];

        try {
            // Get certificates needing sync
            $certificates = $this->getCertificatesToSync();
            $results['total'] = count($certificates);
            
            if ($results['total'] === 0) {
                $this->updateSetting('last_status_sync', date('Y-m-d H:i:s'));
                return $results;
            }

            // Process each certificate
            foreach ($certificates as $cert) {
                // Skip if no remote ID
                if (empty($cert->remoteid)) {
                    $results['skipped']++;
                    continue;
                }
                
                try {
                    $syncResult = $this->syncSingleCertificate($cert);
                    
                    if ($syncResult['success']) {
                        if ($syncResult['status_changed']) {
                            $results['updated']++;
                            
                            // Track completed certificates for notifications
                            if ($syncResult['new_status'] === 'complete') {
                                $results['completed']++;
                                $results['completed_orders'][] = [
                                    'id' => $cert->id,
                                    'remoteid' => $cert->remoteid,
                                    'old_status' => $syncResult['old_status'],
                                ];
                                
                                // Send notification
                                $this->sendCompletionNotification($cert, $syncResult);
                            }
                        } else {
                            $results['unchanged']++;
                        }
                    } else {
                        $results['failed']++;
                        if (!empty($syncResult['error'])) {
                            $results['errors'][] = "Order #{$cert->id}: " . $syncResult['error'];
                        }
                    }
                    
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Order #{$cert->id}: " . $e->getMessage();
                }
                
                // Small delay to avoid API rate limiting
                usleep(100000); // 100ms
            }

            // Update last sync timestamp
            $this->updateSetting('last_status_sync', date('Y-m-d H:i:s'));
            
            // Reset error count on successful sync
            $this->updateSetting('sync_error_count', '0');

            // Log activity
            $duration = round(microtime(true) - $startTime, 2);
            
            if ($this->logger) {
                $this->logger->log('auto_sync_status', 'cron', null, null, json_encode([
                    'total' => $results['total'],
                    'updated' => $results['updated'],
                    'completed' => $results['completed'],
                    'failed' => $results['failed'],
                    'duration_seconds' => $duration,
                ]));
            }

        } catch (\Exception $e) {
            $this->incrementErrorCount();
            $this->logError('Status sync failed: ' . $e->getMessage());
            $results['errors'][] = 'Sync process error: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Get certificates that need syncing
     * 
     * @return array Array of certificate records
     */
    private function getCertificatesToSync(): array
    {
        try {
            return Capsule::table('nicsrs_sslorders')
                ->whereIn('status', self::SYNCABLE_STATUSES)
                ->whereNotNull('remoteid')
                ->where('remoteid', '!=', '')
                ->orderBy('id', 'asc')
                ->limit($this->batchSize)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->logError('Failed to get certificates: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync a single certificate with NicSRS API
     * 
     * @param object $cert Certificate record from database
     * @return array Sync result
     */
    private function syncSingleCertificate(object $cert): array
    {
        $result = [
            'success' => false,
            'status_changed' => false,
            'old_status' => $cert->status,
            'new_status' => $cert->status,
            'error' => null,
        ];
        
        $certId = $cert->remoteid;
        
        // Call NicSRS API with retry
        $apiResult = $this->callApiWithRetry('collect', ['certId' => $certId]);
        
        if (!$apiResult) {
            $result['error'] = 'API call failed after retries';
            return $result;
        }
        
        // Check API response code
        if (!isset($apiResult['code'])) {
            $result['error'] = 'Invalid API response format';
            return $result;
        }
        
        // Code 2 means certificate is still being processed
        if ($apiResult['code'] == 2) {
            $result['success'] = true;
            return $result;
        }
        
        if ($apiResult['code'] != 1) {
            $result['error'] = $apiResult['msg'] ?? 'API error code: ' . $apiResult['code'];
            return $result;
        }
        
        // Determine new status
        $newStatus = strtolower($apiResult['status'] ?? $apiResult['certStatus'] ?? $cert->status);
        $result['new_status'] = $newStatus;
        $result['status_changed'] = ($newStatus !== $cert->status);
        
        // Parse and update config data
        $configData = json_decode($cert->configdata, true) ?: [];
        
        // Update applyReturn with API data
        if (isset($apiResult['data']) && is_array($apiResult['data'])) {
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
                    'rsaPrivateKey' => $data['rsaPrivateKey'] ?? null,
                    'jks' => $data['jks'] ?? null,
                    'pkcs12' => $data['pkcs12'] ?? null,
                    'jksPass' => $data['jksPass'] ?? null,
                    'pkcsPass' => $data['pkcsPass'] ?? null,
                    'vendorId' => $data['vendorId'] ?? null,
                    'vendorCertId' => $data['vendorCertId'] ?? null,
                ]
            );
            
            // Update DCV information
            $configData['applyReturn']['DCVfileName'] = $data['DCVfileName'] ?? null;
            $configData['applyReturn']['DCVfileContent'] = $data['DCVfileContent'] ?? null;
            $configData['applyReturn']['DCVfilePath'] = $data['DCVfilePath'] ?? null;
            $configData['applyReturn']['DCVdnsHost'] = $data['DCVdnsHost'] ?? null;
            $configData['applyReturn']['DCVdnsValue'] = $data['DCVdnsValue'] ?? null;
            $configData['applyReturn']['DCVdnsType'] = $data['DCVdnsType'] ?? null;
            
            // Update DCV list
            if (!empty($data['dcvList']) && is_array($data['dcvList'])) {
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
            
            // Update application status
            if (isset($data['application'])) {
                $configData['applicationStatus'] = $data['application'];
            }
            if (isset($data['dcv'])) {
                $configData['dcvStatus'] = $data['dcv'];
            }
            if (isset($data['issued'])) {
                $configData['issuedStatus'] = $data['issued'];
            }
        }
        
        // Add sync metadata
        $configData['lastAutoSync'] = date('Y-m-d H:i:s');
        $configData['syncSource'] = 'cron';
        
        // Build update data
        $updateData = [
            'status' => $newStatus,
            'configdata' => json_encode($configData),
        ];
        
        // Set completiondate when status changes to complete
        if ($newStatus === 'complete' && $cert->status !== 'complete') {
            $beginDate = $configData['applyReturn']['beginDate'] ?? null;
            
            if ($beginDate) {
                // Handle datetime format from API (Y-m-d H:i:s)
                $completionDate = substr($beginDate, 0, 10);
            } else {
                $completionDate = date('Y-m-d');
            }
            
            $updateData['completiondate'] = $completionDate;
        }
        
        // Update database
        try {
            Capsule::table('nicsrs_sslorders')
                ->where('id', $cert->id)
                ->update($updateData);
            
            $result['success'] = true;
            
        } catch (\Exception $e) {
            $result['error'] = 'Database update failed: ' . $e->getMessage();
        }
        
        return $result;
    }

    /**
     * Call API with retry mechanism
     * 
     * @param string $method API method name
     * @param array $params API parameters
     * @return array|null API response or null on failure
     */
    private function callApiWithRetry(string $method, array $params): ?array
    {
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $result = $this->apiService->$method($params['certId'] ?? $params);
                return $result;
                
            } catch (\Exception $e) {
                $lastException = $e;
                
                // Wait before retry (exponential backoff)
                if ($attempt < $this->maxRetries) {
                    sleep(pow(2, $attempt - 1));
                }
            }
        }
        
        if ($lastException) {
            $this->logError("API call failed after {$this->maxRetries} retries: " . $lastException->getMessage());
        }
        
        return null;
    }

    /**
     * Sync products from NicSRS API
     * 
     * Fetches product list and pricing from all vendors
     * and updates the local product catalog.
     * 
     * @return array Sync results
     */
    public function syncProducts(): array
    {
        $startTime = microtime(true);
        
        $results = [
            'vendors' => [],
            'total_products' => 0,
            'updated' => 0,
            'inserted' => 0,
            'errors' => [],
        ];

        foreach (self::VENDORS as $vendor) {
            try {
                $apiResult = $this->apiService->getProductList($vendor);
                
                if (!isset($apiResult['code']) || $apiResult['code'] != 1) {
                    $results['errors'][] = "{$vendor}: " . ($apiResult['msg'] ?? 'Unknown error');
                    continue;
                }
                
                $products = $apiResult['data'] ?? [];
                
                if (empty($products)) {
                    $results['vendors'][$vendor] = 0;
                    continue;
                }
                
                $vendorResults = $this->saveProducts($products, $vendor);
                
                $results['vendors'][$vendor] = $vendorResults['total'];
                $results['total_products'] += $vendorResults['total'];
                $results['updated'] += $vendorResults['updated'];
                $results['inserted'] += $vendorResults['inserted'];
                
            } catch (\Exception $e) {
                $results['errors'][] = "{$vendor}: " . $e->getMessage();
            }
            
            // Small delay between vendor calls
            usleep(500000); // 500ms
        }

        // Update last sync timestamp
        $this->updateSetting('last_product_sync', date('Y-m-d H:i:s'));

        // Log activity
        $duration = round(microtime(true) - $startTime, 2);
        
        if ($this->logger) {
            $this->logger->log('auto_sync_products', 'cron', null, null, json_encode([
                'total_products' => $results['total_products'],
                'vendors' => $results['vendors'],
                'duration_seconds' => $duration,
            ]));
        }

        return $results;
    }

    /**
     * Save products to database
     * 
     * @param array $products Product list from API
     * @param string $vendor Vendor name
     * @return array Save results
     */
    private function saveProducts(array $products, string $vendor): array
    {
        $results = [
            'total' => 0,
            'updated' => 0,
            'inserted' => 0,
        ];
        
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
                'validation_type' => $product['validation_type'] ?? $product['validationType'] ?? 'DV',
                'price_1year' => $this->parsePrice($product['price_1year'] ?? $product['price1Year'] ?? null),
                'price_2year' => $this->parsePrice($product['price_2year'] ?? $product['price2Year'] ?? null),
                'price_3year' => $this->parsePrice($product['price_3year'] ?? $product['price3Year'] ?? null),
                'is_wildcard' => (int) ($product['is_wildcard'] ?? $product['isWildcard'] ?? 0),
                'is_multidomain' => (int) ($product['is_multidomain'] ?? $product['isMultidomain'] ?? 0),
                'max_domains' => (int) ($product['max_domains'] ?? $product['maxDomains'] ?? 1),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            try {
                // Check if product exists
                $existing = Capsule::table('mod_nicsrs_products')
                    ->where('product_code', $productCode)
                    ->where('vendor', $vendor)
                    ->first();
                
                if ($existing) {
                    Capsule::table('mod_nicsrs_products')
                        ->where('id', $existing->id)
                        ->update($data);
                    $results['updated']++;
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    Capsule::table('mod_nicsrs_products')->insert($data);
                    $results['inserted']++;
                }
                
                $results['total']++;
                
            } catch (\Exception $e) {
                $this->logError("Failed to save product {$productCode}: " . $e->getMessage());
            }
        }
        
        return $results;
    }

    /**
     * Parse price value
     * 
     * @param mixed $price Price value
     * @return float|null
     */
    private function parsePrice($price): ?float
    {
        if ($price === null || $price === '') {
            return null;
        }
        
        return (float) $price;
    }

    /**
     * Send completion notification email
     * 
     * @param object $cert Certificate record
     * @param array $syncResult Sync result data
     * @return void
     */
    private function sendCompletionNotification(object $cert, array $syncResult): void
    {
        // Check if notifications are enabled
        if (empty($this->settings['email_on_issuance'])) {
            return;
        }

        try {
            $notificationPath = __DIR__ . '/NotificationService.php';
            
            if (!file_exists($notificationPath)) {
                return;
            }
            
            require_once $notificationPath;
            
            $notifier = new NotificationService();
            $notifier->sendCertificateIssuedNotification($cert);
            
        } catch (\Exception $e) {
            $this->logError('Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Update a setting value in database
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return void
     */
    private function updateSetting(string $key, $value): void
    {
        try {
            Capsule::table('mod_nicsrs_settings')
                ->updateOrInsert(
                    ['setting_key' => $key],
                    [
                        'setting_value' => (string) $value,
                        'setting_type' => 'string',
                    ]
                );
            
            $this->settings[$key] = $value;
            
        } catch (\Exception $e) {
            $this->logError("Failed to update setting {$key}: " . $e->getMessage());
        }
    }

    /**
     * Increment error count
     * 
     * @return void
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
     * @return void
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

    /**
     * Get sync status for display
     * 
     * @return array Sync status information
     */
    public function getSyncStatus(): array
    {
        $statusSyncInterval = (int) ($this->settings['sync_interval_hours'] ?? 6);
        $productSyncInterval = (int) ($this->settings['product_sync_hours'] ?? 24);
        
        $lastStatusSync = $this->settings['last_status_sync'] ?? null;
        $lastProductSync = $this->settings['last_product_sync'] ?? null;
        
        // Calculate next sync times
        $nextStatusSync = null;
        $nextProductSync = null;
        
        if ($lastStatusSync) {
            $nextStatusSync = date('Y-m-d H:i:s', strtotime($lastStatusSync) + ($statusSyncInterval * 3600));
        }
        
        if ($lastProductSync) {
            $nextProductSync = date('Y-m-d H:i:s', strtotime($lastProductSync) + ($productSyncInterval * 3600));
        }
        
        // Get pending certificates count
        $pendingCount = 0;
        try {
            $pendingCount = Capsule::table('nicsrs_sslorders')
                ->whereIn('status', self::SYNCABLE_STATUSES)
                ->whereNotNull('remoteid')
                ->where('remoteid', '!=', '')
                ->count();
        } catch (\Exception $e) {
            // Ignore
        }
        
        return [
            'enabled' => !empty($this->settings['auto_sync_status']),
            'status_sync' => [
                'interval_hours' => $statusSyncInterval,
                'last_sync' => $lastStatusSync,
                'next_sync' => $nextStatusSync,
                'pending_certificates' => $pendingCount,
            ],
            'product_sync' => [
                'interval_hours' => $productSyncInterval,
                'last_sync' => $lastProductSync,
                'next_sync' => $nextProductSync,
            ],
            'error_count' => (int) ($this->settings['sync_error_count'] ?? 0),
            'batch_size' => $this->batchSize,
        ];
    }

    /**
     * Force sync now (manual trigger)
     * 
     * @param string $type Sync type: 'status', 'products', or 'all'
     * @return array Sync results
     */
    public function forceSyncNow(string $type = 'all'): array
    {
        $results = [
            'status_sync' => null,
            'product_sync' => null,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        if (!$this->apiService) {
            return [
                'error' => 'API service not available',
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        }

        if ($type === 'status' || $type === 'all') {
            $results['status_sync'] = $this->syncCertificateStatuses();
        }

        if ($type === 'products' || $type === 'all') {
            $results['product_sync'] = $this->syncProducts();
        }

        return $results;
    }
}