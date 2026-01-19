<?php
/**
 * Dashboard Controller
 * Handles dashboard statistics and overview display
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Controller;

use WHMCS\Database\Capsule;
use NicsrsAdmin\Service\NicsrsApiService;

class DashboardController extends BaseController
{
    /**
     * @var NicsrsApiService API service instance
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
     * Render dashboard page
     * 
     * @param string $action Current action
     * @return void
     */
    public function render(string $action): void
    {
        $data = [
            'statistics' => $this->getStatistics(),
            'recentOrders' => $this->getRecentOrders(10),
            'expiringCertificates' => $this->getExpiringCertificates(30, 10),
            'statusDistribution' => $this->getStatusDistribution(),
            'monthlyOrders' => $this->getMonthlyOrders(6),
            'apiConnected' => $this->testApiConnection(),
        ];

        $this->includeTemplate('dashboard', $data);
    }

    /**
     * Get dashboard statistics
     * 
     * @return array Statistics data
     */
    private function getStatistics(): array
    {
        // Total orders
        $totalOrders = Capsule::table('nicsrs_sslorders')->count();
        
        // Pending orders (awaiting, draft, pending)
        $pendingOrders = Capsule::table('nicsrs_sslorders')
            ->whereIn('status', ['awaiting', 'draft', 'pending'])
            ->count();
        
        // Issued/Complete certificates
        $issuedCerts = Capsule::table('nicsrs_sslorders')
            ->where('status', 'complete')
            ->count();
        
        // Expiring within 30 days
        $expiringCount = $this->countExpiringCertificates(30);

        // Calculate month-over-month trend
        $thisMonthStart = date('Y-m-01');
        $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

        $lastMonthTotal = Capsule::table('nicsrs_sslorders')
            ->whereBetween('provisiondate', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $thisMonthTotal = Capsule::table('nicsrs_sslorders')
            ->where('provisiondate', '>=', $thisMonthStart)
            ->count();

        $trend = 0;
        if ($lastMonthTotal > 0) {
            $trend = round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1);
        }

        // Cancelled orders
        $cancelledOrders = Capsule::table('nicsrs_sslorders')
            ->where('status', 'cancelled')
            ->count();

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'issued_certs' => $issuedCerts,
            'expiring_soon' => $expiringCount,
            'cancelled_orders' => $cancelledOrders,
            'this_month' => $thisMonthTotal,
            'trend' => $trend,
        ];
    }

    /**
     * Count certificates expiring within days
     * 
     * @param int $days Number of days
     * @return int Count
     */
    private function countExpiringCertificates(int $days): int
    {
        $count = 0;
        $orders = Capsule::table('nicsrs_sslorders')
            ->where('status', 'complete')
            ->get();

        $now = time();
        $threshold = strtotime("+{$days} days");

        foreach ($orders as $order) {
            $config = json_decode($order->configdata, true);
            if (isset($config['applyReturn']['endDate'])) {
                $endDate = strtotime($config['applyReturn']['endDate']);
                if ($endDate && $endDate > $now && $endDate <= $threshold) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get recent orders
     * 
     * @param int $limit Number of orders to return
     * @return array Orders
     */
    private function getRecentOrders(int $limit = 10): array
    {
        $orders = Capsule::table('nicsrs_sslorders as o')
            ->leftJoin('tblclients as c', 'o.userid', '=', 'c.id')
            ->select([
                'o.id',
                'o.remoteid',
                'o.certtype',
                'o.status',
                'o.provisiondate',
                'o.configdata',
                'c.firstname',
                'c.lastname',
                'c.companyname',
            ])
            ->orderBy('o.id', 'desc')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($orders as $order) {
            $config = json_decode($order->configdata, true);
            $domain = 'N/A';
            if (isset($config['domainInfo'][0]['domainName'])) {
                $domain = $config['domainInfo'][0]['domainName'];
            }
            
            $clientName = trim($order->firstname . ' ' . $order->lastname);
            if ($order->companyname) {
                $clientName .= " ({$order->companyname})";
            }

            $result[] = [
                'id' => $order->id,
                'remoteid' => $order->remoteid,
                'domain' => $domain,
                'certtype' => $order->certtype,
                'status' => $order->status,
                'provisiondate' => $order->provisiondate,
                'client_name' => $clientName ?: 'Unknown',
            ];
        }

        return $result;
    }

    /**
     * Get certificates expiring within days
     * 
     * @param int $days Number of days
     * @param int $limit Maximum results
     * @return array Expiring certificates
     */
    private function getExpiringCertificates(int $days = 30, int $limit = 10): array
    {
        $result = [];
        $orders = Capsule::table('nicsrs_sslorders as o')
            ->leftJoin('tblclients as c', 'o.userid', '=', 'c.id')
            ->select(['o.*', 'c.firstname', 'c.lastname', 'c.email'])
            ->where('o.status', 'complete')
            ->get();

        $now = time();
        $threshold = strtotime("+{$days} days");

        foreach ($orders as $order) {
            $config = json_decode($order->configdata, true);
            if (isset($config['applyReturn']['endDate'])) {
                $endDate = strtotime($config['applyReturn']['endDate']);
                if ($endDate && $endDate > $now && $endDate <= $threshold) {
                    $domain = isset($config['domainInfo'][0]['domainName']) 
                        ? $config['domainInfo'][0]['domainName'] 
                        : 'N/A';
                    
                    $daysLeft = ceil(($endDate - $now) / 86400);
                    
                    $result[] = [
                        'id' => $order->id,
                        'domain' => $domain,
                        'certtype' => $order->certtype,
                        'end_date' => $config['applyReturn']['endDate'],
                        'days_left' => $daysLeft,
                        'client_name' => trim($order->firstname . ' ' . $order->lastname),
                        'client_email' => $order->email,
                    ];
                }
            }
        }

        // Sort by days left ascending
        usort($result, function ($a, $b) {
            return $a['days_left'] - $b['days_left'];
        });

        return array_slice($result, 0, $limit);
    }

    /**
     * Get status distribution for chart
     * 
     * @return array Status counts
     */
    private function getStatusDistribution(): array
    {
        $statuses = Capsule::table('nicsrs_sslorders')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $result = [];
        foreach ($statuses as $status) {
            $result[$status->status] = (int) $status->count;
        }

        return $result;
    }

    /**
     * Get monthly order counts for chart
     * 
     * @param int $months Number of months to include
     * @return array Monthly data
     */
    private function getMonthlyOrders(int $months = 6): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
            
            $count = Capsule::table('nicsrs_sslorders')
                ->whereBetween('provisiondate', [$monthStart, $monthEnd])
                ->count();
            
            $data[] = [
                'month' => date('M Y', strtotime($monthStart)),
                'short' => date('M', strtotime($monthStart)),
                'count' => $count,
            ];
        }
        
        return $data;
    }

    /**
     * Test API connection
     * 
     * @return bool Connection status
     */
    private function testApiConnection(): bool
    {
        if (empty($this->getApiToken())) {
            return false;
        }
        
        return $this->apiService->testConnection();
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
            case 'refresh_statistics':
                return $this->jsonSuccess('Statistics refreshed', [
                    'data' => $this->getStatistics(),
                ]);

            case 'test_api':
                $connected = $this->testApiConnection();
                if ($connected) {
                    return $this->jsonSuccess('API connection successful');
                }
                return $this->jsonError('API connection failed. Please check your API token.');

            case 'get_expiring':
                $days = isset($post['days']) ? (int) $post['days'] : 30;
                return $this->jsonSuccess('', [
                    'data' => $this->getExpiringCertificates($days, 50),
                ]);

            default:
                return $this->jsonError('Unknown action');
        }
    }
}