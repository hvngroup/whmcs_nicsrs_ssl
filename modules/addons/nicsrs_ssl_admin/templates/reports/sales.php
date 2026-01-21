<?php
/**
 * SSL Sales Report Template
 * 
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 * @var string $currencyHelper CurrencyHelper class name
 * @var array $reportData Sales report data
 * @var array $chartData Chart data
 * @var array $productData Sales by product
 * @var array $vendors Available vendors
 * @var array $products Available products
 * @var array $datePresets Date presets
 * @var array $filters Current filters
 */

use NicsrsAdmin\Helper\CurrencyHelper;

$filters = $filters ?? [];
?>

<div class="nicsrs-reports nicsrs-sales-report">
    
    <!-- Page Header -->
    <div class="page-header clearfix">
        <h3 class="pull-left">
            <i class="fa fa-line-chart"></i> SSL Sales Report
        </h3>
        <div class="pull-right">
            <a href="<?php echo $modulelink; ?>&action=reports" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Reports
            </a>
            <a href="<?php echo $modulelink; ?>&action=reports&report=sales&export=csv&<?php echo http_build_query($filters); ?>" 
               class="btn btn-success">
                <i class="fa fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-filter"></i> Filters</h4>
        </div>
        <div class="panel-body">
            <form method="GET" class="form-inline" id="filterForm">
                <input type="hidden" name="module" value="nicsrs_ssl_admin">
                <input type="hidden" name="action" value="reports">
                <input type="hidden" name="report" value="sales">
                
                <!-- Date Presets -->
                <div class="form-group" style="margin-right: 15px;">
                    <label>Quick Select:</label>
                    <select name="preset" class="form-control" id="datePreset" style="margin-left: 5px;">
                        <option value="">Custom Range</option>
                        <?php foreach ($datePresets as $key => $preset): ?>
                        <option value="<?php echo $key; ?>"><?php echo $helper->e($preset['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Date Range -->
                <div class="form-group" style="margin-right: 15px;">
                    <label>From:</label>
                    <input type="date" name="date_from" class="form-control" id="dateFrom"
                           value="<?php echo $helper->e($filters['date_from'] ?? ''); ?>" style="margin-left: 5px;">
                </div>
                
                <div class="form-group" style="margin-right: 15px;">
                    <label>To:</label>
                    <input type="date" name="date_to" class="form-control" id="dateTo"
                           value="<?php echo $helper->e($filters['date_to'] ?? ''); ?>" style="margin-left: 5px;">
                </div>
                
                <!-- Vendor Filter -->
                <div class="form-group" style="margin-right: 15px;">
                    <label>Vendor:</label>
                    <select name="vendor" class="form-control" style="margin-left: 5px;">
                        <option value="">All Vendors</option>
                        <?php foreach ($vendors as $vendor): ?>
                        <option value="<?php echo $helper->e($vendor); ?>" 
                                <?php echo ($filters['vendor'] ?? '') === $vendor ? 'selected' : ''; ?>>
                            <?php echo $helper->e($vendor); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Period for Chart -->
                <div class="form-group" style="margin-right: 15px;">
                    <label>Group By:</label>
                    <select name="period" class="form-control" style="margin-left: 5px;">
                        <option value="day" <?php echo ($filters['period'] ?? '') === 'day' ? 'selected' : ''; ?>>Day</option>
                        <option value="week" <?php echo ($filters['period'] ?? '') === 'week' ? 'selected' : ''; ?>>Week</option>
                        <option value="month" <?php echo ($filters['period'] ?? 'month') === 'month' ? 'selected' : ''; ?>>Month</option>
                        <option value="year" <?php echo ($filters['period'] ?? '') === 'year' ? 'selected' : ''; ?>>Year</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Apply Filters
                </button>
                
                <a href="<?php echo $modulelink; ?>&action=reports&report=sales" class="btn btn-default">
                    <i class="fa fa-times"></i> Clear
                </a>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-primary">
                <div class="panel-body text-center">
                    <h2 class="stat-value"><?php echo CurrencyHelper::formatUsd($reportData['summary']['total_sales']); ?></h2>
                    <p class="stat-label">Total Sales</p>
                    <small class="text-muted"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($reportData['summary']['total_sales'])); ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <h2 class="stat-value"><?php echo number_format($reportData['summary']['order_count']); ?></h2>
                    <p class="stat-label">Total Orders</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <h2 class="stat-value"><?php echo CurrencyHelper::formatUsd($reportData['summary']['avg_order_value']); ?></h2>
                    <p class="stat-label">Avg Order Value</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-warning">
                <div class="panel-body text-center">
                    <h2 class="stat-value"><?php echo CurrencyHelper::formatUsd($reportData['summary']['total_recurring']); ?></h2>
                    <p class="stat-label">Recurring Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Sales Trend Chart -->
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-line-chart"></i> Sales Trend</h4>
                </div>
                <div class="panel-body">
                    <canvas id="salesTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Sales by Product Chart -->
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-pie-chart"></i> Sales by Product</h4>
                </div>
                <div class="panel-body">
                    <canvas id="productPieChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Data Table -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-table"></i> Sales Details
                <span class="badge"><?php echo count($reportData['orders']); ?> orders</span>
            </h4>
        </div>
        <div class="panel-body">
            <?php if (empty($reportData['orders'])): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> No orders found for the selected filters.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="salesTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Vendor</th>
                            <th>Client</th>
                            <th class="text-right">Sale Amount</th>
                            <th>Billing</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['orders'] as $order): ?>
                        <tr>
                            <td>
                                <a href="<?php echo $modulelink; ?>&action=orders&view=detail&id=<?php echo $order['order_id']; ?>">
                                    #<?php echo $order['order_id']; ?>
                                </a>
                            </td>
                            <td><?php echo $helper->formatDate($order['provision_date']); ?></td>
                            <td>
                                <strong><?php echo $helper->e($order['product_name']); ?></strong>
                                <br><small class="text-muted"><?php echo $helper->e($order['product_code']); ?></small>
                            </td>
                            <td>
                                <span class="label label-default"><?php echo $helper->e($order['vendor']); ?></span>
                            </td>
                            <td>
                                <?php echo $helper->e($order['client_name']); ?>
                                <?php if (!empty($order['company_name'])): ?>
                                <br><small class="text-muted"><?php echo $helper->e($order['company_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <strong><?php echo CurrencyHelper::formatUsd($order['sale_amount']); ?></strong>
                            </td>
                            <td><?php echo $helper->e($order['billing_cycle'] ?? 'N/A'); ?></td>
                            <td><?php echo $helper->formatStatus($order['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="active">
                            <th colspan="5" class="text-right">Total:</th>
                            <th class="text-right"><?php echo CurrencyHelper::formatUsd($reportData['summary']['total_sales']); ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sales by Product Table -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-list"></i> Sales by Product</h4>
        </div>
        <div class="panel-body">
            <?php if (empty($productData)): ?>
            <div class="alert alert-info">No product data available.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Vendor</th>
                            <th class="text-center">Orders</th>
                            <th class="text-right">Total Sales</th>
                            <th class="text-right">Avg Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productData as $product): ?>
                        <tr>
                            <td>
                                <strong><?php echo $helper->e($product->product_name ?? $product->product_code); ?></strong>
                                <br><small class="text-muted"><?php echo $helper->e($product->product_code); ?></small>
                            </td>
                            <td><span class="label label-default"><?php echo $helper->e($product->vendor ?? 'Unknown'); ?></span></td>
                            <td class="text-center"><?php echo number_format($product->order_count); ?></td>
                            <td class="text-right"><strong><?php echo CurrencyHelper::formatUsd($product->total_sales); ?></strong></td>
                            <td class="text-right">
                                <?php 
                                $avg = $product->order_count > 0 ? $product->total_sales / $product->order_count : 0;
                                echo CurrencyHelper::formatUsd($avg); 
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
$(document).ready(function() {
    // Date preset handler
    $('#datePreset').on('change', function() {
        var presets = <?php echo json_encode($datePresets); ?>;
        var selected = $(this).val();
        if (selected && presets[selected]) {
            $('#dateFrom').val(presets[selected].from);
            $('#dateTo').val(presets[selected].to);
        }
    });

    // Sales Trend Chart
    var chartLabels = <?php echo json_encode($chartData['labels'] ?? []); ?>;
    var chartSales = <?php echo json_encode($chartData['datasets']['sales'] ?? []); ?>;
    var chartOrders = <?php echo json_encode($chartData['datasets']['orders'] ?? []); ?>;

    if (chartLabels.length > 0) {
        var ctx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Sales (USD)',
                        data: chartSales,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders',
                        data: chartOrders,
                        type: 'line',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Sales (USD)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Orders' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // Product Pie Chart
    var productData = <?php echo json_encode($productData ?? []); ?>;
    if (productData.length > 0) {
        var pieLabels = productData.slice(0, 5).map(function(p) { return p.product_name || p.product_code; });
        var pieValues = productData.slice(0, 5).map(function(p) { return parseFloat(p.total_sales); });
        var pieColors = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF'];

        var ctxPie = document.getElementById('productPieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieValues,
                    backgroundColor: pieColors
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<style>
.nicsrs-sales-report .stat-value {
    font-size: 24px;
    font-weight: 600;
    margin: 0;
}
.nicsrs-sales-report .stat-label {
    margin: 5px 0 0;
    color: #666;
}
.nicsrs-sales-report .panel-primary .panel-body { background: #f0f7ff; }
.nicsrs-sales-report .panel-success .panel-body { background: #f0fff4; }
.nicsrs-sales-report .panel-info .panel-body { background: #f0ffff; }
.nicsrs-sales-report .panel-warning .panel-body { background: #fffcf0; }
</style>