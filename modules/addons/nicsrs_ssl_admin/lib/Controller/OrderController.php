<?php
/**
 * Order Controller
 * Handles SSL orders management and certificate operations
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Controller;

use WHMCS\Database\Capsule;
use NicsrsAdmin\Service\NicsrsApiService;
use NicsrsAdmin\Service\OrderService;
use NicsrsAdmin\Helper\Pagination;

class OrderController extends BaseController
{
    /**
     * @var NicsrsApiService API service
     */
    private $apiService;
    
    /**
     * @var OrderService Order service
     */
    private $orderService;

    /**
     * Constructor
     * 
     * @param array $vars Module variables
     */
    public function __construct(array $vars)
    {
        parent::__construct($vars);
        $this->apiService = new NicsrsApiService($this->getApiToken());
        $this->orderService = new OrderService($this->apiService);
    }

    /**
     * Render page based on action
     * 
     * @param string $action Current action
     * @return void
     */
    public function render(string $action): void
    {
        if ($action === 'order' && isset($_GET['id'])) {
            $this->renderDetail((int) $_GET['id']);
        } else {
            $this->renderList();
        }
    }

    /**
     * Render orders list
     * 
     * @return void
     */
    private function renderList(): void
    {
        $page = $this->getCurrentPage();
        $perPage = $this->getItemsPerPage();
        $status = isset($_GET['status']) ? $this->sanitize($_GET['status']) : '';
        $search = isset($_GET['search']) ? $this->sanitize($_GET['search']) : '';

        // Build query
        $query = Capsule::table('nicsrs_sslorders as o')
            ->leftJoin('tblclients as c', 'o.userid', '=', 'c.id')
            ->leftJoin('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->select([
                'o.id',
                'o.userid',
                'o.serviceid',
                'o.remoteid',
                'o.certtype',
                'o.status',
                'o.provisiondate',
                'o.completiondate',
                'o.configdata',
                'c.firstname',
                'c.lastname',
                'c.companyname',
                'c.email',
                'h.domainstatus as service_status',
            ]);

        // Apply filters
        if ($status) {
            // Map short status names to actual database values
            $statusMap = [
                'awaiting' => 'Awaiting Configuration',
                'draft' => 'draft',
                'pending' => 'pending',
                'complete' => 'complete',
                'cancelled' => 'cancelled',
                'revoked' => 'revoked',
            ];
            
            // Check if it's a short name that needs mapping
            if (isset($statusMap[strtolower($status)])) {
                $query->where('o.status', $statusMap[strtolower($status)]);
            } else {
                // Direct match (for full status names like "Awaiting Configuration")
                $query->where('o.status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('o.remoteid', 'like', "%{$search}%")
                  ->orWhere('c.firstname', 'like', "%{$search}%")
                  ->orWhere('c.lastname', 'like', "%{$search}%")
                  ->orWhere('c.email', 'like', "%{$search}%")
                  ->orWhere('c.companyname', 'like', "%{$search}%")
                  ->orWhere('o.certtype', 'like', "%{$search}%");
            });
        }

        // Get total
        $total = $query->count();

        // Get orders
        $orders = $query
            ->orderBy('o.id', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // Process orders
        $processedOrders = [];
        foreach ($orders as $order) {
            $config = json_decode($order->configdata, true) ?: [];
            
            $domain = 'N/A';
            if (isset($config['domainInfo'][0]['domainName'])) {
                $domain = $config['domainInfo'][0]['domainName'];
            }
            
            $endDate = null;
            if (isset($config['applyReturn']['endDate'])) {
                $endDate = $config['applyReturn']['endDate'];
            }

            $clientName = trim($order->firstname . ' ' . $order->lastname);
            
            $processedOrders[] = [
                'id' => $order->id,
                'userid' => $order->userid,
                'serviceid' => $order->serviceid,
                'remoteid' => $order->remoteid,
                'domain' => $domain,
                'certtype' => $order->certtype,
                'status' => $order->status,
                'provisiondate' => $this->formatDbDate($order->provisiondate),
                'completiondate' => $this->formatDbDate($order->completiondate),
                'end_date' => $endDate,
                'client_name' => $clientName ?: 'Unknown',
                'client_email' => $order->email,
                'companyname' => $order->companyname,
                'service_status' => $order->service_status,
            ];
        }

        // Get status counts for filters
        // Normalize status names for consistent counting
        $rawStatusCounts = Capsule::table('nicsrs_sslorders')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Normalize status keys (map "Awaiting Configuration" to "awaiting", etc.)
        $statusCounts = [];
        foreach ($rawStatusCounts as $dbStatus => $count) {
            $normalizedStatus = $this->normalizeStatus($dbStatus);
            if (isset($statusCounts[$normalizedStatus])) {
                $statusCounts[$normalizedStatus] += $count;
            } else {
                $statusCounts[$normalizedStatus] = $count;
            }
        }

        // Create pagination
        $paginationParams = [];
        if ($status) $paginationParams['status'] = $status;
        if ($search) $paginationParams['search'] = $search;
        
        $pagination = new Pagination(
            $total, 
            $perPage, 
            $page, 
            $this->modulelink . '&action=orders',
            $paginationParams
        );

        $data = [
            'orders' => $processedOrders,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
            'search' => $search,
            'pagination' => $pagination,
            'total' => $total,
        ];

        $this->includeTemplate('orders/list', $data);
    }

    /**
     * Normalize status string for consistent display and filtering
     * Maps database values to short lowercase keys
     * 
     * @param string $status Status from database
     * @return string Normalized status key
     */
    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        
        // Map full names to short names
        $normalizeMap = [
            'awaiting configuration' => 'awaiting',
            'awaiting' => 'awaiting',
            'pending validation' => 'pending',
            'pending' => 'pending',
            'complete' => 'complete',
            'completed' => 'complete',
            'issued' => 'complete',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'revoked' => 'revoked',
            'draft' => 'draft',
            'expired' => 'expired',
        ];
        
        return $normalizeMap[$status] ?? $status;
    }

    /**
     * Get database status value from normalized key
     * 
     * @param string $normalizedStatus Normalized status key
     * @return string Database status value
     */
    private function getDatabaseStatus(string $normalizedStatus): string
    {
        $dbStatusMap = [
            'awaiting' => 'Awaiting Configuration',
            'pending' => 'pending',
            'complete' => 'complete',
            'cancelled' => 'cancelled',
            'revoked' => 'revoked',
            'draft' => 'draft',
            'expired' => 'expired',
        ];
        
        return $dbStatusMap[strtolower($normalizedStatus)] ?? $normalizedStatus;
    }

    /**
     * Render order detail view
     * 
     * @param int $orderId Order ID
     * @return void
     */
    private function renderDetail(int $orderId): void
    {
        // Get order with client info
        $order = Capsule::table('nicsrs_sslorders as o')
            ->leftJoin('tblclients as c', 'o.userid', '=', 'c.id')
            ->leftJoin('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->leftJoin('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->select([
                'o.*',
                'c.firstname',
                'c.lastname',
                'c.companyname',
                'c.email as client_email',
                'c.phonenumber',
                'c.address1',
                'c.city',
                'c.state',
                'c.postcode',
                'c.country',
                'h.domainstatus as service_status',
                'h.domain as service_domain',
                'h.billingcycle',
                'h.nextduedate',
                'h.regdate',
                'h.amount',
                'p.name as whmcs_product_name',
            ])
            ->where('o.id', $orderId)
            ->first();


        if (!$order) {
            echo '<div class="alert alert-danger">Order not found</div>';
            echo '<a href="' . $this->modulelink . '&action=orders" class="btn btn-default">Back to Orders</a>';
            return;
        }

        // Parse config data
        $configData = json_decode($order->configdata, true) ?: [];
        
        // Get activity logs for this order
        $activityLogs = Capsule::table('mod_nicsrs_activity_log as l')
            ->leftJoin('tbladmins as a', 'l.admin_id', '=', 'a.id')
            ->select(['l.*', 'a.username', 'a.firstname as admin_firstname', 'a.lastname as admin_lastname'])
            ->where('l.entity_type', 'order')
            ->where('l.entity_id', $orderId)
            ->orderBy('l.created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();

        // Extract domain info
        $domains = [];
        if (isset($configData['domainInfo']) && is_array($configData['domainInfo'])) {
            foreach ($configData['domainInfo'] as $domainInfo) {
                $domains[] = [
                    'domain' => $domainInfo['domainName'] ?? 'N/A',
                    'dcv_method' => $domainInfo['dcvMethod'] ?? 'N/A',
                    'dcv_email' => $domainInfo['dcvEmail'] ?? '',
                    'is_verified' => isset($domainInfo['isVerified']) ? $domainInfo['isVerified'] : false,
                ];
            }
        }

        // Extract certificate info
        $certInfo = [];
        if (isset($configData['applyReturn'])) {
            $certInfo = [
                'cert_id' => $configData['applyReturn']['certId'] ?? '',
                'vendor_id' => $configData['applyReturn']['vendorId'] ?? '',
                'begin_date' => $configData['applyReturn']['beginDate'] ?? '',
                'end_date' => $configData['applyReturn']['endDate'] ?? '',
                'has_certificate' => !empty($configData['applyReturn']['certificate']),
            ];
        }

        $data = [
            'order' => $order,
            'config' => $configData,
            'domains' => $domains,
            'certInfo' => $certInfo,
            'activityLogs' => $activityLogs,
            'clientName' => trim($order->firstname . ' ' . $order->lastname),
            'provisiondate' => $this->formatDbDate($order->provisiondate),
            'completiondate' => $this->formatDbDate($order->completiondate),
        ];

        $this->includeTemplate('orders/detail', $data);
    }

    /**
     * Helper to format database date (handle 0000-00-00)
     */
    private function formatDbDate($date): ?string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return null;
        }
        return $date;
    }

    /**
     * Check if date is valid (not empty or 0000-00-00)
     */
    private function isValidDate($date): bool
    {
        return !empty($date) && $date !== '0000-00-00' && $date !== '0000-00-00 00:00:00';
    }

    /**
     * Fix dates for existing orders
     */
    private function fixOrderDates(int $orderId): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }

            $configData = json_decode($order->configdata, true) ?: [];
            $updateData = [];
            $changes = [];

            // Fix provisiondate
            if (!$this->isValidDate($order->provisiondate)) {
                // Try to get from configdata or use current date
                $provDate = date('Y-m-d');
                if (isset($configData['importedAt'])) {
                    $provDate = date('Y-m-d', strtotime($configData['importedAt']));
                } elseif (isset($configData['linkedAt'])) {
                    $provDate = date('Y-m-d', strtotime($configData['linkedAt']));
                }
                $updateData['provisiondate'] = $provDate;
                $changes[] = "provisiondate set to {$provDate}";
            }

            // Fix completiondate for complete orders
            if ($order->status === 'complete' && !$this->isValidDate($order->completiondate)) {
                $compDate = date('Y-m-d H:i:s');
                if (isset($configData['applyReturn']['beginDate'])) {
                    $compDate = $configData['applyReturn']['beginDate'];
                    if (strlen($compDate) === 10) {
                        $compDate .= ' 00:00:00';
                    }
                }
                $updateData['completiondate'] = $compDate;
                $changes[] = "completiondate set to {$compDate}";
            }

            if (empty($updateData)) {
                return $this->jsonSuccess('No date fixes needed');
            }

            Capsule::table('nicsrs_sslorders')
                ->where('id', $orderId)
                ->update($updateData);

            $this->logger->log('fix_dates', 'order', $orderId, null, json_encode($changes));

            return $this->jsonSuccess('Dates fixed: ' . implode(', ', $changes), [
                'changes' => $changes,
            ]);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
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
        $orderId = isset($post['order_id']) ? (int) $post['order_id'] : 0;

        switch ($action) {
            case 'refresh_status':
                return $this->refreshStatus($orderId);

            case 'cancel':
                $reason = isset($post['reason']) ? $this->sanitize($post['reason']) : '';
                return $this->cancelOrder($orderId, $reason);

            case 'revoke':
                $reason = isset($post['reason']) ? $this->sanitize($post['reason']) : '';
                return $this->revokeOrder($orderId, $reason);

            case 'reissue':
                return $this->reissueOrder($orderId, $post);

            case 'renew':
                return $this->renewOrder($orderId);

            case 'resend_dcv':
                $domain = isset($post['domain']) ? $this->sanitize($post['domain']) : '';
                return $this->resendDcv($orderId, $domain);
            
            case 'download_cert':
                return $this->downloadCertificate($orderId);

            case 'download_jks':
                return $this->downloadJks($orderId);

            case 'download_pkcs12':
                return $this->downloadPkcs12($orderId);

            default:
                return $this->jsonError('Unknown action');
        }
    }

    /**
     * Refresh order status from API
     * 
     * @param int $orderId Order ID
     * @return string JSON response
     */
    private function refreshStatus(int $orderId): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            if (empty($order->remoteid)) {
                throw new \Exception('No remote certificate ID');
            }

            $result = $this->apiService->collect($order->remoteid);
            
            if ($result['code'] == 1 || $result['code'] == 2) {
                $configData = json_decode($order->configdata, true) ?: [];
                
                // Properly merge API response data
                if (isset($result['data'])) {
                    if (!isset($configData['applyReturn'])) {
                        $configData['applyReturn'] = [];
                    }
                    
                    // Preserve existing data and merge new data
                    $apiData = $result['data'];
                    
                    // Update certificate core data
                    $configData['applyReturn']['certId'] = $order->remoteid;
                    
                    // Vendor tracking fields
                    if (!empty($apiData['vendorId'])) {
                        $configData['applyReturn']['vendorId'] = $apiData['vendorId'];
                    }
                    if (!empty($apiData['vendorCertId'])) {
                        $configData['applyReturn']['vendorCertId'] = $apiData['vendorCertId'];
                    }
                    
                    // Certificate dates
                    if (!empty($apiData['beginDate'])) {
                        $configData['applyReturn']['beginDate'] = $apiData['beginDate'];
                    }
                    if (!empty($apiData['endDate'])) {
                        $configData['applyReturn']['endDate'] = $apiData['endDate'];
                    }
                    if (!empty($apiData['dueDate'])) {
                        $configData['applyReturn']['dueDate'] = $apiData['dueDate'];
                    }
                    if (!empty($apiData['applyTime'])) {
                        $configData['applyReturn']['applyTime'] = $apiData['applyTime'];
                    }
                    
                    // Certificate content
                    if (!empty($apiData['certificate'])) {
                        $configData['applyReturn']['certificate'] = $apiData['certificate'];
                    }
                    if (!empty($apiData['caCertificate'])) {
                        $configData['applyReturn']['caCertificate'] = $apiData['caCertificate'];
                    }
                    if (!empty($apiData['privateKey'])) {
                        $configData['applyReturn']['privateKey'] = $apiData['privateKey'];
                    }
                    
                    // JKS and PKCS12 data (important for download)
                    if (!empty($apiData['jks'])) {
                        $configData['applyReturn']['jks'] = $apiData['jks'];
                    }
                    if (!empty($apiData['jksPass'])) {
                        $configData['applyReturn']['jksPass'] = $apiData['jksPass'];
                    }
                    if (!empty($apiData['pkcs12'])) {
                        $configData['applyReturn']['pkcs12'] = $apiData['pkcs12'];
                    }
                    if (!empty($apiData['pkcsPass'])) {
                        $configData['applyReturn']['pkcsPass'] = $apiData['pkcsPass'];
                    }
                    
                    // DCV validation fields
                    if (!empty($apiData['DCVfileName'])) {
                        $configData['applyReturn']['DCVfileName'] = $apiData['DCVfileName'];
                    }
                    if (!empty($apiData['DCVfileContent'])) {
                        $configData['applyReturn']['DCVfileContent'] = $apiData['DCVfileContent'];
                    }
                    if (!empty($apiData['DCVfilePath'])) {
                        $configData['applyReturn']['DCVfilePath'] = $apiData['DCVfilePath'];
                    }
                    if (!empty($apiData['DCVdnsHost'])) {
                        $configData['applyReturn']['DCVdnsHost'] = $apiData['DCVdnsHost'];
                    }
                    if (!empty($apiData['DCVdnsValue'])) {
                        $configData['applyReturn']['DCVdnsValue'] = $apiData['DCVdnsValue'];
                    }
                    if (!empty($apiData['DCVdnsType'])) {
                        $configData['applyReturn']['DCVdnsType'] = $apiData['DCVdnsType'];
                    }
                    
                    // Process status tracking
                    if (!empty($apiData['application'])) {
                        $configData['applyReturn']['application'] = $apiData['application'];
                    }
                    if (!empty($apiData['dcv'])) {
                        $configData['applyReturn']['dcv'] = $apiData['dcv'];
                    }
                    if (!empty($apiData['issued'])) {
                        $configData['applyReturn']['issued'] = $apiData['issued'];
                    }
                    
                    // Update DCV list if available
                    if (!empty($apiData['dcvList'])) {
                        $configData['domainInfo'] = [];
                        foreach ($apiData['dcvList'] as $dcv) {
                            $configData['domainInfo'][] = [
                                'domainName' => $dcv['domainName'] ?? '',
                                'dcvMethod' => $dcv['dcvMethod'] ?? 'EMAIL',
                                'dcvEmail' => $dcv['dcvEmail'] ?? '',
                                'isVerified' => ($dcv['is_verify'] ?? '') === 'verified',
                                'is_verify' => $dcv['is_verify'] ?? '',
                            ];
                        }
                    }
                }

                // Add last refresh timestamp
                $configData['lastRefresh'] = date('Y-m-d H:i:s');

                // Determine new status
                $newStatus = $order->status;
                if (isset($result['status'])) {
                    $newStatus = strtolower($result['status']);
                } elseif (isset($result['certStatus'])) {
                    $newStatus = strtolower($result['certStatus']);
                }
                
                // Build update data with proper date handling
                $updateData = [
                    'status' => $newStatus,
                    'configdata' => json_encode($configData),
                ];
                
                // Set provisiondate if empty
                if (!$this->isValidDate($order->provisiondate)) {
                    $updateData['provisiondate'] = date('Y-m-d');
                }
                
                // Set completiondate when status is complete
                if ($newStatus === 'complete') {
                    if (!$this->isValidDate($order->completiondate)) {
                        // Use certificate begin date or current date
                        $completionDate = $configData['applyReturn']['beginDate'] ?? date('Y-m-d H:i:s');
                        // Ensure it's datetime format
                        if (strlen($completionDate) === 10) {
                            $completionDate .= ' 00:00:00';
                        }
                        $updateData['completiondate'] = $completionDate;
                    }
                }

                Capsule::table('nicsrs_sslorders')
                    ->where('id', $orderId)
                    ->update($updateData);

                $this->logger->log('refresh_status', 'order', $orderId, $order->status, $newStatus);

                // Prepare response data
                $responseData = [
                    'status' => $newStatus,
                    'old_status' => $order->status,
                    'lastRefresh' => $configData['lastRefresh'],
                ];
                
                // Add cert status info if available
                if (!empty($configData['applyReturn'])) {
                    $responseData['has_certificate'] = !empty($configData['applyReturn']['certificate']);
                    $responseData['has_jks'] = !empty($configData['applyReturn']['jks']);
                    $responseData['has_pkcs12'] = !empty($configData['applyReturn']['pkcs12']);
                    $responseData['vendor_id'] = $configData['applyReturn']['vendorId'] ?? null;
                    $responseData['vendor_cert_id'] = $configData['applyReturn']['vendorCertId'] ?? null;
                }

                return $this->jsonSuccess('Status refreshed successfully', $responseData);
            }

            throw new \Exception($result['msg'] ?? 'API error (code: ' . $result['code'] . ')');
            
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Cancel order
     * 
     * @param int $orderId Order ID
     * @param string $reason Cancellation reason
     * @return string JSON response
     */
    private function cancelOrder(int $orderId, string $reason): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            if (empty($order->remoteid)) {
                throw new \Exception('No remote certificate ID');
            }

            // Check if can be cancelled
            if (in_array($order->status, ['cancelled', 'revoked'])) {
                throw new \Exception('Order is already cancelled/revoked');
            }

            $result = $this->apiService->cancel($order->remoteid, $reason);
            
            if ($result['code'] == 1) {
                Capsule::table('nicsrs_sslorders')
                    ->where('id', $orderId)
                    ->update(['status' => 'cancelled']);

                $this->logger->log('cancel', 'order', $orderId, $order->status, 'cancelled');

                return $this->jsonSuccess('Order cancelled successfully');
            }

            throw new \Exception($result['msg'] ?? 'Cancel failed');
            
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Revoke certificate
     * 
     * @param int $orderId Order ID
     * @param string $reason Revocation reason
     * @return string JSON response
     */
    private function revokeOrder(int $orderId, string $reason): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            if (empty($order->remoteid)) {
                throw new \Exception('No remote certificate ID');
            }

            // Only complete certificates can be revoked
            if ($order->status !== 'complete') {
                throw new \Exception('Only issued certificates can be revoked');
            }

            $result = $this->apiService->revoke($order->remoteid, $reason);
            
            if ($result['code'] == 1) {
                Capsule::table('nicsrs_sslorders')
                    ->where('id', $orderId)
                    ->update(['status' => 'revoked']);

                $this->logger->log('revoke', 'order', $orderId, 'complete', 'revoked');

                return $this->jsonSuccess('Certificate revoked successfully');
            }

            throw new \Exception($result['msg'] ?? 'Revoke failed');
            
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Reissue certificate
     * 
     * @param int $orderId Order ID
     * @param array $data Reissue data (csr, domainInfo)
     * @return string JSON response
     */
    private function reissueOrder(int $orderId, array $data): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            if (empty($order->remoteid)) {
                throw new \Exception('No remote certificate ID');
            }

            // Only complete certificates can be reissued
            if ($order->status !== 'complete') {
                throw new \Exception('Only issued certificates can be reissued');
            }

            // Prepare reissue data
            $reissueData = [];
            
            if (!empty($data['csr'])) {
                $reissueData['csr'] = $data['csr'];
            }
            
            if (!empty($data['domainInfo'])) {
                $domainInfo = json_decode($data['domainInfo'], true);
                if ($domainInfo) {
                    $reissueData['domainInfo'] = $domainInfo;
                }
            }

            $result = $this->apiService->reissue($order->remoteid, $reissueData);
            
            if ($result['code'] == 1) {
                // Update config data
                $configData = json_decode($order->configdata, true) ?: [];
                $configData['replaceTimes'] = ($configData['replaceTimes'] ?? 0) + 1;
                
                if (!empty($reissueData['csr'])) {
                    $configData['csr'] = $reissueData['csr'];
                }
                
                Capsule::table('nicsrs_sslorders')
                    ->where('id', $orderId)
                    ->update([
                        'status' => 'pending',
                        'configdata' => json_encode($configData),
                    ]);

                $this->logger->log('reissue', 'order', $orderId, 'complete', 'pending');

                return $this->jsonSuccess('Reissue request submitted successfully');
            }

            throw new \Exception($result['msg'] ?? 'Reissue failed');
            
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Renew certificate
     * 
     * @param int $orderId Order ID
     * @return string JSON response
     */
    private function renewOrder(int $orderId): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            if (empty($order->remoteid)) {
                throw new \Exception('No remote certificate ID');
            }

            $result = $this->apiService->renew($order->remoteid);
            
            if ($result['code'] == 1) {
                $this->logger->log('renew', 'order', $orderId, null, json_encode([
                    'newCertId' => $result['data']['certId'] ?? null,
                ]));

                return $this->jsonSuccess('Renewal submitted successfully', [
                    'newCertId' => $result['data']['certId'] ?? null,
                ]);
            }

            throw new \Exception($result['msg'] ?? 'Renewal failed');
            
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Resend DCV email
     * 
     * @param int $orderId Order ID
     * @param string $domain Domain name
     * @return string JSON response
     */
    private function resendDcv(int $orderId, string $domain): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            if (empty($order->remoteid)) {
                throw new \Exception('No remote certificate ID');
            }

            if (empty($domain)) {
                throw new \Exception('Domain is required');
            }

            $result = $this->apiService->resendDcv($order->remoteid, $domain);
            
            if ($result['code'] == 1) {
                $this->logger->log('resend_dcv', 'order', $orderId, null, json_encode([
                    'domain' => $domain,
                ]));

                return $this->jsonSuccess('DCV email resent successfully');
            }

            throw new \Exception($result['msg'] ?? 'Resend DCV failed');
            
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Download certificate as ZIP file
     * Creates ZIP with Apache, Nginx, IIS, Tomcat formats
     * 
     * @param int $orderId Order ID
     * @return string JSON response with base64 encoded ZIP
     */
    private function downloadCertificate(int $orderId): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }

            $configData = json_decode($order->configdata, true) ?: [];
            
            // Check if certificate exists
            if (empty($configData['applyReturn']['certificate'])) {
                throw new \Exception('Certificate not yet issued');
            }

            $certificate = $configData['applyReturn']['certificate'];
            $caCertificate = $configData['applyReturn']['caCertificate'] ?? '';
            $privateKey = $configData['applyReturn']['privateKey'] ?? '';
            
            // Get primary domain for filename
            $primaryDomain = 'certificate';
            if (!empty($configData['domainInfo'][0]['domainName'])) {
                $primaryDomain = $configData['domainInfo'][0]['domainName'];
            }
            
            // Create ZIP file
            $result = $this->createCertificateZip($primaryDomain, $certificate, $caCertificate, $privateKey);
            
            if ($result['status'] === 1) {
                $this->logger->log('download_cert', 'order', $orderId, null, 'Certificate downloaded');
                
                return $this->jsonSuccess('Certificate ready', [
                    'content' => $result['data']['content'],
                    'name' => $result['data']['name'],
                ]);
            }
            
            throw new \Exception($result['msg'] ?? 'Failed to create certificate package');

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Download certificate as JKS file
     * 
     * @param int $orderId Order ID
     * @return string JSON response with base64 encoded JKS and password
     */
    private function downloadJks(int $orderId): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }

            $configData = json_decode($order->configdata, true) ?: [];
            
            // Check if JKS exists
            if (empty($configData['applyReturn']['jks'])) {
                throw new \Exception('JKS file not available for this certificate');
            }

            $jksData = $configData['applyReturn']['jks'];
            $jksPass = $configData['applyReturn']['jksPass'] ?? '';
            
            // Get primary domain for filename
            $primaryDomain = 'certificate';
            if (!empty($configData['domainInfo'][0]['domainName'])) {
                $primaryDomain = $configData['domainInfo'][0]['domainName'];
            }
            
            // Sanitize filename
            $filename = str_replace('*', 'wildcard', str_replace('.', '_', $primaryDomain)) . '.jks';
            
            $this->logger->log('download_jks', 'order', $orderId, null, 'JKS downloaded');

            return $this->jsonSuccess('JKS ready', [
                'content' => $jksData, // Already base64 encoded from API
                'name' => $filename,
                'password' => $jksPass,
                'format' => 'jks',
            ]);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Download certificate as PKCS12/PFX file
     * 
     * @param int $orderId Order ID
     * @return string JSON response with base64 encoded PKCS12 and password
     */
    private function downloadPkcs12(int $orderId): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }

            $configData = json_decode($order->configdata, true) ?: [];
            
            // Check if PKCS12 exists
            if (empty($configData['applyReturn']['pkcs12'])) {
                throw new \Exception('PKCS12/PFX file not available for this certificate');
            }

            $pkcs12Data = $configData['applyReturn']['pkcs12'];
            $pkcsPass = $configData['applyReturn']['pkcsPass'] ?? '';
            
            // Get primary domain for filename
            $primaryDomain = 'certificate';
            if (!empty($configData['domainInfo'][0]['domainName'])) {
                $primaryDomain = $configData['domainInfo'][0]['domainName'];
            }
            
            // Sanitize filename
            $filename = str_replace('*', 'wildcard', str_replace('.', '_', $primaryDomain)) . '.pfx';
            
            $this->logger->log('download_pkcs12', 'order', $orderId, null, 'PKCS12 downloaded');

            return $this->jsonSuccess('PKCS12 ready', [
                'content' => $pkcs12Data, // Already base64 encoded from API
                'name' => $filename,
                'password' => $pkcsPass,
                'format' => 'pkcs12',
            ]);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Create ZIP file with certificate in multiple formats
     * Similar to nicsrsFunc::zipCert() in server module
     * 
     * @param string $primaryDomain Primary domain name
     * @param string $certificate Certificate content
     * @param string $caCertificate CA Certificate content
     * @param string $privateKey Private key (if available)
     * @return array Result with status and data
     */
    private function createCertificateZip(string $primaryDomain, string $certificate, string $caCertificate, string $privateKey = ''): array
    {
        // Sanitize filename
        $certFilename = str_replace('*', 'WILDCARD', str_replace('.', '_', $primaryDomain));
        $tempDir = sys_get_temp_dir() . '/nicsrs_cert_' . sha1(time() . $primaryDomain);
        $zipFilename = $certFilename . '.zip';
        $zipPath = $tempDir . '.zip';

        try {
            // Create temp directories
            if (!mkdir($tempDir, 0777, true)) {
                throw new \Exception('Cannot create temp directory');
            }
            mkdir($tempDir . '/Apache', 0777, true);
            mkdir($tempDir . '/Nginx', 0777, true);

            // === Apache Format ===
            // .crt file (certificate only)
            file_put_contents($tempDir . '/Apache/' . $certFilename . '.crt', trim($certificate));
            
            // .ca-bundle file (CA certificate)
            if (!empty($caCertificate)) {
                file_put_contents($tempDir . '/Apache/' . $certFilename . '.ca-bundle', trim($caCertificate));
            }
            
            // .key file (private key if available)
            if (!empty($privateKey)) {
                file_put_contents($tempDir . '/Apache/' . $certFilename . '.key', trim($privateKey));
            }

            // === Nginx Format ===
            // .pem file (certificate + CA certificate combined)
            $pemContent = trim($certificate) . PHP_EOL;
            if (!empty($caCertificate)) {
                $pemContent .= trim($caCertificate);
            }
            file_put_contents($tempDir . '/Nginx/' . $certFilename . '.pem', $pemContent);
            
            // .key file for Nginx
            if (!empty($privateKey)) {
                file_put_contents($tempDir . '/Nginx/' . $certFilename . '.key', trim($privateKey));
            }

            // === Create ZIP ===
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Cannot create ZIP file');
            }

            // Add Apache files
            $zip->addEmptyDir('Apache');
            $zip->addFile($tempDir . '/Apache/' . $certFilename . '.crt', 'Apache/' . $certFilename . '.crt');
            if (!empty($caCertificate)) {
                $zip->addFile($tempDir . '/Apache/' . $certFilename . '.ca-bundle', 'Apache/' . $certFilename . '.ca-bundle');
            }
            if (!empty($privateKey)) {
                $zip->addFile($tempDir . '/Apache/' . $certFilename . '.key', 'Apache/' . $certFilename . '.key');
            }

            // Add Nginx files
            $zip->addEmptyDir('Nginx');
            $zip->addFile($tempDir . '/Nginx/' . $certFilename . '.pem', 'Nginx/' . $certFilename . '.pem');
            if (!empty($privateKey)) {
                $zip->addFile($tempDir . '/Nginx/' . $certFilename . '.key', 'Nginx/' . $certFilename . '.key');
            }

            // Add README
            $readme = "SSL Certificate Package for: {$primaryDomain}\n";
            $readme .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
            $readme .= "=== Apache Installation ===\n";
            $readme .= "SSLCertificateFile /path/to/{$certFilename}.crt\n";
            $readme .= "SSLCertificateKeyFile /path/to/{$certFilename}.key\n";
            $readme .= "SSLCertificateChainFile /path/to/{$certFilename}.ca-bundle\n\n";
            $readme .= "=== Nginx Installation ===\n";
            $readme .= "ssl_certificate /path/to/{$certFilename}.pem;\n";
            $readme .= "ssl_certificate_key /path/to/{$certFilename}.key;\n\n";
            $readme .= "Generated by NicSRS SSL Admin - HVN GROUP (https://hvn.vn)\n";
            
            $zip->addFromString('README.txt', $readme);
            
            $zip->close();

            // Read ZIP content
            $zipContent = file_get_contents($zipPath);
            $base64Content = base64_encode($zipContent);

            // Cleanup
            $this->deleteDirectory($tempDir);
            @unlink($zipPath);

            return [
                'status' => 1,
                'data' => [
                    'content' => $base64Content,
                    'name' => $zipFilename,
                ]
            ];

        } catch (\Exception $e) {
            // Cleanup on error
            $this->deleteDirectory($tempDir);
            @unlink($zipPath);
            
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * Recursively delete directory
     * 
     * @param string $dir Directory path
     * @return bool
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }

}