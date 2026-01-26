<link rel="stylesheet" href="{$WEB_ROOT}/{$MODULE_PATH}/assets/css/ssl-manager.css">

<div class="sslm-container">
    <script type="application/json" id="sslmConfig">{
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}&modop=custom",
        "lang": {$_LANG_JSON}
    }</script>

    <!-- Page Header -->
    <div class="sslm-alert sslm-alert--info">
        <i>🔄</i>
        <span>{$_LANG.replace_des}</span>
    </div>

    <!-- Current Certificate Info -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.certificate_info}</h3>
            <span class="sslm-badge sslm-badge--info">{$_LANG.reissue}</span>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-cert-info">
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_id}</label>
                    <span>{$remoteid|default:'N/A'}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_type}</label>
                    <span>{$productCode}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.status}</label>
                    <span class="sslm-badge sslm-badge--warning">{$_LANG.reissue_pending}</span>
                </div>
            </div>
        </div>
    </div>

    <form id="sslReissueForm" method="post">
        <input type="hidden" name="privateKey" id="privateKey" value="">
        <input type="hidden" name="certId" value="{$remoteid}">

        <!-- CSR Section -->
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.csr_configuration}</h3>
            </div>
            <div class="sslm-card__body">
                <p class="sslm-help-text">{$_LANG.csr_des}</p>
                
                <!-- CSR Mode Toggle -->
                <div class="sslm-radio-group" style="margin-top: 16px;">
                    <label class="sslm-radio">
                        <input type="radio" name="csrMode" value="auto" checked>
                        <span>{$_LANG.auto_generate_csr}</span>
                    </label>
                    <label class="sslm-radio">
                        <input type="radio" name="csrMode" value="manual">
                        <span>{$_LANG.manual_csr}</span>
                    </label>
                </div>

                <!-- Auto CSR Fields -->
                <div id="autoCSRFields" class="sslm-form-section">
                    <div class="sslm-form-row">
                        <div class="sslm-form-group">
                            <label>{$_LANG.common_name} <span class="sslm-required">*</span></label>
                            <input type="text" name="commonName" class="sslm-input" 
                                   value="{$existingData.domainInfo[0].domainName|escape:'html'}"
                                   placeholder="example.com or *.example.com" required>
                        </div>
                        <div class="sslm-form-group">
                            <label>{$_LANG.organization}</label>
                            <input type="text" name="organization" class="sslm-input" 
                                   value="{$existingData.Administrator.organation|escape:'html'}">
                        </div>
                    </div>
                    <div class="sslm-form-row">
                        <div class="sslm-form-group">
                            <label>{$_LANG.city}</label>
                            <input type="text" name="city" class="sslm-input" 
                                   value="{$existingData.organizationInfo.city|escape:'html'}">
                        </div>
                        <div class="sslm-form-group">
                            <label>{$_LANG.state}</label>
                            <input type="text" name="state" class="sslm-input" 
                                   value="{$existingData.organizationInfo.state|escape:'html'}">
                        </div>
                    </div>
                    <div class="sslm-form-row">
                        <div class="sslm-form-group">
                            <label>{$_LANG.country}</label>
                            <select name="country" class="sslm-select">
                                <option value="">{$_LANG.please_choose}</option>
                                {foreach $countries as $code => $name}
                                <option value="{$code}" {if $existingData.organizationInfo.country == $code}selected{/if}>{$name}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="sslm-form-group">
                            <label>{$_LANG.email}</label>
                            <input type="email" name="csrEmail" class="sslm-input" 
                                   value="{$existingData.Administrator.email|escape:'html'}">
                        </div>
                    </div>
                </div>

                <!-- Manual CSR Textarea -->
                <div id="manualCSRField" class="sslm-form-section" style="display: none;">
                    <div class="sslm-form-group">
                        <label>{$_LANG.paste_csr} <span class="sslm-required">*</span></label>
                        <textarea name="csr" class="sslm-textarea sslm-textarea--code" 
                                  placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>
                    </div>
                    <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                            onclick="SSLManager.decodeCSR()">
                        {$_LANG.decode_csr}
                    </button>
                </div>
            </div>
        </div>

        <!-- Domain Information -->
        {if $sslType == 'website_ssl'}
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.domain_info}</h3>
                {if $isMultiDomain}
                <span class="sslm-badge sslm-badge--info">{$_LANG.max_domain}: {$maxDomains}</span>
                {/if}
            </div>
            <div class="sslm-card__body">
                <div class="sslm-domain-list" data-max-domains="{$maxDomains}">
                    {foreach $existingData.domainInfo as $index => $domain}
                    <div class="sslm-domain-row" style="margin-bottom: 16px;">
                        <div class="sslm-form-row">
                            <div class="sslm-form-group" style="flex: 2;">
                                <label><span class="sslm-domain-number">{$index+1}</span>. {$_LANG.domain_name} <span class="sslm-required">*</span></label>
                                <input type="text" name="domainName[]" class="sslm-input" 
                                       value="{$domain.domainName|escape:'html'}"
                                       placeholder="example.com" required>
                            </div>
                            <div class="sslm-form-group" style="flex: 1;">
                                <label>{$_LANG.dcv_method} <span class="sslm-required">*</span></label>
                                <select name="dcvMethod[]" class="sslm-select" required>
                                    <option value="">{$_LANG.please_choose}</option>
                                    <option value="HTTP_CSR_HASH" {if $domain.dcvMethod == 'HTTP_CSR_HASH'}selected{/if}>{$_LANG.http_csr_hash}</option>
                                    <option value="HTTPS_CSR_HASH" {if $domain.dcvMethod == 'HTTPS_CSR_HASH'}selected{/if}>{$_LANG.https_csr_hash}</option>
                                    <option value="CNAME_CSR_HASH" {if $domain.dcvMethod == 'CNAME_CSR_HASH'}selected{/if}>{$_LANG.cname_csr_hash}</option>
                                    <option value="DNS_CSR_HASH" {if $domain.dcvMethod == 'DNS_CSR_HASH'}selected{/if}>{$_LANG.dns_csr_hash}</option>
                                </select>
                            </div>
                            <div class="sslm-form-group" style="flex: 0 0 80px; align-self: flex-end;">
                                {if $index > 0}
                                <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm sslm-remove-domain">
                                    {$_LANG.remove_domain}
                                </button>
                                {/if}
                            </div>
                        </div>
                    </div>
                    {foreachelse}
                    <!-- Default empty row if no existing domains -->
                    <div class="sslm-domain-row" style="margin-bottom: 16px;">
                        <div class="sslm-form-row">
                            <div class="sslm-form-group" style="flex: 2;">
                                <label><span class="sslm-domain-number">1</span>. {$_LANG.domain_name} <span class="sslm-required">*</span></label>
                                <input type="text" name="domainName[]" class="sslm-input" 
                                       placeholder="example.com" required>
                            </div>
                            <div class="sslm-form-group" style="flex: 1;">
                                <label>{$_LANG.dcv_method} <span class="sslm-required">*</span></label>
                                <select name="dcvMethod[]" class="sslm-select" required>
                                    <option value="">{$_LANG.please_choose}</option>
                                    <option value="HTTP_CSR_HASH">{$_LANG.http_csr_hash}</option>
                                    <option value="HTTPS_CSR_HASH">{$_LANG.https_csr_hash}</option>
                                    <option value="CNAME_CSR_HASH">{$_LANG.cname_csr_hash}</option>
                                    <option value="DNS_CSR_HASH">{$_LANG.dns_csr_hash}</option>
                                </select>
                            </div>
                            <div class="sslm-form-group" style="flex: 0 0 80px;"></div>
                        </div>
                    </div>
                    {/foreach}
                </div>

                {if $isMultiDomain}
                <div style="margin-top: 12px;">
                    <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm sslm-add-domain">
                        + {$_LANG.add_domain}
                    </button>
                    <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm sslm-set-for-all" style="margin-left: 8px;">
                        {$_LANG.set_for_all}
                    </button>
                </div>
                {/if}
            </div>
        </div>
        {/if}

        <!-- Contact Information (for OV/EV) -->
        {if $requiresOrganization}
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.contacts}</h3>
            </div>
            <div class="sslm-card__body">
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.first_name} <span class="sslm-required">*</span></label>
                        <input type="text" name="adminFirstName" class="sslm-input" 
                               value="{$existingData.Administrator.firstName|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.last_name} <span class="sslm-required">*</span></label>
                        <input type="text" name="adminLastName" class="sslm-input" 
                               value="{$existingData.Administrator.lastName|escape:'html'}" required>
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.email_address} <span class="sslm-required">*</span></label>
                        <input type="email" name="adminEmail" class="sslm-input" 
                               value="{$existingData.Administrator.email|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.phone} <span class="sslm-required">*</span></label>
                        <input type="text" name="adminPhone" class="sslm-input" 
                               value="{$existingData.Administrator.mobile|escape:'html'}" required>
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.organization_name} <span class="sslm-required">*</span></label>
                        <input type="text" name="adminOrganizationName" class="sslm-input" 
                               value="{$existingData.Administrator.organation|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.title}</label>
                        <input type="text" name="adminTitle" class="sslm-input" 
                               value="{$existingData.Administrator.job|escape:'html'}">
                    </div>
                </div>
            </div>
        </div>
        {/if}

        <!-- Server Type -->
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.server_type}</h3>
            </div>
            <div class="sslm-card__body">
                <div class="sslm-form-group">
                    <select name="server" class="sslm-select" style="max-width: 300px;">
                        {foreach $serverTypes as $code => $name}
                        <option value="{$code}" {if $configdata.server == $code}selected{/if}>{$name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>

        <!-- Confirmation -->
        <div class="sslm-card sslm-card--warning">
            <div class="sslm-card__body">
                <div class="sslm-alert sslm-alert--warning" style="margin: 0;">
                    <i>⚠️</i>
                    <span><strong>{$_LANG.sure_to_replace}</strong><br>
                    Reissuing will invalidate the current certificate. The new certificate will need domain validation again.</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="sslm-action-bar">
            <a href="clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn--secondary">
                {$_LANG.back}
            </a>
            <button type="button" class="sslm-btn sslm-btn--primary sslm-btn--lg" data-action="reissueCert">
                🔄 {$_LANG.reissue_certificate}
            </button>
        </div>
    </form>
</div>

<script src="{$WEB_ROOT}/{$MODULE_PATH}/assets/js/ssl-manager.js"></script>