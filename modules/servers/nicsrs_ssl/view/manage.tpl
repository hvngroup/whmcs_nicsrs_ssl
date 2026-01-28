{**
 * NicSRS SSL Module - Message/Pending Template
 * Shows DCV instructions while certificate is pending validation
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
            <span class="sslm-badge sslm-badge-warning">{$_LANG.pending|default:'Pending'}</span>
        </div>
    </div>

    {* Status Card *}
    <div class="sslm-status-card">
        <div class="sslm-status-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="sslm-status-content">
            <div class="sslm-status-title">{$_LANG.certificate_pending|default:'Certificate Pending Validation'}</div>
            <div class="sslm-status-desc">{$_LANG.message_des|default:'Your certificate request has been submitted. Please complete domain validation below.'}</div>
        </div>
        <button type="button" id="refreshStatusBtn" class="sslm-btn sslm-btn-primary">
            <i class="fas fa-sync-alt"></i> {$_LANG.refresh_status|default:'Refresh Status'}
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
                    <span class="sslm-info-value"><span class="sslm-badge sslm-badge-warning">{$_LANG.pending|default:'Pending'}</span></span>
                </div>
                {if $configData.applyReturn.applyTime}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.submit_time|default:'Submitted'}:</span>
                    <span class="sslm-info-value">{$configData.applyReturn.applyTime|escape:'html'}</span>
                </div>
                {/if}
            </div>
        </div>
    </div>

    {* Domain Validation *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-check-circle"></i> {$_LANG.domain_validation|default:'Domain Validation'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-alert sslm-alert-info">
                <i class="fas fa-info-circle"></i>
                <span>{$_LANG.dcv_instructions|default:'Complete the domain validation using one of the methods below for each domain.'}</span>
            </div>

            {foreach $domainInfo as $index => $domain}
            <div class="sslm-info-card">
                <div class="sslm-info-card-title">
                    <i class="fas fa-globe"></i>
                    {$domain.domainName|escape:'html'}
                    {if $domain.verified}
                    <span class="sslm-badge sslm-badge-success">{$_LANG.verified|default:'Verified'}</span>
                    {else}
                    <span class="sslm-badge sslm-badge-warning">{$_LANG.un_verified|default:'Pending'}</span>
                    {/if}
                </div>

                {* DCV Method *}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.dcv_method|default:'DCV Method'}:</span>
                    <span class="sslm-info-value">
                        {if $domain.dcvMethod eq 'CNAME_CSR_HASH'}
                            {$_LANG.cname_csr_hash|default:'DNS CNAME'}
                        {elseif $domain.dcvMethod eq 'HTTP_CSR_HASH'}
                            {$_LANG.http_csr_hash|default:'HTTP File'}
                        {elseif $domain.dcvMethod eq 'HTTPS_CSR_HASH'}
                            {$_LANG.https_csr_hash|default:'HTTPS File'}
                        {elseif $domain.dcvMethod eq 'DNS_CSR_HASH'}
                            {$_LANG.dns_csr_hash|default:'DNS TXT'}
                        {elseif $domain.dcvMethod eq 'EMAIL'}
                            {$_LANG.email_validation|default:'Email'} ({$domain.dcvEmail|escape:'html'})
                        {else}
                            {$domain.dcvMethod|escape:'html'}
                        {/if}
                    </span>
                </div>

                {* DNS CNAME Instructions *}
                {if $domain.dcvMethod eq 'CNAME_CSR_HASH' && $dcvDnsHost}
                <div class="sslm-dcv-section">
                    <div class="sslm-dcv-title">
                        <i class="fas fa-server"></i> {$_LANG.dns_cname_instructions|default:'Add this CNAME record to your DNS:'}
                    </div>
                    <div class="sslm-dcv-content">
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.dns_host|default:'Host'}:</span>
                            <span class="sslm-dcv-value">{$dcvDnsHost|escape:'html'}</span>
                        </div>
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.dns_type|default:'Type'}:</span>
                            <span class="sslm-dcv-value">CNAME</span>
                        </div>
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.dns_value|default:'Value'}:</span>
                            <span class="sslm-dcv-value">{$dcvDnsValue|escape:'html'}</span>
                        </div>
                    </div>
                </div>
                {/if}

                {* DNS TXT Instructions *}
                {if $domain.dcvMethod eq 'DNS_CSR_HASH' && $dcvDnsHost}
                <div class="sslm-dcv-section">
                    <div class="sslm-dcv-title">
                        <i class="fas fa-server"></i> {$_LANG.dns_txt_instructions|default:'Add this TXT record to your DNS:'}
                    </div>
                    <div class="sslm-dcv-content">
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.dns_host|default:'Host'}:</span>
                            <span class="sslm-dcv-value">{$dcvDnsHost|escape:'html'}</span>
                        </div>
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.dns_type|default:'Type'}:</span>
                            <span class="sslm-dcv-value">TXT</span>
                        </div>
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.dns_value|default:'Value'}:</span>
                            <span class="sslm-dcv-value">{$dcvDnsValue|escape:'html'}</span>
                        </div>
                    </div>
                </div>
                {/if}

                {* HTTP File Instructions *}
                {if ($domain.dcvMethod eq 'HTTP_CSR_HASH' || $domain.dcvMethod eq 'HTTPS_CSR_HASH') && $dcvFileName}
                <div class="sslm-dcv-section">
                    <div class="sslm-dcv-title">
                        <i class="fas fa-file-alt"></i> 
                        {if $domain.dcvMethod eq 'HTTPS_CSR_HASH'}
                            {$_LANG.https_instructions|default:'Create this file on your HTTPS server:'}
                        {else}
                            {$_LANG.http_instructions|default:'Create this file on your HTTP server:'}
                        {/if}
                    </div>
                    <div class="sslm-dcv-content">
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.file_path|default:'Path'}:</span>
                            <span class="sslm-dcv-value">
                                {if $domain.dcvMethod eq 'HTTPS_CSR_HASH'}https://{else}http://{/if}{$domain.domainName|escape:'html'}/.well-known/pki-validation/{$dcvFileName|escape:'html'}
                            </span>
                        </div>
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.file_content|default:'Content'}:</span>
                            <span class="sslm-dcv-value">{$dcvFileContent|escape:'html'}</span>
                        </div>
                    </div>
                    <div style="margin-top: 12px;">
                        <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-secondary download-dcv-file" 
                                data-filename="{$dcvFileName|escape:'html'}" 
                                data-content="{$dcvFileContent|escape:'html'}">
                            <i class="fas fa-download"></i> {$_LANG.down_txt|default:'Download File'}
                        </button>
                    </div>
                </div>
                {/if}

                {* Email Instructions *}
                {if $domain.dcvMethod eq 'EMAIL'}
                <div class="sslm-dcv-section">
                    <div class="sslm-dcv-title">
                        <i class="fas fa-envelope"></i> {$_LANG.email_instructions|default:'Validation email has been sent to:'}
                    </div>
                    <div class="sslm-dcv-content">
                        <div class="sslm-dcv-row">
                            <span class="sslm-dcv-label">{$_LANG.email|default:'Email'}:</span>
                            <span class="sslm-dcv-value">{$domain.dcvEmail|escape:'html'}</span>
                        </div>
                        <p class="sslm-help-text">{$_LANG.email_wait_info|default:'Please check your email and follow the instructions to complete validation.'}</p>
                    </div>
                    <div style="margin-top: 12px;">
                        <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-secondary resend-dcv-btn" 
                                data-domain="{$domain.domainName|escape:'html'}">
                            <i class="fas fa-redo"></i> {$_LANG.resend_dcv|default:'Resend Email'}
                        </button>
                    </div>
                </div>
                {/if}
            </div>
            {/foreach}
        </div>
    </div>

    {* Actions *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-cog"></i> {$_LANG.actions|default:'Actions'}</h3>
        </div>
        <div class="sslm-section-body">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button" id="refreshStatusBtn2" class="sslm-btn sslm-btn-primary">
                    <i class="fas fa-sync-alt"></i> {$_LANG.refresh_status|default:'Refresh Status'}
                </button>
                <button type="button" id="cancelOrderBtn" class="sslm-btn sslm-btn-danger">
                    <i class="fas fa-times"></i> {$_LANG.cancel_order|default:'Cancel Order'}
                </button>
            </div>
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
</script>

{* Load JS *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>