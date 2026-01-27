{**
 * NicSRS SSL Module - Pending Certificate Template
 * Shows DCV validation status and instructions
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

<div class="sslm-container">
    {* Configuration for JavaScript - FIXED ajaxUrl *}
    <script type="application/json" id="sslmConfig">{literal}{{/literal}
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}",
        "lang": {if $lang_json}{$lang_json nofilter}{else}{ldelim}{rdelim}{/if}
    {literal}}{/literal}</script>

    {* Status Alert *}
    <div class="sslm-alert sslm-alert--warning">
        <i>⏳</i>
        <span>{$_LANG.message_des|default:'Your certificate is pending domain validation. Please complete the verification steps below.'}</span>
    </div>

    {* Certificate Status Card *}
    <div class="sslm-card sslm-card--warning">
        <div class="sslm-card__header">
            <h3>{$_LANG.certificate_pending|default:'Certificate Pending'}</h3>
            <span class="sslm-badge sslm-badge--warning">{$status|escape:'html'}</span>
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
                    <label>{$_LANG.status|default:'Status'}</label>
                    <span class="sslm-badge sslm-badge--warning">{$status|escape:'html'}</span>
                </div>
                {if $applyTime}
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.submit|default:'Submitted'}</label>
                    <span>{$applyTime|escape:'html'}</span>
                </div>
                {/if}
            </div>
        </div>
    </div>

    {* Domain Validation Status *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.domain_validation|default:'Domain Validation'}</h3>
            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" data-action="refreshStatus">
                🔄 {$_LANG.refresh_status|default:'Refresh Status'}
            </button>
        </div>
        <div class="sslm-card__body">
            {if $dcvStatus|@count > 0}
            <table class="sslm-table">
                <thead>
                    <tr>
                        <th>{$_LANG.domain|default:'Domain'}</th>
                        <th>{$_LANG.dcv_method|default:'Validation Method'}</th>
                        <th>{$_LANG.status|default:'Status'}</th>
                        <th>{$_LANG.actions|default:'Actions'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $dcvStatus as $dcv}
                    <tr class="sslm-dcv-row">
                        <td>
                            <strong>{$dcv.domain|escape:'html'}</strong>
                            <input type="hidden" name="dcvDomain" value="{$dcv.domain|escape:'html'}">
                        </td>
                        <td>
                            <select name="dcvMethod" class="sslm-select" style="width: auto;">
                                {foreach $dcvMethods as $code => $method}
                                <option value="{$code}" {if $dcv.method == $code}selected{/if}>{$method.name}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td>
                            <span class="sslm-badge sslm-badge--{$dcv.statusClass|default:'warning'}">{$dcv.statusText|default:'Pending'}</span>
                        </td>
                        <td>
                            {if !$dcv.isVerified}
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    onclick="SSLManager.openModal('dcvModal_{$dcv@index}')">
                                📋 {$_LANG.dcv_instructions|default:'View Instructions'}
                            </button>
                            {else}
                            <span class="sslm-badge sslm-badge--success">✓ {$_LANG.verified|default:'Verified'}</span>
                            {/if}
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>

            <div class="sslm-action-bar">
                <button type="button" class="sslm-btn sslm-btn--primary" data-action="updateDCV">
                    💾 {$_LANG.update_dcv|default:'Update DCV Methods'}
                </button>
            </div>
            {else}
            <p class="sslm-help-text">{$_LANG.please_wait|default:'Please wait while we retrieve validation status...'}</p>
            {/if}
        </div>
    </div>

    {* DCV Instructions - DNS CNAME *}
    {if $applyReturn.DCVdnsHost || $applyReturn.DCVdnsValue}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>🔗 {$_LANG.dns_validation|default:'DNS Validation Instructions'}</h3>
        </div>
        <div class="sslm-card__body">
            <p class="sslm-help-text">{$_LANG.dns_instructions|default:'Add the following CNAME record to your DNS settings:'}</p>
            
            {if $applyReturn.DCVdnsHost}
            <div class="sslm-info-box">
                <table class="sslm-table sslm-table--simple">
                    <tr>
                        <th style="width: 120px;">{$_LANG.dns_type|default:'Type'}</th>
                        <td><code>{$applyReturn.DCVdnsType|default:'CNAME'}</code></td>
                    </tr>
                    <tr>
                        <th>{$_LANG.dns_host|default:'Host/Name'}</th>
                        <td>
                            <code>{$applyReturn.DCVdnsHost|escape:'html'}</code>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="{$applyReturn.DCVdnsHost|escape:'html'}" 
                                    style="margin-left: 8px;">
                                📋 {$_LANG.copy|default:'Copy'}
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>{$_LANG.dns_value|default:'Value/Target'}</th>
                        <td>
                            <code>{$applyReturn.DCVdnsValue|escape:'html'}</code>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="{$applyReturn.DCVdnsValue|escape:'html'}" 
                                    style="margin-left: 8px;">
                                📋 {$_LANG.copy|default:'Copy'}
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}
        </div>
    </div>
    {/if}

    {* DCV Instructions - HTTP File *}
    {if $applyReturn.DCVfilePath || $applyReturn.DCVfileContent}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>📁 {$_LANG.http_validation|default:'HTTP File Validation Instructions'}</h3>
        </div>
        <div class="sslm-card__body">
            <p class="sslm-help-text">{$_LANG.http_instructions|default:'Create a file on your web server with the following details:'}</p>
            
            <div class="sslm-info-box">
                <table class="sslm-table sslm-table--simple">
                    {if $applyReturn.DCVfilePath}
                    <tr>
                        <th style="width: 120px;">{$_LANG.file_path|default:'File Path'}</th>
                        <td>
                            <code>{$applyReturn.DCVfilePath|escape:'html'}</code>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="{$applyReturn.DCVfilePath|escape:'html'}" 
                                    style="margin-left: 8px;">
                                📋 {$_LANG.copy|default:'Copy'}
                            </button>
                        </td>
                    </tr>
                    {/if}
                    {if $applyReturn.DCVfileContent}
                    <tr>
                        <th>{$_LANG.file_content|default:'File Content'}</th>
                        <td>
                            <textarea class="sslm-textarea" readonly rows="3" style="font-family: monospace; font-size: 12px;">{$applyReturn.DCVfileContent|escape:'html'}</textarea>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="{$applyReturn.DCVfileContent|escape:'html'}" 
                                    style="margin-top: 8px;">
                                📋 {$_LANG.copy|default:'Copy Content'}
                            </button>
                        </td>
                    </tr>
                    {/if}
                </table>
            </div>
        </div>
    </div>
    {/if}

    {* Quick Actions *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.actions|default:'Actions'}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-bar sslm-action-bar--centered" style="border: none; margin: 0; padding: 0;">
                <button type="button" class="sslm-btn sslm-btn--primary" data-action="refreshStatus">
                    🔄 {$_LANG.refresh_status|default:'Refresh Status'}
                </button>
                <button type="button" class="sslm-btn sslm-btn--danger" data-action="cancelOrder">
                    ❌ {$_LANG.cancel_order|default:'Cancel Order'}
                </button>
            </div>
        </div>
    </div>

    {if $lastRefresh}
    <p class="sslm-help-text" style="text-align: center; margin-top: 20px;">
        {$_LANG.last_refresh|default:'Last Refresh'}: {$lastRefresh|escape:'html'}
    </p>
    {/if}
