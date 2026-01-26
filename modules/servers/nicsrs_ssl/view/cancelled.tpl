<link rel="stylesheet" href="{$WEB_ROOT}/{$MODULE_PATH}/assets/css/ssl-manager.css">

<div class="sslm-container">
    <script type="application/json" id="sslmConfig">{
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}&modop=custom",
        "lang": {$_LANG_JSON}
    }</script>

    <!-- Status Alert -->
    <div class="sslm-alert sslm-alert--error">
        <i>❌</i>
        <span>{$_LANG.cancelled_des}</span>
    </div>

    <!-- Certificate Status Card -->
    <div class="sslm-card sslm-card--error">
        <div class="sslm-card__header">
            <h3>
                {if $status == 'Revoked'}
                🚫 {$_LANG.revoked}
                {elseif $status == 'Expired'}
                ⏰ {$_LANG.expired}
                {else}
                ❌ {$_LANG.cancelled}
                {/if}
            </h3>
            <span class="sslm-badge sslm-badge--danger">{$status}</span>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-cert-info">
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_id}</label>
                    <span>{$order->remoteid|default:'N/A'}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_type}</label>
                    <span>{$productCode}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.status}</label>
                    <span class="sslm-badge sslm-badge--danger">{$status}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    {if $canRenew}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.actions}</h3>
        </div>
        <div class="sslm-card__body" style="text-align: center;">
            <p class="sslm-help-text" style="margin-bottom: 16px;">
                You can order a new certificate or renew your service.
            </p>
            <button type="button" class="sslm-btn sslm-btn--primary sslm-btn--lg">
                📅 {$_LANG.renew_certificate}
            </button>
        </div>
    </div>
    {/if}
</div>

<script src="{$WEB_ROOT}/{$MODULE_PATH}/assets/js/ssl-manager.js"></script>