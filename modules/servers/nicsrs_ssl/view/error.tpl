{**
 * NicSRS SSL Module - Error Template
 * Displays error messages with troubleshooting suggestions
 * 
 * @package    nicsrs_ssl
 * @version    2.1.0
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
    </div>

    {* Error Status Card *}
    <div class="sslm-status-card">
        <div class="sslm-status-icon error">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="sslm-status-content">
            <div class="sslm-status-title">{$errorTitle|default:'An Error Occurred'}</div>
            <div class="sslm-status-desc">{$errorMessage|escape:'html'|default:'Something went wrong. Please try again.'}</div>
        </div>
    </div>

    {* Error Details (if available) *}
    {if $errorCode || $errorDetails}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-info-circle"></i> {$_LANG.error_details|default:'Error Details'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-code-group">
                {if $errorCode}
                <div class="sslm-code-row">
                    <div class="sslm-code-label">{$_LANG.error_code|default:'Error Code'}</div>
                    <div class="sslm-code-value">
                        <code>{$errorCode|escape:'html'}</code>
                        <button type="button" class="sslm-code-copy" onclick="SSLManager.copyToClipboard('{$errorCode|escape:'javascript'}')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                {/if}
                {if $errorDetails}
                <div class="sslm-code-row">
                    <div class="sslm-code-label">{$_LANG.details|default:'Details'}</div>
                    <div class="sslm-code-value">
                        <code>{$errorDetails|escape:'html'}</code>
                    </div>
                </div>
                {/if}
                {if $errorTimestamp}
                <div class="sslm-code-row">
                    <div class="sslm-code-label">{$_LANG.timestamp|default:'Timestamp'}</div>
                    <div class="sslm-code-value">
                        <code>{$errorTimestamp|escape:'html'}</code>
                    </div>
                </div>
                {/if}
            </div>
        </div>
    </div>
    {/if}

    {* Troubleshooting Suggestions *}
    <div class="sslm-section">
        <div class="sslm-section-header">
            <h3><i class="fas fa-lightbulb"></i> {$_LANG.troubleshooting|default:'Troubleshooting'}</h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-help-grid">
                <div class="sslm-help-item">
                    <h4><i class="fas fa-redo"></i> {$_LANG.try_again|default:'Try Again'}</h4>
                    <p>{$_LANG.try_again_desc|default:'Refresh the page or try the operation again. Temporary issues often resolve themselves.'}</p>
                </div>
                <div class="sslm-help-item">
                    <h4><i class="fas fa-clock"></i> {$_LANG.wait_moment|default:'Wait a Moment'}</h4>
                    <p>{$_LANG.wait_desc|default:'The server may be temporarily busy. Please wait a few minutes before trying again.'}</p>
                </div>
                <div class="sslm-help-item">
                    <h4><i class="fas fa-life-ring"></i> {$_LANG.contact_support|default:'Contact Support'}</h4>
                    <p>{$_LANG.contact_desc|default:'If the problem persists, please contact our support team with the error details above.'}</p>
                </div>
            </div>
        </div>
    </div>

    {* Common Issues *}
    <div class="sslm-section sslm-collapsible">
        <div class="sslm-section-header" onclick="this.parentElement.classList.toggle('collapsed')">
            <h3>
                <span><i class="fas fa-question-circle"></i> {$_LANG.common_issues|default:'Common Issues'}</span>
                <i class="fas fa-chevron-down sslm-collapse-icon"></i>
            </h3>
        </div>
        <div class="sslm-section-body">
            <div class="sslm-info-card">
                <div class="sslm-info-row">
                    <span class="sslm-info-label"><strong>{$_LANG.issue_api|default:'API Connection Failed'}</strong></span>
                    <span class="sslm-info-value">{$_LANG.issue_api_fix|default:'Check your API credentials in module settings'}</span>
                </div>
                <div class="sslm-info-row">
                    <span class="sslm-info-label"><strong>{$_LANG.issue_validation|default:'Validation Error'}</strong></span>
                    <span class="sslm-info-value">{$_LANG.issue_validation_fix|default:'Ensure all required fields are filled correctly'}</span>
                </div>
                <div class="sslm-info-row">
                    <span class="sslm-info-label"><strong>{$_LANG.issue_timeout|default:'Request Timeout'}</strong></span>
                    <span class="sslm-info-value">{$_LANG.issue_timeout_fix|default:'The server took too long to respond. Try again later.'}</span>
                </div>
                <div class="sslm-info-row">
                    <span class="sslm-info-label"><strong>{$_LANG.issue_permission|default:'Permission Denied'}</strong></span>
                    <span class="sslm-info-value">{$_LANG.issue_permission_fix|default:'You may not have access to this resource'}</span>
                </div>
            </div>
        </div>
    </div>

    {* Action Buttons *}
    <div class="sslm-section">
        <div class="sslm-section-body" style="text-align: center;">
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <button type="button" class="sslm-btn sslm-btn-primary" onclick="location.reload()">
                    <i class="fas fa-redo"></i> {$_LANG.retry|default:'Try Again'}
                </button>
                <a href="{$WEB_ROOT}/clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn-secondary">
                    <i class="fas fa-arrow-left"></i> {$_LANG.back|default:'Go Back'}
                </a>
                <a href="{$WEB_ROOT}/clientarea.php" class="sslm-btn sslm-btn-outline">
                    <i class="fas fa-home"></i> {$_LANG.dashboard|default:'Dashboard'}
                </a>
                <a href="{$WEB_ROOT}/submitticket.php" class="sslm-btn sslm-btn-outline">
                    <i class="fas fa-ticket-alt"></i> {$_LANG.open_ticket|default:'Open Ticket'}
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
    lang: {
        copied: '{$_LANG.copied|default:"Copied!"}',
        copy_failed: '{$_LANG.copy_failed|default:"Copy failed"}'
    }
};
</script>

{* Load JavaScript *}
<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>