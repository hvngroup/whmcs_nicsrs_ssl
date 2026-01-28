{**
 * NicSRS SSL Module - Reissue Certificate Template
 * Form for reissuing/replacing an existing certificate
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
            <i class="fas fa-redo"></i>
            {$_LANG.reissue_certificate|default:'Reissue Certificate'}
        </h2>
        <div class="sslm-header-info">
            <span class="sslm-product-name">{$productCode|escape:'html'}</span>
            <span class="sslm-badge sslm-badge-info">{$_LANG.reissue|default:'Reissue'}</span>
        </div>
    </div>

    {* Info Alert *}
    <div class="sslm-alert sslm-alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>{$_LANG.reissue_warning_title|default:'Important Information'}</strong>
            <p style="margin: 8px 0 0 0;">{$_LANG.reissue_warning|default:'Reissuing will generate a new certificate. The previous certificate will remain valid until it expires or you choose to revoke it.'}</p>
        </div>
    </div>

    {* Reissue Form *}
    <form id="sslm-reissue-form" class="sslm-form" method="post">
        
        {* Domain Configuration *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">1</span> {$_LANG.domain_info|default:'Domain Information'}</h3>
            </div>
            <div class="sslm-section-body">
                {* Original Domains *}
                {if $originalDomains}
                <div class="sslm-info-card" style="margin-bottom: 16px;">
                    <div class="sslm-info-card-title">
                        <i class="fas fa-history"></i> {$_LANG.original_domains|default:'Original Domains'}
                    </div>
                    {foreach $originalDomains as $domain}
                    <div class="sslm-info-row">
                        <span class="sslm-info-value">{$domain|escape:'html'}</span>
                    </div>
                    {/foreach}
                </div>
                {/if}

                {* Domain List *}
                <div class="sslm-form-group">
                    <label>{$_LANG.domain_name|default:'Domain Names'} <span class="required">*</span></label>
                    <div id="domainList" class="sslm-domain-list">
                        {* First domain row - pre-filled from existing data *}
                        <div class="sslm-domain-row">
                            <div class="sslm-domain-input-group">
                                <input type="text" 
                                       class="sslm-input sslm-domain-input" 
                                       name="domainName" 
                                       placeholder="{$_LANG.common_name|default:'example.com'}"
                                       value="{if isset($configData.domainInfo[0].domainName)}{$configData.domainInfo[0].domainName|escape:'html'}{/if}"
                                       required>
                                <select class="sslm-select sslm-dcv-select" name="dcvMethod">
                                    <option value="CNAME_CSR_HASH">{$_LANG.cname_csr_hash|default:'DNS CNAME'}</option>
                                    <option value="HTTP_CSR_HASH">{$_LANG.http_csr_hash|default:'HTTP File'}</option>
                                    {if $supportOptions.supportHttps}
                                    <option value="HTTPS_CSR_HASH">{$_LANG.https_csr_hash|default:'HTTPS File'}</option>
                                    {/if}
                                    <option value="DNS_CSR_HASH">{$_LANG.dns_csr_hash|default:'DNS TXT'}</option>
                                </select>
                                <button type="button" class="sslm-btn sslm-btn-icon sslm-remove-domain" title="{$_LANG.remove_domain|default:'Remove'}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {* Add Domain Button *}
                    {if $isMultiDomain || $maxDomains > 1}
                    <div class="sslm-domain-actions">
                        <button type="button" id="addDomainBtn" class="sslm-btn sslm-btn-secondary">
                            <i class="fas fa-plus"></i> {$_LANG.add_domain|default:'Add Domain'}
                        </button>
                        <span class="sslm-domain-count">
                            {$_LANG.max_domain|default:'Maximum domains'}: {$maxDomains|default:1}
                        </span>
                    </div>
                    {/if}
                </div>
            </div>
        </div>

        {* CSR Configuration *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">2</span> {$_LANG.csr_configuration|default:'CSR Configuration'}</h3>
            </div>
            <div class="sslm-section-body">
                <div class="sslm-alert sslm-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>{$_LANG.reissue_csr_info|default:'A new CSR is recommended for reissuance. You can generate a new one or use your existing CSR.'}</span>
                </div>

                {* CSR Toggle *}
                <div class="sslm-form-group">
                    <label class="sslm-toggle">
                        <input type="checkbox" id="isManualCsr" checked>
                        <span class="sslm-toggle-slider"></span>
                        <span class="sslm-toggle-label">{$_LANG.is_manual_csr|default:'I have my own CSR'}</span>
                    </label>
                </div>

                {* Auto Generate Section *}
                <div id="autoGenSection" class="sslm-csr-auto" style="display: none;">
                    <p class="sslm-info-text">
                        <i class="fas fa-info-circle"></i>
                        {$_LANG.auto_generate_csr|default:'CSR will be automatically generated based on your domain and contact information.'}
                    </p>
                    <button type="button" id="generateCsrBtn" class="sslm-btn sslm-btn-primary">
                        <i class="fas fa-key"></i> {$_LANG.generate_csr|default:'Generate CSR'}
                    </button>
                </div>

                {* Manual CSR Section *}
                <div id="csrSection" class="sslm-csr-manual">
                    <div class="sslm-form-group">
                        <label for="csr">{$_LANG.csr|default:'CSR'} <span class="required">*</span></label>
                        <textarea id="csr" name="csr" class="sslm-textarea sslm-code" rows="8" 
                                  placeholder="-----BEGIN CERTIFICATE REQUEST-----" required></textarea>
                        <div class="sslm-textarea-actions">
                            <button type="button" id="decodeCsrBtn" class="sslm-btn sslm-btn-sm sslm-btn-secondary">
                                <i class="fas fa-search"></i> {$_LANG.decode_csr|default:'Decode CSR'}
                            </button>
                        </div>
                    </div>
                    
                    {* Private Key (hidden) *}
                    <input type="hidden" id="privateKey" name="privateKey" value="">
                </div>
            </div>
        </div>

        {* Organization Information (for OV/EV) *}
        {if $requiresOrganization}
        <div class="sslm-section" id="organizationPart">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">3</span> {$_LANG.organization_info|default:'Organization Information'}</h3>
            </div>
            <div class="sslm-section-body">
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.org_name|default:'Organization Name'} <span class="required">*</span></label>
                        <input type="text" name="organizationName" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationName|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.org_phone|default:'Phone'}</label>
                        <input type="text" name="organizationPhone" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationMobile|escape:'html'}">
                    </div>
                </div>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.org_address|default:'Address'} <span class="required">*</span></label>
                    <input type="text" name="organizationAddress" class="sslm-input" 
                           value="{$configData.organizationInfo.organizationAddress|escape:'html'}" required>
                </div>
                
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-col-4">
                        <label>{$_LANG.org_city|default:'City'} <span class="required">*</span></label>
                        <input type="text" name="organizationCity" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationCity|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group sslm-col-4">
                        <label>{$_LANG.org_state|default:'State/Province'}</label>
                        <input type="text" name="organizationProvince" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationState|escape:'html'}">
                    </div>
                    <div class="sslm-form-group sslm-col-4">
                        <label>{$_LANG.org_country|default:'Country'} <span class="required">*</span></label>
                        <input type="text" name="organizationCountry" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationCountry|escape:'html'}" required>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {* Form Actions *}
        <div class="sslm-form-actions">
            <a href="{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn-secondary">
                <i class="fas fa-arrow-left"></i> {$_LANG.back|default:'Back'}
            </a>
            <button type="submit" id="submitBtn" class="sslm-btn sslm-btn-primary">
                <i class="fas fa-paper-plane"></i> {$_LANG.submit_request|default:'Submit Reissue Request'}
            </button>
        </div>
    </form>