</div>

{* DCV Modal Templates *}
{if $dcvStatus|@count > 0}
{foreach $dcvStatus as $dcv}
<div id="dcvModal_{$dcv@index}" class="sslm-modal-overlay">
    <div class="sslm-modal">
        <div class="sslm-modal__header">
            <h3 class="sslm-modal__title">{$_LANG.dcv_instructions_for|default:'Validation Instructions for'} {$dcv.domain|escape:'html'}</h3>
            <button class="sslm-modal__close" onclick="SSLManager.closeModal('dcvModal_{$dcv@index}')">&times;</button>
        </div>
        <div class="sslm-modal__body">
            <p><strong>{$_LANG.method|default:'Method'}:</strong> {$dcv.methodName|default:$dcv.method}</p>
            
            {if $dcv.method == 'EMAIL'}
            <p>{$_LANG.email_dcv_desc|default:'A verification email will be sent to the domain administrator email address.'}</p>
            {elseif $dcv.method == 'CNAME_CSR_HASH' || $dcv.method == 'DNS_CSR_HASH'}
            <p>{$_LANG.dns_dcv_desc|default:'Add a CNAME record to your DNS settings as shown above.'}</p>
            {else}
            <p>{$_LANG.http_dcv_desc|default:'Upload a verification file to your web server as shown above.'}</p>
            {/if}
        </div>
        <div class="sslm-modal__footer">
            <button type="button" class="sslm-btn sslm-btn--secondary" onclick="SSLManager.closeModal('dcvModal_{$dcv@index}')">
                {$_LANG.close|default:'Close'}
            </button>
        </div>
    </div>
</div>
{/foreach}
{/if}

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>