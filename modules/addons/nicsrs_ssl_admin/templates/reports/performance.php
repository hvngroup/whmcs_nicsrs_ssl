<?php
/**
 * Product Performance Report Template
 * 
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 * @var string $currencyHelper CurrencyHelper class name
 * @var array $reportData Performance report data
 * @var array $vendors Available vendors
 * @var array $datePresets Date presets
 * @var array $filters Current filters
 */

use NicsrsAdmin\Helper\CurrencyHelper;

$filters = $filters ?? [];
$summary = $reportData['summary'] ?? [];
$products = $reportData['products'] ?? [];
?>

<div class="nicsrs-reports nicsrs-performance-report">
    
    <!-- Page Header -->
    <div class="page-header clearfix">
        <h3 class="pull-left">
            <i class="fa fa-trophy"></i> Product Performance Report
        </h3>
        <div class="pull-right">
            <a href="<?php echo $modulelink; ?>&action=reports" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Reports
            </a>
            <a href="<?php echo $modulelink; ?>&action=reports&report=performance&export=csv&<?php echo http_build_query($filters); ?>" 
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
            <form method="GET" class="form-inline">
                <input type="hidden" name="module" value="nicsrs_ssl_admin">
                <input type="hidden" name="action" value="reports">
                <input type="hidden" name="report" value="performance">
                
                <!-- Date Presets -->
                <div class="form-group" style="margin-right: 15px;">
                    <label>Quick Select:</label>
                    <select name="preset" class="form-control" id="datePreset" style="margin-left: 5px;">
                        <option value="">All Time</option>
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
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Apply
                </button>
                
                <a href="<?php echo $modulelink; ?>&action=reports&report=performance" class="btn btn-default">
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
                    <h4 class="text-muted">Total Products</h4>
                    <h2 class="stat-value"><?php echo number_format($summary['total_products'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Total Orders</h4>
                    <h2 class="stat-value"><?php echo number_format($summary['total_orders'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Total Revenue</h4>
                    <h2 class="stat-value"><?php echo CurrencyHelper::formatUsd($summary['total_revenue'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-warning">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Avg Renewal Rate</h4>
                    <h2 class="stat-value"><?php echo number_format($summary['avg_renewal_rate'] ?? 0, 1); ?>%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Top Products Chart -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-bar-chart"></i> Top Products by Orders</h4>
                </div>
                <div class="panel-body">
                    <canvas id="topProductsChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Revenue Distribution Chart -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-pie-chart"></i> Revenue Distribution</h4>
                </div>
                <div class="panel-body">
                    <canvas id="revenueDistChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Data Table -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-table"></i> Product Performance Details
                <span class="badge"><?php echo count($products); ?> products</span>
            </h4>
        </div>
        <div class="panel-body">
            <?php if (empty($products)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> No product data available for the selected filters.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="performanceTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Vendor</th>
                            <th>Type</th>
                            <th class="text-center">Total Orders</th>
                            <th class="text-center">Active</th>
                            <th class="text-center">Cancelled</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-right">Avg Order</th>
                            <th class="text-center">Renewal Rate</th>
                            <th class="text-center">Completion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $index => $product): ?>
                        <?php 
                        $renewalClass = $product['renewal_rate'] >= 30 ? 'label-success' : ($product['renewal_rate'] >= 15 ? 'label-warning' : 'label-default');
                        $completionClass = $product['completion_rate'] >= 80 ? 'label-success' : ($product['completion_rate'] >= 50 ? 'label-warning' : 'label-danger');
                        ?>
                        <tr>
                            <td>
                                <?php if ($index < 3): ?>
                                <span class="badge badge-rank rank-<?php echo $index + 1; ?>">#<?php echo $index + 1; ?></span>
                                <?php endif; ?>
                                <strong><?php echo $helper->e($product['product_name']); ?></strong>
                                <br><small class="text-muted"><?php echo $helper->e($product['product_code']); ?></small>
                            </td>
                            <td><span class="label label-default"><?php echo $helper->e($product['vendor']); ?></span></td>
                            <td><span class="label label-info"><?php echo $helper->e($product['validation_type']); ?></span></td>
                            <td class="text-center"><strong><?php echo number_format($product['total_orders']); ?></strong></td>
                            <td class="text-center text-success"><?php echo number_format($product['active_count']); ?></td>
                            <td class="text-center text-danger"><?php echo number_format($product['cancelled_count']); ?></td>
                            <td class="text-right"><strong><?php echo CurrencyHelper::formatUsd($product['total_revenue']); ?></strong></td>
                            <td class="text-right"><?php echo CurrencyHelper::formatUsd($product['avg_order_value']); ?></td>
                            <td class="text-center">
                                <span class="label <?php echo $renewalClass; ?>">
                                    <?php echo number_format($product['renewal_rate'], 1); ?>%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="label <?php echo $completionClass; ?>">
                                    <?php echo number_format($product['completion_rate'], 1); ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Performance Metrics Legend -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-info-circle"></i> Metrics Explanation</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fa fa-refresh"></i> Renewal Rate</h5>
                    <p class="text-muted">Percentage of orders from returning customers who purchased the same product before.</p>
                    <ul class="list-unstyled">
                        <li><span class="label label-success">≥30%</span> Excellent</li>
                        <li><span class="label label-warning">15-29%</span> Good</li>
                        <li><span class="label label-default">&lt;15%</span> Needs improvement</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5><i class="fa fa-check-circle"></i> Completion Rate</h5>
                    <p class="text-muted">Percentage of orders successfully completed (certificate issued).</p>
                    <ul class="list-unstyled">
                        <li><span class="label label-success">≥80%</span> Excellent</li>
                        <li><span class="label label-warning">50-79%</span> Average</li>
                        <li><span class="label label-danger">&lt;50%</span> Needs attention</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5><i class="fa fa-dollar"></i> Avg Order Value</h5>
                    <p class="text-muted">Average revenue per order. Higher values indicate premium product sales or upselling success.</p>
                </div>
            </div>
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

    var products = <?php echo json_encode(array_slice($products, 0, 10)); ?>;
    
    if (products.length > 0) {
        // Top Products Bar Chart
        var productLabels = products.map(function(p) { return p.product_name.substring(0, 20); });
        var productOrders = products.map(function(p) { return p.total_orders; });
        
        var ctx1 = document.getElementById('topProductsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: productLabels,
                datasets: [{
                    label: 'Orders',
                    data: productOrders,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(199, 199, 199, 0.7)',
                        'rgba(83, 102, 255, 0.7)',
                        'rgba(255, 99, 255, 0.7)',
                        'rgba(99, 255, 132, 0.7)'
                    ]
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Revenue Distribution Pie Chart
        var revenueLabels = products.slice(0, 5).map(function(p) { return p.product_name.substring(0, 15); });
        var revenueData = products.slice(0, 5).map(function(p) { return p.total_revenue; });
        
        var ctx2 = document.getElementById('revenueDistChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: revenueLabels,
                datasets: [{
                    data: revenueData,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }
});
</script>

<style>
.nicsrs-performance-report .stat-value {
    font-size: 28px;
    font-weight: 600;
    margin: 10px 0 0;
}
.nicsrs-performance-report .badge-rank {
    display: inline-block;
    width: 24px;
    height: 24px;
    line-height: 24px;
    text-align: center;
    border-radius: 50%;
    margin-right: 5px;
    font-size: 12px;
}
.nicsrs-performance-report .rank-1 { background: #FFD700; color: #000; }
.nicsrs-performance-report .rank-2 { background: #C0C0C0; color: #000; }
.nicsrs-performance-report .rank-3 { background: #CD7F32; color: #fff; }
.nicsrs-performance-report .panel-primary .panel-body { background: #f0f7ff; }
.nicsrs-performance-report .panel-success .panel-body { background: #f0fff4; }
.nicsrs-performance-report .panel-info .panel-body { background: #f0ffff; }
.nicsrs-performance-report .panel-warning .panel-body { background: #fffcf0; }
</style>