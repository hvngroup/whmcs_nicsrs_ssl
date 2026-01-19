<?php
/**
 * Dashboard Template
 * 
 * @var array $statistics Dashboard statistics
 * @var array $recentOrders Recent orders list
 * @var array $expiringCertificates Expiring certificates
 * @var array $statusDistribution Status distribution data
 * @var array $monthlyOrders Monthly orders data
 * @var bool $apiConnected API connection status
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 */
?>

<div class="nicsrs-dashboard">
    
    <!-- API Status Alert -->
    <?php if (!$apiConnected): ?>
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        <strong>API Not Connected!</strong> 
        Please configure your NicSRS API token in the 
        <a href="<?php echo $modulelink; ?>&action=settings">Settings</a> page.
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon bg-primary">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?php echo number_format($statistics['total_orders']); ?></div>
                    <div class="stats-label">Total Orders</div>
                    <?php if ($statistics['trend'] != 0): ?>
                    <div class="stats-trend <?php echo $statistics['trend'] > 0 ? 'text-success' : 'text-danger'; ?>">
                        <i class="fa fa-<?php echo $statistics['trend'] > 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <?php echo abs($statistics['trend']); ?>% this month
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon bg-warning">
                    <i class="fa fa-clock-o"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?php echo number_format($statistics['pending_orders']); ?></div>
                    <div class="stats-label">Pending Orders</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon bg-success">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?php echo number_format($statistics['issued_certs']); ?></div>
                    <div class="stats-label">Issued Certificates</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon bg-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?php echo number_format($statistics['expiring_soon']); ?></div>
                    <div class="stats-label">Expiring Soon (30d)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-pie-chart"></i> Certificate Status Distribution</h3>
                </div>
                <div class="panel-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-bar-chart"></i> Monthly Orders</h3>
                </div>
                <div class="panel-body">
                    <canvas id="ordersChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Expiring Certificates -->
    <div class="row" style="margin-top: 20px;">
        <!-- Recent Orders -->
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-list"></i> Recent Orders
                        <a href="<?php echo $modulelink; ?>&action=orders" class="btn btn-xs btn-default pull-right">
                            View All
                        </a>
                    </h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    <table class="table table-striped table-hover" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Domain</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No orders found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo $modulelink; ?>&action=order&id=<?php echo $order['id']; ?>">
                                        #<?php echo $order['id']; ?>
                                    </a>
                                </td>
                                <td><?php echo $helper->truncate($order['domain'], 30); ?></td>
                                <td><small><?php echo $helper->e($order['certtype']); ?></small></td>
                                <td><?php echo $helper->statusBadge($order['status']); ?></td>
                                <td><small><?php echo $helper->formatDate($order['provisiondate']); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Expiring Certificates -->
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-calendar-times-o"></i> Expiring Soon
                    </h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    <table class="table table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expiringCertificates)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">No expiring certificates</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($expiringCertificates as $cert): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo $modulelink; ?>&action=order&id=<?php echo $cert['id']; ?>">
                                        <?php echo $helper->truncate($cert['domain'], 25); ?>
                                    </a>
                                </td>
                                <td><?php echo $helper->daysLeftBadge($cert['end_date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Pie Chart
    var statusData = <?php echo json_encode($statusDistribution); ?>;
    var statusLabels = Object.keys(statusData).map(function(s) {
        return s.charAt(0).toUpperCase() + s.slice(1);
    });
    var statusValues = Object.values(statusData);
    var statusColors = {
        'complete': '#52c41a',
        'pending': '#faad14',
        'awaiting': '#8c8c8c',
        'draft': '#1890ff',
        'cancelled': '#ff4d4f',
        'revoked': '#ff4d4f'
    };
    var bgColors = Object.keys(statusData).map(function(s) {
        return statusColors[s] || '#d9d9d9';
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: bgColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Monthly Orders Bar Chart
    var monthlyData = <?php echo json_encode($monthlyOrders); ?>;
    new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(function(d) { return d.short; }),
            datasets: [{
                label: 'Orders',
                data: monthlyData.map(function(d) { return d.count; }),
                backgroundColor: '#1890ff',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>