# NicSRS SSL Admin - Reports Module Implementation Plan v1.3.0

## 📋 Project Overview

| Item | Details |
|------|---------|
| **Feature** | Sales & Revenue Reports |
| **Version** | 1.3.0 |
| **Module** | nicsrs_ssl_admin |
| **Author** | HVN GROUP |
| **Estimated Duration** | 2 weeks |
| **Priority** | P1 (Recommended) |

---

## 🎯 Scope Definition

### Reports to Implement

| # | Report Name | Description | Priority |
|---|-------------|-------------|----------|
| 1 | SSL Sales Report | Doanh thu theo sản phẩm SSL, thời gian | P0 |
| 2 | SSL Profit Report | Lợi nhuận = Doanh thu - Chi phí NicSRS (với quy đổi USD-VND) | P0 |
| 3 | Product Performance | Sản phẩm bán chạy, tỷ lệ renewal, so sánh | P1 |
| 4 | Revenue by Brand | Doanh thu theo hãng (Sectigo, DigiCert, GoGetSSL...) | P1 |

---

## 🗄️ Database Schema Updates

### New Settings Keys (mod_nicsrs_settings)

```sql
-- Currency Exchange Rate
INSERT INTO mod_nicsrs_settings (setting_key, setting_value, setting_type) VALUES
('usd_vnd_rate', '25000', 'number'),
('currency_display', 'both', 'string'), -- 'usd', 'vnd', 'both'
('auto_update_rate', '0', 'boolean'),
('rate_last_updated', NULL, 'datetime');
```

### Data Sources for Reports

| Data Point | Source Table | Fields |
|------------|--------------|--------|
| **Sales Revenue** | `tblhosting` | `firstpaymentamount`, `amount`, `billingcycle` |
| **Order Date** | `nicsrs_sslorders` | `provisiondate`, `completiondate` |
| **Product Info** | `tblproducts` | `name`, `configoption1` (product_code) |
| **Cost (NicSRS)** | `mod_nicsrs_products` | `price_data` (JSON với basePrice) |
| **Vendor/Brand** | `mod_nicsrs_products` | `vendor` |
| **Client Info** | `tblclients` | `id`, `firstname`, `lastname` |
| **Renewal Status** | `tblhosting` | `domainstatus`, `nextduedate` |

---

## 📁 File Structure

```
modules/addons/nicsrs_ssl_admin/
├── lib/
│   ├── Controller/
│   │   └── ReportController.php          # NEW: Report controller
│   ├── Service/
│   │   └── ReportService.php             # NEW: Report business logic
│   └── Helper/
│       └── CurrencyHelper.php            # NEW: Currency conversion
├── templates/
│   └── reports/
│       ├── index.php                     # Reports dashboard/menu
│       ├── sales.php                     # SSL Sales Report
│       ├── profit.php                    # SSL Profit Report
│       ├── performance.php               # Product Performance Report
│       └── brand.php                     # Revenue by Brand Report
└── assets/
    └── js/
        └── reports.js                    # Charts & export functions
```

---

## 🔧 Technical Implementation

### Phase 1: Foundation (Days 1-3)

#### Task 1.1: Settings Extension - Currency Exchange
**File:** `lib/Controller/SettingsController.php`

```php
// Add to settings form
$currencySettings = [
    'usd_vnd_rate' => [
        'label' => 'USD to VND Exchange Rate',
        'type' => 'number',
        'default' => 25000,
        'help' => 'Rate used for profit calculation (e.g., 25000)'
    ],
    'currency_display' => [
        'label' => 'Currency Display',
        'type' => 'select',
        'options' => ['usd' => 'USD Only', 'vnd' => 'VND Only', 'both' => 'Both'],
        'default' => 'both'
    ]
];
```

**TODO:**
- [ ] Add currency settings section to settings.php template
- [ ] Add save handler for new settings
- [ ] Add "Update Rate from API" button (optional - có thể dùng exchangerate-api.com)

