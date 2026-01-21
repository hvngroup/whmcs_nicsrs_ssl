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
use NicsrsAdmin\Helper\DateHelper;
use NicsrsAdmin\Helper\DcvHelper;

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
            $query->where('o.status', $status);
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
        $statusCounts = Capsule::table('nicsrs_sslorders')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

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
        $applyReturn = $configData['applyReturn'] ?? [];
        
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

        // Extract domain info with DCV helper
        $domains = [];
        if (isset($configData['domainInfo']) && is_array($configData['domainInfo'])) {
            foreach ($configData['domainInfo'] as $domainInfo) {
                $dcvMethod = $domainInfo['dcvMethod'] ?? 'EMAIL';
                $domains[] = [
                    'domain' => $domainInfo['domainName'] ?? 'N/A',
                    'dcv_method' => $dcvMethod,
                    'dcv_method_display' => DcvHelper::getDisplayName($dcvMethod),
                    'dcv_method_icon' => DcvHelper::getIcon($dcvMethod),
                    'dcv_method_color' => DcvHelper::getColor($dcvMethod),
                    'dcv_method_type' => DcvHelper::getType($dcvMethod),
                    'dcv_email' => $domainInfo['dcvEmail'] ?? '',
                    'is_verified' => !empty($domainInfo['isVerified']) || ($domainInfo['is_verify'] ?? '') === 'verified',
                ];
            }
        }

        // Extract certificate info from applyReturn
        $certInfo = [
            'cert_id' => $applyReturn['certId'] ?? $order->remoteid ?? '',
            'vendor_id' => $applyReturn['vendorId'] ?? '',
            'vendor_cert_id' => $applyReturn['vendorCertId'] ?? '',
            'begin_date' => $applyReturn['beginDate'] ?? '',
            'end_date' => $applyReturn['endDate'] ?? '',
            'due_date' => $applyReturn['dueDate'] ?? '',
            'apply_time' => $applyReturn['applyTime'] ?? '',
            'has_certificate' => !empty($applyReturn['certificate']),
            'has_jks' => !empty($applyReturn['jks']),
            'has_pkcs12' => !empty($applyReturn['pkcs12']),
            'jks_password' => $applyReturn['jksPass'] ?? '',
            'pkcs12_password' => $applyReturn['pkcsPass'] ?? '',
            // Process status
            'application_status' => $applyReturn['application']['status'] ?? null,
            'dcv_status' => $applyReturn['dcv']['status'] ?? null,
            'issued_status' => $applyReturn['issued']['status'] ?? null,
        ];

        // DCV Instructions (for pending orders)
        $dcvInstructions = [];
        if (strtolower($order->status) === 'pending' && !empty($applyReturn)) {
            // DNS validation
            if (!empty($applyReturn['DCVdnsHost']) && !empty($applyReturn['DCVdnsValue'])) {
                $dcvInstructions['dns'] = [
                    'type' => $applyReturn['DCVdnsType'] ?? 'CNAME',
                    'host' => $applyReturn['DCVdnsHost'],
                    'value' => $applyReturn['DCVdnsValue'],
                ];
            }
            
            // HTTP validation
            if (!empty($applyReturn['DCVfileName']) && !empty($applyReturn['DCVfileContent'])) {
                $dcvInstructions['http'] = [
                    'filename' => $applyReturn['DCVfileName'],
                    'content' => $applyReturn['DCVfileContent'],
                    'path' => $applyReturn['DCVfilePath'] ?? '',
                ];
            }
        }

        // Cert status (from configdata or result status)
        $certStatus = $configData['certStatus'] ?? strtolower($order->status);

        // Last refresh time
        $lastRefresh = $configData['lastRefresh'] ?? null;

        // CSR availability
        $hasCsr = !empty($configData['csr']);

        $data = [
            'order' => $order,
            'config' => $configData,
            'applyReturn' => $applyReturn,
            'domains' => $domains,
            'certInfo' => $certInfo,
            'certStatus' => $certStatus,
            'lastRefresh' => $lastRefresh,
            'dcvInstructions' => $dcvInstructions,
            'hasCsr' => $hasCsr,
            'activityLogs' => $activityLogs,
            'clientName' => trim($order->firstname . ' ' . $order->lastname),
            'provisiondate' => DateHelper::isValidDate($order->provisiondate) ? $order->provisiondate : null,
            'completiondate' => DateHelper::isValidDate($order->completiondate) ? $order->completiondate : null,
        ];

        $this->includeTemplate('orders/detail', $data);
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
            if (!DateHelper::isValidDate($order->provisiondate)) {
                $provDate = DateHelper::today();
                if (isset($configData['importedAt'])) {
                    $provDate = DateHelper::parseDate($configData['importedAt']) ?? $provDate;
                } elseif (isset($configData['linkedAt'])) {
                    $provDate = DateHelper::parseDate($configData['linkedAt']) ?? $provDate;
                }
                $updateData['provisiondate'] = $provDate;
                $changes[] = "provisiondate set to {$provDate}";
            }

            // Fix completiondate for complete orders
            if ($order->status === 'complete' && !DateHelper::isValidDate($order->completiondate)) {
                $compDate = DateHelper::now();
                if (isset($configData['applyReturn']['beginDate'])) {
                    $compDate = DateHelper::parseDateTime($configData['applyReturn']['beginDate']) ?? $compDate;
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
            
            case 'fix_dates':
                return $this->fixOrderDates($orderId);

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
            
            if ($result['code'] != 1 && $result['code'] != 2) {
                throw new \Exception($result['msg'] ?? 'API Error');
            }

            $configData = json_decode($order->configdata, true) ?: [];
            $data = $result['data'] ?? [];
            
            // Initialize applyReturn if not exists
            if (!isset($configData['applyReturn'])) {
                $configData['applyReturn'] = [];
            }
            
            // Update applyReturn with all API response fields
            $configData['applyReturn'] = array_merge($configData['applyReturn'], [
                'certId' => $order->remoteid,
                
                // Dates (API returns full datetime: Y-m-d H:i:s)
                'beginDate' => $data['beginDate'] ?? $configData['applyReturn']['beginDate'] ?? null,
                'endDate' => $data['endDate'] ?? $configData['applyReturn']['endDate'] ?? null,
                'dueDate' => $data['dueDate'] ?? $configData['applyReturn']['dueDate'] ?? null,
                'applyTime' => $data['applyTime'] ?? $configData['applyReturn']['applyTime'] ?? null,
                
                // Vendor tracking
                'vendorId' => $data['vendorId'] ?? $configData['applyReturn']['vendorId'] ?? null,
                'vendorCertId' => $data['vendorCertId'] ?? $configData['applyReturn']['vendorCertId'] ?? null,
                
                // Certificate data
                'certificate' => $data['certificate'] ?? $configData['applyReturn']['certificate'] ?? null,
                'caCertificate' => $data['caCertificate'] ?? $configData['applyReturn']['caCertificate'] ?? null,
                'certPath' => $data['certPath'] ?? $configData['applyReturn']['certPath'] ?? null,
                
                // Pre-formatted certificates (Base64 encoded)
                'jks' => $data['jks'] ?? $configData['applyReturn']['jks'] ?? null,
                'pkcs12' => $data['pkcs12'] ?? $configData['applyReturn']['pkcs12'] ?? null,
                'jksPass' => $data['jksPass'] ?? $configData['applyReturn']['jksPass'] ?? null,
                'pkcsPass' => $data['pkcsPass'] ?? $configData['applyReturn']['pkcsPass'] ?? null,
                
                // DCV validation fields
                'DCVfileName' => $data['DCVfileName'] ?? $configData['applyReturn']['DCVfileName'] ?? null,
                'DCVfileContent' => $data['DCVfileContent'] ?? $configData['applyReturn']['DCVfileContent'] ?? null,
                'DCVfilePath' => $data['DCVfilePath'] ?? $configData['applyReturn']['DCVfilePath'] ?? null,
                'DCVdnsHost' => $data['DCVdnsHost'] ?? $configData['applyReturn']['DCVdnsHost'] ?? null,
                'DCVdnsValue' => $data['DCVdnsValue'] ?? $configData['applyReturn']['DCVdnsValue'] ?? null,
                'DCVdnsType' => $data['DCVdnsType'] ?? $configData['applyReturn']['DCVdnsType'] ?? null,
                
                // Process status tracking
                'application' => $data['application'] ?? $configData['applyReturn']['application'] ?? null,
                'dcv' => $data['dcv'] ?? $configData['applyReturn']['dcv'] ?? null,
                'issued' => $data['issued'] ?? $configData['applyReturn']['issued'] ?? null,
                
                // Store dcvList in applyReturn as well for reference
                'dcvList' => $data['dcvList'] ?? $configData['applyReturn']['dcvList'] ?? [],
            ]);
            
            // Update domainInfo from dcvList
            if (!empty($data['dcvList'])) {
                $configData['domainInfo'] = [];
                foreach ($data['dcvList'] as $dcv) {
                    $configData['domainInfo'][] = [
                        'domainName' => $dcv['domainName'] ?? '',
                        'dcvMethod' => $dcv['dcvMethod'] ?? 'EMAIL',
                        'dcvEmail' => $dcv['dcvEmail'] ?? '',
                        'isVerified' => ($dcv['is_verify'] ?? '') === 'verified',
                        'is_verify' => $dcv['is_verify'] ?? '',
                    ];
                }
            }
            
            // Store applyParams if provided
            if (!empty($data['applyParams'])) {
                $configData['applyParams'] = $data['applyParams'];
            }
            
            // Update privateKey if provided (from applyParams or direct)
            if (!empty($data['rsaPrivateKey'])) {
                $configData['privateKey'] = $data['rsaPrivateKey'];
            }

            // Update last refresh timestamp
            $configData['lastRefresh'] = DateHelper::now();
            
            // Store certStatus separately for easy access
            $certStatus = strtolower($result['certStatus'] ?? $result['status'] ?? 'pending');
            $configData['certStatus'] = $certStatus;

            // Determine new order status
            $newStatus = strtolower($result['status'] ?? $order->status);
            
            // Build update data
            $updateData = [
                'status' => $newStatus,
                'configdata' => json_encode($configData),
            ];
            
            // Set provisiondate if empty/invalid
            if (!DateHelper::isValidDate($order->provisiondate)) {
                $updateData['provisiondate'] = DateHelper::today();
            }
            
            // Set completiondate when status is complete
            if ($newStatus === 'complete' && !DateHelper::isValidDate($order->completiondate)) {
                $completionDate = $data['beginDate'] ?? DateHelper::now();
                // Ensure datetime format
                if (strlen($completionDate) === 10) {
                    $completionDate .= ' 00:00:00';
                }
                $updateData['completiondate'] = $completionDate;
            }
            
            // Update database
            Capsule::table('nicsrs_sslorders')
                ->where('id', $orderId)
                ->update($updateData);

            // Log activity
            $this->logger->log('refresh_status', 'order', $orderId, $order->status, $newStatus);

            // Prepare response data
            $hasJks = !empty($configData['applyReturn']['jks']);
            $hasPkcs12 = !empty($configData['applyReturn']['pkcs12']);
            $hasCertificate = !empty($configData['applyReturn']['certificate']);
            
            return $this->jsonSuccess('Status refreshed successfully', [
                'status' => $newStatus,
                'cert_status' => $certStatus,
                'begin_date' => DateHelper::formatDisplay($configData['applyReturn']['beginDate'] ?? null),
                'end_date' => DateHelper::formatDisplay($configData['applyReturn']['endDate'] ?? null),
                'due_date' => DateHelper::formatDateOnly($configData['applyReturn']['dueDate'] ?? null),
                'vendor_id' => $configData['applyReturn']['vendorId'] ?? null,
                'vendor_cert_id' => $configData['applyReturn']['vendorCertId'] ?? null,
                'has_certificate' => $hasCertificate,
                'has_jks' => $hasJks,
                'has_pkcs12' => $hasPkcs12,
                'last_refresh' => $configData['lastRefresh'],
            ]);

        } catch (\Exception $e) {
            return $this->jsonError('Refresh failed: ' . $e->getMessage());
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
     * Download certificate in specified format
     * 
     * @param int $orderId Order ID
     * @return string JSON response with download data
     */
    private function downloadCertificate(int $orderId): string
    {
        try {
            $order = Capsule::table('nicsrs_sslorders')->find($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            $configData = json_decode($order->configdata, true) ?: [];
            $applyReturn = $configData['applyReturn'] ?? [];
            
            $format = isset($_POST['format']) ? strtolower($_POST['format']) : 'zip';
            
            // Get primary domain for filename
            $primaryDomain = 'certificate';
            if (!empty($configData['domainInfo'][0]['domainName'])) {
                $primaryDomain = preg_replace('/[^a-zA-Z0-9.-]/', '_', $configData['domainInfo'][0]['domainName']);
            }
            
            switch ($format) {
                case 'jks':
                    return $this->downloadJks($applyReturn, $primaryDomain);
                
                case 'pkcs12':
                case 'pfx':
                case 'p12':
                    return $this->downloadPkcs12($applyReturn, $primaryDomain);
                
                case 'pem':
                    return $this->downloadPem($applyReturn, $configData, $primaryDomain);
                
                case 'zip':
                default:
                    return $this->downloadZip($applyReturn, $configData, $primaryDomain);
            }
            
        } catch (\Exception $e) {
            return $this->jsonError('Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Download JKS format
     */
    private function downloadJks(array $applyReturn, string $domain): string
    {
        if (empty($applyReturn['jks'])) {
            throw new \Exception('JKS format not available for this certificate');
        }
        
        return $this->jsonSuccess('Download ready', [
            'content' => $applyReturn['jks'], // Already Base64
            'filename' => $domain . '.jks',
            'mime' => 'application/octet-stream',
            'password' => $applyReturn['jksPass'] ?? null,
            'format' => 'jks',
        ]);
    }

    /**
     * Download PKCS12 format
     */
    private function downloadPkcs12(array $applyReturn, string $domain): string
    {
        if (empty($applyReturn['pkcs12'])) {
            throw new \Exception('PKCS12 format not available for this certificate');
        }
        
        return $this->jsonSuccess('Download ready', [
            'content' => $applyReturn['pkcs12'], // Already Base64
            'filename' => $domain . '.pfx',
            'mime' => 'application/x-pkcs12',
            'password' => $applyReturn['pkcsPass'] ?? null,
            'format' => 'pkcs12',
        ]);
    }

    /**
     * Download PEM format (combined certificate + CA bundle)
     */
    private function downloadPem(array $applyReturn, array $configData, string $domain): string
    {
        if (empty($applyReturn['certificate'])) {
            throw new \Exception('Certificate not available');
        }
        
        $content = $applyReturn['certificate'];
        
        if (!empty($applyReturn['caCertificate'])) {
            $content .= "\n" . $applyReturn['caCertificate'];
        }
        
        return $this->jsonSuccess('Download ready', [
            'content' => base64_encode($content),
            'filename' => $domain . '.pem',
            'mime' => 'application/x-pem-file',
            'format' => 'pem',
        ]);
    }

    /**
     * Download all formats as ZIP
     */
    private function downloadZip(array $applyReturn, array $configData, string $domain): string
    {
        if (empty($applyReturn['certificate'])) {
            throw new \Exception('Certificate not available');
        }
        
        $zip = new \ZipArchive();
        $tmpFile = tempnam(sys_get_temp_dir(), 'cert_');
        
        if ($zip->open($tmpFile, \ZipArchive::CREATE) !== true) {
            throw new \Exception('Failed to create ZIP file');
        }
        
        // PEM format files
        if (!empty($applyReturn['certificate'])) {
            $zip->addFromString("{$domain}.crt", $applyReturn['certificate']);
        }
        if (!empty($applyReturn['caCertificate'])) {
            $zip->addFromString("{$domain}.ca-bundle", $applyReturn['caCertificate']);
        }
        if (!empty($configData['privateKey'])) {
            $zip->addFromString("{$domain}.key", $configData['privateKey']);
        } elseif (!empty($applyReturn['privateKey'])) {
            $zip->addFromString("{$domain}.key", $applyReturn['privateKey']);
        }
        
        // Combined PEM (for Nginx)
        $combinedPem = $applyReturn['certificate'];
        if (!empty($applyReturn['caCertificate'])) {
            $combinedPem .= "\n" . $applyReturn['caCertificate'];
        }
        $zip->addFromString("{$domain}_fullchain.pem", $combinedPem);
        
        // JKS format (if available)
        if (!empty($applyReturn['jks'])) {
            $zip->addFromString("{$domain}.jks", base64_decode($applyReturn['jks']));
        }
        
        // PKCS12 format (if available)
        if (!empty($applyReturn['pkcs12'])) {
            $zip->addFromString("{$domain}.pfx", base64_decode($applyReturn['pkcs12']));
        }
        
        // Passwords file
        $passwords = [];
        $passwords[] = "Certificate Download - {$domain}";
        $passwords[] = "Generated: " . date('Y-m-d H:i:s');
        $passwords[] = "";
        
        if (!empty($applyReturn['jksPass'])) {
            $passwords[] = "JKS Password: " . $applyReturn['jksPass'];
        }
        if (!empty($applyReturn['pkcsPass'])) {
            $passwords[] = "PKCS12/PFX Password: " . $applyReturn['pkcsPass'];
        }
        
        if (count($passwords) > 3) {
            $zip->addFromString("passwords.txt", implode("\n", $passwords));
        }
        
        // Readme file
        $readme = $this->generateReadme($domain, $applyReturn);
        $zip->addFromString("README.txt", $readme);
        
        $zip->close();
        
        $content = file_get_contents($tmpFile);
        unlink($tmpFile);
        
        $this->logger->log('download_cert', 'order', 0, null, "Downloaded {$domain} certificates (ZIP)");
        
        return $this->jsonSuccess('Download ready', [
            'content' => base64_encode($content),
            'filename' => "{$domain}_certificates.zip",
            'mime' => 'application/zip',
            'format' => 'zip',
            'jks_password' => $applyReturn['jksPass'] ?? null,
            'pkcs12_password' => $applyReturn['pkcsPass'] ?? null,
        ]);
    }

    /**
     * Generate README for certificate download
     */
    private function generateReadme(string $domain, array $applyReturn): string
    {
        $readme = [];
        $readme[] = "SSL Certificate Package - {$domain}";
        $readme[] = str_repeat("=", 50);
        $readme[] = "";
        $readme[] = "Generated: " . date('Y-m-d H:i:s');
        $readme[] = "";
        $readme[] = "Files included:";
        $readme[] = "  - {$domain}.crt        : Server certificate";
        $readme[] = "  - {$domain}.ca-bundle  : CA bundle (intermediate certificates)";
        $readme[] = "  - {$domain}.key        : Private key";
        $readme[] = "  - {$domain}_fullchain.pem : Combined certificate (for Nginx)";
        
        if (!empty($applyReturn['jks'])) {
            $readme[] = "  - {$domain}.jks        : Java KeyStore format";
        }
        if (!empty($applyReturn['pkcs12'])) {
            $readme[] = "  - {$domain}.pfx        : PKCS#12 format (for IIS/Windows)";
        }
        
        $readme[] = "";
        $readme[] = "Installation Instructions:";
        $readme[] = "--------------------------";
        $readme[] = "";
        $readme[] = "Apache:";
        $readme[] = "  SSLCertificateFile /path/to/{$domain}.crt";
        $readme[] = "  SSLCertificateKeyFile /path/to/{$domain}.key";
        $readme[] = "  SSLCertificateChainFile /path/to/{$domain}.ca-bundle";
        $readme[] = "";
        $readme[] = "Nginx:";
        $readme[] = "  ssl_certificate /path/to/{$domain}_fullchain.pem;";
        $readme[] = "  ssl_certificate_key /path/to/{$domain}.key;";
        $readme[] = "";
        
        if (!empty($applyReturn['jksPass'])) {
            $readme[] = "Tomcat (JKS):";
            $readme[] = "  keystoreFile=\"/path/to/{$domain}.jks\"";
            $readme[] = "  keystorePass=\"{$applyReturn['jksPass']}\"";
            $readme[] = "";
        }
        
        if (!empty($applyReturn['pkcsPass'])) {
            $readme[] = "IIS (PFX):";
            $readme[] = "  Import {$domain}.pfx file";
            $readme[] = "  Password: {$applyReturn['pkcsPass']}";
            $readme[] = "";
        }
        
        $readme[] = "---";
        $readme[] = "Generated by NicSRS SSL Admin Module";
        $readme[] = "https://hvn.vn";
        
        return implode("\n", $readme);
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