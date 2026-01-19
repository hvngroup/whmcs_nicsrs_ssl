<?php
/**
 * Import SSL Orders Template
 * 
 * File: templates/import.php
 * 
 * @var array $availableServices Services available for linking
 * @var array $linkedOrders Recently linked orders
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 */
?>

<div class="nicsrs-import">
    
    <!-- Header -->
    <div class="page-header">
        <h3>
            <i class="fa fa-download"></i> Import SSL Certificates
            <small class="text-muted">Link NicSRS certificates to WHMCS</small>
        </h3>
    </div>

    <div class="row">
        <!-- Left: Import Form -->
        <div class="col-md-6">
            
            <!-- Lookup Certificate -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-search"></i> Step 1: Lookup Certificate</h3>
                </div>
                <div class="panel-body">
                    <form id="lookupForm">
                        <div class="form-group">
                            <label>Certificate ID from NicSRS:</label>
                            <input type="text" class="form-control" id="certId" name="cert_id" 
                                   placeholder="Enter NicSRS Certificate ID (e.g., 12345678)" required>
                            <p class="help-block">You can find this ID in your NicSRS portal</p>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Lookup Certificate
                        </button>
                    </form>

                    <!-- Lookup Result -->
                    <div id="lookupResult" style="display: none; margin-top: 20px;">
                        <hr>
                        <h4><i class="fa fa-certificate"></i> Certificate Details</h4>
                        <table class="table table-condensed">
                            <tr>
                                <th style="width: 120px;">Cert ID:</th>
                                <td><code id="resultCertId"></code></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><span id="resultStatus"></span></td>
                            </tr>
                            <tr>
                                <th>Domain(s):</th>
                                <td id="resultDomains"></td>
                            </tr>
                            <tr>
                                <th>Valid From:</th>
                                <td id="resultBeginDate"></td>
                            </tr>
                            <tr>
                                <th>Valid Until:</th>
                                <td id="resultEndDate"></td>
                            </tr>
                            <tr>
                                <th>Certificate:</th>
                                <td id="resultHasCert"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Link to Service -->
            <div class="panel panel-success" id="linkPanel" style="display: none;">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-link"></i> Step 2: Link to WHMCS Service</h3>
                </div>
                <div class="panel-body">
                    <form id="linkForm">
                        <input type="hidden" id="linkCertId" name="cert_id">
                        
                        <div class="form-group">
                            <label>Select WHMCS Service to Link:</label>
                            <select class="form-control" id="serviceSelect" name="service_id">
                                <option value="">-- Select a Service --</option>
                                <?php foreach ($availableServices as $service): ?>
                                <option value="<?php echo $service->serviceid; ?>" 
                                        data-user="<?php echo $helper->e($service->firstname . ' ' . $service->lastname); ?>"
                                        data-email="<?php echo $helper->e($service->email); ?>">
                                    #<?php echo $service->serviceid; ?> - 
                                    <?php echo $helper->e($service->domain ?: 'No domain'); ?> 
                                    (<?php echo $helper->e($service->firstname . ' ' . $service->lastname); ?>)
                                    [<?php echo $service->domainstatus; ?>]
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="help-block">Only SSL services without linked certificate are shown</p>
                        </div>

                        <div class="form-group">
                            <label>Or Search Service:</label>
                            <input type="text" class="form-control" id="searchService" 
                                   placeholder="Search by service ID, domain, or client name...">
                        </div>

                        <div id="searchResults" style="display: none; max-height: 200px; overflow-y: auto; margin-bottom: 15px;">
                        </div>

                        <hr>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="createNewService" name="create_service">
                                <strong>Create new WHMCS service</strong> (if no existing service to link)
                            </label>
                        </div>

                        <div id="newServiceFields" style="display: none; margin-top: 15px;">
                            <div class="form-group">
                                <label>Select Client:</label>
                                <select class="form-control" id="newUserId" name="user_id">
                                    <option value="">-- Select Client --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select SSL Product:</label>
                                <select class="form-control" id="newProductId" name="product_id">
                                    <option value="">-- Select Product --</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fa fa-check"></i> Import & Link Certificate
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Right: Info & History -->
        <div class="col-md-6">
            
            <!-- Instructions -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-info-circle"></i> How to Import</h3>
                </div>
                <div class="panel-body">
                    <ol>
                        <li><strong>Get Certificate ID</strong> from your NicSRS portal (Orders → SSL Orders)</li>
                        <li><strong>Lookup</strong> the certificate using the form</li>
                        <li><strong>Link</strong> to an existing WHMCS service OR create a new one</li>
                        <li>The certificate will appear in Orders list and be manageable from admin</li>
                    </ol>
                    
                    <hr>
                    
                    <h5><i class="fa fa-lightbulb-o"></i> Use Cases:</h5>
                    <ul>
                        <li>Import certificates purchased directly from NicSRS</li>
                        <li>Migrate existing certificates to WHMCS management</li>
                        <li>Link certificates issued outside WHMCS workflow</li>
                    </ul>
                </div>
            </div>

            <!-- Bulk Import -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-list"></i> Bulk Import</h3>
                </div>
                <div class="panel-body">
                    <form id="bulkImportForm">
                        <div class="form-group">
                            <label>Enter Certificate IDs (one per line):</label>
                            <textarea class="form-control" id="bulkCertIds" name="cert_ids" rows="5" 
                                      placeholder="12345678&#10;23456789&#10;34567890"></textarea>
                        </div>
                        <button type="submit" class="btn btn-default">
                            <i class="fa fa-upload"></i> Bulk Import (without linking)
                        </button>
                        <p class="help-block">
                            <small>Certificates will be imported without linking to services. You can link them later.</small>
                        </p>
                    </form>
                </div>
            </div>

            <!-- Recently Imported -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-history"></i> Recently Imported</h3>
                </div>
                <div class="panel-body" style="padding: 0; max-height: 300px; overflow-y: auto;">
                    <table class="table table-striped table-condensed" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Expires</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($linkedOrders)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No imported certificates yet</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($linkedOrders as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo $modulelink; ?>&action=order&id=<?php echo $order->id; ?>">
                                        #<?php echo $order->id; ?>
                                    </a>
                                </td>
                                <td><?php echo $helper->truncate($order->domain, 25); ?></td>
                                <td><?php echo $helper->statusBadge($order->status); ?></td>
                                <td>
                                    <?php if ($order->end_date): ?>
                                    <?php echo $helper->daysLeftBadge($order->end_date); ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
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

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding: 40px;">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                <h4 style="margin-top: 20px;" id="loadingMessage">Processing...</h4>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modulelink = '<?php echo $modulelink; ?>';
    var certData = null;

    // Lookup Certificate
    document.getElementById('lookupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var certId = document.getElementById('certId').value.trim();
        if (!certId) return;

        showLoading('Looking up certificate...');

        $.ajax({
            url: modulelink + '&action=import',
            method: 'POST',
            data: { ajax_action: 'lookup_cert', cert_id: certId },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    certData = response.certificate;
                    displayCertificate(certData);
                } else {
                    alert('Error: ' + response.message);
                    document.getElementById('lookupResult').style.display = 'none';
                    document.getElementById('linkPanel').style.display = 'none';
                }
            },
            error: function() {
                hideLoading();
                alert('Request failed. Please try again.');
            }
        });
    });

    // Display certificate info
    function displayCertificate(cert) {
        document.getElementById('resultCertId').textContent = cert.certId;
        document.getElementById('resultStatus').innerHTML = getStatusBadge(cert.status);
        document.getElementById('resultBeginDate').textContent = cert.beginDate || 'N/A';
        document.getElementById('resultEndDate').textContent = cert.endDate || 'N/A';
        document.getElementById('resultHasCert').innerHTML = cert.hasCertificate 
            ? '<span class="text-success"><i class="fa fa-check"></i> Available</span>'
            : '<span class="text-muted">Not yet issued</span>';

        // Display domains
        var domainsHtml = '';
        if (cert.domains && cert.domains.length) {
            cert.domains.forEach(function(d) {
                domainsHtml += '<div>' + d.domain;
                if (d.verified) {
                    domainsHtml += ' <span class="label label-success">Verified</span>';
                }
                domainsHtml += '</div>';
            });
        } else {
            domainsHtml = cert.domain || 'N/A';
        }
        document.getElementById('resultDomains').innerHTML = domainsHtml;

        document.getElementById('lookupResult').style.display = 'block';
        document.getElementById('linkPanel').style.display = 'block';
        document.getElementById('linkCertId').value = cert.certId;
    }

    function getStatusBadge(status) {
        var badges = {
            'complete': '<span class="label label-success">Complete</span>',
            'pending': '<span class="label label-warning">Pending</span>',
            'cancelled': '<span class="label label-danger">Cancelled</span>'
        };
        return badges[status.toLowerCase()] || '<span class="label label-default">' + status + '</span>';
    }

    // Toggle new service fields
    document.getElementById('createNewService').addEventListener('change', function() {
        var fields = document.getElementById('newServiceFields');
        var serviceSelect = document.getElementById('serviceSelect');
        
        if (this.checked) {
            fields.style.display = 'block';
            serviceSelect.disabled = true;
            loadClientsAndProducts();
        } else {
            fields.style.display = 'none';
            serviceSelect.disabled = false;
        }
    });

    // Load clients and products for new service
    function loadClientsAndProducts() {
        // Load clients - simplified, in production use AJAX search
        var userSelect = document.getElementById('newUserId');
        if (userSelect.options.length <= 1) {
            // This would typically be an AJAX call
            userSelect.innerHTML = '<option value="">-- Type to search clients --</option>';
        }
    }

    // Search services
    var searchTimeout;
    document.getElementById('searchService').addEventListener('input', function() {
        var query = this.value.trim();
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            document.getElementById('searchResults').style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: modulelink + '&action=import',
                method: 'POST',
                data: { ajax_action: 'search_services', query: query },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.services) {
                        displaySearchResults(response.services);
                    }
                }
            });
        }, 300);
    });

    function displaySearchResults(services) {
        var container = document.getElementById('searchResults');
        if (!services.length) {
            container.innerHTML = '<p class="text-muted">No services found</p>';
            container.style.display = 'block';
            return;
        }

        var html = '<table class="table table-condensed table-hover" style="margin-bottom:0;">';
        services.forEach(function(s) {
            html += '<tr class="search-result-row" data-id="' + s.serviceid + '" style="cursor:pointer;">';
            html += '<td>#' + s.serviceid + '</td>';
            html += '<td>' + (s.domain || 'N/A') + '</td>';
            html += '<td>' + s.firstname + ' ' + s.lastname + '</td>';
            html += '<td><span class="label label-default">' + s.domainstatus + '</span></td>';
            html += '</tr>';
        });
        html += '</table>';
        
        container.innerHTML = html;
        container.style.display = 'block';

        // Click to select
        container.querySelectorAll('.search-result-row').forEach(function(row) {
            row.addEventListener('click', function() {
                var id = this.dataset.id;
                document.getElementById('serviceSelect').value = id;
                container.style.display = 'none';
                document.getElementById('searchService').value = '';
            });
        });
    }

    // Link Form Submit
    document.getElementById('linkForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var certId = document.getElementById('linkCertId').value;
        var serviceId = document.getElementById('serviceSelect').value;
        var createNew = document.getElementById('createNewService').checked;

        if (!certId) {
            alert('Please lookup a certificate first');
            return;
        }

        if (!serviceId && !createNew) {
            alert('Please select a service to link or check "Create new service"');
            return;
        }

        var action = serviceId ? 'link_existing' : 'import_cert';
        var data = {
            ajax_action: action,
            cert_id: certId,
            service_id: serviceId,
            create_service: createNew ? 1 : 0,
            user_id: document.getElementById('newUserId').value,
            product_id: document.getElementById('newProductId').value
        };

        showLoading('Importing certificate...');

        $.ajax({
            url: modulelink + '&action=import',
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                hideLoading();
                alert('Request failed. Please try again.');
            }
        });
    });

    // Bulk Import
    document.getElementById('bulkImportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var textarea = document.getElementById('bulkCertIds').value.trim();
        if (!textarea) {
            alert('Please enter at least one certificate ID');
            return;
        }

        var certIds = textarea.split('\n').map(function(id) {
            return id.trim();
        }).filter(function(id) {
            return id.length > 0;
        });

        if (!certIds.length) {
            alert('No valid certificate IDs found');
            return;
        }

        if (!confirm('Import ' + certIds.length + ' certificates?')) {
            return;
        }

        showLoading('Importing ' + certIds.length + ' certificates...');

        $.ajax({
            url: modulelink + '&action=import',
            method: 'POST',
            data: { ajax_action: 'bulk_import', cert_ids: certIds },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                var msg = response.message;
                if (response.errors && response.errors.length) {
                    msg += '\n\nErrors:\n' + response.errors.join('\n');
                }
                alert(msg);
                if (response.imported > 0) {
                    location.reload();
                }
            },
            error: function() {
                hideLoading();
                alert('Request failed. Please try again.');
            }
        });
    });

    function showLoading(message) {
        document.getElementById('loadingMessage').textContent = message || 'Processing...';
        $('#loadingModal').modal('show');
    }

    function hideLoading() {
        $('#loadingModal').modal('hide');
    }
});
</script>