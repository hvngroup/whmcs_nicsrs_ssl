<?php
/**
 * Settings Controller
 * Handles module settings management
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Controller;

use WHMCS\Database\Capsule;
use NicsrsAdmin\Service\NicsrsApiService;

class SettingsController extends BaseController
{
    /**
     * @var NicsrsApiService API service
     */
    private $apiService;

    /**
     * Constructor
     * 
     * @param array $vars Module variables
     */
    public function __construct(array $vars)
    {
        parent::__construct($vars);
        $this->apiService = new NicsrsApiService($this->getApiToken());
    }

    /**
     * Render settings page
     * 
     * @param string $action Current action
     * @return void
     */
    public function render(string $action): void
    {
        // Get all settings
        $settings = $this->getAllSettings();
        
        // Get activity logs (recent)
        $activityLogs = Capsule::table('mod_nicsrs_activity_log as l')
            ->leftJoin('tbladmins as a', 'l.admin_id', '=', 'a.id')
            ->select(['l.*', 'a.username', 'a.firstname', 'a.lastname'])
            ->orderBy('l.created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();

        // Check API status
        $apiConnected = !empty($this->getApiToken()) && $this->apiService->testConnection();

        $currencyInfo = \NicsrsAdmin\Helper\CurrencyHelper::getRateInfo();

        $data = [
            'settings' => $settings,
            'activityLogs' => $activityLogs,
            'apiConnected' => $apiConnected,
            'apiToken' => $this->maskApiToken($this->getApiToken()),
            'csrfToken' => $this->generateCsrfToken(),
            'currencyInfo' => $currencyInfo,
        ];

        $this->includeTemplate('settings', $data);
    }

    /**
     * Get all settings as array
     * 
     * @return array Settings
     */
    private function getAllSettings(): array
    {
        $rows = Capsule::table('mod_nicsrs_settings')->get();
        
        $settings = [];
        foreach ($rows as $row) {
            $value = $row->setting_value;
            
            // Convert based on type
            switch ($row->setting_type) {
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $settings[$row->setting_key] = $value;
        }
        
        return $settings;
    }

    /**
     * Mask API token for display
     * 
     * @param string $token API token
     * @return string Masked token
     */
    private function maskApiToken(string $token): string
    {
        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }
        
        return substr($token, 0, 4) . str_repeat('*', strlen($token) - 8) . substr($token, -4);
    }

    /**
     * Handle AJAX requests
     * 
     * @param array $post POST data
     * @return string JSON response
     */
    public function handleAjax(array $post): string
    {
        $action = isset($post['ajax_action']) ? $post['ajax_action'] : '';

        switch ($action) {
            case 'save_settings':
                return $this->saveSettings($post);

            case 'test_api':
                return $this->testApiConnection();

            case 'clear_logs':
                return $this->clearActivityLogs($post);

            case 'export_logs':
                return $this->exportActivityLogs();

            case 'manual_sync':
                return $this->handleManualSync($post);

            case 'get_sync_status':
                return $this->getSyncStatus();

            case 'check_expiring':
                return $this->checkExpiringCertificates();

            case 'update_exchange_rate':
                return $this->updateExchangeRate($post);

            default:
                return $this->jsonError('Unknown action');
        }
    }
    /**
     * Save settings
     * 
     * @param array $post POST data
     * @return string JSON response
     */
    private function saveSettings(array $post): string
    {
        try {
            // Define settings to save - UPDATED with currency settings
            $settingsConfig = [
                // Notification settings
                'email_on_issuance' => 'boolean',
                'email_on_expiry' => 'boolean',
                'expiry_days' => 'integer',
                // Sync settings
                'auto_sync_status' => 'boolean',
                'sync_interval_hours' => 'integer',
                'product_sync_hours' => 'integer',
                // Display settings
                'date_format' => 'string',
                'admin_email' => 'string',
                // Currency settings (for Reports)
                'usd_vnd_rate' => 'number',
                'currency_display' => 'string',
            ];

            $savedSettings = [];

            foreach ($settingsConfig as $key => $type) {
                // For boolean, checkbox not sent = false
                if ($type === 'boolean') {
                    $value = isset($post[$key]) && ($post[$key] === '1' || $post[$key] === 'true' || $post[$key] === true);
                    $this->saveSetting($key, $value, $type);
                    $savedSettings[$key] = $value;
                } elseif (isset($post[$key])) {
                    $value = $post[$key];
                    
                    // Validate based on type
                    switch ($type) {
                        case 'integer':
                            $value = (int) $value;
                            if ($value < 0) $value = 0;
                            break;
                        case 'number':
                            $value = (float) $value;
                            if ($value < 0) $value = 0;
                            break;
                        case 'string':
                            $value = $this->sanitize($value);
                            // Validate currency_display
                            if ($key === 'currency_display' && !in_array($value, ['usd', 'vnd', 'both'])) {
                                $value = 'both';
                            }
                            break;
                    }
                    
                    $this->saveSetting($key, $value, $type);
                    $savedSettings[$key] = $value;
                }
            }

            // If usd_vnd_rate was updated, also update the timestamp
            if (isset($savedSettings['usd_vnd_rate'])) {
                $this->saveSetting('rate_last_updated', date('Y-m-d H:i:s'), 'datetime');
            }

            // Log activity
            $this->logger->log('save_settings', 'settings', null, null, json_encode($savedSettings));

            return $this->jsonSuccess('Settings saved successfully');
            
        } catch (\Exception $e) {
            return $this->jsonError('Failed to save settings: ' . $e->getMessage());
        }
    }

    /**
     * Test API connection
     * 
     * @return string JSON response
     */
    private function testApiConnection(): string
    {
        if (empty($this->getApiToken())) {
            return $this->jsonError('API token is not configured. Please set it in the module configuration.');
        }

        try {
            $connected = $this->apiService->testConnection();
            
            if ($connected) {
                $this->logger->log('test_api', 'settings', null, null, 'success');
                return $this->jsonSuccess('API connection successful! Your API token is valid.');
            }
            
            return $this->jsonError('API connection failed. Please check your API token.');
            
        } catch (\Exception $e) {
            return $this->jsonError('API test failed: ' . $e->getMessage());
        }
    }

    /**
     * Clear activity logs
     * 
     * @param array $post POST data
     * @return string JSON response
     */
    private function clearActivityLogs(array $post): string
    {
        try {
            $days = isset($post['days']) ? (int) $post['days'] : 0;
            
            $query = Capsule::table('mod_nicsrs_activity_log');
            
            if ($days > 0) {
                // Delete logs older than X days
                $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
                $deleted = $query->where('created_at', '<', $date)->delete();
                $message = "Deleted {$deleted} log entries older than {$days} days";
            } else {
                // Delete all logs
                $deleted = $query->delete();
                $message = "Deleted all {$deleted} log entries";
            }

            // Log this action
            $this->logger->log('clear_logs', 'settings', null, null, json_encode([
                'days' => $days,
                'deleted' => $deleted,
            ]));

            return $this->jsonSuccess($message, ['deleted' => $deleted]);
            
        } catch (\Exception $e) {
            return $this->jsonError('Failed to clear logs: ' . $e->getMessage());
        }
    }

    /**
     * Export activity logs as CSV
     * 
     * @return string JSON response with CSV data
     */
    private function exportActivityLogs(): string
    {
        try {
            $logs = Capsule::table('mod_nicsrs_activity_log as l')
                ->leftJoin('tbladmins as a', 'l.admin_id', '=', 'a.id')
                ->select([
                    'l.id',
                    'l.action',
                    'l.entity_type',
                    'l.entity_id',
                    'l.old_value',
                    'l.new_value',
                    'l.ip_address',
                    'l.created_at',
                    'a.username',
                ])
                ->orderBy('l.created_at', 'desc')
                ->limit(5000)
                ->get();

            // Build CSV content
            $csv = "ID,Action,Entity Type,Entity ID,Old Value,New Value,IP Address,Admin,Date\n";
            
            foreach ($logs as $log) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,%s,%s,%s,%s,%s\n",
                    $log->id,
                    $this->escapeCsv($log->action),
                    $this->escapeCsv($log->entity_type),
                    $log->entity_id ?: '',
                    $this->escapeCsv($log->old_value),
                    $this->escapeCsv($log->new_value),
                    $log->ip_address,
                    $this->escapeCsv($log->username),
                    $log->created_at
                );
            }

            return $this->jsonSuccess('Export ready', [
                'csv' => base64_encode($csv),
                'filename' => 'nicsrs_activity_log_' . date('Y-m-d') . '.csv',
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonError('Failed to export logs: ' . $e->getMessage());
        }
    }

    /**
     * Handle manual sync AJAX request
     * 
     * Add this case to your existing handleAjax() method:
     * 
     * case 'manual_sync':
     *     return $this->handleManualSync($post);
     */
    
    /**
     * Handle manual sync request
     * 
     * @param array $post POST data
     * @return string JSON response
     */
    private function handleManualSync(array $post): string
    {
        $syncType = isset($post['sync_type']) ? $this->sanitize($post['sync_type']) : 'all';
        
        // Validate sync type
        if (!in_array($syncType, ['status', 'products', 'all'])) {
            return $this->jsonError('Invalid sync type');
        }
        
        try {
            // Load SyncService
            require_once __DIR__ . '/SyncService.php';
            
            $syncService = new \NicsrsAdmin\Service\SyncService();
            $results = $syncService->forceSyncNow($syncType);
            
            // Check for errors
            if (isset($results['error'])) {
                return $this->jsonError($results['error']);
            }
            
            // Build response message
            $messages = [];
            
            if (isset($results['status_sync'])) {
                $ss = $results['status_sync'];
                $messages[] = "Status Sync: {$ss['updated']} updated, {$ss['completed']} completed, {$ss['failed']} failed";
            }
            
            if (isset($results['product_sync'])) {
                $ps = $results['product_sync'];
                $messages[] = "Product Sync: {$ps['total_products']} products synced";
            }
            
            // Log activity
            if ($this->logger) {
                $this->logger->log('manual_sync', 'settings', null, null, json_encode([
                    'sync_type' => $syncType,
                    'results' => $results,
                ]));
            }
            
            return $this->jsonSuccess(implode('. ', $messages), [
                'results' => $results,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonError('Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Get sync status AJAX handler
     * 
     * Add this case to your existing handleAjax() method:
     * 
     * case 'get_sync_status':
     *     return $this->getSyncStatus();
     */
    
    /**
     * Get current sync status
     * 
     * @return string JSON response
     */
    private function getSyncStatus(): string
    {
        try {
            require_once __DIR__ . '/SyncService.php';
            
            $syncService = new \NicsrsAdmin\Service\SyncService();
            $status = $syncService->getSyncStatus();
            
            return $this->jsonSuccess('Sync status retrieved', $status);
            
        } catch (\Exception $e) {
            return $this->jsonError('Failed to get sync status: ' . $e->getMessage());
        }
    }

    /**
     * Check expiring certificates and send warnings
     * 
     * Add this case to your existing handleAjax() method:
     * 
     * case 'check_expiring':
     *     return $this->checkExpiringCertificates();
     */
    
    /**
     * Check expiring certificates
     * 
     * @return string JSON response
     */
    private function checkExpiringCertificates(): string
    {
        try {
            require_once __DIR__ . '/NotificationService.php';
            
            $notifier = new \NicsrsAdmin\Service\NotificationService();
            $results = $notifier->checkAndSendExpiryWarnings();
            
            return $this->jsonSuccess(
                "Checked {$results['checked']} certificates, sent {$results['warnings_sent']} warnings",
                $results
            );
            
        } catch (\Exception $e) {
            return $this->jsonError('Check failed: ' . $e->getMessage());
        }
    }

    /**
     * Escape value for CSV
     * 
     * @param string|null $value Value to escape
     * @return string Escaped value
     */
    private function escapeCsv($value): string
    {
        if ($value === null) {
            return '';
        }
        
        // Replace quotes and wrap in quotes if contains special chars
        $value = str_replace('"', '""', $value);
        
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . $value . '"';
        }
        
        return $value;
    }
}