---

#### Task 1.2: CurrencyHelper Class
**File:** `lib/Helper/CurrencyHelper.php`

```php
<?php
namespace NicsrsAdmin\Helper;

use WHMCS\Database\Capsule;

class CurrencyHelper
{
    /**
     * Get USD to VND exchange rate from settings
     */
    public static function getUsdVndRate(): float
    {
        $rate = Capsule::table('mod_nicsrs_settings')
            ->where('setting_key', 'usd_vnd_rate')
            ->value('setting_value');
        
        return $rate ? (float) $rate : 25000.00;
    }

    /**
     * Convert USD to VND
     */
    public static function usdToVnd(float $usd): float
    {
        return $usd * self::getUsdVndRate();
    }

    /**
     * Format currency for display
     * @param float $amount
     * @param string $currency 'usd', 'vnd', 'both'
     */
    public static function format(float $amount, string $currency = 'usd'): string
    {
        switch ($currency) {
            case 'vnd':
                return number_format(self::usdToVnd($amount), 0, ',', '.') . ' VND';
            case 'both':
                return '$' . number_format($amount, 2) . 
                       ' (' . number_format(self::usdToVnd($amount), 0, ',', '.') . ' VND)';
            default:
                return '$' . number_format($amount, 2);
        }
    }

    /**
     * Get display mode from settings
     */
    public static function getDisplayMode(): string
    {
        $mode = Capsule::table('mod_nicsrs_settings')
            ->where('setting_key', 'currency_display')
            ->value('setting_value');
        
        return $mode ?: 'both';
    }
}
```

---

#### Task 1.3: ReportController Base
**File:** `lib/Controller/ReportController.php`

```php
<?php
namespace NicsrsAdmin\Controller;

use WHMCS\Database\Capsule;
use NicsrsAdmin\Service\ReportService;
use NicsrsAdmin\Helper\ViewHelper;
use NicsrsAdmin\Helper\CurrencyHelper;

class ReportController extends BaseController
{
    private ReportService $reportService;

    public function __construct(string $modulelink)
    {
        parent::__construct($modulelink);
        $this->reportService = new ReportService();
    }

    /**
     * Main dispatch method
     */
    public function dispatch(): void
    {
        $report = $_GET['report'] ?? 'index';
        
        switch ($report) {
            case 'sales':
                $this->renderSalesReport();
                break;
            case 'profit':
                $this->renderProfitReport();
                break;
            case 'performance':
                $this->renderPerformanceReport();
                break;
            case 'brand':
                $this->renderBrandReport();
                break;
            default:
                $this->renderIndex();
        }
    }

    /**
     * Reports dashboard/menu
     */
    private function renderIndex(): void
    {
        $helper = new ViewHelper();
        include __DIR__ . '/../../templates/reports/index.php';
    }

    // ... other render methods
}
```

---

### Phase 2: SSL Sales Report (Days 4-6)

#### Task 2.1: ReportService - Sales Methods
**File:** `lib/Service/ReportService.php`

