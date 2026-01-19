<?php
/**
 * Settings Template
 * 
 * @var array $settings Current settings
 * @var array $activityLogs Recent activity logs
 * @var bool $apiConnected API connection status
 * @var string $apiToken Masked API token
 * @var string $csrfToken CSRF token
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 */
?>

<div class="nicsrs-settings">
    
    <!-- Header -->
    <div class="page-header">
        <h3><i class="fa fa-cog"></i> Module Settings</h3>
    </div>

    <div class="row">
        <!-- Settings Form -->
        <div class="col-md-6">
            
            <!-- API Configuration -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-plug"></i> API Configuration</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label>API Token Status:</label>
                        <div>
                            <?php if ($apiConnected): ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Connected</span>
                            <?php else: ?>
                            <span class="label label-danger"><i class="fa fa-times"></i> Not Connected</span>
                            <?php endif; ?>
                            <span class="text-muted" style="margin-left: 10px;">
                                Token: <?php echo $helper->e($apiToken); ?>
                            </span>
                        </div>
                        <p class="help-block">
                            API token is configured in <strong>Setup → Addon Modules → NicSRS SSL Admin</strong>
                        </p>
                    </div>
                    <button type="button" class="btn btn-default" id="btnTestApi">
                        <i class="fa fa-plug"></i> Test API Connection
                    </button>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-bell"></i> Notification Settings</h3>
                </div>
                <div class="panel-body">
                    <form id="settingsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="email_on_issuance" value="1"
                                       <?php echo !empty($settings['email_on_issuance']) ? 'checked' : ''; ?>>
                                Email admin when certificate is issued
                            </label>
                        </div>
                        
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="email_on_expiry" value="1"
                                       <?php echo !empty($settings['email_on_expiry']) ? 'checked' : ''; ?>>
                                Email admin before certificate expires
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>Expiry Warning Days:</label>
                            <input type="number" name="expiry_days" class="form-control" style="width: 100px;"
                                   value="<?php echo isset($settings['expiry_days']) ? (int)$settings['expiry_days'] : 30; ?>"
                                   min="1" max="90">
                            <p class="help-block">Send expiry warning this many days before certificate expires</p>
                        </div>
                        
                        <div class="form-group">
                            <label>Admin Email:</label>
                            <input type="email" name="admin_email" class="form-control" style="width: 300px;"
                                   value="<?php echo $helper->e($settings['admin_email'] ?? ''); ?>"
                                   placeholder="admin@example.com">
                            <p class="help-block">Leave empty to use WHMCS system email</p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sync Settings -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-refresh"></i> Auto-Sync Settings</h3>
                </div>
                <div class="panel-body">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="auto_sync_status" value="1" form="settingsForm"
                                   <?php echo !empty($settings['auto_sync_status']) ? 'checked' : ''; ?>>
                            Enable automatic status sync
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>Status Sync Interval (hours):</label>
                        <input type="number" name="sync_interval_hours" class="form-control" style="width: 100px;"
                               form="settingsForm"
                               value="<?php echo isset($settings['sync_interval_hours']) ? (int)$settings['sync_interval_hours'] : 6; ?>"
                               min="1" max="24">
                    </div>
                    
                    <div class="form-group">
                        <label>Product Sync Interval (hours):</label>
                        <input type="number" name="product_sync_hours" class="form-control" style="width: 100px;"
                               form="settingsForm"
                               value="<?php echo isset($settings['product_sync_hours']) ? (int)$settings['product_sync_hours'] : 24; ?>"
                               min="1" max="168">
                    </div>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-desktop"></i> Display Settings</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label>Date Format:</label>
                        <select name="date_format" class="form-control" style="width: 200px;" form="settingsForm">
                            <option value="Y-m-d" <?php echo ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : ''; ?>>
                                2025-01-19
                            </option>
                            <option value="d/m/Y" <?php echo ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : ''; ?>>
                                19/01/2025
                            </option>
                            <option value="m/d/Y" <?php echo ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : ''; ?>>
                                01/19/2025
                            </option>
                            <option value="d M Y" <?php echo ($settings['date_format'] ?? '') === 'd M Y' ? 'selected' : ''; ?>>
                                19 Jan 2025
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <button type="button" class="btn btn-primary btn-lg" id="btnSaveSettings">
                <i class="fa fa-save"></i> Save Settings
            </button>
        </div>

        <!-- Activity Log -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading clearfix">
                    <h3 class="panel-title pull-left"><i class="fa fa-history"></i> Recent Activity</h3>
                    <div class="pull-right">
                        <button type="button" class="btn btn-xs btn-default" id="btnExportLogs" title="Export Logs">
                            <i class="fa fa-download"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-danger" id="btnClearLogs" title="Clear Old Logs">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="panel-body" style="padding: 0; max-height: 600px; overflow-y: auto;">
                    <table class="table table-striped table-condensed" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activityLogs)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No activity recorded</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($activityLogs as $log): ?>
                            <tr>
                                <td><small><?php echo $helper->timeAgo($log->created_at); ?></small></td>
                                <td>
                                    <span class="label label-default">
                                        <?php echo $helper->truncate($log->action, 20); ?>
                                    </span>
                                    <?php if ($log->entity_type && $log->entity_id): ?>
                                    <br><small class="text-muted">
                                        <?php echo ucfirst($log->entity_type); ?> #<?php echo $log->entity_id; ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <?php 
                                        $adminName = trim(($log->firstname ?? '') . ' ' . ($log->lastname ?? ''));
                                        echo $helper->e($adminName ?: $log->username ?: 'System');
                                        ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Module Info -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-info-circle"></i> Module Information</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed" style="margin-bottom: 0;">
                        <tr>
                            <th>Version:</th>
                            <td><?php echo NICSRS_ADMIN_VERSION; ?></td>
                        </tr>
                        <tr>
                            <th>Author:</th>
                            <td><a href="https://hvn.vn" target="_blank">HVN GROUP</a></td>
                        </tr>
                        <tr>
                            <th>Support:</th>
                            <td><a href="mailto:support@hvn.vn">support@hvn.vn</a></td>
                        </tr>
                        <tr>
                            <th>Documentation:</th>
                            <td><a href="https://hvn.vn/docs/nicsrs-ssl" target="_blank">View Docs</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Clear Activity Logs</h4>
            </div>
            <div class="modal-body">
                <p>Delete activity logs older than:</p>
                <select class="form-control" id="clearLogsDays">
                    <option value="7">7 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="90">90 days</option>
                    <option value="0">All logs</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmClearLogs">Delete Logs</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modulelink = '<?php echo $modulelink; ?>';

    // Test API Connection
    document.getElementById('btnTestApi').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Testing...';

        $.ajax({
            url: modulelink + '&action=settings',
            method: 'POST',
            data: { ajax_action: 'test_api' },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('Request failed. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-plug"></i> Test API Connection';
            }
        });
    });

    // Save Settings
    document.getElementById('btnSaveSettings').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

        var formData = $('#settingsForm').serialize();
        formData += '&ajax_action=save_settings';

        $.ajax({
            url: modulelink + '&action=settings',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Settings saved successfully!');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Request failed. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save"></i> Save Settings';
            }
        });
    });

    // Clear Logs Modal
    document.getElementById('btnClearLogs').addEventListener('click', function() {
        $('#clearLogsModal').modal('show');
    });

    // Confirm Clear Logs
    document.getElementById('confirmClearLogs').addEventListener('click', function() {
        var btn = this;
        var days = document.getElementById('clearLogsDays').value;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';

        $.ajax({
            url: modulelink + '&action=settings',
            method: 'POST',
            data: { ajax_action: 'clear_logs', days: days },
            dataType: 'json',
            success: function(response) {
                $('#clearLogsModal').modal('hide');
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                $('#clearLogsModal').modal('hide');
                alert('Request failed. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = 'Delete Logs';
            }
        });
    });

    // Export Logs
    document.getElementById('btnExportLogs').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;

        $.ajax({
            url: modulelink + '&action=settings',
            method: 'POST',
            data: { ajax_action: 'export_logs' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.csv) {
                    // Create download link
                    var blob = new Blob([atob(response.csv)], { type: 'text/csv' });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = response.filename || 'activity_log.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                } else {
                    alert('Error: ' + (response.message || 'Export failed'));
                }
            },
            error: function() {
                alert('Request failed. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
            }
        });
    });
});
</script>