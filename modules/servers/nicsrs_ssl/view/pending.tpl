{**
 * NicSRS SSL - Pending Certificate Template
 * Shows pending certificate with DCV instructions
 *
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/nicsrs-modern.css">

<div class="nicsrs-ssl-container">
    
    {* Alert Container *}
    <div class="nicsrs-alert-container"></div>
    
    {* Status Banner *}
    <div class="alert alert-info nicsrs-pending-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-clock-o fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.certificate_pending|default:'Certificate Pending Validation'}</h4>
                <p style="margin-bottom: 0;">{$_LANG.pending_message|default:'Your certificate order is being processed. Please complete domain validation below.'}</p>
            </div>
        </div>
    </div>
    
    {* Progress Tracker *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-tasks"></i> {$_LANG.order_progress|default:'Order Progress'}
            </h3>
        </div>
        <div class="panel-body">
            <div class="nicsrs-progress-tracker">
                <div class="progress-step {if $applicationStatus == 'done'}completed{else}active{/if}">
                    <div class="step-icon">
                        {if $applicationStatus == 'done'}
                            <i class="fa fa-check"></i>
                        {else}
                            <i class="fa fa-spinner fa-spin"></i>
                        {/if}
                    </div>
                    <div class="step-label">{$_LANG.step_application|default:'Application'}</div>
                </div>
                
                <div class="progress-line {if $applicationStatus == 'done'}completed{/if}"></div>
                
                <div class="progress-step {if $dcvStatus == 'done'}completed{elseif $applicationStatus == 'done'}active{else}pending{/if}">
                    <div class="step-icon">
                        {if $dcvStatus == 'done'}
                            <i class="fa fa-check"></i>
                        {elseif $applicationStatus == 'done'}
                            <i class="fa fa-spinner fa-spin"></i>
                        {else}
                            <i class="fa fa-circle-o"></i>
                        {/if}
                    </div>
                    <div class="step-label">{$_LANG.step_validation|default:'Domain Validation'}</div>
                </div>
                
                <div class="progress-line {if $dcvStatus == 'done'}completed{/if}"></div>
                
                <div class="progress-step {if $issuedStatus == 'done'}completed{elseif $dcvStatus == 'done'}active{else}pending{/if}">
                    <div class="step-icon">
                        {if $issuedStatus == 'done'}
                            <i class="fa fa-check"></i>
                        {elseif $dcvStatus == 'done'}
                            <i class="fa fa-spinner fa-spin"></i>
                        {else}
                            <i class="fa fa-circle-o"></i>
                        {/if}
                    </div>
                    <div class="step-label">{$_LANG.step_issuance|default:'Certificate Issuance'}</div>
                </div>
            </div>
        </div>
    </div>
    
    {* Order Information *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-info-circle"></i> {$_LANG.order_information|default:'Order Information'}
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 40%;">{$_LANG.certificate_id|default:'Certificate ID'}:</th>
                            <td><code>{$certId|escape:'html'}</code></td>
                        </tr>
                        <tr>
                            <th>{$_LANG.primary_domain|default:'Primary Domain'}:</th>
                            <td><strong>{$domain|escape:'html'}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 40%;">{$_LANG.status|default:'Status'}:</th>
                            <td><span class="label label-warning">{$_LANG.pending|default:'Pending'}</span></td>
                        </tr>
                        <tr>
                            <th>{$_LANG.domains_count|default:'Domains'}:</th>
                            <td>{$domainInfo|count}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {* Domain Validation Section *}
    <div class="panel panel-warning">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-shield"></i> {$_LANG.domain_validation|default:'Domain Validation'}
            </h3>
        </div>
        <div class="panel-body">
            <p class="text-muted">
                <i class="fa fa-info-circle"></i>
                {$_LANG.dcv_instruction|default:'Complete domain validation for each domain below. Choose a validation method and follow the instructions.'}
            </p>
            
            {* Domain List with DCV Status *}
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dcvTable">
                    <thead>
                        <tr>
                            <th>{$_LANG.domain|default:'Domain'}</th>
                            <th style="width: 180px;">{$_LANG.method|default:'Method'}</th>
                            <th style="width: 120px;">{$_LANG.status|default:'Status'}</th>
                            <th style="width: 100px;">{$_LANG.actions|default:'Actions'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $dcvInstructions as $dcv}
                        <tr data-domain="{$dcv.domain|escape:'html'}">
                            <td>
                                <strong>{$dcv.domain|escape:'html'}</strong>
                            </td>
                            <td>
                                <select class="form-control input-sm dcv-method-select" 
                                        data-domain="{$dcv.domain|escape:'html'}"
                                        {if $dcv.isVerified}disabled{/if}>
                                    {foreach $dcvMethods as $code => $method}
                                    <option value="{$code}" {if $dcv.method == $code}selected{/if}>
                                        {$method.name}
                                    </option>
                                    {/foreach}
                                </select>
                            </td>
                            <td class="text-center">
                                {if $dcv.isVerified}
                                    <span class="label label-success">
                                        <i class="fa fa-check"></i> {$_LANG.verified|default:'Verified'}
                                    </span>
                                {else}
                                    <span class="label label-warning">
                                        <i class="fa fa-clock-o"></i> {$_LANG.pending|default:'Pending'}
                                    </span>
                                {/if}
                            </td>
                            <td class="text-center">
                                {if !$dcv.isVerified}
                                <button type="button" class="btn btn-xs btn-info" 
                                        onclick="NicsrsSSL.showDcvDetails('{$dcv.domain|escape:'js'}')">
                                    <i class="fa fa-eye"></i> {$_LANG.view|default:'View'}
                                </button>
                                {else}
                                <span class="text-success"><i class="fa fa-check-circle"></i></span>
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            
            <div class="text-right" style="margin-top: 15px;">
                <button type="button" class="btn btn-primary" id="btnSaveDcv" onclick="NicsrsSSL.saveDcvMethods()">
                    <i class="fa fa-save"></i> {$_LANG.save_changes|default:'Save Changes'}
                </button>
                <button type="button" class="btn btn-default" id="btnRefreshStatus" onclick="NicsrsSSL.refreshStatus()">
                    <i class="fa fa-refresh"></i> {$_LANG.refresh_status|default:'Refresh Status'}
                </button>
            </div>
        </div>
    </div>
    
    {* DCV Instructions Panel *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-list-alt"></i> {$_LANG.validation_instructions|default:'Validation Instructions'}
            </h3>
        </div>
        <div class="panel-body">
            {* HTTP/HTTPS Validation *}
            {if $applyReturn.DCVfileName}
            <div class="dcv-method-block" id="dcvHttpBlock">
                <h5><i class="fa fa-file-text-o"></i> {$_LANG.http_validation|default:'HTTP/HTTPS File Validation'}</h5>
                <p class="text-muted">{$_LANG.http_validation_desc|default:'Create a file with the following content at the specified path:'}</p>
                
                <div class="well well-sm">
                    <strong>{$_LANG.file_name|default:'File Name'}:</strong><br>
                    <code>{$applyReturn.DCVfileName|escape:'html'}</code>
                </div>
                
                <div class="well well-sm">
                    <strong>{$_LANG.file_content|default:'File Content'}:</strong><br>
                    <code>{$applyReturn.DCVfileContent|escape:'html'}</code>
                    <button type="button" class="btn btn-xs btn-default pull-right" 
                            onclick="NicsrsSSL.copyToClipboard('{$applyReturn.DCVfileContent|escape:'js'}')">
                        <i class="fa fa-copy"></i> {$_LANG.copy|default:'Copy'}
                    </button>
                </div>
                
                <div class="well well-sm">
                    <strong>{$_LANG.file_path|default:'File Path'}:</strong><br>
                    <code>http://{$domain}/.well-known/pki-validation/{$applyReturn.DCVfileName|escape:'html'}</code>
                </div>
            </div>
            {/if}
            
            {* DNS Validation *}
            {if $applyReturn.DCVdnsHost}
            <div class="dcv-method-block" id="dcvDnsBlock">
                <h5><i class="fa fa-globe"></i> {$_LANG.dns_validation|default:'DNS Validation'}</h5>
                <p class="text-muted">{$_LANG.dns_validation_desc|default:'Add the following DNS record to your domain:'}</p>
                
                <div class="well well-sm">
                    <strong>{$_LANG.record_type|default:'Record Type'}:</strong> 
                    <code>{$applyReturn.DCVdnsType|default:'CNAME'}</code>
                </div>
                
                <div class="well well-sm">
                    <strong>{$_LANG.host_name|default:'Host Name'}:</strong><br>
                    <code>{$applyReturn.DCVdnsHost|escape:'html'}.{$domain}</code>
                    <button type="button" class="btn btn-xs btn-default pull-right" 
                            onclick="NicsrsSSL.copyToClipboard('{$applyReturn.DCVdnsHost|escape:'js'}.{$domain|escape:'js'}')">
                        <i class="fa fa-copy"></i> {$_LANG.copy|default:'Copy'}
                    </button>
                </div>
                
                <div class="well well-sm">
                    <strong>{$_LANG.value|default:'Value'}:</strong><br>
                    <code>{$applyReturn.DCVdnsValue|escape:'html'}</code>
                    <button type="button" class="btn btn-xs btn-default pull-right" 
                            onclick="NicsrsSSL.copyToClipboard('{$applyReturn.DCVdnsValue|escape:'js'}')">
                        <i class="fa fa-copy"></i> {$_LANG.copy|default:'Copy'}
                    </button>
                </div>
            </div>
            {/if}
            
            {* Email Validation Note *}
            <div class="dcv-method-block" id="dcvEmailBlock">
                <h5><i class="fa fa-envelope"></i> {$_LANG.email_validation|default:'Email Validation'}</h5>
                <p class="text-muted">{$_LANG.email_validation_desc|default:'If using email validation, check your inbox for the validation email and click the approval link.'}</p>
                <p><strong>{$_LANG.valid_emails|default:'Valid approval emails'}:</strong></p>
                <ul>
                    <li>admin@{$domain}</li>
                    <li>administrator@{$domain}</li>
                    <li>hostmaster@{$domain}</li>
                    <li>postmaster@{$domain}</li>
                    <li>webmaster@{$domain}</li>
                </ul>
            </div>
        </div>
    </div>
    
    {* Cancel Order Option *}
    <div class="text-right" style="margin-top: 20px;">
        <button type="button" class="btn btn-danger btn-sm" onclick="NicsrsSSL.confirmCancel()">
            <i class="fa fa-times"></i> {$_LANG.cancel_order|default:'Cancel Order'}
        </button>
    </div>
</div>

{* DCV Details Modal *}
<div class="modal fade" id="dcvDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-shield"></i> {$_LANG.validation_details|default:'Validation Details'}
                </h4>
            </div>
            <div class="modal-body" id="dcvDetailsContent">
                {* Content loaded dynamically *}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {$_LANG.close|default:'Close'}
                </button>
            </div>
        </div>
    </div>
</div>

{* Cancel Confirmation Modal *}
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-danger">
                    <i class="fa fa-exclamation-triangle"></i> {$_LANG.confirm_cancel|default:'Confirm Cancellation'}
                </h4>
            </div>
            <div class="modal-body">
                <p>{$_LANG.cancel_confirm_message|default:'Are you sure you want to cancel this certificate order? This action cannot be undone.'}</p>
                <div class="form-group">
                    <label for="cancelReason">{$_LANG.reason|default:'Reason'} ({$_LANG.optional|default:'Optional'})</label>
                    <textarea class="form-control" id="cancelReason" rows="3" 
                              placeholder="{$_LANG.cancel_reason_placeholder|default:'Please provide a reason for cancellation...'}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {$_LANG.no_keep|default:'No, Keep Order'}
                </button>
                <button type="button" class="btn btn-danger" onclick="NicsrsSSL.cancelOrder()">
                    <i class="fa fa-times"></i> {$_LANG.yes_cancel|default:'Yes, Cancel Order'}
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/nicsrs-client.js"></script>
<script>
    NicsrsSSL.init({
        serviceId: {$serviceId},
        baseUrl: '{$moduleLink}',
        dcvInstructions: {$dcvInstructions|@json_encode},
        lang: {
            refreshing: '{$_LANG.refreshing|default:"Refreshing..."}',
            saving: '{$_LANG.saving|default:"Saving..."}',
            success: '{$_LANG.success|default:"Success"}',
            error: '{$_LANG.error|default:"Error"}',
            copied: '{$_LANG.copied|default:"Copied to clipboard!"}'
        }
    });
</script>

<style>
    /* Progress Tracker Styles */
    .nicsrs-progress-tracker {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0;
    }
    
    .progress-step {
        text-align: center;
        flex: 0 0 auto;
    }
    
    .progress-step .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #f5f5f5;
        border: 3px solid #d9d9d9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 18px;
        color: #999;
    }
    
    .progress-step.completed .step-icon {
        background: #52c41a;
        border-color: #52c41a;
        color: white;
    }
    
    .progress-step.active .step-icon {
        background: #1890ff;
        border-color: #1890ff;
        color: white;
    }
    
    .progress-step.pending .step-icon {
        background: #f5f5f5;
        border-color: #d9d9d9;
        color: #bfbfbf;
    }
    
    .progress-step .step-label {
        font-size: 13px;
        font-weight: 500;
        color: #595959;
    }
    
    .progress-step.completed .step-label {
        color: #52c41a;
    }
    
    .progress-step.active .step-label {
        color: #1890ff;
    }
    
    .progress-step.pending .step-label {
        color: #bfbfbf;
    }
    
    .progress-line {
        flex: 1;
        height: 3px;
        background: #d9d9d9;
        margin: 0 10px;
        margin-bottom: 30px;
    }
    
    .progress-line.completed {
        background: #52c41a;
    }
    
    /* DCV Method Block Styles */
    .dcv-method-block {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .dcv-method-block:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .dcv-method-block h5 {
        color: #1890ff;
        margin-bottom: 10px;
    }
    
    .dcv-method-block .well {
        position: relative;
        margin-bottom: 10px;
    }
    
    .dcv-method-block .well .btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .dcv-method-block code {
        display: inline-block;
        max-width: calc(100% - 80px);
        word-break: break-all;
    }
    
    /* Table Styles */
    #dcvTable .dcv-method-select {
        min-width: 150px;
    }
    
    #dcvTable .dcv-email-select {
        min-width: 150px;
        margin-top: 5px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .nicsrs-progress-tracker {
            flex-direction: column;
        }
        
        .progress-line {
            width: 3px;
            height: 30px;
            margin: 10px 0;
        }
        
        .progress-step .step-icon {
            width: 40px;
            height: 40px;
            font-size: 14px;
        }
        
        .dcv-method-block .well .btn {
            position: static;
            transform: none;
            margin-top: 10px;
            display: block;
        }
        
        .dcv-method-block code {
            max-width: 100%;
        }
    }
</style>