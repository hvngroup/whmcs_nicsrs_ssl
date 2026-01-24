{**
 * NicSRS SSL - Status Message Template
 * Shows certificate status messages (cancelled, revoked, expired, etc.)
 *
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/nicsrs-modern.css">

<div class="nicsrs-ssl-container">
    
    {* Status Banner *}
    {if $status == 'cancelled'}
    <div class="alert alert-warning nicsrs-status-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-ban fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.order_cancelled|default:'Order Cancelled'}</h4>
                <p style="margin-bottom: 0;">{$statusMessage}</p>
            </div>
        </div>
    </div>
    
    {elseif $status == 'revoked'}
    <div class="alert alert-danger nicsrs-status-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-times-circle fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.certificate_revoked|default:'Certificate Revoked'}</h4>
                <p style="margin-bottom: 0;">{$statusMessage}</p>
            </div>
        </div>
    </div>
    
    {elseif $status == 'expired'}
    <div class="alert alert-danger nicsrs-status-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-calendar-times-o fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.certificate_expired|default:'Certificate Expired'}</h4>
                <p style="margin-bottom: 0;">{$statusMessage}</p>
            </div>
        </div>
    </div>
    
    {elseif $status == 'rejected'}
    <div class="alert alert-danger nicsrs-status-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-exclamation-circle fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.request_rejected|default:'Request Rejected'}</h4>
                <p style="margin-bottom: 0;">{$statusMessage}</p>
            </div>
        </div>
    </div>
    
    {else}
    <div class="alert alert-info nicsrs-status-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-info-circle fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.certificate_status|default:'Certificate Status'}</h4>
                <p style="margin-bottom: 0;">{$statusMessage}</p>
            </div>
        </div>
    </div>
    {/if}
    
    {* Order Details *}
    {if $order}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-file-text-o"></i> {$_LANG.order_details|default:'Order Details'}
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 40%;">{$_LANG.order_id|default:'Order ID'}:</th>
                            <td>#{$order->id}</td>
                        </tr>
                        {if $order->remoteid}
                        <tr>
                            <th>{$_LANG.certificate_id|default:'Certificate ID'}:</th>
                            <td><code>{$order->remoteid|escape:'html'}</code></td>
                        </tr>
                        {/if}
                        <tr>
                            <th>{$_LANG.domain|default:'Domain'}:</th>
                            <td><strong>{$domain|escape:'html'}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 40%;">{$_LANG.status|default:'Status'}:</th>
                            <td>
                                {if $status == 'cancelled'}
                                <span class="label label-warning">{$_LANG.status_cancelled|default:'Cancelled'}</span>
                                {elseif $status == 'revoked'}
                                <span class="label label-danger">{$_LANG.status_revoked|default:'Revoked'}</span>
                                {elseif $status == 'expired'}
                                <span class="label label-danger">{$_LANG.status_expired|default:'Expired'}</span>
                                {elseif $status == 'rejected'}
                                <span class="label label-danger">{$_LANG.status_rejected|default:'Rejected'}</span>
                                {else}
                                <span class="label label-default">{$status|escape:'html'|ucfirst}</span>
                                {/if}
                            </td>
                        </tr>
                        <tr>
                            <th>{$_LANG.order_date|default:'Order Date'}:</th>
                            <td>{$order->provisiondate|escape:'html'}</td>
                        </tr>
                        {if $order->completiondate && $order->completiondate != '0000-00-00 00:00:00'}
                        <tr>
                            <th>{$_LANG.completion_date|default:'Completion Date'}:</th>
                            <td>{$order->completiondate|escape:'html'}</td>
                        </tr>
                        {/if}
                    </table>
                </div>
            </div>
            
            {* Additional Info from configData *}
            {if $configData.cancel_reason}
            <div class="well well-sm">
                <strong>{$_LANG.cancellation_reason|default:'Cancellation Reason'}:</strong><br>
                {$configData.cancel_reason|escape:'html'}
            </div>
            {/if}
            
            {if $configData.revoke_reason}
            <div class="well well-sm">
                <strong>{$_LANG.revocation_reason|default:'Revocation Reason'}:</strong><br>
                {$configData.revoke_reason|escape:'html'}
            </div>
            {/if}
        </div>
    </div>
    {/if}
    
    {* Actions *}
    {if $canReapply}
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-plus-circle"></i> {$_LANG.next_steps|default:'Next Steps'}
            </h3>
        </div>
        <div class="panel-body">
            <p>{$_LANG.reapply_message|default:'You can apply for a new certificate by clicking the button below.'}</p>
            <a href="{$moduleLink}&action=reapply" class="btn btn-success btn-lg">
                <i class="fa fa-certificate"></i> {$_LANG.apply_new_certificate|default:'Apply for New Certificate'}
            </a>
        </div>
    </div>
    {else}
    <div class="panel panel-default">
        <div class="panel-body">
            <p class="text-muted">
                <i class="fa fa-info-circle"></i>
                {$_LANG.contact_support_message|default:'If you have any questions, please contact our support team.'}
            </p>
            <a href="submitticket.php" class="btn btn-default">
                <i class="fa fa-life-ring"></i> {$_LANG.contact_support|default:'Contact Support'}
            </a>
        </div>
    </div>
    {/if}
</div>

<style>
    .nicsrs-status-banner {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.09);
    }
    .nicsrs-status-banner.alert-warning {
        background: linear-gradient(135deg, #fffbe6 0%, #fff1b8 100%);
        border-left: 4px solid #faad14;
    }
    .nicsrs-status-banner.alert-danger {
        background: linear-gradient(135deg, #fff2f0 0%, #ffccc7 100%);
        border-left: 4px solid #ff4d4f;
    }
    .nicsrs-status-banner.alert-warning i { color: #d48806; }
    .nicsrs-status-banner.alert-danger i { color: #cf1322; }
</style>