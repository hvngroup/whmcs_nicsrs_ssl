{**
 * NicSRS SSL Module - Pending/Message Template
 * Shows DCV instructions with change DCV functionality
 * 
 * @package    nicsrs_ssl
 * @version    2.0.3
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

<div class="sslm-container">
    {* Header *}
    <div class="sslm-header">
        <h2 class="sslm-title">
            <i class="fas fa-shield-alt"></i>
            {$_LANG.certificate_status|default:'Certificate Status'}
        </h2>
        <div class="sslm-header-info">
            <span class="sslm-product-name">{$productCode|escape:'html'}</span>
        </div>
    </div>

    {* Progress Steps *}
    <div class="sslm-progress">
        <div class="sslm-progress-step completed">
            <div class="sslm-progress-icon"><i class="fas fa-check"></i></div>
            <div class="sslm-progress-label">{$_LANG.step_ordered|default:'Ordered'}</div>
        </div>
        <div class="sslm-progress-step completed">
            <div class="sslm-progress-icon"><i class="fas fa-check"></i></div>
            <div class="sslm-progress-label">{$_LANG.step_submitted|default:'Submitted'}</div>
        </div>
        <div class="sslm-progress-step active">
            <div class="sslm-progress-icon"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="sslm-progress-label">{$_LANG.step_validation|default:'Validation'}</div>
        </div>
        <div class="sslm-progress-step">
            <div class="sslm-progress-icon"><i class="fas fa-certificate"></i></div>
            <div class="sslm-progress-label">{$_LANG.step_issued|default:'Issued'}</div>
        </div>
    </div>

    {* Order Info Card *}
    <div class="sslm-card">
        <div class="sslm-card-header">
            <h3><i class="fas fa-info-circle"></i> {$_LANG.order_details|default:'Order Details'}</h3>
            <div class="sslm-card-header-actions">
                <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-primary" id="refreshStatusBtn" onclick="SSLManager.refreshStatus()">
                    <i class="fas fa-sync-alt"></i> {$_LANG.refresh|default:'Refresh'}
                </button>
            </div>
        </div>
        <div class="sslm-card-body">
            <div class="sslm-info-grid">
                <div class="sslm-info-item">
                    <label>{$_LANG.order_id|default:'Order ID'}</label>
                    <span>#{$order->id|escape:'html'}</span>
                </div>
                <div class="sslm-info-item">
                    <label>{$_LANG.certificate_id|default:'Certificate ID'}</label>
                    <span class="sslm-cert-id">{$certId|escape:'html'|default:'N/A'}</span>
                </div>
                <div class="sslm-info-item">
                    <label>{$_LANG.status|default:'Status'}</label>
                    <span class="sslm-badge sslm-status-pending">
                        <i class="fas fa-clock"></i> {$_LANG.pending_validation|default:'Pending Validation'}
                    </span>
                </div>
                <div class="sslm-info-item">
                    <label>{$_LANG.product|default:'Product'}</label>
                    <span>{$productCode|escape:'html'}</span>
                </div>
                <div class="sslm-info-item">
                    <label>{$_LANG.order_date|default:'Order Date'}</label>
                    <span>{$order->provisiondate|escape:'html'|default:'N/A'}</span>
                </div>
                <div class="sslm-info-item">
                    <label>{$_LANG.primary_domain|default:'Primary Domain'}</label>
                    <span>{$configData.domainInfo[0].domainName|escape:'html'|default:'N/A'}</span>
                </div>
            </div>
        </div>
    </div>

    {* DCV Required Card *}
    <div class="sslm-card sslm-card-warning">
        <div class="sslm-card-header">
            <h3><i class="fas fa-exclamation-triangle"></i> {$_LANG.action_required|default:'Action Required: Domain Validation'}</h3>
        </div>
        <div class="sslm-card-body">
            <div class="sslm-alert sslm-alert-warning">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>{$_LANG.important|default:'Important'}:</strong>
                    {$_LANG.dcv_instruction_main|default:'Complete domain validation to receive your SSL certificate. Follow the instructions below for each domain.'}
                </div>
            </div>

            {* Domain DCV Cards *}
            {if $configData.domainInfo}
            {foreach from=$configData.domainInfo item=domain key=idx}
            {assign var="dcvMethod" value=$domain.dcvMethod|default:'CNAME_CSR_HASH'}
            <div class="sslm-dcv-card" data-domain="{$domain.domainName|escape:'html'}">
                <div class="sslm-dcv-card-header">
                    <div class="sslm-dcv-domain">
                        <i class="fas fa-globe"></i>
                        <span>{$domain.domainName|escape:'html'}</span>
                    </div>
                    <div class="sslm-dcv-card-actions">
                        <span class="sslm-dcv-method-tag">
                            {if $dcvMethod == 'EMAIL'}
                                <i class="fas fa-envelope"></i> Email
                            {elseif $dcvMethod == 'HTTP_CSR_HASH' || $dcvMethod == 'HTTPS_CSR_HASH'}
                                <i class="fas fa-file-alt"></i> HTTP File
                            {else}
                                <i class="fas fa-server"></i> DNS CNAME
                            {/if}
                        </span>
                        <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-outline" 
                                onclick="SSLManager.openChangeDCVModal('{$domain.domainName|escape:'javascript'}', '{$dcvMethod|escape:'javascript'}')">
                            <i class="fas fa-exchange-alt"></i> {$_LANG.change_method|default:'Change'}
                        </button>
                    </div>
                </div>
                <div class="sslm-dcv-card-body">
                    {* DNS CNAME Validation *}
                    {if $dcvMethod == 'CNAME_CSR_HASH' || $dcvMethod == 'DNS_CSR_HASH'}
                    <p style="margin-bottom:12px;">
                        <i class="fas fa-info-circle" style="color:var(--sslm-info);"></i>
                        {$_LANG.dns_instruction|default:'Add the following DNS record to your domain:'}
                    </p>
                    <div class="sslm-code-group">
                        <div class="sslm-code-row">
                            <div class="sslm-code-label">{$_LANG.type|default:'Type'}</div>
                            <div class="sslm-code-value">
                                <code>{$applyReturn.DCVdnsType|escape:'html'|default:'CNAME'}</code>
                                <button type="button" class="sslm-code-copy" onclick="SSLManager.copyToClipboard('{$applyReturn.DCVdnsType|escape:'javascript'|default:'CNAME'}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sslm-code-row">
                            <div class="sslm-code-label">{$_LANG.host_name|default:'Host/Name'}</div>
                            <div class="sslm-code-value">
                                <code>{$applyReturn.DCVdnsHost|escape:'html'}</code>
                                <button type="button" class="sslm-code-copy" onclick="SSLManager.copyToClipboard('{$applyReturn.DCVdnsHost|escape:'javascript'}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sslm-code-row">
                            <div class="sslm-code-label">{$_LANG.value|default:'Value/Points To'}</div>
                            <div class="sslm-code-value">
                                <code>{$applyReturn.DCVdnsValue|escape:'html'}</code>
                                <button type="button" class="sslm-code-copy" onclick="SSLManager.copyToClipboard('{$applyReturn.DCVdnsValue|escape:'javascript'}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="sslm-help-text" style="margin-top:12px;">
                        <i class="fas fa-clock"></i> {$_LANG.dns_propagation|default:'DNS changes may take 5-30 minutes to propagate. TTL: 300-3600 seconds recommended.'}
                    </p>

                    {* HTTP File Validation *}
                    {elseif $dcvMethod == 'HTTP_CSR_HASH' || $dcvMethod == 'HTTPS_CSR_HASH'}
                    <p style="margin-bottom:12px;">
                        <i class="fas fa-info-circle" style="color:var(--sslm-info);"></i>
                        {$_LANG.http_instruction|default:'Create a file at the following location on your web server:'}
                    </p>
                    <div class="sslm-code-group">
                        <div class="sslm-code-row">
                            <div class="sslm-code-label">{$_LANG.file_url|default:'File URL'}</div>
                            <div class="sslm-code-value">
                                <code>{if $dcvMethod == 'HTTPS_CSR_HASH'}https{else}http{/if}://{$domain.domainName}/.well-known/pki-validation/{$applyReturn.DCVfileName|escape:'html'}</code>
                                <button type="button" class="sslm-code-copy" onclick="SSLManager.copyToClipboard('{if $dcvMethod == 'HTTPS_CSR_HASH'}https{else}http{/if}://{$domain.domainName|escape:'javascript'}/.well-known/pki-validation/{$applyReturn.DCVfileName|escape:'javascript'}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sslm-code-row">
                            <div class="sslm-code-label">{$_LANG.file_name|default:'File Name'}</div>
                            <div class="sslm-code-value">
                                <code>{$applyReturn.DCVfileName|escape:'html'}</code>
                                <button type="button" class="sslm-code-copy" onclick="SSLManager.copyToClipboard('{$applyReturn.DCVfileName|escape:'javascript'}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sslm-code-row">
                            <div class="sslm-code-label">{$_LANG.file_content|default:'File Content'}</div>
                            <div class="sslm-code-value">
                                <code>{$applyReturn.DCVfileContent|escape:'html'}</code>
                                <button type="button" class="sslm-code-copy" onclick="SSLManager.copyToClipboard('{$applyReturn.DCVfileContent|escape:'javascript'}', this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="sslm-help-text" style="margin-top:12px;">
                        <i class="fas fa-exclamation-circle"></i> {$_LANG.http_note|default:'Ensure the file is accessible without redirects. Content-Type should be text/plain.'}
                    </p>

                    {* Email Validation *}
                    {elseif $dcvMethod == 'EMAIL'}
                    <p style="margin-bottom:12px;">
                        <i class="fas fa-info-circle" style="color:var(--sslm-info);"></i>
                        {$_LANG.email_instruction|default:'A validation email has been sent to:'}
                    </p>
                    <div class="sslm-code-group">
                        <div class="sslm-code-row">
                            <div class="sslm-code-label">{$_LANG.email|default:'Email'}</div>
                            <div class="sslm-code-value">
                                <code>{$domain.dcvEmail|escape:'html'|default:'admin@'|cat:$domain.domainName}</code>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:16px;">
                        <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-primary resend-dcv-btn" data-domain="{$domain.domainName|escape:'html'}">
                            <i class="fas fa-paper-plane"></i> {$_LANG.resend_email|default:'Resend Email'}
                        </button>
                    </div>
                    <p class="sslm-help-text" style="margin-top:12px;">
                        <i class="fas fa-envelope-open-text"></i> {$_LANG.email_note|default:'Check your inbox and spam folder. Click the validation link in the email.'}
                    </p>
                    {/if}
                </div>
            </div>
            {/foreach}
            {/if}
        </div>
    </div>

    {* Help Section *}
    <div class="sslm-card">
        <div class="sslm-card-header">
            <h3><i class="fas fa-question-circle"></i> {$_LANG.need_help|default:'Need Help?'}</h3>
        </div>
        <div class="sslm-card-body">
            <div class="sslm-help-grid">
                <div class="sslm-help-item">
                    <h4><i class="fas fa-clock"></i> {$_LANG.how_long|default:'How long does it take?'}</h4>
                    <p>{$_LANG.time_note|default:'DNS: 5-30 minutes after propagation. HTTP: Usually instant. Email: Depends on when you click the link.'}</p>
                </div>
                <div class="sslm-help-item">
                    <h4><i class="fas fa-sync"></i> {$_LANG.check_status|default:'Check Status'}</h4>
                    <p>{$_LANG.status_note|default:'Click "Refresh" button above to check if validation is complete and certificate is issued.'}</p>
                </div>
                <div class="sslm-help-item">
                    <h4><i class="fas fa-exchange-alt"></i> {$_LANG.change_method|default:'Change Method'}</h4>
                    <p>{$_LANG.change_note|default:'Click "Change" next to each domain to switch to a different validation method.'}</p>
                </div>
            </div>
            <div style="text-align:center; margin-top:20px; padding-top:20px; border-top:1px solid var(--sslm-border-color);">
                <p style="margin-bottom:12px; color:var(--sslm-text-secondary);">{$_LANG.still_need_help|default:'Still having trouble?'}</p>
                <a href="{$WEB_ROOT}/submitticket.php" class="sslm-btn sslm-btn-outline">
                    <i class="fas fa-headset"></i> {$_LANG.contact_support|default:'Contact Support'}
                </a>
            </div>
        </div>
    </div>
</div>

{* JavaScript Config *}
<script>
window.sslmConfig = {
    ajaxUrl: '{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}',
    serviceid: '{$serviceid}',
    lang: {
        status_refreshed: '{$_LANG.status_refreshed|default:"Status refreshed"}',
        dcv_email_sent: '{$_LANG.dcv_email_sent|default:"Email sent successfully"}',
        dcv_changed: '{$_LANG.dcv_changed|default:"DCV method changed successfully"}',
        error: '{$_LANG.error|default:"An error occurred"}',
        network_error: '{$_LANG.network_error|default:"Network error"}',
        change_dcv_method: '{$_LANG.change_dcv_method|default:"Change DCV Method"}',
        domain: '{$_LANG.domain|default:"Domain"}',
        new_dcv_method: '{$_LANG.new_dcv_method|default:"New DCV Method"}',
        please_choose: '{$_LANG.please_choose|default:"-- Select --"}',
        file_dns_validation: '{$_LANG.file_dns_validation|default:"File/DNS Validation"}',
        email_validation: '{$_LANG.email_validation|default:"Email Validation"}',
        http_file: '{$_LANG.http_file|default:"HTTP File Validation"}',
        https_file: '{$_LANG.https_file|default:"HTTPS File Validation"}',
        dns_cname: '{$_LANG.dns_cname|default:"DNS CNAME Validation"}',
        email: '{$_LANG.email|default:"Email Validation"}',
        dcv_email: '{$_LANG.dcv_email|default:"DCV Email"}',
        dcv_email_note: '{$_LANG.dcv_email_note|default:"Select an email to receive validation."}',
        dcv_change_note: '{$_LANG.dcv_change_note|default:"You will need to complete the new validation."}',
        cancel: '{$_LANG.cancel|default:"Cancel"}',
        confirm_change: '{$_LANG.confirm_change|default:"Confirm Change"}'
    }
};

// Resend DCV email handler
document.querySelectorAll('.resend-dcv-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var domain = this.getAttribute('data-domain');
        if (!domain) return;
        
        this.disabled = true;
        this.classList.add('sslm-loading');
        var btnRef = this;
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.sslmConfig.ajaxUrl + '&step=resendDCVEmail', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            btnRef.disabled = false;
            btnRef.classList.remove('sslm-loading');
            
            try {
                var response = JSON.parse(xhr.responseText);
                SSLManager.showToast(
                    response.success ? window.sslmConfig.lang.dcv_email_sent : (response.message || 'Failed'),
                    response.success ? 'success' : 'error'
                );
            } catch (e) {
                SSLManager.showToast('Error', 'error');
            }
        };
        
        xhr.send('domain=' + encodeURIComponent(domain));
    });
});
</script>

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>