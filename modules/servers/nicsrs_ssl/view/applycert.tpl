{**
 * SSL Certificate Application Form
 * Auto-generate CSR as default, auto-fill contact from client info
 * 
 * @package    NicSRS SSL Module
 * @author     HVN GROUP
 * @version    2.0.0
 *}

<link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css" rel="stylesheet" type="text/css">

{* Parse configData if it's a JSON string *}
{if is_string($configData) && $configData}
    {assign var="cfgData" value=$configData|json_decode:true}
{elseif is_array($configData)}
    {assign var="cfgData" value=$configData}
{else}
    {assign var="cfgData" value=[]}
{/if}

{* Parse countries if it's a JSON string *}
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
    {* Header Alert *}
    <div class="sslm-alert sslm-alert-info">
        <i class="fas fa-info-circle"></i>
        <span>{$_LANG.apply_cert_notice|default:'You are requesting the certificate. Please submit the necessary information required.'}</span>
    </div>

    <form id="sslm-apply-form" method="post">
        <input type="hidden" name="action" value="submitApply">
        <input type="hidden" name="serviceid" value="{$serviceid}">
        
        {* Section 1: Renew or Not *}
        <div class="sslm-card">
            <div class="sslm-card-header">
                <span class="sslm-step-number">1</span>
                <span class="sslm-step-title">{$_LANG.renew_or_not|default:'Renew or not'}</span>
                <div class="sslm-radio-group">
                    <label class="sslm-radio">
                        <input type="radio" name="renewOrNot" value="purchase" checked>
                        <span class="sslm-radio-mark"></span>
                        {$_LANG.purchase|default:'Purchase'}
                    </label>
                    <label class="sslm-radio">
                        <input type="radio" name="renewOrNot" value="renew">
                        <span class="sslm-radio-mark"></span>
                        {$_LANG.renew|default:'Renew'}
                    </label>
                </div>
            </div>
            <div class="sslm-card-body">
                <p class="sslm-text-muted">{$_LANG.renew_description|default:'If you already have an SSL certificate and need to renew it, please choose Renew. An SSL certificate can be renewed within 30 days of expiration. If this is not a renewal order, please choose Purchase.'}</p>
            </div>
        </div>

        {* Section 2: CSR *}
        <div class="sslm-card">
            <div class="sslm-card-header">
                <span class="sslm-step-number">2</span>
                <span class="sslm-step-title">{$_LANG.csr|default:'CSR'}</span>
            </div>
            <div class="sslm-card-body">
                <p class="sslm-text-muted">{$_LANG.csr_description|default:'The CSR is the original file of your public key, which contains your server and organization information that will be validated by CA. To complete the validation successfully, it is recommended to generate a CSR created by the system.'}</p>
                
                <div class="sslm-toggle-row">
                    <span class="sslm-toggle-label">{$_LANG.fill_manually|default:'Fill manually'}</span>
                    <label class="sslm-switch">
                        <input type="checkbox" id="isManualCsr" name="isManualCsr" {if !empty($cfgData.csr)}checked{/if}>
                        <span class="sslm-slider"></span>
                    </label>
                </div>
                
                <div id="csrTextarea" class="sslm-csr-textarea" style="{if empty($cfgData.csr)}display: none;{/if}">
                    <textarea name="csr" id="csr" placeholder="{$_LANG.csr_placeholder|default:'-----BEGIN CERTIFICATE REQUEST-----\n...\n-----END CERTIFICATE REQUEST-----'}">{$cfgData.csr|default:''}</textarea>
                </div>
            </div>
        </div>

        {* Section 3: Domain Information *}
        <div class="sslm-card">
            <div class="sslm-card-header">
                <span class="sslm-step-number">3</span>
                <span class="sslm-step-title">{$_LANG.domain_information|default:'Domain Information'}</span>
            </div>
            <div class="sslm-card-body">
                <div class="sslm-domain-header">
                    <div class="sslm-domain-col">{$_LANG.domain|default:'Domain'}</div>
                    <div class="sslm-dcv-col">{$_LANG.dcv_method|default:'DCV method'}</div>
                </div>
                
                <div id="domainList">
                    {assign var="domainValue" value=""}
                    {assign var="dcvValue" value=""}
                    {if !empty($cfgData.domainInfo[0].domainName)}
                        {assign var="domainValue" value=$cfgData.domainInfo[0].domainName}
                    {/if}
                    {if !empty($cfgData.domainInfo[0].dcvMethod)}
                        {assign var="dcvValue" value=$cfgData.domainInfo[0].dcvMethod}
                    {/if}
                    
                    <div class="sslm-domain-row" data-index="0">
                        <div class="sslm-domain-col">
                            <input type="text" name="domains[0][name]" class="sslm-input sslm-domain-input" placeholder="{$_LANG.domain_placeholder|default:'example.com'}" value="{$domainValue}">
                        </div>
                        <div class="sslm-dcv-col">
                            <select name="domains[0][dcvMethod]" class="sslm-select sslm-dcv-select">
                                <option value="">{$_LANG.choose|default:'Choose'}</option>
                                <option value="CNAME_CSR_HASH" {if $dcvValue == 'CNAME_CSR_HASH'}selected{/if}>{$_LANG.dns_cname|default:'DNS CNAME'}</option>
                                <option value="HTTP_CSR_HASH" {if $dcvValue == 'HTTP_CSR_HASH'}selected{/if}>{$_LANG.http_file|default:'HTTP File'}</option>
                                <option value="HTTPS_CSR_HASH" {if $dcvValue == 'HTTPS_CSR_HASH'}selected{/if}>{$_LANG.https_file|default:'HTTPS File'}</option>
                                <option value="EMAIL" {if $dcvValue == 'EMAIL'}selected{/if}>{$_LANG.email|default:'Email'}</option>
                            </select>
                        </div>
                        {if $ismultidomain || $maxdomain > 1}
                        <div class="sslm-action-col">
                            <button type="button" class="sslm-btn-icon sslm-btn-remove" onclick="removeDomain(this)" style="display:none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        {/if}
                    </div>
                </div>
                
                {if $ismultidomain || $maxdomain > 1}
                <div class="sslm-domain-actions">
                    <button type="button" class="sslm-btn sslm-btn-outline" onclick="addDomain()">
                        <i class="fas fa-plus"></i> {$_LANG.add_domain|default:'Add Domain'}
                    </button>
                    <span class="sslm-text-muted sslm-domain-count">
                        {$_LANG.max_domains|default:'Maximum'}: <strong id="maxDomainCount">{$maxdomain|default:1}</strong> {$_LANG.domains|default:'domains'}
                    </span>
                </div>
                {/if}
            </div>
        </div>

        {* Section 4: Contacts *}
        <div class="sslm-card">
            <div class="sslm-card-header">
                <span class="sslm-step-number">4</span>
                <span class="sslm-step-title">{$_LANG.contacts|default:'Contacts'}</span>
            </div>
            <div class="sslm-card-body">
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization|default:'Org'}</label>
                        <input type="text" name="adminOrganizationName" class="sslm-input" value="{if !empty($cfgData.Administrator.organation)}{$cfgData.Administrator.organation}{elseif !empty($clientsdetails.companyname)}{$clientsdetails.companyname}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.title|default:'Title'}</label>
                        <input type="text" name="adminTitle" class="sslm-input" value="{if !empty($cfgData.Administrator.job)}{$cfgData.Administrator.job}{else}IT Manager{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.first_name|default:'First Name'}</label>
                        <input type="text" name="adminFirstName" class="sslm-input" value="{if !empty($cfgData.Administrator.firstName)}{$cfgData.Administrator.firstName}{elseif !empty($clientsdetails.firstname)}{$clientsdetails.firstname}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.last_name|default:'Last Name'}</label>
                        <input type="text" name="adminLastName" class="sslm-input" value="{if !empty($cfgData.Administrator.lastName)}{$cfgData.Administrator.lastName}{elseif !empty($clientsdetails.lastname)}{$clientsdetails.lastname}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.email|default:'Email'}</label>
                        <input type="email" name="adminEmail" class="sslm-input" value="{if !empty($cfgData.Administrator.email)}{$cfgData.Administrator.email}{elseif !empty($clientsdetails.email)}{$clientsdetails.email}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.phone|default:'Phone'}</label>
                        <input type="text" name="adminPhone" class="sslm-input" value="{if !empty($cfgData.Administrator.mobile)}{$cfgData.Administrator.mobile}{elseif !empty($clientsdetails.phonenumber)}{$clientsdetails.phonenumber}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.country|default:'Country'}</label>
                        <select name="adminCountry" class="sslm-select" id="adminCountry">
                            {foreach from=$countryList item=country}
                                <option value="{$country.code}" {if $country.code == $selectedCountry}selected{/if}>{$country.name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.address|default:'Address'}</label>
                        <input type="text" name="adminAddress" class="sslm-input" value="{if !empty($cfgData.Administrator.address)}{$cfgData.Administrator.address}{elseif !empty($clientsdetails.address1)}{$clientsdetails.address1}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-grid sslm-form-grid-3">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.city|default:'City'}</label>
                        <input type="text" name="adminCity" class="sslm-input" value="{if !empty($cfgData.Administrator.city)}{$cfgData.Administrator.city}{elseif !empty($clientsdetails.city)}{$clientsdetails.city}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.province|default:'Province'}</label>
                        <input type="text" name="adminProvince" class="sslm-input" value="{if !empty($cfgData.Administrator.state)}{$cfgData.Administrator.state}{elseif !empty($clientsdetails.state)}{$clientsdetails.state}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.postcode|default:'Postcode'}</label>
                        <input type="text" name="adminPostcode" class="sslm-input" value="{if !empty($cfgData.Administrator.postCode)}{$cfgData.Administrator.postCode}{elseif !empty($clientsdetails.postcode)}{$clientsdetails.postcode}{/if}">
                    </div>
                </div>
            </div>
        </div>

        {* OV/EV Additional Organization Info *}
        {if $validationType == 'ov' || $validationType == 'ev'}
        <div class="sslm-card" id="organizationSection">
            <div class="sslm-card-header">
                <span class="sslm-step-number">5</span>
                <span class="sslm-step-title">{$_LANG.organization_info|default:'Organization Information'}</span>
            </div>
            <div class="sslm-card-body">
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_name|default:'Organization Name'}</label>
                        <input type="text" name="organizationName" class="sslm-input" value="{if !empty($cfgData.organizationInfo.organizationName)}{$cfgData.organizationInfo.organizationName}{elseif !empty($clientsdetails.companyname)}{$clientsdetails.companyname}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.division|default:'Division'}</label>
                        <input type="text" name="division" class="sslm-input" value="{if !empty($cfgData.organizationInfo.division)}{$cfgData.organizationInfo.division}{else}IT Department{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.duns|default:'DUNS'}</label>
                        <input type="text" name="duns" class="sslm-input" value="{$cfgData.organizationInfo.duns|default:''}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_phone|default:'Organization Phone'}</label>
                        <input type="text" name="organizationPhone" class="sslm-input" value="{if !empty($cfgData.organizationInfo.organizationPhone)}{$cfgData.organizationInfo.organizationPhone}{elseif !empty($clientsdetails.phonenumber)}{$clientsdetails.phonenumber}{/if}">
                    </div>
                </div>
                
                <div class="sslm-form-grid sslm-form-grid-3">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_city|default:'City'}</label>
                        <input type="text" name="organizationCity" class="sslm-input" value="{if !empty($cfgData.organizationInfo.organizationCity)}{$cfgData.organizationInfo.organizationCity}{elseif !empty($clientsdetails.city)}{$clientsdetails.city}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_region|default:'Region'}</label>
                        <input type="text" name="organizationRegion" class="sslm-input" value="{if !empty($cfgData.organizationInfo.organizationRegion)}{$cfgData.organizationInfo.organizationRegion}{elseif !empty($clientsdetails.state)}{$clientsdetails.state}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_country|default:'Country'}</label>
                        <select name="organizationCountry" class="sslm-select">
                            {foreach from=$countryList item=country}
                                <option value="{$country.code}" {if $country.code == $selectedCountry}selected{/if}>{$country.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_address|default:'Address'}</label>
                        <input type="text" name="organizationAddress" class="sslm-input" value="{if !empty($cfgData.organizationInfo.organizationAddress)}{$cfgData.organizationInfo.organizationAddress}{elseif !empty($clientsdetails.address1)}{$clientsdetails.address1}{/if}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_postcode|default:'Postcode'}</label>
                        <input type="text" name="organizationPostalCode" class="sslm-input" value="{if !empty($cfgData.organizationInfo.organizationPostalCode)}{$cfgData.organizationInfo.organizationPostalCode}{elseif !empty($clientsdetails.postcode)}{$clientsdetails.postcode}{/if}">
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {* Submit Buttons *}
        <div class="sslm-card sslm-card-actions">
            <button type="submit" class="sslm-btn sslm-btn-primary" id="submitBtn">
                <i class="fas fa-paper-plane"></i> {$_LANG.submit|default:'Submit'}
            </button>
            <button type="button" class="sslm-btn sslm-btn-secondary" id="saveBtn" onclick="saveDraft()">
                <i class="fas fa-save"></i> {$_LANG.save|default:'Save'}
            </button>
        </div>
    </form>
</div>

{* Hidden Config Data for JavaScript *}
<script>
    var sslmConfig = {
        serviceid: '{$serviceid}',
        maxDomain: {$maxdomain|default:1},
        isWildcard: {if $iswildcard}true{else}false{/if},
        isMultiDomain: {if $ismultidomain}true{else}false{/if},
        validationType: '{$validationType|default:"dv"}',
        sslType: '{$sslType|default:"ssl"}',
        productCode: '{$productCode|escape:"javascript"}',
        configData: {if is_array($cfgData)}{$cfgData|@json_encode nofilter}{else}{ldelim}{rdelim}{/if},
        lang: {if is_array($_LANG)}{$_LANG|@json_encode nofilter}{else}{ldelim}{rdelim}{/if},
        other: {if is_string($other)}{$other nofilter}{elseif is_array($other)}{$other|@json_encode nofilter}{else}{ldelim}{rdelim}{/if},
        ajaxUrl: '{$systemurl}clientarea.php?action=productdetails&id={$serviceid}'
    };
</script>
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>