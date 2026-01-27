{**
 * NicSRS SSL Module - Reissue Certificate Template
 * Form for reissuing/replacing an existing certificate
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

<div class="sslm-container">
    {* Configuration for JavaScript *}
    <script type="application/json" id="sslmConfig">{literal}{{/literal}
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}",
        "maxDomain": {$maxdomain|default:1},
        "isWildcard": {if $iswildcard}true{else}false{/if},
        "isMultiDomain": {if $ismultidomain}true{else}false{/if},
        "validationType": "{$validationType|default:'dv'}",
        "sslType": "{$sslType|default:'ssl'}",
        "productCode": "{$productCode|escape:'javascript'}",
        "configData": {if $configData}{$configData|@json_encode nofilter}{else}{ldelim}{rdelim}{/if},
        "lang": {if $lang_json}{$lang_json nofilter}{else}{ldelim}{rdelim}{/if},
        "isReissue": true
    {literal}}{/literal}</script>

    {* Page Header *}
    <div class="sslm-page-header">
        <h2>🔄 {$_LANG.reissue_certificate|default:'Reissue Certificate'}</h2>
        <p class="sslm-help-text">{$_LANG.reissue_desc|default:'Submit a new CSR and domain information to reissue your certificate.'}</p>
    </div>

    {* Info Alert *}
    <div class="sslm-alert sslm-alert--info">
        <i>ℹ️</i>
        <span>{$_LANG.reissue_info|default:'Reissuing will generate a new certificate with the same validity period. Your current certificate will remain valid until the new one is issued.'}</span>
    </div>

    {* Current Certificate Info *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.current_certificate|default:'Current Certificate'}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-cert-info">
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_id|default:'Certificate ID'}</label>
                    <span>{$remoteid|default:'N/A'}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_type|default:'Certificate Type'}</label>
                    <span>{$productCode|escape:'html'}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.primary_domain|default:'Primary Domain'}</label>
                    <span>{$primaryDomain|default:'N/A'}</span>
                </div>
            </div>
        </div>
    </div>

    {* Reissue Form *}
    <form id="sslm-reissue-form" class="sslm-form">
        
        {* CSR Section *}
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3><span class="sslm-step-number">1</span> {$_LANG.csr_input|default:'Certificate Signing Request (CSR)'}</h3>
            </div>
            <div class="sslm-card__body">
                <p class="sslm-help-text">{$_LANG.csr_reissue_desc|default:'Enter a new CSR for your certificate. The CSR must match the domains you want to secure.'}</p>
                
                <div class="sslm-form-group">
                    <label class="sslm-label">{$_LANG.csr|default:'CSR'} <span class="sslm-required">*</span></label>
                    <textarea name="csr" id="csr" class="sslm-textarea" rows="8" 
                              placeholder="-----BEGIN CERTIFICATE REQUEST-----"
                              required>{$configData.csr|default:''}</textarea>
                    <small class="sslm-help-text">{$_LANG.csr_help|default:'Paste your CSR here. It should start with -----BEGIN CERTIFICATE REQUEST-----'}</small>
                    <div class="sslm-error-message" id="csrError" style="display: none;"></div>
                </div>

                <button type="button" class="sslm-btn sslm-btn--secondary" id="btnDecodeCsr">
                    🔍 {$_LANG.decode_csr|default:'Decode & Verify CSR'}
                </button>
            </div>
        </div>

        {* Domain Information *}
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3><span class="sslm-step-number">2</span> {$_LANG.domain_info|default:'Domain Information'}</h3>
                {if $ismultidomain && $maxdomain > 1}
                <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" onclick="addDomain()">
                    + {$_LANG.add_domain|default:'Add Domain'}
                </button>
                {/if}
            </div>
            <div class="sslm-card__body">
                <p class="sslm-help-text">
                    {$_LANG.domain_reissue_desc|default:'Enter the domains you want to secure. The primary domain should match your CSR.'}
                    {if $maxdomain > 1}
                    <strong>{$_LANG.max_domains|default:'Maximum domains'}: {$maxdomain}</strong>
                    {/if}
                </p>

                <div id="domainList" class="sslm-domain-list">
                    {* Primary Domain Row *}
                    <div class="sslm-domain-row">
                        <div class="sslm-domain-col">
                            <input type="text" name="domains[0][name]" 
                                   class="sslm-input sslm-domain-input" 
                                   placeholder="{$_LANG.enter_domain|default:'Enter domain (e.g., example.com)'}"
                                   value="{$configData.domainInfo[0].domainName|default:$primaryDomain|default:''}"
                                   required>
                        </div>
                        <div class="sslm-dcv-col">
                            <select name="domains[0][dcvMethod]" class="sslm-select sslm-dcv-select">
                                <option value="CNAME_CSR_HASH" {if ($configData.domainInfo[0].dcvMethod|default:'') == 'CNAME_CSR_HASH'}selected{/if}>{$_LANG.dns_cname|default:'DNS CNAME'}</option>
                                <option value="HTTP_CSR_HASH" {if ($configData.domainInfo[0].dcvMethod|default:'') == 'HTTP_CSR_HASH'}selected{/if}>{$_LANG.http_file|default:'HTTP File'}</option>
                                <option value="HTTPS_CSR_HASH" {if ($configData.domainInfo[0].dcvMethod|default:'') == 'HTTPS_CSR_HASH'}selected{/if}>{$_LANG.https_file|default:'HTTPS File'}</option>
                                <option value="EMAIL" {if ($configData.domainInfo[0].dcvMethod|default:'') == 'EMAIL'}selected{/if}>{$_LANG.email|default:'Email'}</option>
                            </select>
                        </div>
                        <div class="sslm-action-col">
                            <button type="button" class="sslm-btn-icon sslm-btn-remove" onclick="removeDomain(this)" style="display: none;">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {* Organization Info (for OV/EV) *}
        {if $validationType == 'ov' || $validationType == 'ev'}
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3><span class="sslm-step-number">3</span> {$_LANG.organization_info|default:'Organization Information'}</h3>
            </div>
            <div class="sslm-card__body">
                <div class="sslm-form-grid">
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_name|default:'Organization Name'} <span class="sslm-required">*</span></label>
                        <input type="text" name="organizationName" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationName|default:''}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_country|default:'Country'} <span class="sslm-required">*</span></label>
                        <select name="organizationCountry" class="sslm-select" required>
                            <option value="">{$_LANG.select_country|default:'Select Country'}</option>
                            {if $countries}
                            {foreach $countries as $code => $name}
                            <option value="{$code}" {if ($configData.organizationInfo.organizationCountry|default:'') == $code}selected{/if}>{$name}</option>
                            {/foreach}
                            {/if}
                        </select>
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_state|default:'State/Province'}</label>
                        <input type="text" name="organizationState" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationState|default:''}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_city|default:'City'} <span class="sslm-required">*</span></label>
                        <input type="text" name="organizationCity" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationCity|default:''}" required>
                    </div>
                    <div class="sslm-form-group sslm-form-group--full">
                        <label class="sslm-label">{$_LANG.organization_address|default:'Address'}</label>
                        <input type="text" name="organizationAddress" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationAddress|default:''}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_postcode|default:'Postal Code'}</label>
                        <input type="text" name="organizationPostalCode" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationPostalCode|default:''}">
                    </div>
                    <div class="sslm-form-group">
                        <label class="sslm-label">{$_LANG.organization_phone|default:'Phone'}</label>
                        <input type="text" name="organizationMobile" class="sslm-input" 
                               value="{$configData.organizationInfo.organizationMobile|default:''}">
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {* Submit Buttons *}
        <div class="sslm-card sslm-card-actions">
            <div class="sslm-action-bar" style="border: none; margin: 0; padding: 0;">
                <a href="clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn--secondary">
                    ← {$_LANG.cancel|default:'Cancel'}
                </a>
                <button type="submit" class="sslm-btn sslm-btn--primary" id="submitReissueBtn">
                    🔄 {$_LANG.submit_reissue|default:'Submit Reissue Request'}
                </button>
            </div>
        </div>
    </form>
</div>

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>