<link rel="stylesheet" href="{$WEB_ROOT}/{$MODULE_PATH}/assets/css/ssl-manager.css">

<div class="sslm-container">
    <script type="application/json" id="sslmConfig">{
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}&modop=custom",
        "lang": {$_LANG_JSON}
    }</script>

    <!-- Status Alert -->
    <div class="sslm-alert sslm-alert--success">
        <i>✅</i>
        <span>{$_LANG.complete_des}</span>
    </div>

    {if $isExpiringSoon}
    <div class="sslm-alert sslm-alert--warning">
        <i>⚠️</i>
        <span>{$_LANG.expiring_soon}: {$daysLeft} {$_LANG.days_remaining}</span>
    </div>
    {/if}

    <!-- Certificate Status Card -->
    <div class="sslm-card sslm-card--success">
        <div class="sslm-card__header">
            <h3>✅ {$_LANG.certificate_issued}</h3>
            <span class="sslm-badge sslm-badge--success">{$status}</span>
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
                    <label>{$_LANG.cert_begin}</label>
                    <span>{$beginDate}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.cert_end}</label>
                    <span>{$endDate}</span>
                </div>
                {if $daysLeft !== null}
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.days_remaining}</label>
                    <span class="{if $daysLeft <= 30}sslm-text-warning{/if}">
                        {$daysLeft} days
                    </span>
                </div>
                {/if}
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.actions}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-grid">
                {if $canDownload}
                <div class="sslm-action-item" onclick="SSLManager.openModal('downloadModal')">
                    <i>📥</i>
                    <span>{$_LANG.download_certificate}</span>
                </div>
                {/if}
                {if $canReissue}
                <div class="sslm-action-item" onclick="location.href='clientarea.php?action=productdetails&id={$serviceid}&modop=custom&a=reissue'">
                    <i>🔄</i>
                    <span>{$_LANG.reissue_certificate}</span>
                </div>
                {/if}
                {if $canRenew}
                <div class="sslm-action-item" onclick="SSLManager.confirmRenew()">
                    <i>📅</i>
                    <span>{$_LANG.renew_certificate}</span>
                </div>
                {/if}
                <div class="sslm-action-item" data-action="refreshStatus">
                    <i>🔃</i>
                    <span>{$_LANG.refresh_status}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Domain Validation Status -->
    {if $dcvStatus|@count > 0}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.domain_validation}</h3>
        </div>
        <div class="sslm-card__body">
            <table class="sslm-table">
                <thead>
                    <tr>
                        <th>{$_LANG.domain}</th>
                        <th>{$_LANG.dcv_method}</th>
                        <th>{$_LANG.status}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $dcvStatus as $dcv}
                    <tr>
                        <td><strong>{$dcv.domain}</strong></td>
                        <td>{$dcv.methodName}</td>
                        <td><span class="sslm-badge sslm-badge--{$dcv.statusClass}">{$dcv.statusText}</span></td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {/if}

    <!-- Danger Zone -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3 style="color: var(--sslm-error);">⚠️ Danger Zone</h3>
        </div>
        <div class="sslm-card__body">
            <p class="sslm-help-text" style="margin-bottom: 16px;">
                Revoking a certificate is permanent and cannot be undone.
            </p>
            <button type="button" class="sslm-btn sslm-btn--danger" data-action="revokeCert">
                {$_LANG.revoke_certificate}
            </button>
        </div>
    </div>

    {if $lastRefresh}
    <p class="sslm-help-text" style="text-align: center; margin-top: 20px;">
        {$_LANG.last_refresh}: {$lastRefresh}
    </p>
    {/if}
</div>

<!-- Download Modal -->
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

<script src="{$WEB_ROOT}/{$MODULE_PATH}/assets/js/ssl-manager.js"></script>