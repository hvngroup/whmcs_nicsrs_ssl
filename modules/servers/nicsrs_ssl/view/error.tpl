{**
 * NicSRS SSL - Error Display Template
 * Shows error messages
 *
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/nicsrs-modern.css">

<div class="nicsrs-ssl-container">
    
    {* Error Banner *}
    <div class="alert alert-danger nicsrs-error-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-exclamation-triangle fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.error_occurred|default:'An Error Occurred'}</h4>
                <p style="margin-bottom: 0;">
                    {if $usefulErrorHelper}
                        {$usefulErrorHelper|escape:'html'}
                    {else}
                        {$_LANG.unknown_error|default:'An unknown error has occurred. Please try again or contact support.'}
                    {/if}
                </p>
            </div>
        </div>
    </div>
    
    {* Troubleshooting Tips *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-lightbulb-o"></i> {$_LANG.troubleshooting|default:'Troubleshooting Tips'}
            </h3>
        </div>
        <div class="panel-body">
            <ul class="list-unstyled" style="margin-bottom: 0;">
                <li style="padding: 8px 0;">
                    <i class="fa fa-check-circle text-success"></i>
                    {$_LANG.tip_refresh|default:'Try refreshing the page'}
                </li>
                <li style="padding: 8px 0;">
                    <i class="fa fa-check-circle text-success"></i>
                    {$_LANG.tip_clear_cache|default:'Clear your browser cache and cookies'}
                </li>
                <li style="padding: 8px 0;">
                    <i class="fa fa-check-circle text-success"></i>
                    {$_LANG.tip_wait|default:'Wait a few minutes and try again'}
                </li>
                <li style="padding: 8px 0;">
                    <i class="fa fa-check-circle text-success"></i>
                    {$_LANG.tip_different_browser|default:'Try using a different browser'}
                </li>
            </ul>
        </div>
    </div>
    
    {* Actions *}
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <a href="javascript:location.reload();" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> {$_LANG.try_again|default:'Try Again'}
                    </a>
                    <a href="clientarea.php?action=services" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> {$_LANG.back_to_services|default:'Back to Services'}
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <a href="submitticket.php" class="btn btn-warning">
                        <i class="fa fa-life-ring"></i> {$_LANG.contact_support|default:'Contact Support'}
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    {* Error Details (for debugging - only shown if error details available) *}
    {if $errorDetails}
    <div class="panel panel-default">
        <div class="panel-heading" data-toggle="collapse" data-target="#errorDetails" style="cursor: pointer;">
            <h3 class="panel-title">
                <i class="fa fa-code"></i> {$_LANG.technical_details|default:'Technical Details'}
                <i class="fa fa-chevron-down pull-right"></i>
            </h3>
        </div>
        <div id="errorDetails" class="panel-collapse collapse">
            <div class="panel-body">
                <pre style="background: #282c34; color: #abb2bf; font-size: 12px; max-height: 300px; overflow: auto;">{$errorDetails|escape:'html'}</pre>
            </div>
        </div>
    </div>
    {/if}
</div>

<style>
    .nicsrs-error-banner {
        background: linear-gradient(135deg, #fff2f0 0%, #ffccc7 100%);
        border: none;
        border-left: 4px solid #ff4d4f;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.09);
    }
    .nicsrs-error-banner i {
        color: #cf1322;
    }
</style>