<?php
/**
 * Order Detail Template
 * 
 * @var object $order Order object
 * @var array $config Configuration data
 * @var array $domains Domain list
 * @var array $certInfo Certificate info
 * @var array $activityLogs Activity logs
 * @var string $clientName Client name
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 */
?>

<div class="nicsrs-order-detail">
    
    <!-- Breadcrumb -->
    <ol class="breadcrumb">
        <li><a href="<?php echo $modulelink; ?>">Dashboard</a></li>
        <li><a href="<?php echo $modulelink; ?>&action=orders">Orders</a></li>
        <li class="active">Order #<?php echo $order->id; ?></li>
    </ol>

    <!-- Header -->
    <div class="page-header clearfix">
        <h3 class="pull-left" style="margin: 0;">
            <i class="fa fa-certificate"></i> 
            Order #<?php echo $order->id; ?>
            <?php echo $helper->statusBadge($order->status); ?>
        </h3>
        <div class="pull-right">
            <a href="<?php echo $modulelink; ?>&action=orders" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Order Info -->
        <div class="col-md-6">
            <!-- Order Information -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-info-circle"></i> Order Information</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 140px;">Order ID:</th>
                            <td>#<?php echo $order->id; ?></td>
                        </tr>
                        <tr>
                            <th>Remote ID:</th>
                            <td>
                                <?php if ($order->remoteid): ?>
                                <code><?php echo $helper->e($order->remoteid); ?></code>
                                <?php else: ?>
                                <span class="text-muted">Not yet assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Product:</th>
                            <td><?php echo $helper->e($order->certtype); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><?php echo $helper->statusBadge($order->status); ?></td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?php echo $helper->formatDateTime($order->provisiondate); ?></td>
                        </tr>
                        <?php if ($order->completiondate && $order->completiondate !== '0000-00-00 00:00:00'): ?>
                        <tr>
                            <th>Completed:</th>
                            <td><?php echo $helper->formatDateTime($order->completiondate); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Service ID:</th>
                            <td>
                                <?php if ($order->serviceid): ?>
                                <a href="clientsservices.php?id=<?php echo $order->serviceid; ?>" target="_blank">
                                    #<?php echo $order->serviceid; ?> <i class="fa fa-external-link"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Client Information -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-user"></i> Client Information</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 140px;">Name:</th>
                            <td>
                                <?php if ($order->userid): ?>
                                <a href="clientssummary.php?userid=<?php echo $order->userid; ?>" target="_blank">
                                    <?php echo $helper->e($clientName); ?> <i class="fa fa-external-link"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($order->companyname): ?>
                        <tr>
                            <th>Company:</th>
                            <td><?php echo $helper->e($order->companyname); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo $helper->e($order->client_email); ?></td>
                        </tr>
                        <?php if ($order->phonenumber): ?>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo $helper->e($order->phonenumber); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Certificate Info & Actions -->
        <div class="col-md-6">
            <!-- Certificate Information -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-shield"></i> Certificate Information</h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($certInfo['begin_date'])): ?>
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 140px;">Cert ID:</th>
                            <td><code><?php echo $helper->e($certInfo['cert_id']); ?></code></td>
                        </tr>
                        <tr>
                            <th>Valid From:</th>
                            <td><?php echo $helper->formatDate($certInfo['begin_date']); ?></td>
                        </tr>
                        <tr>
                            <th>Valid Until:</th>
                            <td>
                                <?php echo $helper->formatDate($certInfo['end_date']); ?>
                                <?php echo $helper->daysLeftBadge($certInfo['end_date']); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Certificate:</th>
                            <td>
                                <?php if ($certInfo['has_certificate']): ?>
                                <span class="text-success"><i class="fa fa-check"></i> Available</span>
                                <?php else: ?>
                                <span class="text-muted">Not yet issued</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    <?php else: ?>
                    <p class="text-muted">Certificate not yet issued.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="panel-body">
                    <div class="btn-group-vertical btn-block">
                        <button type="button" class="btn btn-default btn-action" data-action="refresh_status"
                                <?php echo empty($order->remoteid) ? 'disabled' : ''; ?>>
                            <i class="fa fa-refresh"></i> Refresh Status
                        </button>
                        
                        <?php if ($order->status === 'complete'): ?>
                        <button type="button" class="btn btn-warning btn-action" data-action="reissue">
                            <i class="fa fa-repeat"></i> Reissue Certificate
                        </button>
                        <button type="button" class="btn btn-info btn-action" data-action="renew">
                            <i class="fa fa-calendar-plus-o"></i> Renew Certificate
                        </button>
                        <button type="button" class="btn btn-danger btn-action" data-action="revoke">
                            <i class="fa fa-ban"></i> Revoke Certificate
                        </button>
                        <?php endif; ?>
                        
                        <?php if (in_array($order->status, ['awaiting', 'draft', 'pending'])): ?>
                        <button type="button" class="btn btn-danger btn-action" data-action="cancel">
                            <i class="fa fa-times"></i> Cancel Order
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Domain Validation -->
    <?php if (!empty($domains)): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-globe"></i> Domain Validation</h3>
        </div>
        <div class="panel-body" style="padding: 0;">
            <table class="table table-striped" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>DCV Method</th>
                        <th>DCV Email</th>
                        <th class="text-center">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($domains as $domain): ?>
                    <tr>
                        <td><strong><?php echo $helper->e($domain['domain']); ?></strong></td>
                        <td><code><?php echo $helper->e($domain['dcv_method']); ?></code></td>
                        <td><?php echo $helper->e($domain['dcv_email']); ?></td>
                        <td class="text-center">
                            <?php if ($domain['is_verified']): ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Verified</span>
                            <?php else: ?>
                            <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$domain['is_verified'] && $domain['dcv_method'] === 'EMAIL'): ?>
                            <button type="button" class="btn btn-xs btn-default btn-resend-dcv" 
                                    data-domain="<?php echo $helper->e($domain['domain']); ?>">
                                <i class="fa fa-envelope"></i> Resend
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Activity Log -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-history"></i> Activity Log</h3>
        </div>
        <div class="panel-body" style="padding: 0;">
            <table class="table table-striped" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Admin</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activityLogs)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">No activity recorded</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($activityLogs as $log): ?>
                    <tr>
                        <td><small><?php echo $helper->formatDateTime($log->created_at); ?></small></td>
                        <td>
                            <span class="label label-default">
                                <?php echo \NicsrsAdmin\Service\ActivityLogger::getActionDescription($log->action); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $adminName = trim($log->admin_firstname . ' ' . $log->admin_lastname);
                            echo $helper->e($adminName ?: $log->username ?: 'System');
                            ?>
                        </td>
                        <td><small class="text-muted"><?php echo $helper->truncate($log->new_value, 50); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Action Confirmation Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirm Action</h4>
            </div>
            <div class="modal-body">
                <p id="actionMessage"></p>
                <div id="reasonField" style="display: none;">
                    <label>Reason (optional):</label>
                    <textarea class="form-control" id="actionReason" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modulelink = '<?php echo $modulelink; ?>';
    var orderId = <?php echo $order->id; ?>;
    var currentAction = '';

    // Action buttons
    document.querySelectorAll('.btn-action').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentAction = this.dataset.action;
            var messages = {
                'refresh_status': 'Refresh certificate status from NicSRS API?',
                'cancel': 'Are you sure you want to cancel this order? This action cannot be undone.',
                'revoke': 'Are you sure you want to revoke this certificate? This action cannot be undone.',
                'reissue': 'Submit a reissue request for this certificate?',
                'renew': 'Submit a renewal request for this certificate?'
            };
            
            document.getElementById('actionMessage').textContent = messages[currentAction] || 'Proceed with this action?';
            document.getElementById('reasonField').style.display = 
                ['cancel', 'revoke'].includes(currentAction) ? 'block' : 'none';
            document.getElementById('actionReason').value = '';
            
            $('#actionModal').modal('show');
        });
    });

    // Confirm action
    document.getElementById('confirmAction').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

        $.ajax({
            url: modulelink + '&action=order',
            method: 'POST',
            data: {
                ajax_action: currentAction,
                order_id: orderId,
                reason: document.getElementById('actionReason').value
            },
            dataType: 'json',
            success: function(response) {
                $('#actionModal').modal('hide');
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                $('#actionModal').modal('hide');
                alert('Request failed. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = 'Confirm';
            }
        });
    });

    // Resend DCV
    document.querySelectorAll('.btn-resend-dcv').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var domain = this.dataset.domain;
            if (!confirm('Resend DCV email for ' + domain + '?')) return;

            $.ajax({
                url: modulelink + '&action=order',
                method: 'POST',
                data: { ajax_action: 'resend_dcv', order_id: orderId, domain: domain },
                dataType: 'json',
                success: function(response) {
                    alert(response.success ? response.message : 'Error: ' + response.message);
                }
            });
        });
    });
});
</script>