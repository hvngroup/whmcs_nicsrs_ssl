<?php
/**
 * Notification Service
 * 
 * Handles email notifications for SSL certificate events.
 * Supports notifications for certificate issuance, expiry warnings,
 * and other important events.
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 * @version    1.2.1
 */

namespace NicsrsAdmin\Service;

use WHMCS\Database\Capsule;

class NotificationService
{
    /**
     * @var array Module settings cache
     */
    private $settings = [];
    
    /**
     * @var string System URL
     */
    private $systemUrl = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadSettings();
        $this->systemUrl = $this->getSystemUrl();
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
        } catch (\Exception $e) {
            // Use defaults
        }
    }

    /**
     * Send certificate issued notification to admin
     * 
     * @param object $cert Certificate record
     * @return bool Success status
     */
    public function sendCertificateIssuedNotification(object $cert): bool
    {
        if (empty($this->settings['email_on_issuance'])) {
            return false;
        }

        $configData = json_decode($cert->configdata, true) ?: [];
        
        // Get domain from config
        $domain = 'Unknown Domain';
        if (!empty($configData['domainInfo'][0]['domainName'])) {
            $domain = $configData['domainInfo'][0]['domainName'];
        } elseif (!empty($configData['applyReturn']['domain'])) {
            $domain = $configData['applyReturn']['domain'];
        }
        
        // Get certificate dates
        $beginDate = $configData['applyReturn']['beginDate'] ?? 'N/A';
        $endDate = $configData['applyReturn']['endDate'] ?? 'N/A';
        
        // Format dates if valid
        if ($beginDate !== 'N/A' && strtotime($beginDate)) {
            $beginDate = date('Y-m-d', strtotime($beginDate));
        }
        if ($endDate !== 'N/A' && strtotime($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }
        
        // Get admin email
        $adminEmail = $this->getAdminEmail();
        
        if (empty($adminEmail)) {
            return false;
        }
        
        // Get client info
        $clientInfo = $this->getClientInfo($cert->userid);
        
        // Get product info
        $productName = $cert->certtype ?? 'SSL Certificate';
        
        // Build email content
        $subject = "[HVN SSL] Certificate Issued - {$domain}";
        
        $body = $this->buildIssuedEmailBody([
            'order_id' => $cert->id,
            'remote_id' => $cert->remoteid,
            'domain' => $domain,
            'product' => $productName,
            'client_name' => $clientInfo['name'],
            'client_email' => $clientInfo['email'],
            'begin_date' => $beginDate,
            'end_date' => $endDate,
            'service_id' => $cert->serviceid,
        ]);
        
        return $this->sendMail($adminEmail, $subject, $body);
    }

    /**
     * Send expiry warning notification
     * 
     * @param object $cert Certificate record
     * @param int $daysUntilExpiry Days until certificate expires
     * @return bool Success status
     */
    public function sendExpiryWarningNotification(object $cert, int $daysUntilExpiry): bool
    {
        if (empty($this->settings['email_on_expiry'])) {
            return false;
        }
        
        $configData = json_decode($cert->configdata, true) ?: [];
        
        // Get domain
        $domain = 'Unknown Domain';
        if (!empty($configData['domainInfo'][0]['domainName'])) {
            $domain = $configData['domainInfo'][0]['domainName'];
        }
        
        // Get expiry date
        $expiryDate = $configData['applyReturn']['endDate'] ?? 'Unknown';
        if ($expiryDate !== 'Unknown' && strtotime($expiryDate)) {
            $expiryDate = date('Y-m-d', strtotime($expiryDate));
        }
        
        // Get admin email
        $adminEmail = $this->getAdminEmail();
        
        if (empty($adminEmail)) {
            return false;
        }
        
        // Get client info
        $clientInfo = $this->getClientInfo($cert->userid);
        
        // Build email content
        $urgency = $daysUntilExpiry <= 7 ? 'URGENT: ' : '';
        $subject = "{$urgency}[HVN SSL] Certificate Expiring in {$daysUntilExpiry} Days - {$domain}";
        
        $body = $this->buildExpiryEmailBody([
            'order_id' => $cert->id,
            'remote_id' => $cert->remoteid,
            'domain' => $domain,
            'days_until_expiry' => $daysUntilExpiry,
            'expiry_date' => $expiryDate,
            'client_name' => $clientInfo['name'],
            'client_email' => $clientInfo['email'],
            'service_id' => $cert->serviceid,
        ]);
        
        return $this->sendMail($adminEmail, $subject, $body);
    }

    /**
     * Send sync error notification
     * 
     * @param array $errors List of errors
     * @param int $errorCount Consecutive error count
     * @return bool Success status
     */
    public function sendSyncErrorNotification(array $errors, int $errorCount): bool
    {
        $adminEmail = $this->getAdminEmail();
        
        if (empty($adminEmail)) {
            return false;
        }
        
        $subject = "[HVN SSL] Auto-Sync Error Alert";
        
        $errorList = '';
        foreach ($errors as $error) {
            $errorList .= "<li>" . htmlspecialchars($error) . "</li>";
        }
        
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ff4d4f; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .error-list { background: #fff; padding: 15px; border-left: 4px solid #ff4d4f; margin: 15px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>⚠️ Auto-Sync Error Alert</h2>
                </div>
                <div class='content'>
                    <p>The HVN SSL automatic synchronization has encountered errors.</p>
                    
                    <p><strong>Consecutive Errors:</strong> {$errorCount}</p>
                    
                    <div class='error-list'>
                        <h4>Error Details:</h4>
                        <ul>{$errorList}</ul>
                    </div>
                    
                    <p>Please check the module configuration and API credentials.</p>
                    
                    <p>
                        <a href='{$this->systemUrl}admin/addonmodules.php?module=nicsrs_ssl_admin&action=settings' 
                           style='display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none;'>
                            Check Settings
                        </a>
                    </p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from HVN SSL Admin Module</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $this->sendMail($adminEmail, $subject, $body);
    }

    /**
     * Build email body for certificate issued notification
     * 
     * @param array $data Email data
     * @return string HTML email body
     */
    private function buildIssuedEmailBody(array $data): string
    {
        $orderUrl = "{$this->systemUrl}admin/addonmodules.php?module=nicsrs_ssl_admin&action=order_detail&id={$data['order_id']}";
        $serviceUrl = $data['service_id'] ? "{$this->systemUrl}admin/clientsservices.php?id={$data['service_id']}" : '#';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #52c41a; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .info-table td { padding: 10px; border-bottom: 1px solid #eee; }
                .info-table td:first-child { font-weight: bold; width: 40%; background: #fff; }
                .info-table td:last-child { background: #fff; }
                .btn { display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none; margin: 5px; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✅ SSL Certificate Issued Successfully</h2>
                </div>
                <div class='content'>
                    <p>A new SSL certificate has been issued and is ready for use.</p>
                    
                    <table class='info-table'>
                        <tr>
                            <td>Order ID:</td>
                            <td>#{$data['order_id']}</td>
                        </tr>
                        <tr>
                            <td>Certificate ID:</td>
                            <td>{$data['remote_id']}</td>
                        </tr>
                        <tr>
                            <td>Domain:</td>
                            <td><strong>{$data['domain']}</strong></td>
                        </tr>
                        <tr>
                            <td>Product:</td>
                            <td>{$data['product']}</td>
                        </tr>
                        <tr>
                            <td>Client:</td>
                            <td>{$data['client_name']} ({$data['client_email']})</td>
                        </tr>
                        <tr>
                            <td>Valid From:</td>
                            <td>{$data['begin_date']}</td>
                        </tr>
                        <tr>
                            <td>Valid Until:</td>
                            <td>{$data['end_date']}</td>
                        </tr>
                    </table>
                    
                    <p style='text-align: center;'>
                        <a href='{$orderUrl}' class='btn'>View Order Details</a>
                        " . ($data['service_id'] ? "<a href='{$serviceUrl}' class='btn' style='background: #595959;'>View Service</a>" : "") . "
                    </p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from HVN SSL Admin Module</p>
                    <p>Powered by HVN GROUP - https://hvn.vn</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Build email body for expiry warning notification
     * 
     * @param array $data Email data
     * @return string HTML email body
     */
    private function buildExpiryEmailBody(array $data): string
    {
        $orderUrl = "{$this->systemUrl}admin/addonmodules.php?module=nicsrs_ssl_admin&action=order_detail&id={$data['order_id']}";
        
        // Determine urgency color
        $headerColor = '#faad14'; // Warning yellow
        $urgencyText = 'Expiring Soon';
        
        if ($data['days_until_expiry'] <= 7) {
            $headerColor = '#ff4d4f'; // Danger red
            $urgencyText = 'URGENT - Expiring Very Soon';
        } elseif ($data['days_until_expiry'] <= 14) {
            $headerColor = '#fa8c16'; // Orange
            $urgencyText = 'Action Required';
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: {$headerColor}; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .countdown { font-size: 48px; text-align: center; color: {$headerColor}; margin: 20px 0; }
                .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .info-table td { padding: 10px; border-bottom: 1px solid #eee; }
                .info-table td:first-child { font-weight: bold; width: 40%; background: #fff; }
                .info-table td:last-child { background: #fff; }
                .btn { display: inline-block; padding: 12px 24px; background: #1890ff; color: white; text-decoration: none; font-weight: bold; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>⚠️ {$urgencyText}</h2>
                    <p>SSL Certificate Expiry Warning</p>
                </div>
                <div class='content'>
                    <div class='countdown'>
                        {$data['days_until_expiry']} days
                    </div>
                    <p style='text-align: center;'>until certificate expiration</p>
                    
                    <table class='info-table'>
                        <tr>
                            <td>Domain:</td>
                            <td><strong>{$data['domain']}</strong></td>
                        </tr>
                        <tr>
                            <td>Expiry Date:</td>
                            <td style='color: {$headerColor}; font-weight: bold;'>{$data['expiry_date']}</td>
                        </tr>
                        <tr>
                            <td>Order ID:</td>
                            <td>#{$data['order_id']}</td>
                        </tr>
                        <tr>
                            <td>Certificate ID:</td>
                            <td>{$data['remote_id']}</td>
                        </tr>
                        <tr>
                            <td>Client:</td>
                            <td>{$data['client_name']} ({$data['client_email']})</td>
                        </tr>
                    </table>
                    
                    <p><strong>Action Required:</strong> Please renew this certificate before it expires to avoid service interruption.</p>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='{$orderUrl}' class='btn'>Renew Certificate</a>
                    </p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from HVN SSL Admin Module</p>
                    <p>Powered by HVN GROUP - https://hvn.vn</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get admin email address
     * 
     * @return string|null
     */
    private function getAdminEmail(): ?string
    {
        // Use configured admin email first
        if (!empty($this->settings['admin_email'])) {
            return $this->settings['admin_email'];
        }
        
        // Fallback to first active admin email
        try {
            return Capsule::table('tbladmins')
                ->where('disabled', 0)
                ->orderBy('id', 'asc')
                ->value('email');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get client information
     * 
     * @param int|null $userId Client ID
     * @return array
     */
    private function getClientInfo(?int $userId): array
    {
        $default = [
            'name' => 'Unknown',
            'email' => 'N/A',
        ];
        
        if (!$userId) {
            return $default;
        }
        
        try {
            $client = Capsule::table('tblclients')
                ->where('id', $userId)
                ->first();
            
            if ($client) {
                return [
                    'name' => trim($client->firstname . ' ' . $client->lastname),
                    'email' => $client->email,
                ];
            }
        } catch (\Exception $e) {
            // Return default
        }
        
        return $default;
    }

    /**
     * Get WHMCS system URL
     * 
     * @return string
     */
    private function getSystemUrl(): string
    {
        try {
            $url = Capsule::table('tblconfiguration')
                ->where('setting', 'SystemURL')
                ->value('value');
            
            if ($url) {
                // Ensure trailing slash
                return rtrim($url, '/') . '/';
            }
        } catch (\Exception $e) {
            // Fallback
        }
        
        // Try to detect from server
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        return "{$protocol}://{$host}/";
    }

    /**
     * Get system email address (From address)
     * 
     * @return string
     */
    private function getSystemEmail(): string
    {
        try {
            $email = Capsule::table('tblconfiguration')
                ->where('setting', 'SystemEmailsFromEmail')
                ->value('value');
            
            if ($email) {
                return $email;
            }
        } catch (\Exception $e) {
            // Fallback
        }
        
        return 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    /**
     * Get system email name (From name)
     * 
     * @return string
     */
    private function getSystemEmailName(): string
    {
        try {
            $name = Capsule::table('tblconfiguration')
                ->where('setting', 'SystemEmailsFromName')
                ->value('value');
            
            if ($name) {
                return $name;
            }
        } catch (\Exception $e) {
            // Fallback
        }
        
        return 'HVN SSL Admin';
    }

    /**
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return bool Success status
     */
    private function sendMail(string $to, string $subject, string $body): bool
    {
        try {
            $fromEmail = $this->getSystemEmail();
            $fromName = $this->getSystemEmailName();
            
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $fromName . ' <' . $fromEmail . '>',
                'Reply-To: ' . $fromEmail,
                'X-Mailer: HVN-SSL-Admin/1.2.1',
            ];
            
            $result = mail($to, $subject, $body, implode("\r\n", $headers));
            
            // Log email send attempt
            logModuleCall(
                'nicsrs_ssl_admin',
                'SendEmail',
                [
                    'to' => $to,
                    'subject' => $subject,
                ],
                $result ? 'SUCCESS' : 'FAILED',
                ''
            );
            
            return $result;
            
        } catch (\Exception $e) {
            logModuleCall(
                'nicsrs_ssl_admin',
                'SendEmail',
                [
                    'to' => $to,
                    'subject' => $subject,
                ],
                'ERROR: ' . $e->getMessage(),
                ''
            );
            
            return false;
        }
    }

    /**
     * Check and send expiry warnings for all certificates
     * 
     * Should be called from cron to check for expiring certificates.
     * 
     * @return array Results
     */
    public function checkAndSendExpiryWarnings(): array
    {
        $results = [
            'checked' => 0,
            'warnings_sent' => 0,
            'errors' => [],
        ];
        
        if (empty($this->settings['email_on_expiry'])) {
            return $results;
        }
        
        $warningDays = (int) ($this->settings['expiry_days'] ?? 30);
        
        try {
            // Get certificates expiring within warning period
            $expiringCerts = Capsule::table('nicsrs_sslorders')
                ->where('status', 'complete')
                ->whereNotNull('remoteid')
                ->get();
            
            foreach ($expiringCerts as $cert) {
                $results['checked']++;
                
                $configData = json_decode($cert->configdata, true) ?: [];
                $endDate = $configData['applyReturn']['endDate'] ?? null;
                
                if (!$endDate) {
                    continue;
                }
                
                $expiryTimestamp = strtotime($endDate);
                if (!$expiryTimestamp) {
                    continue;
                }
                
                $daysUntilExpiry = ceil(($expiryTimestamp - time()) / 86400);
                
                // Check if within warning period and not already notified recently
                if ($daysUntilExpiry > 0 && $daysUntilExpiry <= $warningDays) {
                    // Check if we've already sent a warning for this interval
                    $lastWarning = $configData['lastExpiryWarning'] ?? null;
                    $warningInterval = $this->getWarningInterval($daysUntilExpiry);
                    
                    if ($this->shouldSendWarning($lastWarning, $warningInterval, $daysUntilExpiry)) {
                        if ($this->sendExpiryWarningNotification($cert, $daysUntilExpiry)) {
                            $results['warnings_sent']++;
                            
                            // Update last warning timestamp
                            $configData['lastExpiryWarning'] = date('Y-m-d H:i:s');
                            $configData['lastExpiryWarningDays'] = $daysUntilExpiry;
                            
                            Capsule::table('nicsrs_sslorders')
                                ->where('id', $cert->id)
                                ->update(['configdata' => json_encode($configData)]);
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Determine warning interval based on days until expiry
     * 
     * @param int $days Days until expiry
     * @return string Interval key
     */
    private function getWarningInterval(int $days): string
    {
        if ($days <= 1) {
            return 'final';
        } elseif ($days <= 7) {
            return 'week';
        } elseif ($days <= 14) {
            return 'twoweeks';
        } else {
            return 'month';
        }
    }

    /**
     * Check if warning should be sent
     * 
     * @param string|null $lastWarning Last warning timestamp
     * @param string $interval Warning interval
     * @param int $daysUntilExpiry Days until expiry
     * @return bool
     */
    private function shouldSendWarning(?string $lastWarning, string $interval, int $daysUntilExpiry): bool
    {
        if (!$lastWarning) {
            return true;
        }
        
        $lastWarningTime = strtotime($lastWarning);
        if (!$lastWarningTime) {
            return true;
        }
        
        // Define minimum hours between warnings for each interval
        $minHours = [
            'final' => 12,      // 12 hours for final day
            'week' => 48,       // 2 days for last week
            'twoweeks' => 72,   // 3 days for 2 weeks
            'month' => 168,     // 7 days for month
        ];
        
        $hours = $minHours[$interval] ?? 168;
        $nextAllowed = $lastWarningTime + ($hours * 3600);
        
        return time() >= $nextAllowed;
    }
}