```php
<?php
namespace NicsrsAdmin\Service;

use WHMCS\Database\Capsule;

class ReportService
{
    /**
     * Get SSL Sales data with filters
     * 
     * @param array $filters ['date_from', 'date_to', 'product_code', 'vendor', 'status']
     * @return array
     */
    public function getSalesReport(array $filters = []): array
    {
        $query = Capsule::table('nicsrs_sslorders as o')
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

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->where('o.provisiondate', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('o.provisiondate', '<=', $filters['date_to']);
        }
        if (!empty($filters['product_code'])) {
            $query->where('o.certtype', $filters['product_code']);
        }
        if (!empty($filters['vendor'])) {
            $query->where('np.vendor', $filters['vendor']);
        }
        if (!empty($filters['status'])) {
            $query->where('o.status', $filters['status']);
        }

        $orders = $query->orderBy('o.provisiondate', 'desc')->get();

        // Calculate totals
        $totalSales = 0;
        $totalRecurring = 0;
        $orderCount = count($orders);

        foreach ($orders as $order) {
            $totalSales += (float) $order->sale_amount;
            $totalRecurring += (float) $order->recurring_amount;
        }

        return [
            'orders' => $orders,
            'summary' => [
                'total_sales' => $totalSales,
                'total_recurring' => $totalRecurring,
                'order_count' => $orderCount,
                'avg_order_value' => $orderCount > 0 ? $totalSales / $orderCount : 0,
            ]
        ];
    }

    /**
     * Get sales grouped by period (day/week/month/year)
     */
    public function getSalesByPeriod(string $period, array $filters = []): array
    {
        $dateFormat = match($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',  // Year-Week number
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m'
        };

        $query = Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->selectRaw("DATE_FORMAT(o.provisiondate, '{$dateFormat}') as period")
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(h.firstpaymentamount) as total_sales')
            ->groupBy('period')
            ->orderBy('period', 'asc');

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->where('o.provisiondate', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('o.provisiondate', '<=', $filters['date_to']);
        }

        return $query->get()->toArray();
    }

    /**
     * Get sales by product
     */
    public function getSalesByProduct(array $filters = []): array
    {
        $query = Capsule::table('nicsrs_sslorders as o')
            ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
            ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
            ->select([
                'o.certtype as product_code',
                'p.name as product_name',
            ])
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(h.firstpaymentamount) as total_sales')
            ->groupBy('o.certtype', 'p.name')
            ->orderBy('total_sales', 'desc');

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->where('o.provisiondate', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('o.provisiondate', '<=', $filters['date_to']);
        }

        return $query->get()->toArray();
    }
}
```

---

#### Task 2.2: Sales Report Template
**File:** `templates/reports/sales.php`

**UI Layout:**
```
┌────────────────────────────────────────────────────────────────────┐
│  SSL Sales Report                                    [Export CSV]  │
├────────────────────────────────────────────────────────────────────┤
│  Filters:                                                          │
│  Date From: [____] To: [____]  Product: [All ▼]  Vendor: [All ▼]  │
│  [Apply Filters]                                                   │
├────────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │ Total Sales │ │ Order Count │ │ Avg Order   │ │ Recurring   │  │
│  │ $12,500.00  │ │     45      │ │  $277.78    │ │ $4,500/mo   │  │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
├────────────────────────────────────────────────────────────────────┤
│  [Chart: Sales Trend by Month - Line/Bar Chart]                    │
├────────────────────────────────────────────────────────────────────┤
│  # │ Date    │ Product      │ Client   │ Amount  │ Status        │
│  ──┼─────────┼──────────────┼──────────┼─────────┼───────────────│
│  1 │ 2025-01 │ Positive SSL │ John Doe │ $49.00  │ ● Complete    │
│  2 │ 2025-01 │ Wildcard SSL │ Jane S.  │ $199.00 │ ● Complete    │
└────────────────────────────────────────────────────────────────────┘
```

---

### Phase 3: SSL Profit Report (Days 7-9)

#### Task 3.1: ReportService - Profit Methods

