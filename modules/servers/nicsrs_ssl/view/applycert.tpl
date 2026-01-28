{**
 * NicSRS SSL Module - Apply Certificate Template
 * Production Ready Version
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
            {$_LANG.configure_certificate|default:'Configure Certificate'}
        </h2>
        <div class="sslm-header-info">
            <span class="sslm-product-name">{$productCode|escape:'html'}</span>
            <span class="sslm-badge sslm-badge-{$sslValidationType|default:'dv'}">{$sslValidationType|upper|default:'DV'}</span>
        </div>
    </div>

    {* Status Alert *}
    {if $configData.isDraft}
    <div class="sslm-alert sslm-alert-info">
        <i class="fas fa-info-circle"></i>
        <span>{$_LANG.draft|default:'Draft'}: {$_LANG.last_saved|default:'Last saved'} {$configData.lastSaved|default:'N/A'}</span>
    </div>
    {/if}

    {* Apply Form *}
    <form id="sslm-apply-form" class="sslm-form" method="post">
        
        {* Step 1: Domain Configuration *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">1</span> {$_LANG.domain_info|default:'Domain Information'}</h3>
            </div>
            <div class="sslm-section-body">
                {* Is Renew Option *}
                <div class="sslm-form-group">
                    <label>{$_LANG.is_renew|default:'Is this a renewal?'}</label>
                    <div class="sslm-radio-group">
                        <label class="sslm-radio">
                            <input type="radio" name="isRenew" value="0" {if !$configData.originalfromOthers || $configData.originalfromOthers eq '0'}checked{/if}>
                            <span>{$_LANG.is_renew_option_new|default:'No, new certificate'}</span>
                        </label>
                        <label class="sslm-radio">
                            <input type="radio" name="isRenew" value="1" {if $configData.originalfromOthers eq '1'}checked{/if}>
                            <span>{$_LANG.is_renew_option_renew|default:'Yes, renewal'}</span>
                        </label>
                    </div>
                    <p class="sslm-help-text">{$_LANG.is_renew_des|default:'Select "Yes" if renewing an existing certificate to receive bonus time.'}</p>
                </div>

                {* Domain List *}
                <div class="sslm-form-group">
                    <label>{$_LANG.domain_name|default:'Domain Names'} <span class="required">*</span></label>
                    <div id="domainList" class="sslm-domain-list">
                        {* First domain row *}
                        <div class="sslm-domain-row">
                            <div class="sslm-domain-input-group">
                                <input type="text" 
                                       class="sslm-input sslm-domain-input" 
                                       name="domainName" 
                                       placeholder="{$_LANG.common_name|default:'example.com'}"
                                       value="{if isset($configData.domainInfo[0].domainName)}{$configData.domainInfo[0].domainName|escape:'html'}{/if}"
                                       required>
                                <select class="sslm-select sslm-dcv-select" name="dcvMethod">
                                    <option value="CNAME_CSR_HASH" {if isset($configData.domainInfo[0].dcvMethod) && $configData.domainInfo[0].dcvMethod eq 'CNAME_CSR_HASH'}selected{/if}>{$_LANG.cname_csr_hash|default:'DNS CNAME'}</option>
                                    <option value="HTTP_CSR_HASH" {if isset($configData.domainInfo[0].dcvMethod) && $configData.domainInfo[0].dcvMethod eq 'HTTP_CSR_HASH'}selected{/if}>{$_LANG.http_csr_hash|default:'HTTP File'}</option>
                                    {if $supportOptions.supportHttps}
                                    <option value="HTTPS_CSR_HASH" {if isset($configData.domainInfo[0].dcvMethod) && $configData.domainInfo[0].dcvMethod eq 'HTTPS_CSR_HASH'}selected{/if}>{$_LANG.https_csr_hash|default:'HTTPS File'}</option>
                                    {/if}
                                    <option value="DNS_CSR_HASH" {if isset($configData.domainInfo[0].dcvMethod) && $configData.domainInfo[0].dcvMethod eq 'DNS_CSR_HASH'}selected{/if}>{$_LANG.dns_csr_hash|default:'DNS TXT'}</option>
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

        {* Step 2: CSR Configuration *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">2</span> {$_LANG.csr_configuration|default:'CSR Configuration'}</h3>
            </div>
            <div class="sslm-section-body">
                {* CSR Toggle *}
                <div class="sslm-form-group">
                    <label class="sslm-toggle">
                        <input type="checkbox" id="isManualCsr" {if $configData.csr}checked{/if}>
                        <span class="sslm-toggle-slider"></span>
                        <span class="sslm-toggle-label">{$_LANG.is_manual_csr|default:'I have my own CSR'}</span>
                    </label>
                </div>

                {* Auto Generate Section *}
                <div id="autoGenSection" class="sslm-csr-auto" style="{if $configData.csr}display:none{/if}">
                    <p class="sslm-info-text">
                        <i class="fas fa-info-circle"></i>
                        {$_LANG.auto_generate_csr|default:'CSR will be automatically generated based on your domain and contact information.'}
                    </p>
                    <button type="button" id="generateCsrBtn" class="sslm-btn sslm-btn-primary">
                        <i class="fas fa-key"></i> {$_LANG.generate_csr|default:'Generate CSR'}
                    </button>
                </div>

                {* Manual CSR Section *}
                <div id="csrSection" class="sslm-csr-manual" style="{if !$configData.csr}display:none{/if}">
                    <div class="sslm-form-group">
                        <label for="csr">{$_LANG.csr|default:'CSR'} <span class="required">*</span></label>
                        <textarea id="csr" name="csr" class="sslm-textarea sslm-code" rows="8" 
                                  placeholder="-----BEGIN CERTIFICATE REQUEST-----">{$configData.csr|escape:'html'}</textarea>
                        <div class="sslm-textarea-actions">
                            <button type="button" id="decodeCsrBtn" class="sslm-btn sslm-btn-sm sslm-btn-secondary">
                                <i class="fas fa-search"></i> {$_LANG.decode_csr|default:'Decode CSR'}
                            </button>
                        </div>
                    </div>
                    
                    {* Private Key (hidden storage) *}
                    <input type="hidden" id="privateKey" name="privateKey" value="{$configData.privateKey|escape:'html'}">
                </div>
            </div>
        </div>

        {* Step 3: Contact Information *}
        <div class="sslm-section">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">3</span> {$_LANG.admin_contact|default:'Administrator Contact'}</h3>
            </div>
            <div class="sslm-section-body" id="personalcontactPart">
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.first_name|default:'First Name'} <span class="required">*</span></label>
                        <input type="text" name="adminFirstName" class="sslm-input" 
                               value="{if $configData.Administrator.firstName}{$configData.Administrator.firstName|escape:'html'}{else}{$client.firstname|escape:'html'}{/if}" required>
                    </div>
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.last_name|default:'Last Name'} <span class="required">*</span></label>
                        <input type="text" name="adminLastName" class="sslm-input" 
                               value="{if $configData.Administrator.lastName}{$configData.Administrator.lastName|escape:'html'}{else}{$client.lastname|escape:'html'}{/if}" required>
                    </div>
                </div>
                
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.email|default:'Email'} <span class="required">*</span></label>
                        <input type="email" name="adminEmail" class="sslm-input" 
                               value="{if $configData.Administrator.email}{$configData.Administrator.email|escape:'html'}{else}{$client.email|escape:'html'}{/if}" required>
                    </div>
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.phone|default:'Phone'}</label>
                        <input type="text" name="adminPhone" class="sslm-input" 
                               value="{if $configData.Administrator.mobile}{$configData.Administrator.mobile|escape:'html'}{else}{$client.phonenumber|escape:'html'}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.title|default:'Title/Position'}</label>
                        <input type="text" name="adminTitle" class="sslm-input" 
                               value="{if $configData.Administrator.job}{$configData.Administrator.job|escape:'html'}{else}IT Manager{/if}">
                    </div>
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.organization_name|default:'Organization'}</label>
                        <input type="text" name="adminOrganizationName" class="sslm-input" 
                               value="{if $configData.Administrator.organation}{$configData.Administrator.organation|escape:'html'}{else}{$client.companyname|escape:'html'}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.country|default:'Country'}</label>
                        <select name="adminCountry" class="sslm-select">
                            {assign var="selectedCountry" value=$configData.Administrator.country|default:$client.country|default:'VN'}
                            {foreach $countries as $country}
                            <option value="{$country.code|escape:'html'}" {if $selectedCountry eq $country.code}selected{/if}>{$country.name|escape:'html'}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.city|default:'City'}</label>
                        <input type="text" name="adminCity" class="sslm-input" 
                               value="{if $configData.Administrator.city}{$configData.Administrator.city|escape:'html'}{else}{$client.city|escape:'html'}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-row">
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.province|default:'State/Province'}</label>
                        <input type="text" name="adminProvince" class="sslm-input" 
                               value="{if $configData.Administrator.state}{$configData.Administrator.state|escape:'html'}{else}{$client.state|escape:'html'}{/if}">
                    </div>
                    <div class="sslm-form-group sslm-col-6">
                        <label>{$_LANG.postal_code|default:'Postal Code'}</label>
                        <input type="text" name="adminPostCode" class="sslm-input" 
                               value="{if $configData.Administrator.postCode}{$configData.Administrator.postCode|escape:'html'}{else}{$client.postcode|escape:'html'}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.address|default:'Address'}</label>
                    <input type="text" name="adminAddress" class="sslm-input" 
                           value="{if $configData.Administrator.address}{$configData.Administrator.address|escape:'html'}{else}{$client.address1|escape:'html'}{/if}">
                </div>
            </div>
        </div>

        {* Step 4: Organization Information (for OV/EV) *}
        {if $requiresOrganization}
        <div class="sslm-section" id="organizationPart">
            <div class="sslm-section-header">
                <h3><span class="sslm-step-number">4</span> {$_LANG.organization_info|default:'Organization Information'}</h3>
            </div>
            <div class="sslm-section-body">
                <div class="sslm-alert sslm-alert-info">
                    <i class="fas fa-info-circle"></i>
                    {$_LANG.org_info_required|default:'Organization information is required for OV/EV certificates and will be verified.'}
                </div>
                
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
                        <label>{$_LANG.org_postal|default:'Postal Code'}</label>
                        <input type="text" name="organizationPostalCode" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationPostCode|escape:'html'}">
                    </div>
                </div>
                
                <div class="sslm-form-group">
                    <label>{$_LANG.org_country|default:'Country'} <span class="required">*</span></label>
                    <select name="organizationCountry" class="sslm-select" required>
                        {assign var="selectedOrgCountry" value=$configData.organizationInfo.organizationCountry|default:'VN'}
                        {foreach $countries as $country}
                        <option value="{$country.code|escape:'html'}" {if $selectedOrgCountry eq $country.code}selected{/if}>{$country.name|escape:'html'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        {/if}

        {* Form Actions *}
        <div class="sslm-form-actions">
            <button type="button" id="saveBtn" class="sslm-btn sslm-btn-secondary" onclick="saveDraft()">
                <i class="fas fa-save"></i> {$_LANG.save_draft|default:'Save Draft'}
            </button>
            <button type="submit" id="submitBtn" class="sslm-btn sslm-btn-primary">
                <i class="fas fa-paper-plane"></i> {$_LANG.submit_request|default:'Submit Request'}
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
    isWildcard: {if $isWildcard}true{else}false{/if},
    maxDomain: {$maxDomains|default:1},
    other: {
        supportNormal: {if $supportOptions.supportNormal}true{else}false{/if},
        supportIp: {if $supportOptions.supportIp}true{else}false{/if},
        supportWild: {if $supportOptions.supportWild}true{else}false{/if},
        supportHttps: {if $supportOptions.supportHttps}true{else}false{/if}
    },
    configData: {$configData|json_encode nofilter},
    lang: {$_LANG_JSON nofilter}
};

console.log('sslmConfig loaded:', window.sslmConfig);
console.log('configData type:', typeof window.sslmConfig.configData);
console.log('configData value:', window.sslmConfig.configData);
</script>

{* Load JS *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>