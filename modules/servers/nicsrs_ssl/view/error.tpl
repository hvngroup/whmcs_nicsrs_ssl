{**
 * NicSRS SSL Module - Error Template
 * Displays error messages
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

<div class="sslm-container">
    
    {* Error Banner *}
    <div class="sslm-alert sslm-alert--danger">
        <i>⚠️</i>
        <div>
            <strong>{$errorTitle|default:$_LANG.error_occurred|default:'An Error Occurred'}</strong>
            {if $errorMessage}
            <p style="margin: 5px 0 0 0;">{$errorMessage|escape:'html'}</p>
            {/if}
        </div>
    </div>

    {* Error Details Card *}
    <div class="sslm-card sslm-card--danger">
        <div class="sslm-card__header">
            <h3>❌ {$_LANG.error_details|default:'Error Details'}</h3>
        </div>
        <div class="sslm-card__body">
            {if $errorMessage}
            <div class="sslm-info-box" style="background: #fef2f2; border-color: #fecaca;">
                <code style="word-break: break-all; color: #dc2626;">{$errorMessage|escape:'html'}</code>
            </div>
            {/if}
            
            {if $usefulErrorHelper}
            <div class="sslm-info-box" style="margin-top: 16px;">
                <strong>{$_LANG.additional_info|default:'Additional Information'}:</strong>
                <p style="margin: 8px 0 0 0;">{$usefulErrorHelper|escape:'html'}</p>
            </div>
            {/if}
        </div>
    </div>

    {* Troubleshooting Tips *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>💡 {$_LANG.troubleshooting|default:'Troubleshooting'}</h3>
        </div>
        <div class="sslm-card__body">
            <p class="sslm-help-text">{$_LANG.troubleshooting_intro|default:'Here are some things you can try:'}</p>
            
            <ul style="margin: 16px 0; padding-left: 20px; color: var(--sslm-text-secondary);">
                <li style="margin-bottom: 8px;">{$_LANG.try_refresh|default:'Refresh the page and try again'}</li>
                <li style="margin-bottom: 8px;">{$_LANG.try_later|default:'Wait a few minutes and try again'}</li>
                <li style="margin-bottom: 8px;">{$_LANG.check_input|default:'Check your input data for any errors'}</li>
                <li style="margin-bottom: 8px;">{$_LANG.contact_if_persist|default:'Contact support if the problem persists'}</li>
            </ul>
        </div>
    </div>

    {* Actions *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>🔧 {$_LANG.actions|default:'Actions'}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-bar" style="border: none; margin: 0; padding: 0; justify-content: flex-start;">
                <a href="clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn--primary">
                    🔄 {$_LANG.try_again|default:'Try Again'}
                </a>
                <a href="clientarea.php?action=services" class="sslm-btn sslm-btn--secondary">
                    ← {$_LANG.back_to_services|default:'Back to Services'}
                </a>
                <a href="submitticket.php" class="sslm-btn sslm-btn--secondary">
                    📧 {$_LANG.contact_support|default:'Contact Support'}
                </a>
            </div>
        </div>
    </div>
</div>