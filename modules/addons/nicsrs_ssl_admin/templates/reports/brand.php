<?php
/**
 * Revenue by Brand Report Template
 * 
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 * @var string $currencyHelper CurrencyHelper class name
 * @var array $reportData Brand revenue data
 * @var array $trendData Brand trend data
 * @var array $datePresets Date presets
 * @var array $filters Current filters
 */

use NicsrsAdmin\Helper\CurrencyHelper;

$filters = $filters ?? [];
$summary = $reportData['summary'] ?? [];
$brands = $reportData['brands'] ?? [];
$chartData = $reportData['chart_data'] ?? [];
?>

<div class="nicsrs-reports nicsrs-brand-report">
    
    <!-- Page Header -->
    <div class="page-header clearfix">
        <h3 class="pull-left">
            <i class="fa fa-pie-chart"></i> Revenue by Brand
        </h3>
        <div class="pull-right">
            <a href="<?php echo $modulelink; ?>&action=reports" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Reports
            </a>
            <a href="<?php echo $modulelink; ?>&action=reports&report=brand&export=csv&<?php echo http_build_query($filters); ?>" 
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
                <input type="hidden" name="report" value="brand">
                
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
                
                <!-- Period for Trend -->
                <div class="form-group" style="margin-right: 15px;">
                    <label>Trend Period:</label>
                    <select name="period" class="form-control" style="margin-left: 5px;">
                        <option value="month" <?php echo ($filters['period'] ?? 'month') === 'month' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="quarter" <?php echo ($filters['period'] ?? '') === 'quarter' ? 'selected' : ''; ?>>Quarterly</option>
                        <option value="year" <?php echo ($filters['period'] ?? '') === 'year' ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Apply
                </button>
                
                <a href="<?php echo $modulelink; ?>&action=reports&report=brand" class="btn btn-default">
                    <i class="fa fa-times"></i> Clear
                </a>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Total Brands</h4>
                    <h2 class="stat-value"><?php echo number_format($summary['total_brands'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Total Orders</h4>
                    <h2 class="stat-value"><?php echo number_format($summary['total_orders'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <h4 class="text-muted">Total Revenue</h4>
                    <h2 class="stat-value"><?php echo CurrencyHelper::formatUsd($summary['total_revenue'] ?? 0); ?></h2>
                    <p class="text-muted"><?php echo CurrencyHelper::formatVnd(CurrencyHelper::usdToVnd($summary['total_revenue'] ?? 0)); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Market Share Pie Chart -->
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-pie-chart"></i> Market Share by Revenue</h4>
                </div>
                <div class="panel-body">
                    <canvas id="marketShareChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Brand Comparison Bar Chart -->
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-bar-chart"></i> Brand Comparison</h4>
                </div>
                <div class="panel-body">
                    <canvas id="brandComparisonChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Brand Trend Chart -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-line-chart"></i> Revenue Trend by Brand</h4>
        </div>
        <div class="panel-body">
            <canvas id="brandTrendChart" height="80"></canvas>
        </div>
    </div>

    <!-- Brand Data Table -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-table"></i> Brand Performance Details
            </h4>
        </div>
        <div class="panel-body">
            <?php if (empty($brands)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> No brand data available for the selected filters.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Brand/Vendor</th>
                            <th class="text-center">Total Orders</th>
                            <th class="text-center">Active Certs</th>
                            <th class="text-right">Total Revenue</th>
                            <th class="text-right">Avg Order Value</th>
                            <th class="text-center">Revenue Share</th>
                            <th class="text-center">Order Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brands as $index => $brand): ?>
                        <?php
                        $brandColors = [
                            'Sectigo' => '#1a73e8',
                            'DigiCert' => '#0066cc',
                            'GoGetSSL' => '#00aa55',
                            'GlobalSign' => '#ff6600',
                            'GeoTrust' => '#cc0000',
                            'Comodo' => '#1a73e8',
                            'RapidSSL' => '#009688',
                            'Thawte' => '#673ab7',
                        ];
                        $brandColor = $brandColors[$brand['vendor']] ?? '#666666';
                        ?>
                        <tr>
                            <td>
                                <span class="brand-indicator" style="background-color: <?php echo $brandColor; ?>"></span>
                                <strong><?php echo $helper->e($brand['vendor']); ?></strong>
                            </td>
                            <td class="text-center"><?php echo number_format($brand['order_count']); ?></td>
                            <td class="text-center text-success"><?php echo number_format($brand['active_count']); ?></td>
                            <td class="text-right">
                                <strong><?php echo CurrencyHelper::formatUsd($brand['total_revenue']); ?></strong>
                            </td>
                            <td class="text-right"><?php echo CurrencyHelper::formatUsd($brand['avg_order_value']); ?></td>
                            <td class="text-center">
                                <div class="progress" style="margin: 0; min-width: 100px;">
                                    <div class="progress-bar progress-bar-info" 
                                         style="width: <?php echo min($brand['revenue_percentage'], 100); ?>%">
                                        <?php echo number_format($brand['revenue_percentage'], 1); ?>%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="label label-default">
                                    <?php echo number_format($brand['order_percentage'], 1); ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="active">
                            <th>Total</th>
                            <th class="text-center"><?php echo number_format($summary['total_orders']); ?></th>
                            <th class="text-center"><?php echo number_format(array_sum(array_column($brands, 'active_count'))); ?></th>
                            <th class="text-right"><?php echo CurrencyHelper::formatUsd($summary['total_revenue']); ?></th>
                            <th class="text-right">
                                <?php 
                                $overallAvg = $summary['total_orders'] > 0 ? $summary['total_revenue'] / $summary['total_orders'] : 0;
                                echo CurrencyHelper::formatUsd($overallAvg);
                                ?>
                            </th>
                            <th class="text-center">100%</th>
                            <th class="text-center">100%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Brand Insights -->
    <?php if (!empty($brands)): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-lightbulb-o"></i> Insights</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="insight-card">
                        <i class="fa fa-trophy text-warning"></i>
                        <h5>Top Performer</h5>
                        <?php 
                        $topBrand = $brands[0] ?? null;
                        if ($topBrand):
                        ?>
                        <p><strong><?php echo $helper->e($topBrand['vendor']); ?></strong> leads with 
                           <?php echo number_format($topBrand['revenue_percentage'], 1); ?>% revenue share</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="insight-card">
                        <i class="fa fa-dollar text-success"></i>
                        <h5>Highest Avg Order</h5>
                        <?php 
                        $highestAvg = collect($brands)->sortByDesc('avg_order_value')->first();
                        if ($highestAvg):
                        ?>
                        <p><strong><?php echo $helper->e($highestAvg['vendor']); ?></strong> has highest avg order at 
                           <?php echo CurrencyHelper::formatUsd($highestAvg['avg_order_value']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="insight-card">
                        <i class="fa fa-users text-info"></i>
                        <h5>Most Orders</h5>
                        <?php 
                        $mostOrders = collect($brands)->sortByDesc('order_count')->first();
                        if ($mostOrders):
                        ?>
                        <p><strong><?php echo $helper->e($mostOrders['vendor']); ?></strong> has most orders with 
                           <?php echo number_format($mostOrders['order_count']); ?> sales</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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

    var chartLabels = <?php echo json_encode($chartData['labels'] ?? []); ?>;
    var chartRevenue = <?php echo json_encode($chartData['revenue'] ?? []); ?>;
    var chartOrders = <?php echo json_encode($chartData['orders'] ?? []); ?>;
    var chartPercentages = <?php echo json_encode($chartData['percentages'] ?? []); ?>;

    var brandColors = {
        'Sectigo': '#1a73e8',
        'DigiCert': '#0066cc',
        'GoGetSSL': '#00aa55',
        'GlobalSign': '#ff6600',
        'GeoTrust': '#cc0000',
        'Comodo': '#1a73e8',
        'RapidSSL': '#009688',
        'Thawte': '#673ab7',
        'Unknown': '#999999'
    };

    var colors = chartLabels.map(function(label) {
        return brandColors[label] || '#' + Math.floor(Math.random()*16777215).toString(16);
    });

    if (chartLabels.length > 0) {
        // Market Share Pie Chart
        var ctx1 = document.getElementById('marketShareChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartRevenue,
                    backgroundColor: colors.map(c => c + 'cc'),
                    borderColor: colors,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var value = context.parsed;
                                var percentage = chartPercentages[context.dataIndex] || 0;
                                return context.label + ': $' + value.toLocaleString() + ' (' + percentage.toFixed(1) + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Brand Comparison Bar Chart
        var ctx2 = document.getElementById('brandComparisonChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Revenue (USD)',
                        data: chartRevenue,
                        backgroundColor: colors.map(c => c + 'aa'),
                        borderColor: colors,
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders',
                        data: chartOrders,
                        type: 'line',
                        borderColor: '#ff6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Revenue (USD)' }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Orders' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // Brand Trend Chart
    var trendLabels = <?php echo json_encode($trendData['labels'] ?? []); ?>;
    var trendDatasets = <?php echo json_encode($trendData['datasets'] ?? []); ?>;

    if (trendLabels.length > 0 && Object.keys(trendDatasets).length > 0) {
        var datasets = [];
        var colorIndex = 0;
        var defaultColors = ['#1a73e8', '#00aa55', '#ff6600', '#cc0000', '#673ab7', '#009688', '#ff9800'];

        for (var brand in trendDatasets) {
            var color = brandColors[brand] || defaultColors[colorIndex % defaultColors.length];
            datasets.push({
                label: brand,
                data: trendDatasets[brand],
                borderColor: color,
                backgroundColor: color + '33',
                tension: 0.4,
                fill: false
            });
            colorIndex++;
        }

        var ctx3 = document.getElementById('brandTrendChart').getContext('2d');
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: datasets
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
                        title: { display: true, text: 'Revenue (USD)' }
                    }
                }
            }
        });
    }
});
</script>

<style>
.nicsrs-brand-report .stat-value {
    font-size: 28px;
    font-weight: 600;
    margin: 10px 0 0;
}
.nicsrs-brand-report .brand-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 2px;
    margin-right: 8px;
}
.nicsrs-brand-report .panel-primary .panel-body { background: #f0f7ff; }
.nicsrs-brand-report .panel-success .panel-body { background: #f0fff4; }
.nicsrs-brand-report .panel-info .panel-body { background: #f0ffff; }
.nicsrs-brand-report .insight-card {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}
.nicsrs-brand-report .insight-card i {
    font-size: 24px;
    margin-bottom: 10px;
}
.nicsrs-brand-report .insight-card h5 {
    margin: 10px 0;
    font-weight: 600;
}
.nicsrs-brand-report .insight-card p {
    margin: 0;
    color: #666;
}
</style>