<link rel="stylesheet" href="{$WEB_ROOT}/{$MODULE_PATH}/assets/css/ssl-manager.css">

<div class="sslm-container">
    <script type="application/json" id="sslmConfig">{
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}&modop=custom",
        "lang": {$_LANG_JSON}
    }</script>

    <!-- Certificate Overview -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.certificate_management}</h3>
            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" data-action="refreshStatus">
                🔄 {$_LANG.refresh}
            </button>
        </div>
        <div class="sslm-card__body">
            <table class="sslm-table">
                <tbody>
                    <tr>
                        <th style="width: 200px;">{$_LANG.certificate_id}</th>
                        <td>{$order.remoteid|default:'N/A'}</td>
                    </tr>
                    <tr>
                        <th>{$_LANG.domain}</th>
                        <td>
                            <strong>{$order.domain|default:'N/A'}</strong>
                            {if $order.allDomains|@count > 1}
                            <br><small class="sslm-help-text">
                                +{$order.allDomains|@count - 1} more domains
                            </small>
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <th>{$_LANG.certificate_type}</th>
                        <td>{$productCode}</td>
                    </tr>
                    <tr>
                        <th>{$_LANG.status}</th>
                        <td>
                            <span class="sslm-badge sslm-badge--{$statusClass}">{$status}</span>
                        </td>
                    </tr>
                    {if $order.issuedDate && $order.issuedDate != 'N/A'}
                    <tr>
                        <th>{$_LANG.cert_begin}</th>
                        <td>{$order.issuedDate}</td>
                    </tr>
                    {/if}
                    {if $order.expiryDate && $order.expiryDate != 'N/A'}
                    <tr>
                        <th>{$_LANG.cert_end}</th>
                        <td>
                            {$order.expiryDate}
                            {if $order.daysLeft !== null}
                            <span class="sslm-badge sslm-badge--{if $order.daysLeft <= 30}warning{else}info{/if}" style="margin-left: 8px;">
                                {$order.daysLeft} {$_LANG.days_remaining}
                            </span>
                            {/if}
                        </td>
                    </tr>
                    {/if}
                    {if $order.vendorId}
                    <tr>
                        <th>{$_LANG.vendor_id}</th>
                        <td>{$order.vendorId}</td>
                    </tr>
                    {/if}
                    {if $order.lastRefresh}
                    <tr>
                        <th>{$_LANG.last_refresh}</th>
                        <td>{$order.lastRefresh}</td>
                    </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>

    <!-- Available Actions -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.actions}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-grid">
                {if $actions.refresh}
                <div class="sslm-action-item" data-action="refreshStatus">
                    <i>🔃</i>
                    <span>{$_LANG.refresh_status}</span>
                </div>
                {/if}
                
                {if $actions.download}
                <div class="sslm-action-item" onclick="SSLManager.openModal('downloadModal')">
                    <i>📥</i>
                    <span>{$_LANG.download_certificate}</span>
                </div>
                {/if}
                
                {if $actions.reissue}
                <div class="sslm-action-item" onclick="location.href='clientarea.php?action=productdetails&id={$serviceid}&modop=custom&a=reissue'">
                    <i>🔄</i>
                    <span>{$_LANG.reissue_certificate}</span>
                </div>
                {/if}
                
                {if $actions.renew}
                <div class="sslm-action-item" onclick="SSLManager.confirmRenew()">
                    <i>📅</i>
                    <span>{$_LANG.renew_certificate}</span>
                </div>
                {/if}
                
                {if $actions.updateDCV}
                <div class="sslm-action-item" onclick="location.href='clientarea.php?action=productdetails&id={$serviceid}'">
                    <i>✅</i>
                    <span>{$_LANG.update_dcv}</span>
                </div>
                {/if}
                
                {if $actions.cancel}
                <div class="sslm-action-item sslm-action-item--danger" data-action="cancelOrder">
                    <i>❌</i>
                    <span>{$_LANG.cancel_order}</span>
                </div>
                {/if}
                
                {if $actions.revoke}
                <div class="sslm-action-item sslm-action-item--danger" data-action="revokeCert">
                    <i>🚫</i>
                    <span>{$_LANG.revoke_certificate}</span>
                </div>
                {/if}
            </div>
        </div>
    </div>

    <!-- All Domains (if multi-domain) -->
    {if $order.allDomains|@count > 1}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.domain_info}</h3>
            <span class="sslm-badge sslm-badge--info">{$order.allDomains|@count} domains</span>
        </div>
        <div class="sslm-card__body">
            <table class="sslm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{$_LANG.domain}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $order.allDomains as $index => $domain}
                    <tr>
                        <td>{$index + 1}</td>
                        <td>{$domain}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {/if}

    <!-- Quick Links -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.more_info}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-bar" style="border: none; margin: 0; padding: 0; justify-content: flex-start;">
                <a href="clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn--secondary">
                    ← {$_LANG.back}
                </a>
                <a href="supporttickets.php" class="sslm-btn sslm-btn--secondary">
                    📧 {$_LANG.help}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Download Modal -->
{if $actions.download}
<div id="downloadModal" class="sslm-modal-overlay">
    <div class="sslm-modal">
        <div class="sslm-modal__header">
            <h3 class="sslm-modal__title">{$_LANG.select_download_format}</h3>
            <button class="sslm-modal__close" onclick="SSLManager.closeModal('downloadModal')">&times;</button>
        </div>
        <div class="sslm-modal__body">
            <div class="sslm-action-grid">
                {foreach $downloadFormats as $code => $format}
                <div class="sslm-action-item" onclick="SSLManager.downloadCertificate('{$code}'); SSLManager.closeModal('downloadModal');">
                    <i>📄</i>
                    <span>{$format.name}</span>
                    <small style="color: var(--sslm-text-secondary); font-size: 11px;">{$format.description}</small>
                </div>
                {/foreach}
            </div>
        </div>
        <div class="sslm-modal__footer">
            <button type="button" class="sslm-btn sslm-btn--secondary" onclick="SSLManager.closeModal('downloadModal')">
                {$_LANG.close}
            </button>
        </div>
    </div>
</div>
{/if}

<script src="{$WEB_ROOT}/{$MODULE_PATH}/assets/js/ssl-manager.js"></script>