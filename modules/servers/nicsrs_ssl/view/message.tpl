{**
 * NicSRS SSL Module - Pending/Message Template
 * Displays DCV validation status and instructions
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
            <i class="fas fa-clock"></i>
            {$_LANG.pending_validation|default:'Pending Validation'}
        </h2>
        <div class="sslm-header-info">
            <span class="sslm-product-name">{$productCode|escape:'html'}</span>
            <span class="sslm-status sslm-status-pending">
                <i class="fas fa-spinner fa-spin"></i> {$_LANG.status_pending|default:'Pending'}
            </span>
        </div>
    </div>

    {* Status Alert *}
    <div class="sslm-alert sslm-alert-info">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>{$_LANG.validation_required|default:'Domain Validation Required'}</strong>
            <p>{$_LANG.validation_instructions|default:'Please complete domain validation using one of the methods below. Once all domains are verified, your certificate will be issued.'}</p>
        </div>
    </div>

    {* Certificate Info *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-certificate"></i> {$_LANG.certificate_info|default:'Certificate Information'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-cert-info">
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.order_id|default:'Order ID'}</div>
                    <div class="sslm-cert-info-value">{$certId|default:'N/A'}</div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.vendor_id|default:'Vendor ID'}</div>
                    <div class="sslm-cert-info-value">{$vendorId|default:'N/A'}</div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.validation_type|default:'Validation Type'}</div>
                    <div class="sslm-cert-info-value">{$sslValidationType|upper|default:'DV'}</div>
                </div>
                <div class="sslm-cert-info-item">
                    <div class="sslm-cert-info-label">{$_LANG.domains_count|default:'Domains'}</div>
                    <div class="sslm-cert-info-value">{$domainInfo|@count|default:0}</div>
                </div>
            </div>
        </div>
    </div>

    {* Domain Validation Status *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-check-double"></i> {$_LANG.domain_validation|default:'Domain Validation Status'}</h3>
            <button type="button" id="refreshStatusBtn" class="sslm-btn sslm-btn-sm sslm-btn-secondary" onclick="SSLManager.refreshStatus()">
                <i class="fas fa-sync-alt"></i> {$_LANG.refresh|default:'Refresh'}
            </button>
        </div>
        <div class="sslm-section-body">
            <table class="sslm-table">
                <thead>
                    <tr>
                        <th>{$_LANG.domain|default:'Domain'}</th>
                        <th>{$_LANG.dcv_method|default:'DCV Method'}</th>
                        <th>{$_LANG.status|default:'Status'}</th>
                        <th>{$_LANG.actions|default:'Actions'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$domainInfo item=domain key=idx}
                    <tr>
                        <td>
                            <strong>{$domain.domainName|escape:'html'}</strong>
                            {if $idx eq 0}<span class="sslm-badge sslm-badge-dv">{$_LANG.primary|default:'Primary'}</span>{/if}
                        </td>
                        <td>
                            {if $domain.dcvMethod eq 'EMAIL'}
                                <i class="fas fa-envelope"></i> {$_LANG.email_validation|default:'Email'}
                                {if $domain.dcvEmail}<br><small>{$domain.dcvEmail}</small>{/if}
                            {elseif $domain.dcvMethod eq 'HTTP_CSR_HASH'}
                                <i class="fas fa-globe"></i> {$_LANG.http_csr_hash|default:'HTTP File'}
                            {elseif $domain.dcvMethod eq 'HTTPS_CSR_HASH'}
                                <i class="fas fa-lock"></i> {$_LANG.https_csr_hash|default:'HTTPS File'}
                            {elseif $domain.dcvMethod eq 'CNAME_CSR_HASH'}
                                <i class="fas fa-network-wired"></i> {$_LANG.cname_csr_hash|default:'DNS CNAME'}
                            {elseif $domain.dcvMethod eq 'DNS_CSR_HASH'}
                                <i class="fas fa-server"></i> {$_LANG.dns_csr_hash|default:'DNS TXT'}
                            {else}
                                {$domain.dcvMethod|default:'N/A'}
                            {/if}
                        </td>
                        <td>
                            {if $domain.isVerified || $domain.is_verify eq 'verified'}
                                <span class="sslm-status sslm-status-complete">
                                    <i class="fas fa-check"></i> {$_LANG.verified|default:'Verified'}
                                </span>
                            {else}
                                <span class="sslm-status sslm-status-pending">
                                    <i class="fas fa-clock"></i> {$_LANG.pending|default:'Pending'}
                                </span>
                            {/if}
                        </td>
                        <td>
                            {if $domain.dcvMethod eq 'EMAIL' && (!$domain.isVerified && $domain.is_verify neq 'verified')}
                                <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-secondary resend-dcv-btn" 
                                        data-domain="{$domain.domainName|escape:'html'}">
                                    <i class="fas fa-paper-plane"></i> {$_LANG.resend_email|default:'Resend'}
                                </button>
                            {/if}
                        </td>
                    </tr>
                    {foreachelse}
                    <tr>
                        <td colspan="4" class="text-center">{$_LANG.no_domains|default:'No domains found'}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    {* DCV Instructions *}
    {if $dcvFileName || $dcvDnsHost}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-book"></i> {$_LANG.dcv_instructions|default:'Validation Instructions'}</h3>
        </div>
        <div class="sslm-section-body">
            
            {* HTTP/HTTPS File Validation *}
            {if $dcvFileName && $dcvFileContent}
            <div class="sslm-dcv-info">
                <h4><i class="fas fa-file-alt"></i> {$_LANG.http_validation|default:'HTTP/HTTPS File Validation'}</h4>
                <p>{$_LANG.http_instructions|default:'Create a file at the following path on your server:'}</p>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.file_path|default:'File Path'}:</label>
                    <div class="sslm-dcv-code">/.well-known/pki-validation/{$dcvFileName}</div>
                </div>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.file_content|default:'File Content'}:</label>
                    <div class="sslm-dcv-code">{$dcvFileContent|escape:'html'}</div>
                </div>
            </div>
            {/if}
            
            {* DNS Validation *}
            {if $dcvDnsHost && $dcvDnsValue}
            <div class="sslm-dcv-info" style="margin-top: 20px;">
                <h4><i class="fas fa-network-wired"></i> {$_LANG.dns_validation|default:'DNS Validation'}</h4>
                <p>{$_LANG.dns_instructions|default:'Add the following DNS record:'}</p>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.record_type|default:'Record Type'}:</label>
                    <div class="sslm-dcv-code">{$dcvDnsType|default:'CNAME'}</div>
                </div>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.host_name|default:'Host/Name'}:</label>
                    <div class="sslm-dcv-code">{$dcvDnsHost|escape:'html'}</div>
                </div>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.value_content|default:'Value/Points To'}:</label>
                    <div class="sslm-dcv-code">{$dcvDnsValue|escape:'html'}</div>
                </div>
            </div>
            {/if}
            
        </div>
    </div>
    {/if}

    {* Actions *}
    <div class="sslm-form-actions">
        <a href="{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn-secondary">
            <i class="fas fa-arrow-left"></i> {$_LANG.back|default:'Back'}
        </a>
        <button type="button" id="refreshStatusBtn2" class="sslm-btn sslm-btn-primary" onclick="SSLManager.refreshStatus()">
            <i class="fas fa-sync-alt"></i> {$_LANG.check_status|default:'Check Status'}
        </button>
    </div>
