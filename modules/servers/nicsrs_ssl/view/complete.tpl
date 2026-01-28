{**
 * NicSRS SSL Module - Complete Template
 * Displays issued certificate information and download options
 * 
 * @package    nicsrs_ssl
 * @version    2.0.1
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 *}

{* Load CSS *}
<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

<div class="sslm-container">
    {* Header *}
    <div class="sslm-header">
        <h2 class="sslm-title">
            <i class="fas fa-certificate"></i>
            {$_LANG.certificate_issued|default:'Certificate Issued'}
        </h2>
        <div class="sslm-header-info">
            <span class="sslm-product-name">{$productCode|escape:'html'}</span>
            <span class="sslm-status sslm-status-complete">
                <i class="fas fa-check-circle"></i> {$_LANG.status_complete|default:'Active'}
            </span>
        </div>
    </div>

    {* Success Alert *}
    <div class="sslm-alert sslm-alert-success">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>{$_LANG.certificate_ready|default:'Your SSL Certificate is Ready!'}</strong>
            <p>{$_LANG.certificate_ready_desc|default:'Your certificate has been issued and is ready for installation. Download the certificate files below.'}</p>
        </div>
    </div>

    {* Certificate Details *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-info-circle"></i> {$_LANG.certificate_details|default:'Certificate Details'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-cert-info">
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.primary_domain|default:'Primary Domain'}</div>
                    <div class="sslm-cert-info-value">
                        {if isset($domainInfo[0].domainName)}
                            {$domainInfo[0].domainName|escape:'html'}
                        {else}
                            N/A
                        {/if}
                    </div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.order_id|default:'Order ID'}</div>
                    <div class="sslm-cert-info-value">{$certId|default:'N/A'}</div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.issued_date|default:'Issued Date'}</div>
                    <div class="sslm-cert-info-value">{$beginDate|default:'N/A'}</div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.expiry_date|default:'Expiry Date'}</div>
                    <div class="sslm-cert-info-value">{$endDate|default:'N/A'}</div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.validation_type|default:'Validation Type'}</div>
                    <div class="sslm-cert-info-value">{$sslValidationType|upper|default:'DV'}</div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.domains_secured|default:'Domains Secured'}</div>
                    <div class="sslm-cert-info-value">{$domainInfo|@count|default:0}</div>
                </div>
            </div>
            
            {* All Domains List *}
            {if $domainInfo|@count > 1}
            <div style="margin-top: 20px;">
                <label class="sslm-cert-info-label">{$_LANG.all_domains|default:'All Secured Domains'}:</label>
                <ul style="margin: 8px 0; padding-left: 20px;">
                    {foreach from=$domainInfo item=domain}
                    <li>{$domain.domainName|escape:'html'}</li>
                    {/foreach}
                </ul>
            </div>
            {/if}
        </div>
    </div>

    {* Download Section *}
    {if $canDownload}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-download"></i> {$_LANG.download_certificate|default:'Download Certificate'}</h3>
        </div>
        <div class="sslm-section-body">
            <p class="sslm-info-text">
                <i class="fas fa-info-circle"></i>
                {$_LANG.download_instructions|default:'Select the format that matches your web server:'}
            </p>
            
            <div class="sslm-download-grid">
                {* PEM/Nginx Format *}
                <div class="sslm-download-btn download-cert-btn" data-format="pem">
                    <i class="fas fa-file-code"></i>
                    <span>PEM / Nginx</span>
                    <small>.pem</small>
                </div>
                
                {* CRT/Apache Format *}
                <div class="sslm-download-btn download-cert-btn" data-format="crt">
                    <i class="fas fa-file-certificate"></i>
                    <span>CRT / Apache</span>
                    <small>.crt + .ca-bundle</small>
                </div>
                
                {* Private Key *}
                {if $canDownloadKey}
                <div class="sslm-download-btn download-cert-btn" data-format="key">
                    <i class="fas fa-key"></i>
                    <span>{$_LANG.private_key|default:'Private Key'}</span>
                    <small>.key</small>
                </div>
                {/if}
            </div>
            
            {if !$canDownloadKey}
            <div class="sslm-alert sslm-alert-warning" style="margin-top: 20px;">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{$_LANG.no_private_key|default:'Private key is not available. If you generated the CSR elsewhere, use your original private key.'}</span>
            </div>
            {/if}
        </div>
    </div>
    {/if}

    {* Certificate Content (Expandable) *}
    {if $certificate}
    <div class="sslm-section">
        <div class="sslm-section-header" style="cursor: pointer;" onclick="toggleCertContent()">
            <h3><i class="fas fa-file-alt"></i> {$_LANG.certificate_content|default:'Certificate Content'}</h3>
            <i class="fas fa-chevron-down" id="certToggleIcon"></i>
        </div>
        <div class="sslm-section-body" id="certContentSection" style="display: none;">
            <div class="sslm-form-group">
                <label>{$_LANG.certificate|default:'Certificate'}:</label>
                <textarea class="sslm-textarea sslm-code" rows="10" readonly>{$certificate|escape:'html'}</textarea>
            </div>
            
            {if $caCertificate}
            <div class="sslm-form-group">
                <label>{$_LANG.ca_bundle|default:'CA Bundle'}:</label>
                <textarea class="sslm-textarea sslm-code" rows="10" readonly>{$caCertificate|escape:'html'}</textarea>
            </div>
            {/if}
        </div>
    </div>
    {/if}

    {* Actions *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-cogs"></i> {$_LANG.certificate_actions|default:'Certificate Actions'}</h3>
        </div>
        <div class="sslm-section-body">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button" id="refreshStatusBtn" class="sslm-btn sslm-btn-secondary" onclick="SSLManager.refreshStatus()">
                    <i class="fas fa-sync-alt"></i> {$_LANG.refresh_status|default:'Refresh Status'}
                </button>
                <a href="{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}&modop=custom&a=reissue" class="sslm-btn sslm-btn-secondary">
                    <i class="fas fa-redo"></i> {$_LANG.reissue_certificate|default:'Reissue Certificate'}
                </a>
            </div>
        </div>
    </div>

    {* Back Button *}
    <div class="sslm-form-actions">
        <a href="{$WEB_ROOT}/clientarea.php?action=services" class="sslm-btn sslm-btn-secondary">
            <i class="fas fa-arrow-left"></i> {$_LANG.back_to_services|default:'Back to Services'}
        </a>
    </div>
</div>

{* JavaScript *}
<script>
window.sslmConfig = {
    ajaxUrl: '{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}',
    serviceid: '{$serviceid}',
    lang: {
        downloading: '{$_LANG.downloading|default:"Downloading..."}',
        download_error: '{$_LANG.download_error|default:"Download failed"}',
        status_refreshed: '{$_LANG.status_refreshed|default:"Status refreshed"}'
    }
};

function toggleCertContent() {
    var section = document.getElementById('certContentSection');
    var icon = document.getElementById('certToggleIcon');
    if (section.style.display === 'none') {
        section.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
    } else {
        section.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
    }
}

// Download handlers
document.querySelectorAll('.download-cert-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var format = this.getAttribute('data-format');
        downloadCertificate(format);
    });
});

function downloadCertificate(format) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.sslmConfig.ajaxUrl + '&step=downCert', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        try {
            var response = JSON.parse(xhr.responseText);
            if (response.success && response.data) {
                // Trigger download
                var content = atob(response.data.content);
                var blob = new Blob([content], { type: response.data.mime || 'application/octet-stream' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = response.data.filename;
                link.click();
                
                // If CA bundle is separate
                if (response.data.caBundle) {
                    var caContent = atob(response.data.caBundle);
                    var caBlob = new Blob([caContent], { type: 'application/octet-stream' });
                    var caLink = document.createElement('a');
                    caLink.href = URL.createObjectURL(caBlob);
                    caLink.download = response.data.caBundleFilename;
                    setTimeout(function() { caLink.click(); }, 500);
                }
                
                SSLManager.showToast('Download started', 'success');
            } else {
                SSLManager.showToast(response.message || 'Download failed', 'error');
            }
        } catch (e) {
            SSLManager.showToast('Download error', 'error');
        }
    };
    
    xhr.send('format=' + encodeURIComponent(format));
}
</script>

{* Load JavaScript *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>