</div>

{* Domain Row Template for JS *}
<template id="domainRowTemplate">
    <div class="sslm-domain-row">
        <div class="sslm-domain-input-group">
            <input type="text" 
                   class="sslm-input sslm-domain-input" 
                   name="domainName" 
                   placeholder="{$_LANG.common_name|default:'example.com'}">
            <select class="sslm-select sslm-dcv-select" name="dcvMethod">
                <option value="CNAME_CSR_HASH">{$_LANG.cname_csr_hash|default:'DNS CNAME'}</option>
                <option value="HTTP_CSR_HASH">{$_LANG.http_csr_hash|default:'HTTP File'}</option>
                {if $supportOptions.supportHttps}
                <option value="HTTPS_CSR_HASH">{$_LANG.https_csr_hash|default:'HTTPS File'}</option>
                {/if}
                <option value="DNS_CSR_HASH">{$_LANG.dns_csr_hash|default:'DNS TXT'}</option>
            </select>
            <button type="button" class="sslm-btn sslm-btn-icon sslm-remove-domain" title="{$_LANG.remove_domain|default:'Remove'}">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</template>

{* JavaScript Configuration *}
<script>
window.sslmConfig = {
    ajaxUrl: "{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}",
    serviceid: "{$serviceid}",
    productCode: "{$productCode|escape:'javascript'}",
    sslType: "{$sslType|default:'website_ssl'}",
    validationType: "{$sslValidationType|default:'dv'}",
    isMultiDomain: {if $isMultiDomain}true{else}false{/if},
    maxDomain: {$maxDomains|default:1},
    other: {
        supportNormal: {if $supportOptions.supportNormal}true{else}false{/if},
        supportIp: {if $supportOptions.supportIp}true{else}false{/if},
        supportWild: {if $supportOptions.supportWild}true{else}false{/if},
        supportHttps: {if $supportOptions.supportHttps}true{else}false{/if}
    },
    configData: {$configData|json_encode nofilter},
    lang: {$_LANG_JSON nofilter},
    isReissue: true
};

