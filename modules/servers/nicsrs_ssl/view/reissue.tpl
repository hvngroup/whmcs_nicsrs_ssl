{**
 * NicSRS SSL Module - Reissue Certificate Template
 * Form for reissuing/replacing an existing certificate
 * 
 * @package    nicsrs_ssl
 * @version    2.1.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 *}

{* Load CSS *}
<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

{* Parse configData *}
{if is_string($configData) && $configData}
    {assign var="cfgData" value=$configData|json_decode:true}
{elseif is_array($configData)}
    {assign var="cfgData" value=$configData}
{else}
    {assign var="cfgData" value=[]}
{/if}

{* Parse countries *}
{if is_string($countries) && $countries}
    {assign var="countryList" value=$countries|json_decode:true}
{elseif is_array($countries)}
    {assign var="countryList" value=$countries}
{else}
    {assign var="countryList" value=[]}
{/if}

{* Determine selected country *}
{assign var="selectedCountry" value=""}
{if !empty($cfgData.Administrator.country)}
    {assign var="selectedCountry" value=$cfgData.Administrator.country}
{elseif !empty($clientsdetails.countrycode)}
    {assign var="selectedCountry" value=$clientsdetails.countrycode}
{/if}

<div class="sslm-container">
    {* Header *}
    <div class="sslm-header">
        <h2 class="sslm-title">
            <i class="fas fa-redo"></i>
            {$_LANG.reissue_certificate|default:'Reissue Certificate'}
        </h2>
        <div class="sslm-header-info">
            <span class="sslm-product-name">{$productCode|escape:'html'}</span>
            <span class="sslm-badge sslm-badge-info">{$_LANG.reissue|default:'Reissue'}</span>
        </div>
    </div>

    {* Warning Alert *}
    <div class="sslm-alert sslm-alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>{$_LANG.reissue_warning_title|default:'Important Information'}</strong>
            <p style="margin: 8px 0 0 0;">{$_LANG.reissue_warning|default:'Reissuing will generate a new certificate. The previous certificate will remain valid until it expires or you choose to revoke it.'}</p>
        </div>
    </div>

    {* Original Certificate Info Card *}
    <div class="sslm-card sslm-card-info">
        <div class="sslm-card-header">
            <h3><i class="fas fa-certificate"></i> {$_LANG.current_certificate|default:'Current Certificate'}</h3>
        </div>
        <div class="sslm-card-body">
            <div class="sslm-info-grid">
                <div class="sslm-info-item">
                    <label>{$_LANG.certificate_id|default:'Certificate ID'}</label>
                    <span class="sslm-code">{$certId|escape:'html'|default:'N/A'}</span>
                </div>
                <div class="sslm-info-item">
                    <label>{$_LANG.status|default:'Status'}</label>
                    <span class="sslm-badge sslm-badge-success">
                        <i class="fas fa-check-circle"></i> {$_LANG.active|default:'Active'}
                    </span>
                </div>
                {if $cfgData.domainInfo[0].domainName}
                <div class="sslm-info-item">
                    <label>{$_LANG.primary_domain|default:'Primary Domain'}</label>
                    <span>{$cfgData.domainInfo[0].domainName|escape:'html'}</span>
                </div>
                {/if}
                {if $cfgData.applyReturn.endDate}
                <div class="sslm-info-item">
                    <label>{$_LANG.expires|default:'Expires'}</label>
                    <span>{$cfgData.applyReturn.endDate|escape:'html'}</span>
                </div>
                {/if}
            </div>

            {* Original Domains List *}
            {if $originalDomains}
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--sslm-border-color);">
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">
                    <i class="fas fa-list"></i> {$_LANG.original_domains|default:'Original Domains'}
                </label>
                {foreach from=$originalDomains item=domain}
                <div class="sslm-domain-status" style="margin-bottom: 8px;">
                    <div class="sslm-domain-status-icon verified">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="sslm-domain-status-content">
                        <div class="sslm-domain-status-name">{$domain|escape:'html'}</div>
                    </div>
                </div>
                {/foreach}
            </div>
            {/if}
        </div>
    </div>

    {* Reissue Reason *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><span class="sslm-step-number">1</span> {$_LANG.reissue_reason|default:'Reason for Reissue'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-form-group">
                <label>{$_LANG.select_reason|default:'Why are you reissuing this certificate?'}</label>
                <select name="reissueReason" id="reissueReason" class="sslm-select">
                    <option value="">{$_LANG.select_reason_placeholder|default:'-- Select Reason --'}</option>
                    <option value="key_compromise">{$_LANG.reason_key_compromise|default:'Private Key Compromised'}</option>
                    <option value="domain_change">{$_LANG.reason_domain_change|default:'Domain Name Change'}</option>
                    <option value="server_change">{$_LANG.reason_server_change|default:'Server Migration'}</option>
                    <option value="lost_key">{$_LANG.reason_lost_key|default:'Lost Private Key'}</option>
                    <option value="csr_change">{$_LANG.reason_csr_change|default:'Need New CSR'}</option>
                    <option value="other">{$_LANG.reason_other|default:'Other'}</option>
                </select>
            </div>

            <div id="revokeWarning" class="sslm-alert sslm-alert-error" style="display: none; margin-top: 16px;">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>{$_LANG.security_notice|default:'Security Notice'}</strong>
                    <p style="margin: 4px 0 0 0;">{$_LANG.key_compromise_warning|default:'If your private key has been compromised, you should also revoke the current certificate after the new one is issued.'}</p>
                </div>
            </div>
        </div>
    </div>

    {* Reissue Form *}
    <form id="sslm-reissue-form" class="sslm-form" method="post">
        <input type="hidden" name="reissueReason" id="reissueReasonHidden" value="">
        
        {* ============================================ *}
        {* Step 2: Domain Configuration *}
        {* ============================================ *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">2</span> {$_LANG.domain_info|default:'Domain Information'}</h3>
            </div>
            <div class="sslm-section-body">
                <div class="sslm-alert sslm-alert-info" style="margin-bottom: 16px;">
                    <i class="fas fa-info-circle"></i>
                    <span>{$_LANG.reissue_domain_info|default:'You can keep the same domains or modify them. The primary domain will be the first one listed.'}</span>
                </div>

                {* Domain List *}
                <div class="sslm-form-group">
                    <label>{$_LANG.domain_names|default:'Domain Names'} <span class="required">*</span></label>
                    
                    <div id="domainList" class="sslm-domain-list">
                        {if $cfgData.domainInfo}
                            {foreach from=$cfgData.domainInfo item=domain key=idx}
                            <div class="sslm-domain-row" data-index="{$idx}">
                                <div class="sslm-domain-input-group">
                                    <span class="sslm-domain-number">{$idx+1}</span>
                                    <input type="text" class="sslm-input sslm-domain-input" 
                                           name="domains[{$idx}][domainName]" 
                                           value="{$domain.domainName|escape:'html'}"
                                           placeholder="example.com">
                                    <select class="sslm-select sslm-dcv-select" name="domains[{$idx}][dcvMethod]">
                                        <optgroup label="{$_LANG.file_dns_validation|default:'File/DNS Validation'}">
                                            <option value="HTTP_CSR_HASH" {if $domain.dcvMethod eq 'HTTP_CSR_HASH'}selected{/if}>{$_LANG.http_file|default:'HTTP File'}</option>
                                            <option value="HTTPS_CSR_HASH" {if $domain.dcvMethod eq 'HTTPS_CSR_HASH'}selected{/if}>{$_LANG.https_file|default:'HTTPS File'}</option>
                                            <option value="CNAME_CSR_HASH" {if $domain.dcvMethod eq 'CNAME_CSR_HASH'}selected{/if}>{$_LANG.dns_cname|default:'DNS CNAME'}</option>
                                        </optgroup>
                                        <optgroup label="{$_LANG.email_validation|default:'Email Validation'}">
                                            <option value="EMAIL" {if $domain.dcvMethod eq 'EMAIL'}selected{/if}>{$_LANG.email|default:'Email'}</option>
                                        </optgroup>
                                    </select>
                                    <select class="sslm-select sslm-dcv-email-select" name="domains[{$idx}][dcvEmail]" style="{if $domain.dcvMethod neq 'EMAIL'}display:none;{/if}">
                                        <option value="">{$_LANG.select_email|default:'Select Email'}</option>
                                    </select>
                                    <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-danger sslm-remove-domain" 
                                            onclick="SSLManager.removeDomainRow(this.closest('.sslm-domain-row'))"
                                            style="{if $idx eq 0}visibility:hidden;{/if}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            {/foreach}
                        {else}
                            <div class="sslm-domain-row" data-index="0">
                                <div class="sslm-domain-input-group">
                                    <span class="sslm-domain-number">1</span>
                                    <input type="text" class="sslm-input sslm-domain-input" 
                                           name="domains[0][domainName]" 
                                           value="{$originalDomains[0]|escape:'html'}"
                                           placeholder="example.com">
                                    <select class="sslm-select sslm-dcv-select" name="domains[0][dcvMethod]">
                                        <optgroup label="{$_LANG.file_dns_validation|default:'File/DNS Validation'}">
                                            <option value="HTTP_CSR_HASH">{$_LANG.http_file|default:'HTTP File'}</option>
                                            <option value="HTTPS_CSR_HASH">{$_LANG.https_file|default:'HTTPS File'}</option>
                                            <option value="CNAME_CSR_HASH" selected>{$_LANG.dns_cname|default:'DNS CNAME'}</option>
                                        </optgroup>
                                        <optgroup label="{$_LANG.email_validation|default:'Email Validation'}">
                                            <option value="EMAIL">{$_LANG.email|default:'Email'}</option>
                                        </optgroup>
                                    </select>
                                    <select class="sslm-select sslm-dcv-email-select" name="domains[0][dcvEmail]" style="display:none;">
                                        <option value="">{$_LANG.select_email|default:'Select Email'}</option>
                                    </select>
                                    <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-danger sslm-remove-domain" style="visibility:hidden;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        {/if}
                    </div>

                    {* Add Domain & Counter *}
                    {if $isMultiDomain}
                    <div class="sslm-domain-actions">
                        <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-outline" onclick="SSLManager.addDomainRow()">
                            <i class="fas fa-plus"></i> {$_LANG.add_domain|default:'Add Domain'}
                        </button>
                        <span class="sslm-domain-count">
                            {$_LANG.domains_used|default:'Domains'}: <span id="domainCount">{$cfgData.domainInfo|count|default:1}</span> / {$maxDomains|default:10}
                        </span>
                    </div>
                    {/if}
                </div>
            </div>
        </div>

        {* ============================================ *}
        {* Step 3: CSR Configuration *}
        {* ============================================ *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">3</span> {$_LANG.csr_config|default:'CSR Configuration'}</h3>
            </div>
            <div class="sslm-section-body">
                <div class="sslm-alert sslm-alert-warning" style="margin-bottom: 16px;">
                    <i class="fas fa-key"></i>
                    <span>{$_LANG.new_csr_required|default:'A new CSR is required for reissue. You can auto-generate one or provide your own.'}</span>
                </div>

                {* CSR Toggle *}
                <div class="sslm-form-group">
                    <label class="sslm-switch">
                        <input type="checkbox" id="isManualCsr" name="isManualCsr">
                        <span class="sslm-switch-slider"></span>
                        <span class="sslm-switch-label">{$_LANG.provide_own_csr|default:'I have my own CSR'}</span>
                    </label>
                </div>

                {* Auto-generate info *}
                <div id="autoGenSection" class="sslm-csr-auto">
                    <div class="sslm-alert sslm-alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span>{$_LANG.auto_csr_info|default:'A new CSR and private key will be automatically generated for you.'}</span>
                    </div>
                </div>

                {* Manual CSR input *}
                <div id="csrSection" class="sslm-csr-manual" style="display:none;">
                    <div class="sslm-form-group">
                        <label>{$_LANG.paste_csr|default:'Paste your CSR'} <span class="required">*</span></label>
                        <textarea class="sslm-textarea sslm-code" name="csr" rows="8" 
                                  placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>
                        <div class="sslm-textarea-actions">
                            <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-outline" onclick="SSLManager.decodeCSR()">
                                <i class="fas fa-search"></i> {$_LANG.decode_csr|default:'Decode CSR'}
                            </button>
                            <button type="button" class="sslm-btn sslm-btn-sm sslm-btn-outline" onclick="SSLManager.clearCSR()">
                                <i class="fas fa-eraser"></i> {$_LANG.clear|default:'Clear'}
                            </button>
                        </div>
                    </div>

                    {* CSR Decode Result *}
                    <div class="sslm-csr-decode-result" id="csrDecodeResult" style="display:none;">
                        <h4><i class="fas fa-info-circle"></i> {$_LANG.csr_info|default:'CSR Information'}</h4>
                        <table class="sslm-info-table">
                            <tr><td>{$_LANG.common_name|default:'Common Name (CN)'}:</td><td id="csrCN">-</td></tr>
                            <tr><td>{$_LANG.organization|default:'Organization'}:</td><td id="csrOrg">-</td></tr>
                            <tr><td>{$_LANG.country|default:'Country'}:</td><td id="csrCountry">-</td></tr>
                            <tr><td>{$_LANG.key_size|default:'Key Size'}:</td><td id="csrKeySize">-</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {* ============================================ *}
        {* Step 4: Contact Information *}
        {* ============================================ *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">4</span> {$_LANG.contact_info|default:'Contact Information'}</h3>
            </div>
            <div class="sslm-section-body">
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.first_name|default:'First Name'} <span class="required">*</span></label>
                        <input type="text" class="sslm-input" name="firstName" 
                               value="{$cfgData.Administrator.firstName|default:$clientsdetails.firstname|escape:'html'}">
                    </div>
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.last_name|default:'Last Name'} <span class="required">*</span></label>
                        <input type="text" class="sslm-input" name="lastName" 
                               value="{$cfgData.Administrator.lastName|default:$clientsdetails.lastname|escape:'html'}">
                    </div>
                </div>

                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.email|default:'Email'} <span class="required">*</span></label>
                        <input type="email" class="sslm-input" name="email" 
                               value="{$cfgData.Administrator.email|default:$clientsdetails.email|escape:'html'}">
                    </div>
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.phone|default:'Phone'} <span class="required">*</span></label>
                        <input type="text" class="sslm-input" name="mobile" 
                               value="{$cfgData.Administrator.mobile|default:$clientsdetails.phonenumber|escape:'html'}"
                               placeholder="+84.123456789">
                    </div>
                </div>

                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.job_title|default:'Job Title'}</label>
                        <input type="text" class="sslm-input" name="job" 
                               value="{$cfgData.Administrator.job|escape:'html'}">
                    </div>
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.country|default:'Country'} <span class="required">*</span></label>
                        <select class="sslm-select" name="country">
                            <option value="">{$_LANG.select_country|default:'Select Country'}</option>
                            {foreach from=$countryList key=code item=name}
                                <option value="{$code}" {if $code eq $selectedCountry}selected{/if}>{$name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                {* Organization fields for OV/EV *}
                {if $requiresOrganization}
                <div class="sslm-form-group" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--sslm-border-color);">
                    <label style="font-weight: 600; margin-bottom: 16px; display: block;">
                        <i class="fas fa-building"></i> {$_LANG.organization_details|default:'Organization Details'}
                    </label>
                </div>

                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.company_name|default:'Company Name'} <span class="required">*</span></label>
                        <input type="text" class="sslm-input" name="companyName" 
                               value="{$cfgData.Administrator.companyName|default:$clientsdetails.companyname|escape:'html'}">
                    </div>
                    <div class="sslm-form-group sslm-form-col-6">
                        <label>{$_LANG.address|default:'Address'} <span class="required">*</span></label>
                        <input type="text" class="sslm-input" name="address" 
                               value="{$cfgData.Administrator.address|default:$clientsdetails.address1|escape:'html'}">
                    </div>
                </div>

                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-form-col-4">
                        <label>{$_LANG.city|default:'City'} <span class="required">*</span></label>
                        <input type="text" class="sslm-input" name="city" 
                               value="{$cfgData.Administrator.city|default:$clientsdetails.city|escape:'html'}">
                    </div>
                    <div class="sslm-form-group sslm-form-col-4">
                        <label>{$_LANG.state|default:'State/Province'}</label>
                        <input type="text" class="sslm-input" name="state" 
                               value="{$cfgData.Administrator.state|default:$clientsdetails.state|escape:'html'}">
                    </div>
                    <div class="sslm-form-group sslm-form-col-4">
                        <label>{$_LANG.postal_code|default:'Postal Code'} <span class="required">*</span></label>
                        <input type="text" class="sslm-input" name="postCode" 
                               value="{$cfgData.Administrator.postCode|default:$clientsdetails.postcode|escape:'html'}">
                    </div>
                </div>
                {/if}
            </div>
        </div>

        {* ============================================ *}
        {* Form Actions *}
        {* ============================================ *}
        <div class="sslm-form-actions">
            <a href="{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn-secondary">
                <i class="fas fa-arrow-left"></i> {$_LANG.back|default:'Back'}
            </a>
            <button type="submit" id="submitBtn" class="sslm-btn sslm-btn-primary sslm-btn-lg">
                <i class="fas fa-redo"></i> {$_LANG.submit_reissue|default:'Submit Reissue Request'}
            </button>
        </div>
    </form>
