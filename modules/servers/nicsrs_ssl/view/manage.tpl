{**
 * NicSRS SSL Module - Manage Certificate Template
 * General certificate management page
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

    {* Page Header *}
    <div class="sslm-page-header">
        <h2>{$_LANG.manage_certificate|default:'Manage Certificate'}</h2>
        <p class="sslm-help-text">{$_LANG.manage_desc|default:'View and manage your SSL certificate.'}</p>
    </div>

    {* Certificate Info *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.certificate_info|default:'Certificate Information'}</h3>
            <span class="sslm-badge sslm-badge--{if $order.status == 'Complete' || $order.status == 'Issued'}success{elseif $order.status == 'Pending'}warning{else}default{/if}">
                {$order.status|escape:'html'}
            </span>
        </div>
        <div class="sslm-card__body">
            <table class="sslm-table sslm-table--simple">
                <tbody>
                    <tr>
                        <th style="width: 180px;">{$_LANG.certificate_id|default:'Certificate ID'}</th>
                        <td>{$order.remoteid|default:'N/A'}</td>
                    </tr>
                    <tr>
                        <th>{$_LANG.certificate_type|default:'Certificate Type'}</th>
                        <td>{$productCode|escape:'html'}</td>
                    </tr>
                    <tr>
                        <th>{$_LANG.primary_domain|default:'Primary Domain'}</th>
                        <td><strong>{$order.primaryDomain|default:'N/A'}</strong></td>
                    </tr>
                    <tr>
                        <th>{$_LANG.status|default:'Status'}</th>
                        <td>
                            <span class="sslm-badge sslm-badge--{if $order.status == 'Complete' || $order.status == 'Issued'}success{elseif $order.status == 'Pending'}warning{elseif $order.status == 'Cancelled' || $order.status == 'Revoked'}danger{else}default{/if}">
                                {$order.status|escape:'html'}
                            </span>
                        </td>
                    </tr>
                    {if $order.issueDate && $order.issueDate != 'N/A'}
                    <tr>
                        <th>{$_LANG.cert_begin|default:'Issue Date'}</th>
                        <td>{$order.issueDate|escape:'html'}</td>
                    </tr>
                    {/if}
                    {if $order.expiryDate && $order.expiryDate != 'N/A'}
                    <tr>
                        <th>{$_LANG.cert_end|default:'Expiry Date'}</th>
                        <td>
                            {$order.expiryDate|escape:'html'}
                            {if $order.daysLeft !== null}
                            <span class="sslm-badge sslm-badge--{if $order.daysLeft <= 30}warning{else}info{/if}" style="margin-left: 8px;">
                                {$order.daysLeft} {$_LANG.days_remaining|default:'days remaining'}
                            </span>
                            {/if}
                        </td>
                    </tr>
                    {/if}
                    {if $order.vendorId}
                    <tr>
                        <th>{$_LANG.vendor_id|default:'Vendor ID'}</th>
                        <td>{$order.vendorId|escape:'html'}</td>
                    </tr>
                    {/if}
                    {if $order.lastRefresh}
                    <tr>
                        <th>{$_LANG.last_refresh|default:'Last Refresh'}</th>
                        <td>{$order.lastRefresh|escape:'html'}</td>
                    </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>

    {* Available Actions *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.actions|default:'Actions'}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-grid">
                {if $actions.refresh}
                <div class="sslm-action-item" data-action="refreshStatus">
                    <i>🔃</i>
                    <span>{$_LANG.refresh_status|default:'Refresh Status'}</span>
                </div>
                {/if}
                
                {if $actions.download}
                <div class="sslm-action-item" onclick="SSLManager.openModal('downloadModal')">
                    <i>📥</i>
                    <span>{$_LANG.download_certificate|default:'Download Certificate'}</span>
                </div>
                {/if}
                
                {if $actions.reissue}
                <div class="sslm-action-item" onclick="location.href='clientarea.php?action=productdetails&id={$serviceid}&modop=custom&a=reissue'">
                    <i>🔄</i>
                    <span>{$_LANG.reissue_certificate|default:'Reissue Certificate'}</span>
                </div>
                {/if}
                
                {if $actions.renew}
                <div class="sslm-action-item" onclick="SSLManager.confirmRenew()">
                    <i>📅</i>
                    <span>{$_LANG.renew_certificate|default:'Renew Certificate'}</span>
                </div>
                {/if}
                
                {if $actions.updateDCV}
                <div class="sslm-action-item" onclick="location.href='clientarea.php?action=productdetails&id={$serviceid}'">
                    <i>✅</i>
                    <span>{$_LANG.update_dcv|default:'Update DCV'}</span>
                </div>
                {/if}
                
                {if $actions.cancel}
                <div class="sslm-action-item sslm-action-item--danger" data-action="cancelOrder">
                    <i>❌</i>
                    <span>{$_LANG.cancel_order|default:'Cancel Order'}</span>
                </div>
                {/if}
                
                {if $actions.revoke}
                <div class="sslm-action-item sslm-action-item--danger" data-action="revokeCert">
                    <i>🚫</i>
                    <span>{$_LANG.revoke_certificate|default:'Revoke Certificate'}</span>
                </div>
                {/if}
            </div>
        </div>
    </div>

    {* All Domains (if multi-domain) *}
    {if $order.allDomains|@count > 1}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.domain_info|default:'All Domains'}</h3>
            <span class="sslm-badge sslm-badge--info">{$order.allDomains|@count} {$_LANG.domains|default:'domains'}</span>
        </div>
        <div class="sslm-card__body">
            <table class="sslm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{$_LANG.domain|default:'Domain'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $order.allDomains as $index => $domain}
                    <tr>
                        <td>{$index + 1}</td>
                        <td>{$domain|escape:'html'}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {/if}

    {* Quick Links *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.more_info|default:'Quick Links'}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-bar" style="border: none; margin: 0; padding: 0; justify-content: flex-start;">
                <a href="clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn--secondary">
                    ← {$_LANG.back|default:'Back to Overview'}
                </a>
                <a href="supporttickets.php" class="sslm-btn sslm-btn--secondary">
                    📧 {$_LANG.help|default:'Get Support'}
                </a>
            </div>
        </div>
    </div>
</div>

{* Download Modal *}
{if $actions.download}
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
{/if}

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>