```php
/**
 * Get SSL Profit data
 * Profit = WHMCS Sale Amount - NicSRS Cost (from mod_nicsrs_products)
 */
public function getProfitReport(array $filters = []): array
{
    $query = Capsule::table('nicsrs_sslorders as o')
        ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
        ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
        ->leftJoin('mod_nicsrs_products as np', 'p.configoption1', '=', 'np.product_code')
        ->select([
            'o.id as order_id',
            'o.certtype as product_code',
            'o.status',
            'o.provisiondate',
            'p.name as product_name',
            'np.vendor',
            'np.price_data',  // JSON containing NicSRS cost
            'h.firstpaymentamount as sale_amount',
            'h.billingcycle',
        ]);

    // Apply filters...
    $orders = $query->get();

    $results = [];
    $totalRevenue = 0;
    $totalCost = 0;
    $totalProfit = 0;

    foreach ($orders as $order) {
        $saleAmount = (float) $order->sale_amount;
        $costUsd = $this->calculateNicsrsCost($order->price_data, $order->billingcycle);
        $profit = $saleAmount - $costUsd;

        $results[] = [
            'order_id' => $order->order_id,
            'product_code' => $order->product_code,
            'product_name' => $order->product_name,
            'vendor' => $order->vendor,
            'date' => $order->provisiondate,
            'status' => $order->status,
            'sale_amount_usd' => $saleAmount,
            'cost_usd' => $costUsd,
            'profit_usd' => $profit,
            'profit_margin' => $saleAmount > 0 ? ($profit / $saleAmount) * 100 : 0,
        ];

        $totalRevenue += $saleAmount;
        $totalCost += $costUsd;
        $totalProfit += $profit;
    }

    return [
        'orders' => $results,
        'summary' => [
            'total_revenue_usd' => $totalRevenue,
            'total_cost_usd' => $totalCost,
            'total_profit_usd' => $totalProfit,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
            'order_count' => count($orders),
        ]
    ];
}

/**
 * Calculate NicSRS cost from price_data JSON
 */
private function calculateNicsrsCost(?string $priceDataJson, ?string $billingCycle): float
{
    if (!$priceDataJson) return 0;

    $priceData = json_decode($priceDataJson, true);
    if (!$priceData || !isset($priceData['basePrice'])) return 0;

    // Map billing cycle to period key
    $periodMap = [
        'Annually' => 'price012',
        'Biennially' => 'price024',
        'Triennially' => 'price036',
    ];

    $periodKey = $periodMap[$billingCycle] ?? 'price012';

    return isset($priceData['basePrice'][$periodKey]) 
        ? (float) $priceData['basePrice'][$periodKey] 
        : 0;
}
```

---

#### Task 3.2: Profit Report Template
**File:** `templates/reports/profit.php`

**UI Layout:**
```
┌────────────────────────────────────────────────────────────────────┐
│  SSL Profit Report                                   [Export CSV]  │
├────────────────────────────────────────────────────────────────────┤
│  Exchange Rate: 1 USD = [25,000 ▼] VND  [Update Rate]              │
│  Display: ○ USD  ○ VND  ● Both                                     │
├────────────────────────────────────────────────────────────────────┤
│  ┌───────────────┐ ┌───────────────┐ ┌───────────────┐             │
│  │ Total Revenue │ │  Total Cost   │ │ Total Profit  │             │
│  │ $12,500 USD   │ │  $8,750 USD   │ │  $3,750 USD   │             │
│  │ 312.5M VND    │ │  218.7M VND   │ │  93.75M VND   │             │
│  │               │ │               │ │ Margin: 30%   │             │
│  └───────────────┘ └───────────────┘ └───────────────┘             │
├────────────────────────────────────────────────────────────────────┤
│  # │ Product    │ Sale(USD) │ Cost(USD) │ Profit  │ Margin │       │
│  ──┼────────────┼───────────┼───────────┼─────────┼────────│       │
│  1 │ Positive   │ $49.00    │ $35.00    │ $14.00  │ 28.6%  │       │
│  2 │ Wildcard   │ $199.00   │ $150.00   │ $49.00  │ 24.6%  │       │
└────────────────────────────────────────────────────────────────────┘
```

---

### Phase 4: Product Performance Report (Days 10-11)

#### Task 4.1: ReportService - Performance Methods

