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

// Get primary domain and product name
$primaryDomain = $helper->getPrimaryDomain($order);
$productName = $helper->getProductName($order->certtype);

// Check CSR and Certificate availability
$hasCsr = !empty($config['csr']);
$hasCert = !empty($config['applyReturn']['certificate']);
?>

<div class="nicsrs-order-detail">
    
    <!-- Breadcrumb -->
    <ol class="breadcrumb">
        <li><a href="<?php echo $modulelink; ?>">Dashboard</a></li>
        <li><a href="<?php echo $modulelink; ?>&action=orders">Orders</a></li>
        <li class="active">Order #<?php echo $order->id; ?></li>
    </ol>

    <!-- Enhanced Header: Order ID, Status, Product Name, Domain -->
    <div class="page-header clearfix" style="border-bottom: 2px solid #1890ff; padding-bottom: 15px; margin-bottom: 20px;">
        <div class="pull-left">
            <h3>
                <span title="OrderID"><i class="fa fa-certificate text-primary"></i> Order #<?php echo $order->id; ?></span>
                <span title="Product"><i class="fa fa-tag text-primary"></i> <?php echo $helper->e($productName); ?></span>
                <?php if ($primaryDomain !== '-'): ?>
                <span title="Domain"><i class="fa fa-globe text-primary"></i> <?php echo $helper->e($primaryDomain); ?></span>
                <?php endif; ?>
                <?php echo $helper->statusBadge($order->status); ?>                                
            </h3>
        </div>
        <div class="pull-right">
            <a href="<?php echo $modulelink; ?>&action=orders" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Order Info + Client Info -->
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
                        <!-- Service ID with link -->
                        <tr class="info">
                            <th>Service ID:</th>
                            <td>
                                <?php if ($order->serviceid): ?>
                                <a href="clientsservices.php?id=<?php echo $order->serviceid; ?>" target="_blank" class="btn btn-xs btn-info">
                                    #<?php echo $order->serviceid; ?> <i class="fa fa-external-link"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Product Name with code -->
                        <tr>
                            <th>Product NicSRS:</th>
                            <td>
                                <strong><?php echo $helper->e($productName); ?></strong>
                                <small class="text-muted">(<?php echo $helper->e($order->certtype); ?>)</small>
                            </td>
                        </tr>
                        <!-- Domain -->
                        <tr>
                            <th>Domain:</th>
                            <td>
                                <?php if ($primaryDomain !== '-'): ?>
                                <strong><?php echo $helper->e($primaryDomain); ?></strong>
                                <?php else: ?>
                                <span class="text-muted">Not configured</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- WHMCS Product Name (if different from SSL product) -->
                        <?php if (!empty($order->whmcs_product_name)): ?>
                        <tr>
                            <th>WHMCS Product:</th>
                            <td><?php echo $helper->e($order->whmcs_product_name); ?></td>
                        </tr>
                        <?php endif; ?>

                        <!-- Billing Cycle -->
                        <tr>
                            <th>Billing Cycle:</th>
                            <td>
                                <?php if (!empty($order->billingcycle)): ?>
                                <span class="label label-info"><?php echo $helper->e($order->billingcycle); ?></span>
                                <?php if (!empty($order->amount) && $order->amount > 0): ?>
                                <span class="text-muted"> - $<?php echo number_format($order->amount, 2); ?></span>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Next Due Date -->
                        <?php if (!empty($order->nextduedate) && $order->nextduedate !== '0000-00-00'): ?>
                        <tr>
                            <th>Next Due Date:</th>
                            <td><?php echo $helper->formatDate($order->nextduedate); ?></td>
                        </tr>
                        <?php endif; ?>

                        <!-- Registration Date (Service) -->
                        <?php if (!empty($order->regdate) && $order->regdate !== '0000-00-00'): ?>
                        <tr>
                            <th>Service Reg Date:</th>
                            <td><?php echo $helper->formatDate($order->regdate); ?></td>
                        </tr>
                        <?php endif; ?>                        
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
                        <?php if (!empty($order->companyname)): ?>
                        <tr>
                            <th>Company:</th>
                            <td><?php echo $helper->e($order->companyname); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo $helper->e($order->client_email); ?></td>
                        </tr>
                        <?php if (!empty($order->phonenumber)): ?>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo $helper->e($order->phonenumber); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Certificate Info + Quick Actions -->
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

            <!-- Domain Validation - Full Width -->
            <?php 
            // Get domains from config domainInfo
            $domainList = isset($config['domainInfo']) ? $config['domainInfo'] : [];
            ?>
            <?php if (!empty($domainList)): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-globe"></i> Domain Validation</h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    <table class="table table-striped table-hover" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>DCV Method</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($domainList as $domain): ?>
                            <?php 
                            $domainName = isset($domain['domainName']) ? $domain['domainName'] : (isset($domain['domain']) ? $domain['domain'] : 'N/A');
                            $dcvMethod = isset($domain['dcvMethod']) ? $domain['dcvMethod'] : (isset($domain['method']) ? $domain['method'] : '-');
                            $isVerified = !empty($domain['isVerified']) || (isset($domain['is_verify']) && $domain['is_verify'] === 'verified');
                            ?>
                            <tr>
                                <td><code><?php echo $helper->e($domainName); ?></code></td>
                                <td><?php echo $helper->e(strtoupper($dcvMethod)); ?></td>
                                <td class="text-center">
                                    <?php if ($isVerified): ?>
                                    <span class="label label-success"><i class="fa fa-check"></i> Verified</span>
                                    <?php else: ?>
                                    <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$isVerified && !empty($order->remoteid)): ?>
                                    <button type="button" class="btn btn-xs btn-primary btn-resend-dcv" 
                                            data-domain="<?php echo $helper->e($domainName); ?>">
                                        <i class="fa fa-send"></i> Resend
                                    </button>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

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
                        
                        <!-- Download CSR Button -->
                        <button type="button" class="btn btn-default" id="btnDownloadCsr"
                                <?php echo !$hasCsr ? 'disabled' : ''; ?>
                                title="<?php echo $hasCsr ? 'Download CSR file' : 'CSR not available'; ?>">
                            <i class="fa fa-file-code-o"></i> Download CSR
                        </button>
                        
                        <!-- Download Certificate Button -->
                        <button type="button" class="btn btn-success" id="btnDownloadCert"
                                <?php echo !$hasCert ? 'disabled' : ''; ?>
                                title="<?php echo $hasCert ? 'Download Certificate files (ZIP)' : 'Certificate not yet issued'; ?>">
                            <i class="fa fa-download"></i> Download Certificate
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
                        
                        <?php if (in_array(strtolower($order->status), ['awaiting', 'draft', 'pending', 'awaiting configuration'])): ?>
                        <button type="button" class="btn btn-danger btn-action" data-action="cancel">
                            <i class="fa fa-times"></i> Cancel Order
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log - Keep Original Structure -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-history"></i> Activity Log</h3>
        </div>
        <div class="panel-body" style="padding: 0;">
            <table class="table table-striped" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 150px;">Date</th>
                        <th style="width: 120px;">Action</th>
                        <th style="width: 120px;">Admin</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activityLogs)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding: 20px;">
                            No activity recorded.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($activityLogs as $log): ?>
                    <tr>
                        <td><small><?php echo $helper->formatDateTime($log->created_at); ?></small></td>
                        <td>
                            <span class="label label-default"><?php echo $helper->e($log->action); ?></span>
                        </td>
                        <td>
                            <?php 
                            $adminName = isset($log->admin_firstname) ? trim($log->admin_firstname . ' ' . $log->admin_lastname) : '';
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