</div>

{* JavaScript Configuration *}
<script>
window.sslmConfig = {
    ajaxUrl: "{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}",
    serviceid: "{$serviceid}",
    maxDomains: {$maxDomains|default:10},
    isMultiDomain: {if $isMultiDomain}true{else}false{/if},
    configData: {$configData|json_encode nofilter},
    lang: {
        validation_error: '{$_LANG.validation_error|default:"Please fill in all required fields"}',
        submit_success: '{$_LANG.submit_success|default:"Reissue request submitted"}',
        submit_failed: '{$_LANG.submit_failed|default:"Submission failed"}',
        at_least_one_domain: '{$_LANG.at_least_one_domain|default:"At least one domain is required"}',
        submitting: '{$_LANG.submitting|default:"Submitting..."}'
    }
};

// Show warning for key compromise
document.getElementById('reissueReason').addEventListener('change', function() {
    var warning = document.getElementById('revokeWarning');
    var hidden = document.getElementById('reissueReasonHidden');
    hidden.value = this.value;
    
    if (this.value === 'key_compromise') {
        warning.style.display = 'flex';
    } else {
        warning.style.display = 'none';
    }
});

// CSR toggle
document.getElementById('isManualCsr').addEventListener('change', function() {
    document.getElementById('csrSection').style.display = this.checked ? 'block' : 'none';
    document.getElementById('autoGenSection').style.display = this.checked ? 'none' : 'block';
});

// Form submission
document.getElementById('sslm-reissue-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + window.sslmConfig.lang.submitting;
    
    // Collect form data
    var formData = new FormData(this);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.sslmConfig.ajaxUrl + '&step=reissue', true);
    
    xhr.onload = function() {
        try {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                SSLManager.showToast(window.sslmConfig.lang.submit_success, 'success');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i> {$_LANG.submit_reissue|default:"Submit Reissue Request"}';
                SSLManager.showToast(response.message || window.sslmConfig.lang.submit_failed, 'error');
            }
        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-redo"></i> {$_LANG.submit_reissue|default:"Submit Reissue Request"}';
            SSLManager.showToast(window.sslmConfig.lang.submit_failed, 'error');
        }
    };
    
    xhr.send(formData);
});
</script>

{* Load JavaScript *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>