```php
/**
 * Get Product Performance data
 */
public function getProductPerformance(array $filters = []): array
{
    // Get all products with sales stats
    $products = Capsule::table('nicsrs_sslorders as o')
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
        ->selectRaw('SUM(h.firstpaymentamount) as total_revenue')
        ->selectRaw('SUM(CASE WHEN o.status = "complete" THEN 1 ELSE 0 END) as active_count')
        ->selectRaw('SUM(CASE WHEN o.status = "cancelled" THEN 1 ELSE 0 END) as cancelled_count')
        ->groupBy('o.certtype', 'p.name', 'np.vendor', 'np.validation_type')
        ->orderBy('total_orders', 'desc')
        ->get();

    // Calculate renewal rate for each product
    foreach ($products as &$product) {
        $renewalRate = $this->calculateRenewalRate($product->product_code, $filters);
        $product->renewal_rate = $renewalRate;
        $product->avg_order_value = $product->total_orders > 0 
            ? $product->total_revenue / $product->total_orders 
            : 0;
    }

    return $products->toArray();
}

/**
 * Calculate renewal rate for a product
 * Renewal = orders where client had previous order for same product
 */
private function calculateRenewalRate(string $productCode, array $filters = []): float
{
    // Count total orders
    $totalOrders = Capsule::table('nicsrs_sslorders')
        ->where('certtype', $productCode)
        ->count();

    if ($totalOrders == 0) return 0;

    // Count renewals (orders where same user had previous order)
    $renewals = Capsule::table('nicsrs_sslorders as o1')
        ->join('nicsrs_sslorders as o2', function($join) {
            $join->on('o1.userid', '=', 'o2.userid')
                 ->on('o1.certtype', '=', 'o2.certtype')
                 ->whereRaw('o1.id > o2.id');
        })
        ->where('o1.certtype', $productCode)
        ->distinct('o1.id')
        ->count('o1.id');

    return ($renewals / $totalOrders) * 100;
}
```

---

### Phase 5: Revenue by Brand Report (Days 12-13)

#### Task 5.1: ReportService - Brand Revenue Methods

```php
/**
 * Get Revenue by Brand (Vendor)
 */
public function getRevenueByBrand(array $filters = []): array
{
    $query = Capsule::table('nicsrs_sslorders as o')
        ->join('tblhosting as h', 'o.serviceid', '=', 'h.id')
        ->join('tblproducts as p', 'h.packageid', '=', 'p.id')
        ->leftJoin('mod_nicsrs_products as np', 'p.configoption1', '=', 'np.product_code')
        ->select('np.vendor')
        ->selectRaw('COUNT(*) as order_count')
        ->selectRaw('SUM(h.firstpaymentamount) as total_revenue')
        ->selectRaw('AVG(h.firstpaymentamount) as avg_order_value')
        ->selectRaw('SUM(CASE WHEN o.status = "complete" THEN 1 ELSE 0 END) as active_count')
        ->whereNotNull('np.vendor')
        ->groupBy('np.vendor')
        ->orderBy('total_revenue', 'desc');

    // Apply date filters
    if (!empty($filters['date_from'])) {
        $query->where('o.provisiondate', '>=', $filters['date_from']);
    }
    if (!empty($filters['date_to'])) {
        $query->where('o.provisiondate', '<=', $filters['date_to']);
    }

    $brands = $query->get();

    // Calculate percentages
    $totalRevenue = $brands->sum('total_revenue');
    
    foreach ($brands as &$brand) {
        $brand->revenue_percentage = $totalRevenue > 0 
            ? ($brand->total_revenue / $totalRevenue) * 100 
            : 0;
    }

    return [
        'brands' => $brands->toArray(),
        'total_revenue' => $totalRevenue,
        'total_orders' => $brands->sum('order_count'),
    ];
}
```

---

## 📊 UI Components

### Charts (using Chart.js)

| Report | Chart Type | Purpose |
|--------|------------|---------|
| Sales Report | Line/Bar Chart | Sales trend over time |
| Profit Report | Stacked Bar Chart | Revenue vs Cost comparison |
| Product Performance | Horizontal Bar | Top products ranking |
| Revenue by Brand | Pie/Donut Chart | Brand market share |

---

## 🔄 Export Functions