</div>

{* JavaScript Configuration *}
<script>
window.sslmConfig = {
    ajaxUrl: '{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}',
    serviceid: '{$serviceid}',
    lang: {
        status_refreshed: '{$_LANG.status_refreshed|default:"Status refreshed"}',
        dcv_email_sent: '{$_LANG.dcv_email_sent|default:"DCV email sent"}',
        error: '{$_LANG.error|default:"An error occurred"}',
        network_error: '{$_LANG.network_error|default:"Network error"}'
    }
};

// Resend DCV email handlers
document.querySelectorAll('.resend-dcv-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var domain = this.getAttribute('data-domain');
        if (domain) {
            this.disabled = true;
            this.classList.add('sslm-loading');
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.sslmConfig.ajaxUrl + '&step=resendDCVEmail', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            var btn = this;
            xhr.onload = function() {
                btn.disabled = false;
                btn.classList.remove('sslm-loading');
                
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        SSLManager.showToast(window.sslmConfig.lang.dcv_email_sent, 'success');
                    } else {
                        SSLManager.showToast(response.message || 'Failed', 'error');
                    }
                } catch (e) {
                    SSLManager.showToast('Error', 'error');
                }
            };
            
            xhr.send('domain=' + encodeURIComponent(domain));
        }
    });
});
</script>

{* Load JavaScript *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>