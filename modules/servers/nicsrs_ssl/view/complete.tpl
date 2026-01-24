{**
 * NicSRS SSL - Certificate Complete Template
 * Shows issued certificate details and management options
 *
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/nicsrs-modern.css">

<div class="nicsrs-ssl-container">
    
    {* Alert Container for AJAX messages *}
    <div class="nicsrs-alert-container"></div>
    
    {* Expiring Warning *}
    {if $isExpiring}
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        <strong>{$_LANG.certificate_expiring|default:'Certificate Expiring Soon'}</strong><br>
        {$_LANG.certificate_expiring_message|default:'Your certificate will expire in'} 
        <strong>{$expiryInfo.daysRemaining}</strong> {$_LANG.days|default:'days'}.
        {$_LANG.please_renew|default:'Please renew your service to get a new certificate.'}
    </div>
    {/if}
    
    {* Success Banner *}
    <div class="alert alert-success nicsrs-success-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-check-circle fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.certificate_issued|default:'Certificate Issued Successfully'}</h4>
                <p style="margin-bottom: 0;">{$_LANG.certificate_ready_message|default:'Your SSL certificate has been issued and is ready for installation.'}</p>
            </div>
        </div>
    </div>
    
    {* Certificate Details Card *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-shield"></i> {$_LANG.certificate_details|default:'Certificate Details'}
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 40%;">{$_LANG.domain|default:'Domain'}:</th>
                            <td><strong class="text-primary">{$domain|escape:'html'}</strong></td>
                        </tr>
                        <tr>
                            <th>{$_LANG.certificate_id|default:'Certificate ID'}:</th>
                            <td><code>{$certId|escape:'html'}</code></td>
                        </tr>
                        <tr>
                            <th>{$_LANG.status|default:'Status'}:</th>
                            <td>
                                <span class="label label-{$expiryInfo.statusClass|default:'success'}">
                                    {if $expiryInfo.isExpired}
                                        <i class="fa fa-times-circle"></i> {$_LANG.status_expired|default:'Expired'}
                                    {elseif $expiryInfo.isExpiring}
                                        <i class="fa fa-exclamation-circle"></i> {$_LANG.status_expiring|default:'Expiring Soon'}
                                    {else}
                                        <i class="fa fa-check-circle"></i> {$_LANG.status_active|default:'Active'}
                                    {/if}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 40%;">{$_LANG.valid_from|default:'Valid From'}:</th>
                            <td>{$validFrom|escape:'html'}</td>
                        </tr>
                        <tr>
                            <th>{$_LANG.valid_until|default:'Valid Until'}:</th>
                            <td>
                                {$validTo|escape:'html'}
                                {if $expiryInfo.daysRemaining !== null}
                                    <small class="text-muted">
                                        ({$expiryInfo.daysRemaining} {$_LANG.days_remaining|default:'days remaining'})
                                    </small>
                                {/if}
                            </td>
                        </tr>
                        <tr>
                            <th>{$_LANG.domains_secured|default:'Domains Secured'}:</th>
                            <td>{$domainInfo|count}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            {* Domain List *}
            {if count($domainInfo) > 1}
            <div class="well well-sm" style="margin-top: 15px;">
                <strong>{$_LANG.secured_domains|default:'Secured Domains'}:</strong>
                <ul style="margin-bottom: 0; margin-top: 5px;">
                    {foreach $domainInfo as $dInfo}
                    <li>
                        {$dInfo.domainName|escape:'html'}
                        {if $dInfo.isVerified}
                            <span class="text-success"><i class="fa fa-check"></i></span>
                        {/if}
                    </li>
                    {/foreach}
                </ul>
            </div>
            {/if}
        </div>
    </div>
    
    {* Quick Actions *}
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-bolt"></i> {$_LANG.quick_actions|default:'Quick Actions'}
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                {* Download Button *}
                <div class="col-md-4 col-sm-6 text-center" style="margin-bottom: 15px;">
                    {if $canDownload}
                    <button type="button" class="btn btn-success btn-lg btn-block nicsrs-action-btn" 
                            id="btnDownload" onclick="NicsrsSSL.showDownloadOptions()">
                        <i class="fa fa-download fa-2x"></i><br>
                        <span>{$_LANG.download_certificate|default:'Download Certificate'}</span>
                    </button>
                    {else}
                    <button type="button" class="btn btn-default btn-lg btn-block" disabled>
                        <i class="fa fa-download fa-2x"></i><br>
                        <span>{$_LANG.download_unavailable|default:'Download Unavailable'}</span>
                    </button>
                    {/if}
                </div>
                
                {* Reissue Button *}
                <div class="col-md-4 col-sm-6 text-center" style="margin-bottom: 15px;">
                    {if $canReissue}
                    <button type="button" class="btn btn-warning btn-lg btn-block nicsrs-action-btn"
                            onclick="NicsrsSSL.showReissueModal()">
                        <i class="fa fa-refresh fa-2x"></i><br>
                        <span>{$_LANG.reissue_certificate|default:'Reissue Certificate'}</span>
                    </button>
                    {else}
                    <button type="button" class="btn btn-default btn-lg btn-block" disabled>
                        <i class="fa fa-refresh fa-2x"></i><br>
                        <span>{$_LANG.reissue_unavailable|default:'Reissue Unavailable'}</span>
                    </button>
                    {/if}
                </div>
                
                {* Refresh Status Button *}
                <div class="col-md-4 col-sm-12 text-center" style="margin-bottom: 15px;">
                    <button type="button" class="btn btn-info btn-lg btn-block nicsrs-action-btn"
                            id="btnRefresh" onclick="NicsrsSSL.refreshStatus()">
                        <i class="fa fa-sync fa-2x"></i><br>
                        <span>{$_LANG.refresh_status|default:'Refresh Status'}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {* Installation Guide (Collapsible) *}
    <div class="panel panel-default">
        <div class="panel-heading" data-toggle="collapse" data-target="#installGuide" style="cursor: pointer;">
            <h3 class="panel-title">
                <i class="fa fa-book"></i> {$_LANG.installation_guide|default:'Installation Guide'}
                <i class="fa fa-chevron-down pull-right"></i>
            </h3>
        </div>
        <div id="installGuide" class="panel-collapse collapse">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fa fa-server"></i> Apache</h5>
                        <pre>SSLCertificateFile /path/to/{$domain}.crt
SSLCertificateKeyFile /path/to/{$domain}.key
SSLCertificateChainFile /path/to/{$domain}.ca-bundle</pre>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fa fa-server"></i> Nginx</h5>
                        <pre>ssl_certificate /path/to/{$domain}.fullchain.crt;
ssl_certificate_key /path/to/{$domain}.key;</pre>
                    </div>
                </div>
                <hr>
                <p class="text-muted">
                    <i class="fa fa-info-circle"></i>
                    {$_LANG.install_note|default:'Download the certificate package for files in multiple formats including PFX for IIS and JKS for Tomcat.'}
                </p>
            </div>
        </div>
    </div>
</div>

{* Download Options Modal *}
<div class="modal fade" id="downloadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-download"></i> {$_LANG.download_certificate|default:'Download Certificate'}
                </h4>
            </div>
            <div class="modal-body">
                <p>{$_LANG.download_format_select|default:'The certificate package includes all formats:'}</p>
                <ul>
                    <li><strong>.crt</strong> - {$_LANG.format_crt_desc|default:'Certificate file'}</li>
                    <li><strong>.ca-bundle</strong> - {$_LANG.format_ca_desc|default:'CA bundle (intermediate certificates)'}</li>
                    <li><strong>.fullchain.crt</strong> - {$_LANG.format_fullchain_desc|default:'Full chain for Nginx'}</li>
                    <li><strong>.key</strong> - {$_LANG.format_key_desc|default:'Private key (if generated)'}</li>
                    <li><strong>.pfx</strong> - {$_LANG.format_pfx_desc|default:'PKCS#12 for IIS/Windows'}</li>
                    <li><strong>.jks</strong> - {$_LANG.format_jks_desc|default:'Java KeyStore for Tomcat'}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {$_LANG.cancel|default:'Cancel'}
                </button>
                <button type="button" class="btn btn-success" onclick="NicsrsSSL.downloadCertificate()">
                    <i class="fa fa-download"></i> {$_LANG.download_now|default:'Download Now'}
                </button>
            </div>
        </div>
    </div>
</div>

{* Reissue Modal *}
<div class="modal fade" id="reissueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-refresh"></i> {$_LANG.reissue_certificate|default:'Reissue Certificate'}
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    {$_LANG.reissue_warning|default:'Warning: Reissuing will generate a new certificate. The current certificate will remain valid until the new one is issued.'}
                </div>
                
                <form id="reissueForm">
                    <div class="form-group">
                        <label for="reissueCsr">
                            {$_LANG.new_csr|default:'New CSR (Certificate Signing Request)'} <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="reissueCsr" name="csr" rows="10" 
                                  placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>
                        <p class="help-block">
                            {$_LANG.csr_help|default:'Paste your new CSR here. Generate a new CSR from your server.'}
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label for="reissuePrivateKey">
                            {$_LANG.private_key|default:'Private Key'} ({$_LANG.optional|default:'Optional'})
                        </label>
                        <textarea class="form-control" id="reissuePrivateKey" name="privateKey" rows="6"
                                  placeholder="-----BEGIN PRIVATE KEY-----"></textarea>
                        <p class="help-block">
                            {$_LANG.private_key_help|default:'If you want to store your private key, paste it here.'}
                        </p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {$_LANG.cancel|default:'Cancel'}
                </button>
                <button type="button" class="btn btn-warning" onclick="NicsrsSSL.submitReissue()">
                    <i class="fa fa-refresh"></i> {$_LANG.submit_reissue|default:'Submit Reissue Request'}
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/nicsrs-client.js"></script>
<script>
    // Initialize NicSRS SSL JavaScript
    NicsrsSSL.init({
        serviceId: {$serviceId},
        baseUrl: '{$moduleLink}',
        lang: {
            refreshing: '{$_LANG.refreshing|default:"Refreshing..."}',
            downloading: '{$_LANG.downloading|default:"Preparing download..."}',
            success: '{$_LANG.success|default:"Success"}',
            error: '{$_LANG.error|default:"Error"}',
            confirm_reissue: '{$_LANG.confirm_reissue|default:"Are you sure you want to reissue this certificate?"}'
        }
    });
</script>