### CSV Export
```php
/**
 * Export report to CSV
 */
public function exportCsv(string $reportType, array $filters = []): void
{
    $filename = "nicsrs_ssl_{$reportType}_" . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Get data based on report type
    $data = match($reportType) {
        'sales' => $this->reportService->getSalesReport($filters),
        'profit' => $this->reportService->getProfitReport($filters),
        'performance' => $this->reportService->getProductPerformance($filters),
        'brand' => $this->reportService->getRevenueByBrand($filters),
    };
    
    // Write headers and data...
    fclose($output);
    exit;
}
```

---

## 🛡️ Security Considerations

1. **Admin Permission Check** - Verify admin access before showing reports
2. **Input Sanitization** - Sanitize all filter inputs
3. **CSRF Protection** - Token validation for export actions
4. **Data Access Control** - Reports only show data admin is authorized to view

---

## ✅ TODO Checklist

### Phase 1: Foundation (Days 1-3)
- [ ] Add currency settings to Settings page (USD-VND rate input)
- [ ] Create `CurrencyHelper.php` class
- [ ] Create `ReportController.php` base
- [ ] Create `ReportService.php` base
- [ ] Add "Reports" menu item to navigation
- [ ] Create `templates/reports/index.php` (reports dashboard)

### Phase 2: SSL Sales Report (Days 4-6)
- [ ] Implement `getSalesReport()` method
- [ ] Implement `getSalesByPeriod()` method
- [ ] Implement `getSalesByProduct()` method
- [ ] Create `templates/reports/sales.php` template
- [ ] Add date range filters
- [ ] Add product/vendor filters
- [ ] Integrate Chart.js for sales trend chart
- [ ] Add CSV export for sales report

### Phase 3: SSL Profit Report (Days 7-9)
- [ ] Implement `getProfitReport()` method
- [ ] Implement `calculateNicsrsCost()` method
- [ ] Create `templates/reports/profit.php` template
- [ ] Add USD/VND toggle display
- [ ] Calculate profit margin per order
- [ ] Add summary cards (Revenue, Cost, Profit)
- [ ] Add CSV export for profit report

### Phase 4: Product Performance (Days 10-11)
- [ ] Implement `getProductPerformance()` method
- [ ] Implement `calculateRenewalRate()` method
- [ ] Create `templates/reports/performance.php` template
- [ ] Add top products ranking chart
- [ ] Show renewal rate by product
- [ ] Add comparison with previous period (optional)

### Phase 5: Revenue by Brand (Days 12-13)
- [ ] Implement `getRevenueByBrand()` method
- [ ] Create `templates/reports/brand.php` template
- [ ] Add pie/donut chart for brand distribution
- [ ] Show brand comparison table
- [ ] Add CSV export

### Phase 6: Testing & Polish (Day 14)
- [ ] Test all reports with real data
- [ ] Verify currency conversion accuracy
- [ ] Test CSV export (UTF-8, Excel compatibility)
- [ ] Performance testing with large datasets
- [ ] Update documentation

---

## 📅 Timeline

| Week | Phase | Deliverables |
|------|-------|--------------|
| Week 1 | Foundation + Sales + Profit | Settings, CurrencyHelper, Sales Report, Profit Report |
| Week 2 | Performance + Brand + Polish | Performance Report, Brand Report, Testing, Export |

**Total Estimated Hours**: ~60h

---

## 📝 Notes

### Data Accuracy
- Revenue data comes from `tblhosting.firstpaymentamount` (actual payment received)
- Cost data comes from `mod_nicsrs_products.price_data` (NicSRS API pricing)
- Profit = Revenue - Cost (simplified calculation)

### Currency Handling
- NicSRS costs are in USD
- WHMCS revenue depends on default currency setting
- USD-VND rate must be manually maintained or integrated with exchange rate API

### Performance
- Large datasets may need pagination
- Consider caching for frequently accessed reports
- Index `provisiondate` column for better query performance

---

**Document Version**: 1.0  
**Created**: 2025-01-21  
**Author**: HVN GROUP