<link rel="stylesheet" href="{$WEB_ROOT}/{$MODULE_PATH}/assets/css/ssl-manager.css">

<div class="sslm-container">
    <div class="sslm-card sslm-card--error">
        <div class="sslm-card__header">
            <h3>❌ {$errorTitle|default:$_LANG.error}</h3>
        </div>
        <div class="sslm-card__body" style="text-align: center; padding: 40px;">
            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
            <h4 style="margin-bottom: 12px; color: var(--sslm-error);">{$errorTitle|default:$_LANG.error}</h4>
            <p style="color: var(--sslm-text-secondary); margin-bottom: 24px;">
                {$errorMessage|default:$_LANG.sys_error}
            </p>
            <a href="clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn--primary">
                {$_LANG.back}
            </a>
        </div>
    </div>
</div>