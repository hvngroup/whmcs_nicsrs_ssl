{**
 * NicSRS SSL Module - Cancelled/Revoked Template
 * Shows certificate status for cancelled, revoked, or expired certificates
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
            {if $status eq 'cancelled' || $status eq 'canceled'}
                <span class="sslm-badge sslm-badge-error">{$_LANG.cancelled|default:'Cancelled'}</span>
            {elseif $status eq 'revoked'}
                <span class="sslm-badge sslm-badge-error">{$_LANG.revoked|default:'Revoked'}</span>
            {elseif $status eq 'expired'}
                <span class="sslm-badge sslm-badge-warning">{$_LANG.expired|default:'Expired'}</span>
            {else}
                <span class="sslm-badge sslm-badge-default">{$status|escape:'html'}</span>
            {/if}
        </div>
    </div>

    {* Status Card *}
    <div class="sslm-status-card">
        <div class="sslm-status-icon error">
            {if $status eq 'expired'}
                <i class="fas fa-calendar-times"></i>
            {elseif $status eq 'revoked'}
                <i class="fas fa-ban"></i>
            {else}
                <i class="fas fa-times-circle"></i>
            {/if}
        </div>
        <div class="sslm-status-content">
            <div class="sslm-status-title">
                {if $status eq 'cancelled' || $status eq 'canceled'}
                    {$_LANG.order_cancelled|default:'Order Cancelled'}
                {elseif $status eq 'revoked'}
                    {$_LANG.certificate_revoked|default:'Certificate Revoked'}
                {elseif $status eq 'expired'}
                    {$_LANG.certificate_expired|default:'Certificate Expired'}
                {else}
                    {$_LANG.certificate_inactive|default:'Certificate Inactive'}
                {/if}
            </div>
            <div class="sslm-status-desc">
                {if $status eq 'cancelled' || $status eq 'canceled'}
                    {$_LANG.cancelled_des|default:'This certificate order has been cancelled. If you need a new certificate, please place a new order.'}
                {elseif $status eq 'revoked'}
                    {$_LANG.revoked_des|default:'This certificate has been revoked and is no longer valid. You may need to order a new certificate.'}
                {elseif $status eq 'expired'}
                    {$_LANG.expired_des|default:'This certificate has expired. Please renew your certificate to continue using SSL protection.'}
                {else}
                    {$_LANG.inactive_des|default:'This certificate is no longer active.'}
                {/if}
            </div>
        </div>
    </div>

    {* Certificate Info *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-info-circle"></i> {$_LANG.certificate_info|default:'Certificate Information'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-info-card">
                {if $certId}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.certificate_id|default:'Certificate ID'}:</span>
                    <span class="sslm-info-value sslm-code">{$certId|escape:'html'}</span>
                </div>
                {/if}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.status|default:'Status'}:</span>
                    <span class="sslm-info-value">
                        {if $status eq 'cancelled' || $status eq 'canceled'}
                            <span class="sslm-badge sslm-badge-error">{$_LANG.cancelled|default:'Cancelled'}</span>
                        {elseif $status eq 'revoked'}
                            <span class="sslm-badge sslm-badge-error">{$_LANG.revoked|default:'Revoked'}</span>
                        {elseif $status eq 'expired'}
                            <span class="sslm-badge sslm-badge-warning">{$_LANG.expired|default:'Expired'}</span>
                        {else}
                            <span class="sslm-badge sslm-badge-default">{$status|escape:'html'}</span>
                        {/if}
                    </span>
                </div>
                {if $configData.applyReturn.beginDate}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.cert_begin|default:'Valid From'}:</span>
                    <span class="sslm-info-value">{$configData.applyReturn.beginDate|escape:'html'}</span>
                </div>
                {/if}
                {if $configData.applyReturn.endDate}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{$_LANG.cert_end|default:'Valid Until'}:</span>
                    <span class="sslm-info-value">{$configData.applyReturn.endDate|escape:'html'}</span>
                </div>
                {/if}
            </div>

            {* Domain List *}
            {if $configData.domainInfo}
            <div class="sslm-info-card">
                <div class="sslm-info-card-title">
                    <i class="fas fa-globe"></i> {$_LANG.domain_info|default:'Domains'}
                </div>
                {foreach $configData.domainInfo as $domain}
                <div class="sslm-info-row">
                    <span class="sslm-info-label">{if $domain@first}{$_LANG.primary_domain|default:'Primary'}{else}{$_LANG.additional_domains|default:'SAN'}{/if}:</span>
                    <span class="sslm-info-value">{$domain.domainName|escape:'html'}</span>
                </div>
                {/foreach}
            </div>
            {/if}
        </div>
    </div>

    {* Help Section *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-question-circle"></i> {$_LANG.help|default:'Need Help?'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-alert sslm-alert-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    {if $status eq 'expired'}
                        <p><strong>{$_LANG.renew_suggestion|default:'Your certificate has expired.'}</strong></p>
                        <p>{$_LANG.renew_info|default:'To continue protecting your website with SSL, please renew your certificate or purchase a new one.'}</p>
                    {else}
                        <p>{$_LANG.contact_support|default:'If you have questions or need assistance, please contact our support team.'}</p>
                    {/if}
                </div>
            </div>
            
            {if $status eq 'expired'}
            <div style="margin-top: 16px;">
                <a href="{$WEB_ROOT}/cart.php?a=add&domain=register" class="sslm-btn sslm-btn-primary">
                    <i class="fas fa-shopping-cart"></i> {$_LANG.order_new|default:'Order New Certificate'}
                </a>
            </div>
            {/if}
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