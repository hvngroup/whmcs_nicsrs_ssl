{**
 * NicSRS SSL Module - Complete Certificate Template
 * Shows issued certificate details and actions
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
    <div class="sslm-alert sslm-alert--success">
        <i>✓</i>
        <span>{$_LANG.cert_issued_success|default:'Your SSL certificate has been issued successfully!'}</span>
    </div>

    {* Expiry Warning *}
    {if $isExpiringSoon}
    <div class="sslm-alert sslm-alert--warning">
        <i>⚠️</i>
        <span>{$_LANG.cert_expiring_soon|default:'Your certificate is expiring soon!'} {$_LANG.days_left|default:'Days left'}: <strong>{$daysLeft}</strong></span>
    </div>
    {/if}

    {* Certificate Info Card *}
    <div class="sslm-card sslm-card--success">
        <div class="sslm-card__header">
            <h3>{$_LANG.certificate_details|default:'Certificate Details'}</h3>
            <span class="sslm-badge sslm-badge--success">{$status|escape:'html'}</span>
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
                    <label>{$_LANG.validation_type|default:'Validation'}</label>
                    <span class="sslm-badge sslm-badge--info">{$sslValidationType|upper}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.status|default:'Status'}</label>
                    <span class="sslm-badge sslm-badge--success">{$status|escape:'html'}</span>
                </div>
            </div>
        </div>
    </div>

    {* Validity Period *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>📅 {$_LANG.validity_period|default:'Validity Period'}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-cert-info">
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.cert_begin|default:'Valid From'}</label>
                    <span>{$beginDate|default:'N/A'}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.cert_end|default:'Valid Until'}</label>
                    <span>
                        {$endDate|default:'N/A'}
                        {if $daysLeft !== null}
                        <span class="sslm-badge sslm-badge--{if $daysLeft <= 30}warning{elseif $daysLeft <= 90}info{else}success{/if}" style="margin-left: 8px;">
                            {$daysLeft} {$_LANG.days_remaining|default:'days remaining'}
                        </span>
                        {/if}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {* Domain Information *}
    {if $dcvStatus|@count > 0}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>🌐 {$_LANG.secured_domains|default:'Secured Domains'}</h3>
            <span class="sslm-badge sslm-badge--info">{$dcvStatus|@count} {$_LANG.domains|default:'domain(s)'}</span>
        </div>
        <div class="sslm-card__body">
            <table class="sslm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{$_LANG.domain|default:'Domain'}</th>
                        <th>{$_LANG.status|default:'Status'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $dcvStatus as $index => $dcv}
                    <tr>
                        <td>{$index + 1}</td>
                        <td><strong>{$dcv.domain|escape:'html'}</strong></td>
                        <td><span class="sslm-badge sslm-badge--success">✓ {$_LANG.secured|default:'Secured'}</span></td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {/if}

    {* Certificate Actions *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>⚡ {$_LANG.actions|default:'Actions'}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-grid">
                {* Download Certificate *}
                {if $canDownload}
                <div class="sslm-action-item" onclick="SSLManager.openModal('downloadModal')">
                    <i>📥</i>
                    <span>{$_LANG.download_certificate|default:'Download Certificate'}</span>
                </div>
                {/if}
                
                {* Refresh Status *}
                <div class="sslm-action-item" data-action="refreshStatus">
                    <i>🔄</i>
                    <span>{$_LANG.refresh_status|default:'Refresh Status'}</span>
                </div>
                
                {* Reissue Certificate *}
                {if $canReissue}
                <div class="sslm-action-item" onclick="location.href='clientarea.php?action=productdetails&id={$serviceid}&modop=custom&a=reissue'">
                    <i>🔁</i>
                    <span>{$_LANG.reissue_certificate|default:'Reissue Certificate'}</span>
                </div>
                {/if}
                
                {* Renew Certificate *}
                {if $canRenew && $isExpiringSoon}
                <div class="sslm-action-item" onclick="SSLManager.confirmRenew()">
                    <i>📅</i>
                    <span>{$_LANG.renew_certificate|default:'Renew Certificate'}</span>
                </div>
                {/if}
            </div>
        </div>
    </div>

    {* Revoke Certificate (Danger Zone) *}
    <div class="sslm-card sslm-card--danger">
        <div class="sslm-card__header">
            <h3>⚠️ {$_LANG.danger_zone|default:'Danger Zone'}</h3>
        </div>
        <div class="sslm-card__body">
            <p class="sslm-help-text" style="color: var(--sslm-danger);">
                {$_LANG.revoke_warning|default:'Revoking a certificate is irreversible. The certificate will no longer be trusted by browsers.'}
            </p>
            <button type="button" class="sslm-btn sslm-btn--danger" data-action="revokeCert">
                🚫 {$_LANG.revoke_certificate|default:'Revoke Certificate'}
            </button>
        </div>
    </div>

    {if $lastRefresh}
    <p class="sslm-help-text" style="text-align: center; margin-top: 20px;">
        {$_LANG.last_refresh|default:'Last Refresh'}: {$lastRefresh|escape:'html'}
    </p>
    {/if}
</div>

{* Download Modal *}
<div id="downloadModal" class="sslm-modal-overlay">
    <div class="sslm-modal">
        <div class="sslm-modal__header">
            <h3 class="sslm-modal__title">{$_LANG.select_download_format|default:'Select Download Format'}</h3>
            <button class="sslm-modal__close" onclick="SSLManager.closeModal('downloadModal')">&times;</button>
        </div>
        <div class="sslm-modal__body">
            <div class="sslm-action-grid">
                {if $downloadFormats}
                {foreach $downloadFormats as $code => $format}
                <div class="sslm-action-item" onclick="SSLManager.downloadCertificate('{$code}'); SSLManager.closeModal('downloadModal');">
                    <i>📄</i>
                    <span>{$format.name}</span>
                    <small style="color: var(--sslm-text-secondary); font-size: 11px;">{$format.description}</small>
                </div>
                {/foreach}
                {else}
                {* Default formats if not provided *}
                <div class="sslm-action-item" onclick="SSLManager.downloadCertificate('apache'); SSLManager.closeModal('downloadModal');">
                    <i>📄</i>
                    <span>Apache / cPanel</span>
                    <small style="color: var(--sslm-text-secondary); font-size: 11px;">.crt + .ca-bundle + .key</small>
                </div>
                <div class="sslm-action-item" onclick="SSLManager.downloadCertificate('nginx'); SSLManager.closeModal('downloadModal');">
                    <i>📄</i>
                    <span>Nginx</span>
                    <small style="color: var(--sslm-text-secondary); font-size: 11px;">.pem combined</small>
                </div>
                <div class="sslm-action-item" onclick="SSLManager.downloadCertificate('iis'); SSLManager.closeModal('downloadModal');">
                    <i>📄</i>
                    <span>IIS / Windows</span>
                    <small style="color: var(--sslm-text-secondary); font-size: 11px;">.pfx (PKCS#12)</small>
                </div>
                <div class="sslm-action-item" onclick="SSLManager.downloadCertificate('tomcat'); SSLManager.closeModal('downloadModal');">
                    <i>📄</i>
                    <span>Tomcat / Java</span>
                    <small style="color: var(--sslm-text-secondary); font-size: 11px;">.jks (Java Keystore)</small>
                </div>
                {/if}
            </div>
        </div>
        <div class="sslm-modal__footer">
            <button type="button" class="sslm-btn sslm-btn--secondary" onclick="SSLManager.closeModal('downloadModal')">
                {$_LANG.close|default:'Close'}
            </button>
        </div>
    </div>
</div>

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>