// Form submission handler for reissue
document.getElementById('sslm-reissue-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var form = this;
    var submitBtn = document.getElementById('submitBtn');
    
    // Collect form data
    var domains = [];
    document.querySelectorAll('.sslm-domain-row').forEach(function(row) {
        var domainInput = row.querySelector('.sslm-domain-input');
        var dcvSelect = row.querySelector('.sslm-dcv-select');
        
        if (domainInput && domainInput.value.trim()) {
            domains.push({
                domainName: domainInput.value.trim(),
                dcvMethod: dcvSelect ? dcvSelect.value : 'CNAME_CSR_HASH'
            });
        }
    });
    
    var data = {
        csr: document.getElementById('csr').value.trim(),
        privateKey: document.getElementById('privateKey').value.trim(),
        domainInfo: domains
    };
    
    // Add organization info if present
    var orgName = form.querySelector('[name="organizationName"]');
    if (orgName && orgName.value.trim()) {
        data.organizationInfo = {
            organizationName: orgName.value.trim(),
            organizationAddress: form.querySelector('[name="organizationAddress"]')?.value.trim() || '',
            organizationCity: form.querySelector('[name="organizationCity"]')?.value.trim() || '',
            organizationState: form.querySelector('[name="organizationProvince"]')?.value.trim() || '',
            organizationCountry: form.querySelector('[name="organizationCountry"]')?.value.trim() || ''
        };
    }
    
    // Validate
    if (!data.csr) {
        alert('{$_LANG.csr_required|default:"CSR is required"}');
        return;
    }
    
    if (domains.length === 0) {
        alert('{$_LANG.domain_required|default:"At least one domain is required"}');
        return;
    }
    
    // Disable button
    submitBtn.disabled = true;
    submitBtn.classList.add('sslm-loading');
    
    // Submit
    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.sslmConfig.ajaxUrl + '&step=submitReissue', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        submitBtn.disabled = false;
        submitBtn.classList.remove('sslm-loading');
        
        try {
            var response = JSON.parse(xhr.responseText);
            
            if (response.success) {
                alert('{$_LANG.reissue_success|default:"Reissue request submitted successfully"}');
                window.location.href = window.sslmConfig.ajaxUrl;
            } else {
                alert(response.message || '{$_LANG.error|default:"An error occurred"}');
            }
        } catch(e) {
            alert('{$_LANG.error|default:"An error occurred"}');
        }
    };
    
    // Serialize data
    var pairs = [];
    function buildPairs(obj, prefix) {
        for (var key in obj) {
            if (!obj.hasOwnProperty(key)) continue;
            var value = obj[key];
            var newKey = prefix ? prefix + '[' + key + ']' : key;
            
            if (Array.isArray(value)) {
                for (var i = 0; i < value.length; i++) {
                    if (typeof value[i] === 'object') {
                        buildPairs(value[i], newKey + '[' + i + ']');
                    } else {
                        pairs.push(encodeURIComponent(newKey + '[' + i + ']') + '=' + encodeURIComponent(value[i]));
                    }
                }
            } else if (typeof value === 'object' && value !== null) {
                buildPairs(value, newKey);
            } else {
                pairs.push(encodeURIComponent(newKey) + '=' + encodeURIComponent(value || ''));
            }
        }
    }
    buildPairs({data: data}, '');
    
    xhr.send(pairs.join('&'));
});
</script>

{* Load JS *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>