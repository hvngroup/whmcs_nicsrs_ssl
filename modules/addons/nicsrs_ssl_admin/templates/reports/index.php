<?php
/**
 * Reports Index Template
 * Reports dashboard with quick stats and navigation
 * 
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 * @var array $quickStats Quick statistics
 */

use NicsrsAdmin\Helper\CurrencyHelper;
?>

<div class="nicsrs-reports">
    
    <!-- Page Header -->
    <div class="page-header">
        <h3><i class="fa fa-bar-chart"></i> Reports & Analytics</h3>
        <p class="text-muted">Sales, profit, and performance reports for SSL certificates</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-default">
                <div class="panel-body text-center">
                    <div class="stat-icon text-primary">
                        <i class="fa fa-dollar fa-2x"></i>
                    </div>
                    <h3 class="stat-value"><?php echo CurrencyHelper::formatUsd($quickStats['this_month_sales'] ?? 0); ?></h3>
                    <p class="stat-label text-muted">This Month Sales</p>
                    <?php if (($quickStats['sales_growth'] ?? 0) != 0): ?>
                    <small class="<?php echo $quickStats['sales_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <i class="fa fa-<?php echo $quickStats['sales_growth'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <?php echo abs($quickStats['sales_growth']); ?>% vs last month
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-default">
                <div class="panel-body text-center">
                    <div class="stat-icon text-success">
                        <i class="fa fa-shopping-cart fa-2x"></i>
                    </div>
                    <h3 class="stat-value"><?php echo number_format($quickStats['this_month_orders'] ?? 0); ?></h3>
                    <p class="stat-label text-muted">Orders This Month</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-default">
                <div class="panel-body text-center">
                    <div class="stat-icon text-info">
                        <i class="fa fa-certificate fa-2x"></i>
                    </div>
                    <h3 class="stat-value"><?php echo number_format($quickStats['active_certificates'] ?? 0); ?></h3>
                    <p class="stat-label text-muted">Active Certificates</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="panel panel-default">
                <div class="panel-body text-center">
                    <div class="stat-icon text-warning">
                        <i class="fa fa-exchange fa-2x"></i>
                    </div>
                    <?php $rateInfo = CurrencyHelper::getRateInfo(); ?>
                    <h3 class="stat-value" style="font-size: 18px;"><?php echo $rateInfo['rate_formatted']; ?></h3>
                    <p class="stat-label text-muted">Exchange Rate</p>
                    <small class="text-muted">Updated: <?php echo $rateInfo['last_updated_formatted']; ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="row">
        
        <!-- SSL Sales Report -->
        <div class="col-md-6">
            <div class="panel panel-default report-card">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fa fa-line-chart text-primary"></i> SSL Sales Report
                    </h4>
                </div>
                <div class="panel-body">
                    <p>View detailed sales data including revenue by product, time period, and client. Track sales trends and identify top-performing products.</p>
                    <ul class="report-features">
                        <li><i class="fa fa-check text-success"></i> Revenue by product & time</li>
                        <li><i class="fa fa-check text-success"></i> Sales trend charts</li>
                        <li><i class="fa fa-check text-success"></i> Filter by vendor, product, date</li>
                        <li><i class="fa fa-check text-success"></i> Export to CSV</li>
                    </ul>
                    <a href="<?php echo $modulelink; ?>&action=reports&report=sales" class="btn btn-primary">
                        <i class="fa fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- SSL Profit Report -->
        <div class="col-md-6">
            <div class="panel panel-default report-card">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fa fa-money text-success"></i> SSL Profit Report
                    </h4>
                </div>
                <div class="panel-body">
                    <p>Calculate profit margins by comparing WHMCS revenue with NicSRS costs. Supports USD to VND conversion for local currency reporting.</p>
                    <ul class="report-features">
                        <li><i class="fa fa-check text-success"></i> Profit = Revenue - Cost</li>
                        <li><i class="fa fa-check text-success"></i> USD/VND conversion</li>
                        <li><i class="fa fa-check text-success"></i> Profit margin analysis</li>
                        <li><i class="fa fa-check text-success"></i> Configurable exchange rate</li>
                    </ul>
                    <a href="<?php echo $modulelink; ?>&action=reports&report=profit" class="btn btn-success">
                        <i class="fa fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Product Performance Report -->
        <div class="col-md-6">
            <div class="panel panel-default report-card">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fa fa-trophy text-warning"></i> Product Performance
                    </h4>
                </div>
                <div class="panel-body">
                    <p>Analyze product performance including best sellers, renewal rates, and completion rates. Identify which products drive the most value.</p>
                    <ul class="report-features">
                        <li><i class="fa fa-check text-success"></i> Top selling products</li>
                        <li><i class="fa fa-check text-success"></i> Renewal rate tracking</li>
                        <li><i class="fa fa-check text-success"></i> Order completion rates</li>
                        <li><i class="fa fa-check text-success"></i> Revenue per product</li>
                    </ul>
                    <a href="<?php echo $modulelink; ?>&action=reports&report=performance" class="btn btn-warning">
                        <i class="fa fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Revenue by Brand Report -->
        <div class="col-md-6">
            <div class="panel panel-default report-card">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fa fa-pie-chart text-info"></i> Revenue by Brand
                    </h4>
                </div>
                <div class="panel-body">
                    <p>Compare revenue across SSL certificate brands (Sectigo, DigiCert, GoGetSSL, etc.). Understand brand performance and market share.</p>
                    <ul class="report-features">
                        <li><i class="fa fa-check text-success"></i> Revenue by vendor/brand</li>
                        <li><i class="fa fa-check text-success"></i> Market share pie chart</li>
                        <li><i class="fa fa-check text-success"></i> Brand trend over time</li>
                        <li><i class="fa fa-check text-success"></i> Average order value by brand</li>
                    </ul>
                    <a href="<?php echo $modulelink; ?>&action=reports&report=brand" class="btn btn-info">
                        <i class="fa fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Currency Settings Quick Access -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-cog"></i> Report Settings
            </h4>
        </div>
        <div class="panel-body">
            <form class="form-inline" id="currencySettingsForm">
                <div class="form-group" style="margin-right: 20px;">
                    <label for="usd_vnd_rate">USD to VND Rate:</label>
                    <input type="number" class="form-control" id="usd_vnd_rate" name="usd_vnd_rate" 
                           value="<?php echo CurrencyHelper::getUsdVndRate(); ?>" 
                           min="1" step="100" style="width: 150px; margin-left: 10px;">
                </div>
                
                <div class="form-group" style="margin-right: 20px;">
                    <label for="currency_display">Display Currency:</label>
                    <select class="form-control" id="currency_display" name="currency_display" style="margin-left: 10px;">
                        <option value="usd" <?php echo CurrencyHelper::getDisplayMode() === 'usd' ? 'selected' : ''; ?>>USD Only</option>
                        <option value="vnd" <?php echo CurrencyHelper::getDisplayMode() === 'vnd' ? 'selected' : ''; ?>>VND Only</option>
                        <option value="both" <?php echo CurrencyHelper::getDisplayMode() === 'both' ? 'selected' : ''; ?>>Both (USD & VND)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Settings
                </button>
                
                <button type="button" class="btn btn-default" id="btnUpdateRateFromApi">
                    <i class="fa fa-refresh"></i> Update Rate from API
                </button>
            </form>
        </div>
    </div>

</div>

<style>
.nicsrs-reports .stat-icon {
    margin-bottom: 10px;
}
.nicsrs-reports .stat-value {
    margin: 10px 0 5px;
    font-weight: 600;
}
.nicsrs-reports .stat-label {
    margin-bottom: 5px;
}
.nicsrs-reports .report-card {
    height: 100%;
    min-height: 280px;
}
.nicsrs-reports .report-card .panel-heading {
    background: #f8f9fa;
}
.nicsrs-reports .report-card .panel-title {
    font-size: 16px;
    font-weight: 600;
}
.nicsrs-reports .report-features {
    list-style: none;
    padding: 0;
    margin: 15px 0;
}
.nicsrs-reports .report-features li {
    padding: 5px 0;
}
.nicsrs-reports .report-features li i {
    margin-right: 8px;
}
</style>

<script>
$(document).ready(function() {
    // Save currency settings
    $('#currencySettingsForm').on('submit', function(e) {
        e.preventDefault();
        
        var $btn = $(this).find('button[type="submit"]');
        var originalText = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        $.ajax({
            url: '<?php echo $modulelink; ?>&action=reports',
            type: 'POST',
            dataType: 'json',
            data: {
                ajax_action: 'save_currency_settings',
                usd_vnd_rate: $('#usd_vnd_rate').val(),
                currency_display: $('#currency_display').val()
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', 'Settings saved successfully');
                } else {
                    showNotification('error', response.error || 'Failed to save settings');
                }
            },
            error: function() {
                showNotification('error', 'An error occurred');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Update rate from API
    $('#btnUpdateRateFromApi').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
        
        $.ajax({
            url: '<?php echo $modulelink; ?>&action=reports',
            type: 'POST',
            dataType: 'json',
            data: {
                ajax_action: 'update_exchange_rate'
            },
            success: function(response) {
                if (response.success && response.data.success) {
                    $('#usd_vnd_rate').val(response.data.rate);
                    showNotification('success', response.data.message + ': ' + response.data.rate_formatted);
                } else {
                    showNotification('error', response.data?.message || 'Failed to update rate');
                }
            },
            error: function() {
                showNotification('error', 'An error occurred');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Notification helper
    function showNotification(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var $alert = $('<div class="alert ' + alertClass + ' alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            message + '</div>');
        $('body').append($alert);
        setTimeout(function() { $alert.fadeOut(); }, 3000);
    }
});
</script>