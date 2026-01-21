<?php
/**
 * Report Service
 * Handles report data queries and calculations
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Service;

use WHMCS\Database\Capsule;
use NicsrsAdmin\Helper\CurrencyHelper;

class ReportService
{
    /**
     * Billing cycle to months mapping
     */
    const BILLING_CYCLE_MONTHS = [
        'Monthly' => 1,
        'Quarterly' => 3,
        'Semi-Annually' => 6,
        'Annually' => 12,
        'Biennially' => 24,
        'Triennially' => 36,
    ];

    /**
     * Billing cycle to price key mapping
     */
    const BILLING_CYCLE_PRICE_KEY = [
        'Monthly' => 'price001',
        'Quarterly' => 'price003',
        'Semi-Annually' => 'price006',
        'Annually' => 'price012',
        'Biennially' => 'price024',
        'Triennially' => 'price036',
    ];

    // =========================================================================
    // SALES REPORT METHODS
    // =========================================================================

    /**
     * Get SSL Sales data with filters
     * 
     * @param array $filters ['date_from', 'date_to', 'product_code', 'vendor', 'status']
     * @return array Sales data with orders and summary
     */
    public function getSalesReport(array $filters = []): array
    {
        $query = $this->buildBaseQuery();

        // Apply filters
        $this->applyDateFilters($query, $filters);
        $this->applyProductFilters($query, $filters);

        if (!empty($filters['status'])) {
            $query->where('o.status', $filters['status']);
        }

        $orders = $query->orderBy('o.provisiondate', 'desc')->get();

        // Calculate totals
        $totalSales = 0;
        $totalRecurring = 0;
        $orderCount = count($orders);

        $processedOrders = [];
        foreach ($orders as $order) {
            $saleAmount = (float) ($order->sale_amount ?? 0);
            $recurringAmount = (float) ($order->recurring_amount ?? 0);
            
            $totalSales += $saleAmount;
            $totalRecurring += $recurringAmount;

            $processedOrders[] = [
                'order_id' => $order->order_id,
                'serviceid' => $order->serviceid,
                'product_code' => $order->product_code,
                'product_name' => $order->product_name ?? $order->product_code,
                'vendor' => $order->vendor ?? 'Unknown',
                'validation_type' => $order->validation_type ?? 'dv',
                'status' => $order->status,
                'provision_date' => $order->provisiondate,
                'completion_date' => $order->completiondate,
                'service_date' => $order->service_date,
                'sale_amount' => $saleAmount,
                'recurring_amount' => $recurringAmount,
                'billing_cycle' => $order->billingcycle,
                'client_name' => trim(($order->firstname ?? '') . ' ' . ($order->lastname ?? '')),
                'company_name' => $order->companyname ?? '',
            ];
        }

        return [
            'orders' => $processedOrders,
            'summary' => [
                'total_sales' => $totalSales,
                'total_recurring' => $totalRecurring,
                'order_count' => $orderCount,
                'avg_order_value' => $orderCount > 0 ? $totalSales / $orderCount : 0,
            ],
            'filters' => $filters,
        ];
    }

    /**
     * Get sales grouped by period (day/week/month/year)
     * 
     * @param string $period 'day', 'week', 'month', 'year'
     * @param array $filters Date filters
     * @return array Grouped sales data
     */
    public function getSalesByPeriod(string $period = 'month', array $filters = []): array
    {
        $dateFormat = match($period) {
            'day' => '%Y-%m-%d',
            'week' => '%x-W%v',  // ISO year-week
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m'
        };

        $labelFormat = match($period) {
            'day' => '%d/%m',
            'week' => 'W%v/%Y',
            'month' => '%m/%Y',
            'year' => '%Y',
            default => '%m/%Y'
        };

        $query = Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->selectRaw("DATE_FORMAT(o.provisiondate, '{$dateFormat}') as period")
            ->selectRaw("DATE_FORMAT(o.provisiondate, '{$labelFormat}') as period_label")
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(COALESCE(h.firstpaymentamount, 0)) as total_sales')
            ->whereNotNull('o.provisiondate')
            ->where('o.provisiondate', '!=', '0000-00-00')
            ->groupBy('period', 'period_label')
            ->orderBy('period', 'asc');

        $this->applyDateFilters($query, $filters, 'o.provisiondate');

        $results = $query->get()->toArray();

        // Convert to chart-friendly format
        $labels = [];
        $salesData = [];
        $orderData = [];

        foreach ($results as $row) {
            $labels[] = $row->period_label;
            $salesData[] = (float) $row->total_sales;
            $orderData[] = (int) $row->order_count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                'sales' => $salesData,
                'orders' => $orderData,
            ],
            'raw' => $results,
        ];
    }

    /**
     * Get sales grouped by product
     * 
     * @param array $filters Date filters
     * @return array Sales by product
     */
    public function getSalesByProduct(array $filters = []): array
    {
        $query = Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->leftJoin('mod_nicsrs_products as np', 'p.configoption1', '=', 'np.product_code')
            ->select([
                'o.certtype as product_code',
                'p.name as product_name',
                'np.vendor',
            ])
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(COALESCE(h.firstpaymentamount, 0)) as total_sales')
            ->groupBy('o.certtype', 'p.name', 'np.vendor')
            ->orderBy('total_sales', 'desc');

        $this->applyDateFilters($query, $filters, 'o.provisiondate');

        return $query->get()->toArray();
    }

    // =========================================================================
    // PROFIT REPORT METHODS
    // =========================================================================

    /**
     * Get SSL Profit data
     * Profit = WHMCS Sale Amount - NicSRS Cost
     * 
     * @param array $filters Date and product filters
     * @return array Profit data with orders and summary
     */
    public function getProfitReport(array $filters = []): array
    {
        $query = $this->buildBaseQuery();
        $query->addSelect('np.price_data');

        $this->applyDateFilters($query, $filters);
        $this->applyProductFilters($query, $filters);

        // Only include completed orders for profit calculation
        if (empty($filters['include_all_status'])) {
            $query->whereIn('o.status', ['complete', 'Complete']);
        }

        $orders = $query->orderBy('o.provisiondate', 'desc')->get();

        $results = [];
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;

        foreach ($orders as $order) {
            $saleAmount = (float) ($order->sale_amount ?? 0);
            $costUsd = $this->calculateNicsrsCost($order->price_data, $order->billingcycle);
            $profit = $saleAmount - $costUsd;
            $profitMargin = $saleAmount > 0 ? ($profit / $saleAmount) * 100 : 0;

            $results[] = [
                'order_id' => $order->order_id,
                'serviceid' => $order->serviceid,
                'product_code' => $order->product_code,
                'product_name' => $order->product_name ?? $order->product_code,
                'vendor' => $order->vendor ?? 'Unknown',
                'status' => $order->status,
                'provision_date' => $order->provisiondate,
                'billing_cycle' => $order->billingcycle,
                'client_name' => trim(($order->firstname ?? '') . ' ' . ($order->lastname ?? '')),
                'sale_amount_usd' => $saleAmount,
                'cost_usd' => $costUsd,
                'profit_usd' => $profit,
                'profit_margin' => round($profitMargin, 2),
            ];

            $totalRevenue += $saleAmount;
            $totalCost += $costUsd;
            $totalProfit += $profit;
        }

        $overallMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        return [
            'orders' => $results,
            'summary' => [
                'total_revenue_usd' => $totalRevenue,
                'total_cost_usd' => $totalCost,
                'total_profit_usd' => $totalProfit,
                'profit_margin' => round($overallMargin, 2),
                'order_count' => count($orders),
            ],
            'filters' => $filters,
        ];
    }

    /**
     * Get profit grouped by period
     * 
     * @param string $period 'month', 'quarter', 'year'
     * @param array $filters Date filters
     * @return array Profit by period
     */
    public function getProfitByPeriod(string $period = 'month', array $filters = []): array
    {
        $dateFormat = match($period) {
            'month' => '%Y-%m',
            'quarter' => "CONCAT(YEAR(o.provisiondate), '-Q', QUARTER(o.provisiondate))",
            'year' => '%Y',
            default => '%Y-%m'
        };

        // We need to calculate profit per order, so get all orders first
        $profitData = $this->getProfitReport($filters);
        
        // Group by period
        $grouped = [];
        foreach ($profitData['orders'] as $order) {
            $date = $order['provision_date'];
            if (!$date || $date === '0000-00-00') continue;

            $periodKey = match($period) {
                'month' => date('Y-m', strtotime($date)),
                'quarter' => date('Y', strtotime($date)) . '-Q' . ceil(date('n', strtotime($date)) / 3),
                'year' => date('Y', strtotime($date)),
                default => date('Y-m', strtotime($date))
            };

            if (!isset($grouped[$periodKey])) {
                $grouped[$periodKey] = [
                    'period' => $periodKey,
                    'revenue' => 0,
                    'cost' => 0,
                    'profit' => 0,
                    'order_count' => 0,
                ];
            }

            $grouped[$periodKey]['revenue'] += $order['sale_amount_usd'];
            $grouped[$periodKey]['cost'] += $order['cost_usd'];
            $grouped[$periodKey]['profit'] += $order['profit_usd'];
            $grouped[$periodKey]['order_count']++;
        }

        // Sort by period
        ksort($grouped);

        // Convert to chart format
        $labels = [];
        $revenueData = [];
        $costData = [];
        $profitChartData = [];

        foreach ($grouped as $period => $data) {
            $labels[] = $period;
            $revenueData[] = $data['revenue'];
            $costData[] = $data['cost'];
            $profitChartData[] = $data['profit'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                'revenue' => $revenueData,
                'cost' => $costData,
                'profit' => $profitChartData,
            ],
            'raw' => array_values($grouped),
        ];
    }

    /**
     * Calculate NicSRS cost from price_data JSON
     * 
     * @param string|null $priceDataJson JSON price data
     * @param string|null $billingCycle Billing cycle
     * @return float Cost in USD
     */
    public function calculateNicsrsCost(?string $priceDataJson, ?string $billingCycle): float
    {
        if (!$priceDataJson) {
            return 0;
        }

        $priceData = json_decode($priceDataJson, true);
        if (!$priceData || !isset($priceData['basePrice'])) {
            return 0;
        }

        // Get price key based on billing cycle
        $periodKey = self::BILLING_CYCLE_PRICE_KEY[$billingCycle] ?? 'price012';

        // Try exact match first
        if (isset($priceData['basePrice'][$periodKey])) {
            return (float) $priceData['basePrice'][$periodKey];
        }

        // Fallback to annual price
        if (isset($priceData['basePrice']['price012'])) {
            return (float) $priceData['basePrice']['price012'];
        }

        // Try first available price
        if (!empty($priceData['basePrice'])) {
            return (float) reset($priceData['basePrice']);
        }

        return 0;
    }

    // =========================================================================
    // PRODUCT PERFORMANCE METHODS
    // =========================================================================

    /**
     * Get Product Performance data
     * 
     * @param array $filters Date filters
     * @return array Performance data by product
     */
    public function getProductPerformance(array $filters = []): array
    {
        $query = Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->leftJoin('mod_nicsrs_products as np', 'p.configoption1', '=', 'np.product_code')
            ->select([
                'o.certtype as product_code',
                'p.name as product_name',
                'np.vendor',
                'np.validation_type',
            ])
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(COALESCE(h.firstpaymentamount, 0)) as total_revenue')
            ->selectRaw("SUM(CASE WHEN o.status IN ('complete', 'Complete') THEN 1 ELSE 0 END) as active_count")
            ->selectRaw("SUM(CASE WHEN o.status IN ('cancelled', 'Cancelled') THEN 1 ELSE 0 END) as cancelled_count")
            ->selectRaw("SUM(CASE WHEN o.status IN ('pending', 'Pending', 'Awaiting Configuration') THEN 1 ELSE 0 END) as pending_count")
            ->groupBy('o.certtype', 'p.name', 'np.vendor', 'np.validation_type')
            ->orderBy('total_orders', 'desc');

        $this->applyDateFilters($query, $filters, 'o.provisiondate');

        $products = $query->get();

        $results = [];
        foreach ($products as $product) {
            $renewalRate = $this->calculateRenewalRate($product->product_code, $filters);
            $avgOrderValue = $product->total_orders > 0 
                ? $product->total_revenue / $product->total_orders 
                : 0;
            
            $completionRate = $product->total_orders > 0
                ? ($product->active_count / $product->total_orders) * 100
                : 0;

            $results[] = [
                'product_code' => $product->product_code,
                'product_name' => $product->product_name ?? $product->product_code,
                'vendor' => $product->vendor ?? 'Unknown',
                'validation_type' => strtoupper($product->validation_type ?? 'DV'),
                'total_orders' => (int) $product->total_orders,
                'total_revenue' => (float) $product->total_revenue,
                'active_count' => (int) $product->active_count,
                'cancelled_count' => (int) $product->cancelled_count,
                'pending_count' => (int) $product->pending_count,
                'avg_order_value' => round($avgOrderValue, 2),
                'renewal_rate' => round($renewalRate, 2),
                'completion_rate' => round($completionRate, 2),
            ];
        }

        // Calculate totals
        $totalOrders = array_sum(array_column($results, 'total_orders'));
        $totalRevenue = array_sum(array_column($results, 'total_revenue'));

        return [
            'products' => $results,
            'summary' => [
                'total_products' => count($results),
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'avg_renewal_rate' => count($results) > 0 
                    ? round(array_sum(array_column($results, 'renewal_rate')) / count($results), 2)
                    : 0,
            ],
            'filters' => $filters,
        ];
    }

    /**
     * Calculate renewal rate for a product
     * Renewal = orders where client had previous order for same product
     * 
     * @param string $productCode Product code
     * @param array $filters Date filters
     * @return float Renewal rate percentage
     */
    public function calculateRenewalRate(string $productCode, array $filters = []): float
    {
        // Count total orders for this product
        $totalQuery = Capsule::table('nicsrs_sslorders')
            ->where('certtype', $productCode);
        
        $this->applyDateFilters($totalQuery, $filters, 'provisiondate');
        $totalOrders = $totalQuery->count();

        if ($totalOrders <= 1) {
            return 0;
        }

        // Count orders that are renewals (same user ordered this product before)
        $renewalQuery = Capsule::table('nicsrs_sslorders as o1')
            ->whereExists(function ($subquery) {
                $subquery->select(Capsule::raw(1))
                    ->from('nicsrs_sslorders as o2')
                    ->whereRaw('o1.userid = o2.userid')
                    ->whereRaw('o1.certtype = o2.certtype')
                    ->whereRaw('o1.id > o2.id');
            })
            ->where('o1.certtype', $productCode);
        
        $this->applyDateFilters($renewalQuery, $filters, 'o1.provisiondate');
        $renewals = $renewalQuery->count();

        return ($renewals / $totalOrders) * 100;
    }

    // =========================================================================
    // REVENUE BY BRAND METHODS
    // =========================================================================

    /**
     * Get Revenue by Brand (Vendor)
     * 
     * @param array $filters Date filters
     * @return array Revenue data by brand
     */
    public function getRevenueByBrand(array $filters = []): array
    {
        $query = Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->leftJoin('mod_nicsrs_products as np', 'p.configoption1', '=', 'np.product_code')
            ->selectRaw('COALESCE(np.vendor, "Unknown") as vendor')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(COALESCE(h.firstpaymentamount, 0)) as total_revenue')
            ->selectRaw('AVG(COALESCE(h.firstpaymentamount, 0)) as avg_order_value')
            ->selectRaw("SUM(CASE WHEN o.status IN ('complete', 'Complete') THEN 1 ELSE 0 END) as active_count")
            ->groupBy('vendor')
            ->orderBy('total_revenue', 'desc');

        $this->applyDateFilters($query, $filters, 'o.provisiondate');

        $brands = $query->get();

        // Calculate percentages
        $totalRevenue = $brands->sum('total_revenue');
        $totalOrders = $brands->sum('order_count');

        $results = [];
        foreach ($brands as $brand) {
            $revenuePercentage = $totalRevenue > 0 
                ? ($brand->total_revenue / $totalRevenue) * 100 
                : 0;
            
            $orderPercentage = $totalOrders > 0
                ? ($brand->order_count / $totalOrders) * 100
                : 0;

            $results[] = [
                'vendor' => $brand->vendor,
                'order_count' => (int) $brand->order_count,
                'total_revenue' => (float) $brand->total_revenue,
                'avg_order_value' => round((float) $brand->avg_order_value, 2),
                'active_count' => (int) $brand->active_count,
                'revenue_percentage' => round($revenuePercentage, 2),
                'order_percentage' => round($orderPercentage, 2),
            ];
        }

        return [
            'brands' => $results,
            'summary' => [
                'total_brands' => count($results),
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
            ],
            'chart_data' => [
                'labels' => array_column($results, 'vendor'),
                'revenue' => array_column($results, 'total_revenue'),
                'orders' => array_column($results, 'order_count'),
                'percentages' => array_column($results, 'revenue_percentage'),
            ],
            'filters' => $filters,
        ];
    }

    /**
     * Get revenue by brand over time
     * 
     * @param string $period 'month', 'quarter', 'year'
     * @param array $filters Date filters
     * @return array Revenue trends by brand
     */
    public function getRevenueByBrandOverTime(string $period = 'month', array $filters = []): array
    {
        $dateFormat = match($period) {
            'month' => '%Y-%m',
            'quarter' => "CONCAT(YEAR(o.provisiondate), '-Q', QUARTER(o.provisiondate))",
            'year' => '%Y',
            default => '%Y-%m'
        };

        $query = Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->leftJoin('mod_nicsrs_products as np', 'p.configoption1', '=', 'np.product_code')
            ->selectRaw("DATE_FORMAT(o.provisiondate, '{$dateFormat}') as period")
            ->selectRaw('COALESCE(np.vendor, "Unknown") as vendor')
            ->selectRaw('SUM(COALESCE(h.firstpaymentamount, 0)) as total_revenue')
            ->selectRaw('COUNT(*) as order_count')
            ->whereNotNull('o.provisiondate')
            ->where('o.provisiondate', '!=', '0000-00-00')
            ->groupBy('period', 'vendor')
            ->orderBy('period', 'asc');

        $this->applyDateFilters($query, $filters, 'o.provisiondate');

        $results = $query->get();

        // Pivot data for chart
        $periods = [];
        $brandData = [];

        foreach ($results as $row) {
            if (!in_array($row->period, $periods)) {
                $periods[] = $row->period;
            }
            
            if (!isset($brandData[$row->vendor])) {
                $brandData[$row->vendor] = [];
            }
            
            $brandData[$row->vendor][$row->period] = (float) $row->total_revenue;
        }

        // Fill missing periods with 0
        $datasets = [];
        foreach ($brandData as $vendor => $data) {
            $values = [];
            foreach ($periods as $period) {
                $values[] = $data[$period] ?? 0;
            }
            $datasets[$vendor] = $values;
        }

        return [
            'labels' => $periods,
            'datasets' => $datasets,
            'raw' => $results->toArray(),
        ];
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Build base query for reports
     * 
     * @return \Illuminate\Database\Query\Builder
     */
    private function buildBaseQuery()
    {
        return Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->leftJoin('mod_nicsrs_products as np', 'p.configoption1', '=', 'np.product_code')
            ->leftJoin('tblclients as c', 'o.userid', '=', 'c.id')
            ->select([
                'o.id as order_id',
                'o.serviceid',
                'o.certtype as product_code',
                'o.status',
                'o.provisiondate',
                'o.completiondate',
                'p.name as product_name',
                'np.vendor',
                'np.validation_type',
                'h.firstpaymentamount as sale_amount',
                'h.amount as recurring_amount',
                'h.billingcycle',
                'h.regdate as service_date',
                'c.firstname',
                'c.lastname',
                'c.companyname',
            ]);
    }

    /**
     * Apply date filters to query
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $filters Filters with date_from and date_to
     * @param string $column Date column name
     */
    private function applyDateFilters($query, array $filters, string $column = 'o.provisiondate'): void
    {
        if (!empty($filters['date_from'])) {
            $query->where($column, '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where($column, '<=', $filters['date_to']);
        }
    }

    /**
     * Apply product filters to query
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $filters Filters with product_code and vendor
     */
    private function applyProductFilters($query, array $filters): void
    {
        if (!empty($filters['product_code'])) {
            $query->where('o.certtype', $filters['product_code']);
        }
        if (!empty($filters['vendor'])) {
            $query->where('np.vendor', $filters['vendor']);
        }
    }

    /**
     * Get available vendors for filter dropdown
     * 
     * @return array List of vendors
     */
    public function getAvailableVendors(): array
    {
        return Capsule::table('mod_nicsrs_products')
            ->distinct()
            ->whereNotNull('vendor')
            ->orderBy('vendor')
            ->pluck('vendor')
            ->toArray();
    }

    /**
     * Get available products for filter dropdown
     * 
     * @return array List of products
     */
    public function getAvailableProducts(): array
    {
        return Capsule::table('mod_nicsrs_products')
            ->select(['product_code', 'product_name', 'vendor'])
            ->orderBy('product_name')
            ->get()
            ->toArray();
    }

    /**
     * Get date range presets
     * 
     * @return array Preset date ranges
     */
    public function getDatePresets(): array
    {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));
        $thisYear = date('Y-01-01');
        $lastYear = date('Y-01-01', strtotime('-1 year'));
        $lastYearEnd = date('Y-12-31', strtotime('-1 year'));

        return [
            'today' => [
                'label' => 'Today',
                'from' => $today,
                'to' => $today,
            ],
            'this_month' => [
                'label' => 'This Month',
                'from' => $thisMonth,
                'to' => $today,
            ],
            'last_month' => [
                'label' => 'Last Month',
                'from' => $lastMonth,
                'to' => $lastMonthEnd,
            ],
            'this_year' => [
                'label' => 'This Year',
                'from' => $thisYear,
                'to' => $today,
            ],
            'last_year' => [
                'label' => 'Last Year',
                'from' => $lastYear,
                'to' => $lastYearEnd,
            ],
            'last_30_days' => [
                'label' => 'Last 30 Days',
                'from' => date('Y-m-d', strtotime('-30 days')),
                'to' => $today,
            ],
            'last_90_days' => [
                'label' => 'Last 90 Days',
                'from' => date('Y-m-d', strtotime('-90 days')),
                'to' => $today,
            ],
        ];
    }
}