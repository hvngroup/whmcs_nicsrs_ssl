{**
 * NicSRS SSL Module - Complete/Issued Template
 * Shows certificate details and download options
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 *}

{* Load CSS *}
<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

<div class="sslm-container">
    {* Header *}
    <div class="sslm-header">
        <h2 class="sslm-title">
            <i class="fas fa-shield-alt"></i>
            {$_LANG.certificate_management|default:'Certificate Management'}
        </h2>
        <div class="sslm-header-info">
            <span class="sslm-product-name">{$productCode|escape:'html'}</span>
            <span class="sslm-badge sslm-badge-success">{$_LANG.issued|default:'Issued'}</span>
        </div>
    </div>

    {* Status Card *}
    <div class="sslm-status-card">
        <div class="sslm-status-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="sslm-status-content">
            <div class="sslm-status-title">{$_LANG.certificate_issued|default:'Certificate Issued'}</div>
            <div class="sslm-status-desc">{$_LANG.complete_des|default:'Your SSL certificate has been issued successfully. You can download it below.'}</div>
        </div>
        <button type="button" id="refreshStatusBtn" class="sslm-btn sslm-btn-secondary">
            <i class="fas fa-sync-alt"></i> {$_LANG.refresh|default:'Refresh'}
        </button>
    </div>

    {* Certificate Info *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-info-circle"></i> {$_LANG.certificate_info|default:'Certificate Information'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-info-card">
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.certificate_id|default:'Certificate ID'}:</span>
                    <span class="sslm-info-value sslm-code">{$certId|escape:'html'}</span>
                </div>
                {if $vendorId}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.vendor_id|default:'Vendor ID'}:</span>
                    <span class="sslm-info-value sslm-code">{$vendorId|escape:'html'}</span>
                </div>
                {/if}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.status|default:'Status'}:</span>
                    <span class="sslm-info-value"><span class="sslm-badge sslm-badge-success">{$_LANG.issued|default:'Issued'}</span></span>
                </div>
                {if $beginDate}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.cert_begin|default:'Valid From'}:</span>
                    <span class="sslm-info-value">{$beginDate|escape:'html'}</span>
                </div>
                {/if}
                {if $endDate}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.cert_end|default:'Valid Until'}:</span>
                    <span class="sslm-info-value">{$endDate|escape:'html'}</span>
                </div>
                {/if}
            </div>

            {* Domain List *}
            {if $domainInfo}
            <div class="sslm-info-card">
                <div class="sslm-info-card-title">
                    <i class="fas fa-globe"></i> {$_LANG.domain_info|default:'Protected Domains'}
                </div>
                {foreach $domainInfo as $domain}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{if $domain@first}{$_LANG.primary_domain|default:'Primary'}{else}{$_LANG.additional_domains|default:'SAN'}{/if}:</span>
                    <span class="sslm-info-value">{$domain.domainName|escape:'html'}</span>
                </div>
                {/foreach}
            </div>
            {/if}
        </div>
    </div>

    {* Download Section *}
    {if $hasCertificate}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-download"></i> {$_LANG.download_certificate|default:'Download Certificate'}</h3>
        </div>
        <div class="sslm-section-body">
            <p class="sslm-help-text" style="margin-bottom: 16px;">
                {$_LANG.select_download_format|default:'Select the format suitable for your server:'}
            </p>
            
            <div class="sslm-download-options">
                <button type="button" class="sslm-download-option download-cert-btn" data-format="nginx">
                    <i class="fas fa-file-code"></i>
                    <span>{$_LANG.format_nginx|default:'Nginx (.pem)'}</span>
                </button>
                <button type="button" class="sslm-download-option download-cert-btn" data-format="apache">
                    <i class="fas fa-file-alt"></i>
                    <span>{$_LANG.format_apache|default:'Apache (.crt)'}</span>
                </button>
                <button type="button" class="sslm-download-option download-cert-btn" data-format="iis">
                    <i class="fab fa-windows"></i>
                    <span>{$_LANG.format_iis|default:'IIS (.pfx)'}</span>
                </button>
                <button type="button" class="sslm-download-option download-cert-btn" data-format="tomcat">
                    <i class="fas fa-coffee"></i>
                    <span>{$_LANG.format_tomcat|default:'Tomcat (.jks)'}</span>
                </button>
                <button type="button" class="sslm-download-option download-cert-btn" data-format="all">
                    <i class="fas fa-file-archive"></i>
                    <span>{$_LANG.format_all|default:'All Formats (ZIP)'}</span>
                </button>
            </div>

            {if $hasPrivateKey}
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--sslm-border-light);">
                <button type="button" id="downloadKeyBtn" class="sslm-btn sslm-btn-secondary">
                    <i class="fas fa-key"></i> {$_LANG.down_key|default:'Download Private Key'}
                </button>
                <p class="sslm-help-text" style="margin-top: 8px;">
                    <i class="fas fa-exclamation-triangle" style="color: var(--sslm-warning);"></i>
                    {$_LANG.private_key_warning|default:'Keep your private key secure. Do not share it with anyone.'}
                </p>
            </div>
            {/if}
        </div>
    </div>
    {/if}

    {* Certificate Content (collapsible) *}
    {if $certificate}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-certificate"></i> {$_LANG.certificate|default:'Certificate Content'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-code-block">
                <button type="button" class="sslm-copy-btn" onclick="copyToClipboard(this, 'certContent')">
                    <i class="fas fa-copy"></i> {$_LANG.copy|default:'Copy'}
                </button>
                <pre id="certContent">{$certificate|escape:'html'}</pre>
            </div>
            
            {if $caCertificate}
            <h4 style="margin-top: 20px; margin-bottom: 12px;">{$_LANG.ca_certificate|default:'CA Bundle'}</h4>
            <div class="sslm-code-block">
                <button type="button" class="sslm-copy-btn" onclick="copyToClipboard(this, 'caContent')">
                    <i class="fas fa-copy"></i> {$_LANG.copy|default:'Copy'}
                </button>
                <pre id="caContent">{$caCertificate|escape:'html'}</pre>
            </div>
            {/if}
        </div>
    </div>
    {/if}

    {* Actions *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-cog"></i> {$_LANG.actions|default:'Actions'}</h3>
        </div>
        <div class="sslm-section-body">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button" id="reissueBtn" class="sslm-btn sslm-btn-primary">
                    <i class="fas fa-redo"></i> {$_LANG.reissue_certificate|default:'Reissue Certificate'}
                </button>
                <button type="button" id="revokeBtn" class="sslm-btn sslm-btn-danger">
                    <i class="fas fa-ban"></i> {$_LANG.revoke_certificate|default:'Revoke Certificate'}
                </button>
            </div>
            <p class="sslm-help-text" style="margin-top: 12px;">
                {$_LANG.reissue_info|default:'Reissue allows you to replace your certificate with a new one (e.g., if your private key was compromised).'}
            </p>
        </div>
    </div>
</div>

{* JavaScript Configuration *}
<script>
window.sslmConfig = {
    ajaxUrl: "{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}",
    serviceid: "{$serviceid}",
    configData: {$configData|json_encode nofilter},
    lang: {$_LANG_JSON nofilter}
};

function copyToClipboard(btn, elementId) {
    var content = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(content).then(function() {
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> {$_LANG.copied|default:"Copied!"}';
        setTimeout(function() {
            btn.innerHTML = originalText;
        }, 2000);
    });
}

// Reissue button handler
document.getElementById('reissueBtn').addEventListener('click', function() {
    if (confirm('{$_LANG.sure_to_replace|default:"Are you sure you want to reissue this certificate?"}')) {
        window.location.href = window.sslmConfig.ajaxUrl + '&action=reissue';
    }
});

// Revoke button handler  
document.getElementById('revokeBtn').addEventListener('click', function() {
    if (confirm('{$_LANG.sure_to_revoke|default:"Are you sure you want to revoke this certificate? This action cannot be undone."}')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.sslmConfig.ajaxUrl + '&step=revoke', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('{$_LANG.revoked_success|default:"Certificate revoked successfully"}');
                    window.location.reload();
                } else {
                    alert(response.message || 'Error');
                }
            } catch(e) {
                alert('Error processing request');
            }
        };
        xhr.send('');
    }
});
</script>

{* Load JS *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>