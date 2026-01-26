<link rel="stylesheet" href="{$WEB_ROOT}/{$MODULE_PATH}/assets/css/ssl-manager.css">

<div class="sslm-container">
    <!-- Config for JS -->
    <script type="application/json" id="sslmConfig">{
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}&modop=custom",
        "lang": {$_LANG_JSON}
    }</script>

    <!-- Page Header -->
    <div class="sslm-alert sslm-alert--info">
        <i>ℹ️</i>
        <span>{$_LANG.apply_des}</span>
    </div>

    <!-- Progress Steps -->
    <div class="sslm-steps">
        <div class="sslm-step sslm-step--active" data-step="1">
            <span class="sslm-step__number">1</span>
            <span class="sslm-step__title">{$_LANG.step_csr}</span>
        </div>
        <div class="sslm-step" data-step="2">
            <span class="sslm-step__number">2</span>
            <span class="sslm-step__title">{$_LANG.step_domain}</span>
        </div>
        {if $requiresOrganization}
        <div class="sslm-step" data-step="3">
            <span class="sslm-step__number">3</span>
            <span class="sslm-step__title">{$_LANG.step_contacts}</span>
        </div>
        <div class="sslm-step" data-step="4">
            <span class="sslm-step__number">4</span>
            <span class="sslm-step__title">{$_LANG.step_organization}</span>
        </div>
        {else}
        <div class="sslm-step" data-step="3">
            <span class="sslm-step__number">3</span>
            <span class="sslm-step__title">{$_LANG.step_contacts}</span>
        </div>
        {/if}
    </div>

    <form id="sslApplyForm" method="post">
        <input type="hidden" name="privateKey" id="privateKey" value="">

        <!-- Renewal Option (for website_ssl) -->
        {if $sslType == 'website_ssl'}
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.is_renew}</h3>
            </div>
            <div class="sslm-card__body">
                <p class="sslm-help-text">{$_LANG.is_renew_des}</p>
                <div class="sslm-radio-group">
                    <label class="sslm-radio">
                        <input type="radio" name="isRenew" value="0" checked>
                        <span>{$_LANG.is_renew_option_new}</span>
                    </label>
                    <label class="sslm-radio">
                        <input type="radio" name="isRenew" value="1">
                        <span>{$_LANG.is_renew_option_renew}</span>
                    </label>
                </div>
            </div>
        </div>
        {/if}

        <!-- CSR Section -->
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.csr_configuration}</h3>
            </div>
            <div class="sslm-card__body">
                <p class="sslm-help-text">{$_LANG.csr_des}</p>
                
                <!-- CSR Mode Toggle - Default: Auto Generate -->
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

                <!-- Auto CSR Fields (shown by default) -->
                <div id="autoCSRFields" class="sslm-form-section">
                    <div class="sslm-form-row">
                        <div class="sslm-form-group">
                            <label>{$_LANG.common_name} <span class="sslm-required">*</span></label>
                            <input type="text" name="commonName" class="sslm-input" 
                                   placeholder="example.com or *.example.com" required>
                            <span class="sslm-help-text">Enter domain name (e.g., example.com)</span>
                        </div>
                        <div class="sslm-form-group">
                            <label>{$_LANG.organization}</label>
                            <input type="text" name="organization" class="sslm-input" 
                                   value="{$client.companyName|escape:'html'}">
                        </div>
                    </div>
                    <div class="sslm-form-row">
                        <div class="sslm-form-group">
                            <label>{$_LANG.city}</label>
                            <input type="text" name="city" class="sslm-input" 
                                   value="{$client.city|escape:'html'}">
                        </div>
                        <div class="sslm-form-group">
                            <label>{$_LANG.state}</label>
                            <input type="text" name="state" class="sslm-input" 
                                   value="{$client.state|escape:'html'}">
                        </div>
                    </div>
                    <div class="sslm-form-row">
                        <div class="sslm-form-group">
                            <label>{$_LANG.country}</label>
                            <select name="country" class="sslm-select">
                                <option value="">{$_LANG.please_choose}</option>
                                {foreach $countries as $code => $name}
                                <option value="{$code}" {if $client.country == $code}selected{/if}>{$name}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="sslm-form-group">
                            <label>{$_LANG.email}</label>
                            <input type="email" name="csrEmail" class="sslm-input" 
                                   value="{$client.email|escape:'html'}">
                        </div>
                    </div>
                </div>

                <!-- Manual CSR Textarea (hidden by default) -->
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
                    <!-- Domain Row -->
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
                                    {if $supportOptions.supportHttps}
                                    <option value="HTTP_CSR_HASH">{$_LANG.http_csr_hash}</option>
                                    <option value="HTTPS_CSR_HASH">{$_LANG.https_csr_hash}</option>
                                    {/if}
                                    <option value="CNAME_CSR_HASH">{$_LANG.cname_csr_hash}</option>
                                    <option value="DNS_CSR_HASH">{$_LANG.dns_csr_hash}</option>
                                </select>
                            </div>
                            <div class="sslm-form-group" style="flex: 0 0 80px; align-self: flex-end;">
                                <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm sslm-remove-domain" 
                                        style="display: none;">
                                    {$_LANG.remove_domain}
                                </button>
                            </div>
                        </div>
                    </div>
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

        <!-- Contact Information -->
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.contacts}</h3>
            </div>
            <div class="sslm-card__body">
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.first_name} {if $requiresOrganization}<span class="sslm-required">*</span>{/if}</label>
                        <input type="text" name="adminFirstName" class="sslm-input" 
                               value="{$client.firstName|escape:'html'}" {if $requiresOrganization}required{/if}>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.last_name} {if $requiresOrganization}<span class="sslm-required">*</span>{/if}</label>
                        <input type="text" name="adminLastName" class="sslm-input" 
                               value="{$client.lastName|escape:'html'}" {if $requiresOrganization}required{/if}>
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.email_address} {if $requiresOrganization}<span class="sslm-required">*</span>{/if}</label>
                        <input type="email" name="adminEmail" class="sslm-input" 
                               value="{$client.email|escape:'html'}" {if $requiresOrganization}required{/if}>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.phone} {if $requiresOrganization}<span class="sslm-required">*</span>{/if}</label>
                        <input type="text" name="adminPhone" class="sslm-input" 
                               value="{$client.phone|escape:'html'}" {if $requiresOrganization}required{/if}>
                    </div>
                </div>
                {if $requiresOrganization}
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.organization_name} <span class="sslm-required">*</span></label>
                        <input type="text" name="adminOrganizationName" class="sslm-input" 
                               value="{$client.companyName|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.title}</label>
                        <input type="text" name="adminTitle" class="sslm-input" 
                               placeholder="IT Manager">
                    </div>
                </div>
                {/if}
            </div>
        </div>

        <!-- Organization Information (for OV/EV) -->
        {if $requiresOrganization}
        <div class="sslm-card">
            <div class="sslm-card__header">
                <h3>{$_LANG.organization_info}</h3>
            </div>
            <div class="sslm-card__body">
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_name} <span class="sslm-required">*</span></label>
                        <input type="text" name="orgName" class="sslm-input" 
                               value="{$client.companyName|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_division}</label>
                        <input type="text" name="orgDivision" class="sslm-input">
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_address} <span class="sslm-required">*</span></label>
                        <input type="text" name="orgAddress" class="sslm-input" 
                               value="{$client.address|escape:'html'}" required>
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_city} <span class="sslm-required">*</span></label>
                        <input type="text" name="orgCity" class="sslm-input" 
                               value="{$client.city|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_state} <span class="sslm-required">*</span></label>
                        <input type="text" name="orgState" class="sslm-input" 
                               value="{$client.state|escape:'html'}" required>
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_country} <span class="sslm-required">*</span></label>
                        <select name="orgCountry" class="sslm-select" required>
                            <option value="">{$_LANG.please_choose}</option>
                            {foreach $countries as $code => $name}
                            <option value="{$code}" {if $client.country == $code}selected{/if}>{$name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_postal}</label>
                        <input type="text" name="orgPostCode" class="sslm-input" 
                               value="{$client.postCode|escape:'html'}">
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.org_phone} <span class="sslm-required">*</span></label>
                        <input type="text" name="orgPhone" class="sslm-input" 
                               value="{$client.phone|escape:'html'}" required>
                    </div>
                    <div class="sslm-form-group">
                        <label>{$_LANG.idType}</label>
                        <select name="idType" class="sslm-select">
                            <option value="TYDMZ">{$_LANG.organizationCode}</option>
                            <option value="OTHERS">{$_LANG.other}</option>
                        </select>
                    </div>
                </div>
                <div class="sslm-form-row">
                    <div class="sslm-form-group">
                        <label>{$_LANG.organizationRegNumber}</label>
                        <input type="text" name="organizationRegNumber" class="sslm-input">
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
                        <option value="{$code}">{$name}</option>
                        {/foreach}
                    </select>
                    <span class="sslm-help-text">Select your web server type for proper certificate format</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="sslm-action-bar">
            <button type="button" class="sslm-btn sslm-btn--secondary" data-action="saveDraft">
                {$_LANG.save_draft}
            </button>
            <button type="button" class="sslm-btn sslm-btn--primary sslm-btn--lg" data-action="submitApply">
                {$_LANG.submit_request}
            </button>
        </div>
    </form>
</div>

<script src="{$WEB_ROOT}/{$MODULE_PATH}/assets/js/ssl-manager.js"></script>