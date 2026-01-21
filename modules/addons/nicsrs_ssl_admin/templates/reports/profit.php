<?php
/**
 * SSL Profit Report Template
 * 
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 * @var string $currencyHelper CurrencyHelper class name
 * @var array $reportData Profit report data
 * @var array $chartData Chart data
 * @var array $rateInfo Exchange rate info
 * @var string $displayMode Currency display mode
 * @var array $vendors Available vendors
 * @var array $products Available products
 * @var array $datePresets Date presets
 * @var array $filters Current filters
 */

use NicsrsAdmin\Helper\CurrencyHelper;

$filters = $filters ?? [];
$summary = $reportData['summary'] ?? [];
?>

<div class="nicsrs-reports nicsrs-profit-report">
    
    <!-- Page Header -->
    <div class="page-header clearfix">
        <h3 class="pull-left">
            <i class="fa fa-money"></i> SSL Profit Report
        </h3>
        <div class="pull-right">
            <a href="<?php echo $modulelink; ?>&action=reports" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Reports
            </a>
            <a href="<?php echo $modulelink; ?>&action=reports&report=profit&export=csv&<?php echo http_build_query($filters); ?>" 
               class="btn btn-success">
                <i class="fa fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Exchange Rate Info -->
    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-6">
                <i class="fa fa-exchange"></i> 
                <strong>Exchange Rate:</strong> <?php echo $rateInfo['rate_formatted']; ?>
                <small class="text-muted">(Updated: <?php echo $rateInfo['last_updated_formatted']; ?>)</small>
            </div>
            <div class="col-md-6 text-right">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-sm btn-default <?php echo $displayMode === 'usd' ? 'active' : ''; ?>">
                        <input type="radio" name="display_currency" value="usd" <?php echo $displayMode === 'usd' ? 'checked' : ''; ?>> USD
                    </label>
                    <label class="btn btn-sm btn-default <?php echo $displayMode === 'vnd' ? 'active' : ''; ?>">
                        <input type="radio" name="display_currency" value="vnd" <?php echo $displayMode === 'vnd' ? 'checked' : ''; ?>> VND
                    </label>
                    <label class="btn btn-sm btn-default <?php echo $displayMode === 'both' ? 'active' : ''; ?>">
                        <input type="radio" name="display_currency" value="both" <?php echo $displayMode === 'both' ? 'checked' : ''; ?>> Both
                    </label>
                </div>
            </div>
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
                <input type="hidden" name="report" value="profit">
                
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
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Apply
                </button>
                
                <a href="<?php echo $modulelink; ?>&action=reports&report=profit" class="btn btn-default">
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
                    <h4 class="text-muted">Total Revenue</h4>
                    <h2 class="stat-value text-primary"><?php echo CurrencyHelper::formatUsd($summary['total_revenue_usd'] ?? 0); ?></h2>
                    <p class="stat-vnd"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($summary['total_revenue_usd'] ?? 0)); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-danger">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Total Cost (NicSRS)</h4>
                    <h2 class="stat-value text-danger"><?php echo CurrencyHelper::formatUsd($summary['total_cost_usd'] ?? 0); ?></h2>
                    <p class="stat-vnd"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($summary['total_cost_usd'] ?? 0)); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Total Profit</h4>
                    <h2 class="stat-value text-success"><?php echo CurrencyHelper::formatUsd($summary['total_profit_usd'] ?? 0); ?></h2>
                    <p class="stat-vnd"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($summary['total_profit_usd'] ?? 0)); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Profit Margin</h4>
                    <h2 class="stat-value text-info"><?php echo number_format($summary['profit_margin'] ?? 0, 1); ?>%</h2>
                    <p class="stat-orders"><?php echo number_format($summary['order_count'] ?? 0); ?> orders</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Chart -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-bar-chart"></i> Profit Trend</h4>
        </div>
        <div class="panel-body">
            <canvas id="profitChart" height="80"></canvas>
        </div>
    </div>

    <!-- Profit Data Table -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-table"></i> Profit Details
                <span class="badge"><?php echo count($reportData['orders'] ?? []); ?> orders</span>
            </h4>
        </div>
        <div class="panel-body">
            <?php if (empty($reportData['orders'])): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> No completed orders found for the selected filters.
                <br><small>Note: Profit report only includes completed orders.</small>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="profitTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Vendor</th>
                            <th class="text-right">Revenue (USD)</th>
                            <th class="text-right">Cost (USD)</th>
                            <th class="text-right">Profit (USD)</th>
                            <th class="text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['orders'] as $order): ?>
                        <?php 
                        $profitClass = $order['profit_usd'] >= 0 ? 'text-success' : 'text-danger';
                        $marginClass = $order['profit_margin'] >= 20 ? 'label-success' : ($order['profit_margin'] >= 10 ? 'label-warning' : 'label-danger');
                        ?>
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
                            <td><span class="label label-default"><?php echo $helper->e($order['vendor']); ?></span></td>
                            <td class="text-right"><?php echo CurrencyHelper::formatUsd($order['sale_amount_usd']); ?></td>
                            <td class="text-right text-danger"><?php echo CurrencyHelper::formatUsd($order['cost_usd']); ?></td>
                            <td class="text-right <?php echo $profitClass; ?>">
                                <strong><?php echo CurrencyHelper::formatUsd($order['profit_usd']); ?></strong>
                            </td>
                            <td class="text-right">
                                <span class="label <?php echo $marginClass; ?>">
                                    <?php echo number_format($order['profit_margin'], 1); ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="active">
                            <th colspan="4" class="text-right">Total:</th>
                            <th class="text-right"><?php echo CurrencyHelper::formatUsd($summary['total_revenue_usd']); ?></th>
                            <th class="text-right text-danger"><?php echo CurrencyHelper::formatUsd($summary['total_cost_usd']); ?></th>
                            <th class="text-right text-success"><?php echo CurrencyHelper::formatUsd($summary['total_profit_usd']); ?></th>
                            <th class="text-right">
                                <span class="label label-info"><?php echo number_format($summary['profit_margin'], 1); ?>%</span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profit Summary in VND -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-calculator"></i> VND Summary (Rate: <?php echo $rateInfo['rate_formatted']; ?>)</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <h5>Total Revenue</h5>
                    <h3 class="text-primary"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($summary['total_revenue_usd'] ?? 0)); ?></h3>
                </div>
                <div class="col-md-4 text-center">
                    <h5>Total Cost</h5>
                    <h3 class="text-danger"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($summary['total_cost_usd'] ?? 0)); ?></h3>
                </div>
                <div class="col-md-4 text-center">
                    <h5>Total Profit</h5>
                    <h3 class="text-success"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($summary['total_profit_usd'] ?? 0)); ?></h3>
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

    // Profit Trend Chart
    var chartLabels = <?php echo json_encode($chartData['labels'] ?? []); ?>;
    var chartRevenue = <?php echo json_encode($chartData['datasets']['revenue'] ?? []); ?>;
    var chartCost = <?php echo json_encode($chartData['datasets']['cost'] ?? []); ?>;
    var chartProfit = <?php echo json_encode($chartData['datasets']['profit'] ?? []); ?>;

    if (chartLabels.length > 0) {
        var ctx = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Revenue (USD)',
                        data: chartRevenue,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Cost (USD)',
                        data: chartCost,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Profit (USD)',
                        data: chartProfit,
                        type: 'line',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true
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
                        beginAtZero: true,
                        title: { display: true, text: 'Amount (USD)' }
                    }
                }
            }
        });
    }
});
</script>

<style>
.nicsrs-profit-report .stat-value {
    font-size: 28px;
    font-weight: 600;
    margin: 10px 0 5px;
}
.nicsrs-profit-report .stat-vnd,
.nicsrs-profit-report .stat-orders {
    color: #888;
    margin: 0;
    font-size: 14px;
}
.nicsrs-profit-report .panel-primary .panel-body { background: #f0f7ff; }
.nicsrs-profit-report .panel-danger .panel-body { background: #fff0f0; }
.nicsrs-profit-report .panel-success .panel-body { background: #f0fff4; }
.nicsrs-profit-report .panel-info .panel-body { background: #f0ffff; }
</style>