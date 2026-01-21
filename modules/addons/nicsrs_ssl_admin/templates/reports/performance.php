<?php
/**
 * Product Performance Report Template - UPDATED
 * 
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 * @var array $reportData Performance report data
 * @var array $vendors Available vendors
 * @var array $datePresets Date presets
 * @var array $filters Current filters
 */

use NicsrsAdmin\Helper\CurrencyHelper;

$filters = $filters ?? [];
$products = $reportData['products'] ?? [];
$summary = $reportData['summary'] ?? [];
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

    <!-- Filters -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-filter"></i> Filters</h4>
        </div>
        <div class="panel-body">
            <form method="get" class="form-inline">
                <input type="hidden" name="module" value="nicsrs_ssl_admin">
                <input type="hidden" name="action" value="reports">
                <input type="hidden" name="report" value="performance">
                
                <div class="form-group" style="margin-right: 15px;">
                    <label>Date From:</label>
                    <input type="date" class="form-control" name="date_from" 
                           value="<?php echo $helper->e($filters['date_from'] ?? ''); ?>">
                </div>
                
                <div class="form-group" style="margin-right: 15px;">
                    <label>To:</label>
                    <input type="date" class="form-control" name="date_to" 
                           value="<?php echo $helper->e($filters['date_to'] ?? ''); ?>">
                </div>
                
                <div class="form-group" style="margin-right: 15px;">
                    <label>Vendor:</label>
                    <select class="form-control" name="vendor">
                        <option value="">All Vendors</option>
                        <?php foreach ($vendors ?? [] as $vendor): ?>
                        <option value="<?php echo $helper->e($vendor); ?>" 
                                <?php echo ($filters['vendor'] ?? '') === $vendor ? 'selected' : ''; ?>>
                            <?php echo $helper->e($vendor); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Apply
                </button>
                <a href="<?php echo $modulelink; ?>&action=reports&report=performance" class="btn btn-default">
                    <i class="fa fa-refresh"></i> Reset
                </a>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-body text-center">
                    <h2 class="text-primary"><?php echo number_format($summary['total_products'] ?? 0); ?></h2>
                    <p class="text-muted">Products Sold</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <h2 class="text-success"><?php echo number_format($summary['total_orders'] ?? 0); ?></h2>
                    <p class="text-muted">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <h2 class="text-info"><?php echo CurrencyHelper::formatUsd($summary['total_revenue_usd'] ?? 0); ?></h2>
                    <p class="text-muted">Total Revenue (USD)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Chart -->
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-bar-chart"></i> Top Products by Orders</h4>
                </div>
                <div class="panel-body">
                    <canvas id="topProductsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-pie-chart"></i> By Validation Type</h4>
                </div>
                <div class="panel-body">
                    <canvas id="validationTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
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
                <i class="fa fa-info-circle"></i> No product data found for the selected filters.
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
                            <th class="text-right">Revenue (USD)</th>
                            <th class="text-right">Avg Order</th>
                            <th class="text-center">Completion Rate</th>
                            <th class="text-center">Renewal Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <?php 
                        $completionClass = $product['completion_rate'] >= 80 ? 'label-success' : ($product['completion_rate'] >= 50 ? 'label-warning' : 'label-danger');
                        $renewalClass = $product['renewal_rate'] >= 50 ? 'label-success' : ($product['renewal_rate'] >= 25 ? 'label-warning' : 'label-default');
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $helper->e($product['product_name']); ?></strong>
                                <br><small class="text-muted"><?php echo $helper->e($product['product_code']); ?></small>
                            </td>
                            <td><span class="label label-default"><?php echo $helper->e($product['vendor']); ?></span></td>
                            <td><?php echo $helper->validationBadge($product['validation_type'] ?? 'dv'); ?></td>
                            <td class="text-center"><strong><?php echo number_format($product['total_orders']); ?></strong></td>
                            <td class="text-center text-success"><?php echo number_format($product['active_count']); ?></td>
                            <td class="text-center text-danger"><?php echo number_format($product['cancelled_count']); ?></td>
                            <td class="text-right"><strong><?php echo CurrencyHelper::formatUsd($product['total_revenue_usd']); ?></strong></td>
                            <td class="text-right"><?php echo CurrencyHelper::formatUsd($product['avg_order_value_usd']); ?></td>
                            <td class="text-center">
                                <span class="label <?php echo $completionClass; ?>">
                                    <?php echo number_format($product['completion_rate'], 1); ?>%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="label <?php echo $renewalClass; ?>">
                                    <?php echo number_format($product['renewal_rate'], 1); ?>%
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

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var products = <?php echo json_encode(array_slice($products, 0, 10)); ?>;
    
    // Top Products Chart
    if (products.length > 0) {
        var labels = products.map(function(p) { return p.product_name.substring(0, 20); });
        var orders = products.map(function(p) { return p.total_orders; });
        var revenue = products.map(function(p) { return p.total_revenue_usd; });
        
        var ctx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: orders,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Revenue (USD)',
                    data: revenue,
                    type: 'line',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    y: {
                        type: 'linear',
                        position: 'bottom',
                        title: { display: true, text: 'Orders' }
                    },
                    y1: {
                        type: 'linear',
                        position: 'top',
                        title: { display: true, text: 'Revenue (USD)' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // Validation Type Chart
    var typeData = {};
    products.forEach(function(p) {
        var type = (p.validation_type || 'dv').toUpperCase();
        typeData[type] = (typeData[type] || 0) + p.total_orders;
    });
    
    if (Object.keys(typeData).length > 0) {
        var pieCtx = document.getElementById('validationTypeChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(typeData),
                datasets: [{
                    data: Object.values(typeData),
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>