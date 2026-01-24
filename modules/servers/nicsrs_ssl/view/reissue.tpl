{**
 * NicSRS SSL - Reissue Certificate Template
 * Form for reissuing existing SSL certificate
 *
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/nicsrs-modern.css">

<div class="nicsrs-ssl-container">
    
    {* Alert Container *}
    <div class="nicsrs-alert-container"></div>
    
    {* Header *}
    <div class="alert alert-warning">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-refresh fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.reissue_certificate|default:'Reissue Certificate'}</h4>
                <p style="margin-bottom: 0;">{$_LANG.reissue_description|default:'Generate a new certificate with a new CSR. Your current certificate will remain valid until the new one is issued.'}</p>
            </div>
        </div>
    </div>
    
    {* Current Certificate Info *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-certificate"></i> {$_LANG.current_certificate|default:'Current Certificate'}
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
                            <th>{$_LANG.domain|default:'Domain'}:</th>
                            <td><strong>{$domain|escape:'html'}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width: 40%;">{$_LANG.status|default:'Status'}:</th>
                            <td><span class="label label-success">{$_LANG.status_active|default:'Active'}</span></td>
                        </tr>
                        <tr>
                            <th>{$_LANG.domains_count|default:'Domains'}:</th>
                            <td>{$domainInfo|count}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            {if count($domainInfo) > 1}
            <div class="well well-sm">
                <strong>{$_LANG.secured_domains|default:'Secured Domains'}:</strong>
                <ul style="margin-bottom: 0; margin-top: 5px; columns: 2;">
                    {foreach $domainInfo as $dInfo}
                    <li>{$dInfo.domainName|escape:'html'}</li>
                    {/foreach}
                </ul>
            </div>
            {/if}
        </div>
    </div>
    
    {* Reissue Form *}
    <form id="reissueForm">
        <input type="hidden" name="serviceid" value="{$serviceId}">
        
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-file-text-o"></i> {$_LANG.new_certificate_request|default:'New Certificate Request'}
                </h3>
            </div>
            <div class="panel-body">
                {* Warning *}
                <div class="alert alert-warning" style="margin-bottom: 20px;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>{$_LANG.important|default:'Important'}:</strong>
                    {$_LANG.reissue_warning_detail|default:'Reissuing will create a new certificate. You will need to reinstall the certificate on your server after the new one is issued. The current certificate remains valid during this process.'}
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        {* New CSR *}
                        <div class="form-group">
                            <label for="newCsr">
                                {$_LANG.new_csr|default:'New CSR (Certificate Signing Request)'} 
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="newCsr" name="csr" rows="12" 
                                      placeholder="-----BEGIN CERTIFICATE REQUEST-----&#10;...&#10;-----END CERTIFICATE REQUEST-----"
                                      required></textarea>
                            <p class="help-block">
                                {$_LANG.new_csr_help|default:'Generate a new CSR from your server with the same domain name.'}
                            </p>
                        </div>
                        
                        {* New Private Key *}
                        <div class="form-group">
                            <label for="newPrivateKey">
                                {$_LANG.new_private_key|default:'New Private Key'} 
                                ({$_LANG.optional|default:'Optional'})
                            </label>
                            <textarea class="form-control" id="newPrivateKey" name="privateKey" rows="6"
                                      placeholder="-----BEGIN PRIVATE KEY-----"></textarea>
                            <p class="help-block">
                                {$_LANG.private_key_help|default:'If you want to store your private key, paste it here.'}
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        {* CSR Decoder *}
                        <div class="well">
                            <h5><i class="fa fa-info-circle"></i> {$_LANG.new_csr_info|default:'New CSR Information'}</h5>
                            <div id="newCsrInfo" style="display: none;">
                                <table class="table table-condensed table-bordered" style="font-size: 12px;">
                                    <tr>
                                        <th>CN</th>
                                        <td id="newCsrCN">-</td>
                                    </tr>
                                    <tr>
                                        <th>O</th>
                                        <td id="newCsrO">-</td>
                                    </tr>
                                    <tr>
                                        <th>C</th>
                                        <td id="newCsrC">-</td>
                                    </tr>
                                </table>
                            </div>
                            <p id="newCsrPlaceholder" class="text-muted" style="font-size: 12px;">
                                {$_LANG.csr_decode_prompt|default:'Enter CSR and click Decode to verify.'}
                            </p>
                            <button type="button" class="btn btn-info btn-sm btn-block" id="btnDecodeNewCsr">
                                <i class="fa fa-search"></i> {$_LANG.decode_csr|default:'Decode CSR'}
                            </button>
                        </div>
                        
                        {* Tips *}
                        <div class="well">
                            <h5><i class="fa fa-lightbulb-o"></i> {$_LANG.tips|default:'Tips'}</h5>
                            <ul style="font-size: 12px; padding-left: 20px; margin-bottom: 0;">
                                <li>{$_LANG.tip_same_domain|default:'Use the same domain name as your current certificate.'}</li>
                                <li>{$_LANG.tip_new_key|default:'Generate a new private key with your new CSR.'}</li>
                                <li>{$_LANG.tip_reinstall|default:'Reinstall certificate after reissue completes.'}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {* Domain Validation *}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-shield"></i> {$_LANG.domain_validation|default:'Domain Validation'}
                </h3>
            </div>
            <div class="panel-body">
                <p class="text-muted">{$_LANG.reissue_dcv_note|default:'Domain validation will be required again for the reissued certificate.'}</p>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="reissueDomainTable">
                        <thead>
                            <tr>
                                <th>{$_LANG.domain|default:'Domain'}</th>
                                <th style="width: 250px;">{$_LANG.dcv_method|default:'Validation Method'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $domainInfo as $idx => $dInfo}
                            <tr>
                                <td>
                                    <strong>{$dInfo.domainName|escape:'html'}</strong>
                                    <input type="hidden" name="domains[{$idx}][domainName]" value="{$dInfo.domainName|escape:'html'}">
                                </td>
                                <td>
                                    <select class="form-control input-sm" name="domains[{$idx}][dcvMethod]">
                                        {foreach $dcvMethods as $code => $method}
                                        <option value="{$code}" {if $dInfo.dcvMethod == $code}selected{/if}>
                                            {$method.name}
                                        </option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        {* Submit Section *}
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{$moduleLink}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> {$_LANG.back|default:'Back'}
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-warning btn-lg" id="btnSubmitReissue">
                            <i class="fa fa-refresh"></i> {$_LANG.submit_reissue|default:'Submit Reissue Request'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/nicsrs-client.js"></script>
<script>
    NicsrsSSL.init({
        serviceId: {$serviceId},
        baseUrl: '{$moduleLink}',
        lang: {
            processing: '{$_LANG.processing|default:"Processing..."}',
            success: '{$_LANG.success|default:"Success"}',
            error: '{$_LANG.error|default:"Error"}'
        }
    });

    $(document).ready(function() {
        // Decode New CSR
        $('#btnDecodeNewCsr').click(function() {
            var csr = $('#newCsr').val().trim();
            if (!csr) {
                NicsrsSSL.showError('{$_LANG.csr_required|default:"Please enter a CSR"}');
                return;
            }
            
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: '{$moduleLink}',
                type: 'POST',
                data: { step: 'decodeCsr', csr: csr, serviceid: {$serviceId} },
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1 && response.data) {
                        $('#newCsrCN').text(response.data.commonName || '-');
                        $('#newCsrO').text(response.data.organization || '-');
                        $('#newCsrC').text(response.data.country || '-');
                        
                        // Verify domain matches
                        if (response.data.commonName && response.data.commonName !== '{$domain|escape:"js"}') {
                            NicsrsSSL.showError('{$_LANG.domain_mismatch|default:"Warning: Domain does not match current certificate!"}');
                        }
                        
                        $('#newCsrPlaceholder').hide();
                        $('#newCsrInfo').show();
                    } else {
                        NicsrsSSL.showError(response.msg || 'Failed to decode CSR');
                    }
                },
                error: function() {
                    NicsrsSSL.showError('Network error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-search"></i> {$_LANG.decode_csr|default:"Decode CSR"}');
                }
            });
        });
        
        // Form Submit
        $('#reissueForm').submit(function(e) {
            e.preventDefault();
            
            var csr = $('#newCsr').val().trim();
            if (!csr) {
                NicsrsSSL.showError('{$_LANG.csr_required|default:"New CSR is required"}');
                return;
            }
            
            if (!confirm('{$_LANG.confirm_reissue|default:"Are you sure you want to reissue this certificate?"}')) {
                return;
            }
            
            var btn = $('#btnSubmitReissue');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {$_LANG.processing|default:"Processing..."}');
            
            // Collect domain info
            var domainInfo = [];
            $('#reissueDomainTable tbody tr').each(function() {
                domainInfo.push({
                    domainName: $(this).find('input[name*="domainName"]').val(),
                    dcvMethod: $(this).find('select').val()
                });
            });
            
            var data = {
                csr: csr,
                privateKey: $('#newPrivateKey').val(),
                domainInfo: domainInfo
            };
            
            $.ajax({
                url: '{$moduleLink}',
                type: 'POST',
                data: { step: 'reissueCertificate', data: JSON.stringify(data), serviceid: {$serviceId} },
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1) {
                        NicsrsSSL.showSuccess('{$_LANG.reissue_submitted|default:"Reissue request submitted successfully!"}');
                        setTimeout(function() {
                            window.location.href = '{$moduleLink}';
                        }, 2000);
                    } else {
                        NicsrsSSL.showError(response.msg || 'Reissue failed');
                        btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> {$_LANG.submit_reissue|default:"Submit Reissue Request"}');
                    }
                },
                error: function() {
                    NicsrsSSL.showError('Network error');
                    btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> {$_LANG.submit_reissue|default:"Submit Reissue Request"}');
                }
            });
        });
    });
</script>