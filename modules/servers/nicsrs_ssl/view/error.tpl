{**
 * NicSRS SSL Module - Error Template
 * Displays error messages
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
            {$_LANG.ssl_management|default:'SSL Management'}
        </h2>
    </div>

    {* Error Card *}
    <div class="sslm-status-card">
        <div class="sslm-status-icon error">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="sslm-status-content">
            <div class="sslm-status-title">{$_LANG.error|default:'Error'}</div>
            <div class="sslm-status-desc">{$errorMessage|escape:'html'|default:'An unexpected error occurred.'}</div>
        </div>
    </div>

    {* Help Section *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-question-circle"></i> {$_LANG.help|default:'What can you do?'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-alert sslm-alert-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <p>{$_LANG.error_help_1|default:'Please try the following:'}</p>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <li>{$_LANG.error_help_2|default:'Refresh the page and try again'}</li>
                        <li>{$_LANG.error_help_3|default:'Check your internet connection'}</li>
                        <li>{$_LANG.error_help_4|default:'Contact support if the problem persists'}</li>
                    </ul>
                </div>
            </div>
            
            <div style="margin-top: 20px; display: flex; gap: 12px;">
                <button type="button" onclick="window.location.reload()" class="sslm-btn sslm-btn-primary">
                    <i class="fas fa-redo"></i> {$_LANG.refresh|default:'Refresh Page'}
                </button>
                <a href="{$WEB_ROOT}/submitticket.php" class="sslm-btn sslm-btn-secondary">
                    <i class="fas fa-life-ring"></i> {$_LANG.contact_support|default:'Contact Support'}
                </a>
            </div>
        </div>
    </div>
</div>

{* JavaScript Configuration *}
<script>
window.sslmConfig = {
    ajaxUrl: "{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}",
    serviceid: "{$serviceid}",
    lang: {$_LANG_JSON nofilter}
};
</script>