<!-- Loading Modal for Download -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding: 30px;">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                <h4 style="margin-top: 15px;">Processing...</h4>
                <p class="text-muted" id="loadingMessage">Please wait</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modulelink = '<?php echo $modulelink; ?>';
    var orderId = <?php echo $order->id; ?>;
    var currentAction = '';

    // CSR data for download
    var csrData = <?php echo json_encode($config['csr'] ?? ''); ?>;
    
    // Primary domain for filename
    var primaryDomain = <?php echo json_encode($primaryDomain !== '-' ? $primaryDomain : 'certificate'); ?>;

    // ===== Download CSR Button =====
    document.getElementById('btnDownloadCsr').addEventListener('click', function() {
        if (!csrData) {
            alert('CSR is not available.');
            return;
        }
        
        // Create blob and download
        var blob = new Blob([csrData], { type: 'application/pkcs10' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = primaryDomain.replace(/\*/g, 'wildcard').replace(/\./g, '_') + '.csr';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    });

    // ===== Download Certificate Button (ZIP via AJAX) =====
    document.getElementById('btnDownloadCert').addEventListener('click', function() {
        var btn = this;
        
        // Show loading
        $('#loadingModal').modal('show');
        $('#loadingMessage').text('Preparing certificate files...');
        
        // AJAX call to download certificate
        $.ajax({
            url: modulelink + '&action=order&id=' + orderId,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax_action: 'download_cert',
                order_id: orderId
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                // Check success and content exists (content is at root level, not in response.data)
                if (response.success && response.content) {
                    // Decode base64 and download
                    var binary = atob(response.content);
                    var len = binary.length;
                    var bytes = new Uint8Array(len);
                    for (var i = 0; i < len; i++) {
                        bytes[i] = binary.charCodeAt(i);
                    }
                    
                    var blob = new Blob([bytes], { type: 'application/zip' });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = response.name || (primaryDomain.replace(/\*/g, 'wildcard').replace(/\./g, '_') + '.zip');
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    alert('Error: ' + (response.message || 'Failed to download certificate'));
                }
            },
            error: function(xhr, status, error) {
                $('#loadingModal').modal('hide');
                alert('Request failed: ' + error);
            }
        });
    });

    // ===== Action buttons (refresh, cancel, revoke, etc.) =====
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
            
            $('#actionModal').modal('show');
        });
    });

    // Confirm action
    document.getElementById('confirmAction').addEventListener('click', function() {
        var reason = document.getElementById('actionReason').value;
        
        $('#actionModal').modal('hide');
        
        // Show loading
        $('#loadingModal').modal('show');
        $('#loadingMessage').text('Processing action...');

        // Send AJAX request
        $.ajax({
            url: modulelink + '&action=order&id=' + orderId,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax_action: currentAction,
                order_id: orderId,
                reason: reason
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                if (response.success) {
                    alert(response.message || 'Action completed successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function() {
                $('#loadingModal').modal('hide');
                alert('Request failed. Please try again.');
            }
        });
    });

    // ===== Resend DCV handler =====
    document.querySelectorAll('.btn-resend-dcv').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var domain = this.dataset.domain;
            var btnEl = this;
            
            if (!confirm('Resend DCV validation email for ' + domain + '?')) {
                return;
            }
            
            btnEl.disabled = true;
            btnEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            
            $.ajax({
                url: modulelink + '&action=order&id=' + orderId,
                type: 'POST',
                dataType: 'json',
                data: {
                    ajax_action: 'resend_dcv',
                    order_id: orderId,
                    domain: domain
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'DCV email sent successfully');
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                    }
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<i class="fa fa-send"></i> Resend';
                },
                error: function() {
                    alert('Request failed');
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<i class="fa fa-send"></i> Resend';
                }
            });
        });
    });
});
</script>