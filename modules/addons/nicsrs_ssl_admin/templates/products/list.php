<?php
/**
 * Products List Template
 * 
 * @var array $products Products list
 * @var array $vendors All vendors
 * @var array $availableVendors Vendors with products
 * @var string $currentVendor Current vendor filter
 * @var string $currentType Current type filter
 * @var string $search Search query
 * @var \NicsrsAdmin\Helper\Pagination $pagination Pagination object
 * @var int $total Total products
 * @var string|null $lastSync Last sync time
 * @var string $modulelink Module link
 * @var \NicsrsAdmin\Helper\ViewHelper $helper View helper
 */
?>

<div class="nicsrs-products">
    
    <!-- Header -->
    <div class="page-header clearfix">
        <h3 class="pull-left" style="margin: 0;">
            <i class="fa fa-cube"></i> SSL Products
            <small class="text-muted">(<?php echo number_format($total); ?> products)</small>
        </h3>
        <div class="pull-right">
            <button type="button" class="btn btn-primary" id="btnSyncAll">
                <i class="fa fa-refresh"></i> Sync All Products
            </button>
        </div>
    </div>

    <!-- Last Sync Info -->
    <?php if ($lastSync): ?>
    <p class="text-muted">
        <small><i class="fa fa-clock-o"></i> Last synced: <?php echo $helper->formatDateTime($lastSync); ?></small>
    </p>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> 
        Products have not been synced yet. Click "Sync All Products" to fetch products from NicSRS API.
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="panel panel-default">
        <div class="panel-body">
            <form method="get" class="form-inline">
                <input type="hidden" name="module" value="nicsrs_ssl_admin">
                <input type="hidden" name="action" value="products">
                
                <div class="form-group">
                    <label>Vendor:</label>
                    <select name="vendor" class="form-control" style="width: 150px;">
                        <option value="">All Vendors</option>
                        <?php foreach ($availableVendors as $vendor): ?>
                        <option value="<?php echo $helper->e($vendor); ?>" <?php echo $currentVendor === $vendor ? 'selected' : ''; ?>>
                            <?php echo $helper->e($vendor); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Type:</label>
                    <select name="type" class="form-control" style="width: 120px;">
                        <option value="">All Types</option>
                        <option value="dv" <?php echo $currentType === 'dv' ? 'selected' : ''; ?>>DV</option>
                        <option value="ov" <?php echo $currentType === 'ov' ? 'selected' : ''; ?>>OV</option>
                        <option value="ev" <?php echo $currentType === 'ev' ? 'selected' : ''; ?>>EV</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Search:</label>
                    <input type="text" name="search" class="form-control" style="width: 200px;"
                           placeholder="Product name or code..." value="<?php echo $helper->e($search); ?>">
                </div>
                
                <button type="submit" class="btn btn-default">
                    <i class="fa fa-filter"></i> Filter
                </button>
                
                <?php if ($currentVendor || $currentType || $search): ?>
                <a href="<?php echo $modulelink; ?>&action=products" class="btn btn-link">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="panel panel-default">
        <div class="panel-body" style="padding: 0;">
            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Vendor</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Wildcard</th>
                        <th class="text-center">SAN</th>
                        <th class="text-center">Max Domains</th>
                        <th class="text-right">Price (1Y)</th>
                        <th class="text-right">Price (2Y)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 40px;">
                            <?php if ($lastSync): ?>
                            No products found matching your filters.
                            <?php else: ?>
                            No products available. Please sync products first.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <code><?php echo $helper->e($product['product_code']); ?></code>
                        </td>
                        <td>
                            <strong><?php echo $helper->e($product['product_name']); ?></strong>
                        </td>
                        <td><?php echo $helper->e($product['vendor']); ?></td>
                        <td class="text-center">
                            <?php echo $helper->validationBadge($product['validation_type']); ?>
                        </td>
                        <td class="text-center">
                            <?php echo $helper->yesNoIcon($product['support_wildcard']); ?>
                        </td>
                        <td class="text-center">
                            <?php echo $helper->yesNoIcon($product['support_san']); ?>
                        </td>
                        <td class="text-center"><?php echo $product['max_domains']; ?></td>
                        <td class="text-right">
                            <?php echo $helper->formatPrice($product['price_1y']); ?>
                        </td>
                        <td class="text-right">
                            <?php echo $helper->formatPrice($product['price_2y']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination->getTotalPages() > 1): ?>
        <div class="panel-footer clearfix">
            <div class="pull-left text-muted" style="line-height: 34px;">
                <?php echo $pagination->getInfo(); ?>
            </div>
            <div class="pull-right">
                <?php echo $pagination->render(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Sync Modal -->
<div class="modal fade" id="syncModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding: 40px;">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                <h4 style="margin-top: 20px;">Syncing Products...</h4>
                <p class="text-muted">Please wait while we fetch products from NicSRS API.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modulelink = '<?php echo $modulelink; ?>';
    
    // Sync All Products
    document.getElementById('btnSyncAll').addEventListener('click', function() {
        if (!confirm('Sync products from all vendors? This may take a moment.')) {
            return;
        }
        
        var btn = this;
        btn.disabled = true;
        $('#syncModal').modal({backdrop: 'static', keyboard: false});
        
        $.ajax({
            url: modulelink + '&action=products',
            method: 'POST',
            data: { ajax_action: 'sync_all' },
            dataType: 'json',
            success: function(response) {
                $('#syncModal').modal('hide');
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                    if (response.errors && response.errors.length) {
                        console.log('Sync errors:', response.errors);
                    }
                }
            },
            error: function() {
                $('#syncModal').modal('hide');
                alert('Request failed. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
            }
        });
    });
});
</script>