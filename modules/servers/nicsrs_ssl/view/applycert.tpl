{**
 * NicSRS SSL - Certificate Application Template
 * Form for applying new SSL certificate
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
    <div class="alert alert-info nicsrs-pending-banner">
        <div class="row">
            <div class="col-sm-1 text-center">
                <i class="fa fa-certificate fa-3x"></i>
            </div>
            <div class="col-sm-11">
                <h4 style="margin-top: 0;">{$_LANG.apply_certificate|default:'Apply for SSL Certificate'}</h4>
                <p style="margin-bottom: 0;">{$_LANG.apply_message|default:'Complete the form below to request your SSL certificate.'}</p>
            </div>
        </div>
    </div>
    
    {* Application Form *}
    <form id="applyForm" class="nicsrs-apply-form">
        <input type="hidden" name="serviceid" value="{$serviceId}">
        
        {* Step 1: CSR *}
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <span class="step-number">1</span>
                    {$_LANG.certificate_signing_request|default:'Certificate Signing Request (CSR)'}
                </h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="csr">
                                CSR <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="csr" name="csr" rows="12" 
                                      placeholder="-----BEGIN CERTIFICATE REQUEST-----&#10;...&#10;-----END CERTIFICATE REQUEST-----"
                                      required>{$configData.csr|default:''}</textarea>
                            <p class="help-block">
                                {$_LANG.csr_help_text|default:'Paste your CSR here. Generate a CSR from your web server or use our CSR generator.'}
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label for="privateKey">
                                {$_LANG.private_key|default:'Private Key'} ({$_LANG.optional|default:'Optional'})
                            </label>
                            <textarea class="form-control" id="privateKey" name="privateKey" rows="6"
                                      placeholder="-----BEGIN PRIVATE KEY-----">{$configData.privateKey|default:''}</textarea>
                            <p class="help-block">
                                {$_LANG.private_key_store_help|default:'Store your private key here for safekeeping (optional).'}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="well">
                            <h5><i class="fa fa-info-circle"></i> {$_LANG.csr_info|default:'CSR Information'}</h5>
                            <div id="csrInfo" style="display: none;">
                                <table class="table table-condensed table-bordered" style="font-size: 12px;">
                                    <tr>
                                        <th>CN</th>
                                        <td id="csrCN">-</td>
                                    </tr>
                                    <tr>
                                        <th>O</th>
                                        <td id="csrO">-</td>
                                    </tr>
                                    <tr>
                                        <th>L</th>
                                        <td id="csrL">-</td>
                                    </tr>
                                    <tr>
                                        <th>ST</th>
                                        <td id="csrST">-</td>
                                    </tr>
                                    <tr>
                                        <th>C</th>
                                        <td id="csrC">-</td>
                                    </tr>
                                </table>
                            </div>
                            <p id="csrPlaceholder" class="text-muted" style="font-size: 12px;">
                                {$_LANG.csr_decode_prompt|default:'Enter CSR and click Decode to view information.'}
                            </p>
                            <button type="button" class="btn btn-info btn-sm btn-block" id="btnDecodeCsr">
                                <i class="fa fa-search"></i> {$_LANG.decode_csr|default:'Decode CSR'}
                            </button>
                        </div>
                        
                        <div class="well">
                            <h5><i class="fa fa-shield"></i> {$_LANG.certificate_type|default:'Certificate Type'}</h5>
                            <p><strong>{$certType|escape:'html'}</strong></p>
                            {if $productInfo}
                            <small class="text-muted">
                                {$productInfo.vendor} - {$productInfo.validation|upper}
                            </small>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {* Step 2: Domain Information *}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <span class="step-number">2</span>
                    {$_LANG.domain_information|default:'Domain Information'}
                </h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label>{$_LANG.primary_domain|default:'Primary Domain'} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="primaryDomain" name="primaryDomain" 
                           value="{$domain|escape:'html'}" readonly>
                    <p class="help-block">{$_LANG.primary_domain_help|default:'Primary domain is extracted from CSR.'}</p>
                </div>
                
                <div class="form-group">
                    <label>{$_LANG.dcv_method|default:'Domain Validation Method'} <span class="text-danger">*</span></label>
                    <select class="form-control" id="dcvMethod" name="dcvMethod">
                        {foreach $dcvMethods as $code => $method}
                        <option value="{$code}">{$method.name} - {$method.description}</option>
                        {/foreach}
                    </select>
                </div>
                
                <div class="form-group" id="dcvEmailGroup" style="display: none;">
                    <label>{$_LANG.dcv_email_address|default:'Validation Email'}</label>
                    <select class="form-control" id="dcvEmail" name="dcvEmail">
                        <option value="">{$_LANG.loading|default:'Loading...'}</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>{$_LANG.server_type|default:'Server Type'}</label>
                    <select class="form-control" id="serverType" name="serverType">
                        <option value="other">{$_LANG.other|default:'Other'}</option>
                        <option value="apache">Apache</option>
                        <option value="nginx">Nginx</option>
                        <option value="iis">Microsoft IIS</option>
                        <option value="tomcat">Tomcat</option>
                        <option value="cpanel">cPanel</option>
                        <option value="plesk">Plesk</option>
                    </select>
                </div>
            </div>
        </div>
        
        {* Step 3: Contact Information (for OV/EV) *}
        {if $validationRequired != 'dv'}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <span class="step-number">3</span>
                    {$_LANG.contact_information|default:'Contact Information'}
                </h3>
            </div>
            <div class="panel-body">
                {* Admin Contact *}
                <h5 class="text-primary"><i class="fa fa-user"></i> {$_LANG.admin_contact|default:'Administrative Contact'}</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.first_name|default:'First Name'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="admin_firstName" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.last_name|default:'Last Name'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="admin_lastName" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.email|default:'Email'} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="admin_email" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.phone|default:'Phone'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="admin_phone" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.job_title|default:'Job Title'}</label>
                            <input type="text" class="form-control" name="admin_jobTitle">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.organization|default:'Organization'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="admin_organization" required>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                {* Organization Info *}
                <h5 class="text-primary"><i class="fa fa-building"></i> {$_LANG.organization_info|default:'Organization Information'}</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{$_LANG.address|default:'Address'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="org_address" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{$_LANG.city|default:'City'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="org_city" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{$_LANG.state|default:'State/Province'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="org_state" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{$_LANG.postal_code|default:'Postal Code'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="org_postalCode" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.country|default:'Country'} <span class="text-danger">*</span></label>
                            <select class="form-control" name="org_country" required>
                                <option value="">{$_LANG.select_country|default:'Select Country'}</option>
                                {foreach $countryList as $code => $name}
                                <option value="{$code}">{$name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{$_LANG.phone|default:'Phone'} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="org_phone" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}
        
        {* Submit Section *}
        <div class="panel panel-success">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="agreeTerms" required>
                                {$_LANG.agree_terms|default:'I confirm that the information provided is accurate and I agree to the terms of service.'}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="submit" class="btn btn-success btn-lg" id="btnSubmitApply">
                            <i class="fa fa-paper-plane"></i> {$_LANG.submit_application|default:'Submit Application'}
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
        // Decode CSR button
        $('#btnDecodeCsr').click(function() {
            var csr = $('#csr').val().trim();
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
                        $('#csrCN').text(response.data.commonName || '-');
                        $('#csrO').text(response.data.organization || '-');
                        $('#csrL').text(response.data.locality || '-');
                        $('#csrST').text(response.data.state || '-');
                        $('#csrC').text(response.data.country || '-');
                        
                        if (response.data.commonName) {
                            $('#primaryDomain').val(response.data.commonName);
                        }
                        
                        $('#csrPlaceholder').hide();
                        $('#csrInfo').show();
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
        
        // DCV Method change
        $('#dcvMethod').change(function() {
            var method = $(this).val();
            if (method === 'EMAIL') {
                $('#dcvEmailGroup').show();
                loadDcvEmails();
            } else {
                $('#dcvEmailGroup').hide();
            }
        });
        
        // Load DCV emails
        function loadDcvEmails() {
            var domain = $('#primaryDomain').val();
            if (!domain) return;
            
            $.ajax({
                url: '{$moduleLink}',
                type: 'POST',
                data: { step: 'getDcvEmails', domain: domain, serviceid: {$serviceId} },
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1 && response.data && response.data.emails) {
                        var select = $('#dcvEmail').empty();
                        $.each(response.data.emails, function(i, email) {
                            select.append('<option value="' + email + '">' + email + '</option>');
                        });
                    }
                }
            });
        }
        
        // Form submit
        $('#applyForm').submit(function(e) {
            e.preventDefault();
            
            if (!$('#agreeTerms').is(':checked')) {
                NicsrsSSL.showError('{$_LANG.agree_required|default:"Please agree to the terms"}');
                return;
            }
            
            var csr = $('#csr').val().trim();
            if (!csr) {
                NicsrsSSL.showError('{$_LANG.csr_required|default:"CSR is required"}');
                return;
            }
            
            var btn = $('#btnSubmitApply');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {$_LANG.processing|default:"Processing..."}');
            
            var domainInfo = [{
                domainName: $('#primaryDomain').val(),
                dcvMethod: $('#dcvMethod').val(),
                dcvEmail: $('#dcvEmail').val() || ''
            }];
            
            var data = {
                csr: csr,
                privateKey: $('#privateKey').val(),
                domainInfo: domainInfo,
                server: $('#serverType').val()
            };
            
            {if $validationRequired != 'dv'}
            data.Administrator = {
                firstName: $('[name="admin_firstName"]').val(),
                lastName: $('[name="admin_lastName"]').val(),
                email: $('[name="admin_email"]').val(),
                mobile: $('[name="admin_phone"]').val(),
                job: $('[name="admin_jobTitle"]').val(),
                organation: $('[name="admin_organization"]').val()
            };
            
            data.organizationInfo = {
                name: $('[name="admin_organization"]').val(),
                address: $('[name="org_address"]').val(),
                city: $('[name="org_city"]').val(),
                state: $('[name="org_state"]').val(),
                postCode: $('[name="org_postalCode"]').val(),
                country: $('[name="org_country"]').val(),
                phone: $('[name="org_phone"]').val()
            };
            {/if}
            
            $.ajax({
                url: '{$moduleLink}',
                type: 'POST',
                data: { step: 'submitApply', data: JSON.stringify(data), serviceid: {$serviceId} },
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1) {
                        NicsrsSSL.showSuccess('{$_LANG.application_submitted|default:"Application submitted successfully!"}');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        NicsrsSSL.showError(response.msg || 'Submission failed');
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> {$_LANG.submit_application|default:"Submit Application"}');
                    }
                },
                error: function() {
                    NicsrsSSL.showError('Network error');
                    btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> {$_LANG.submit_application|default:"Submit Application"}');
                }
            });
        });
    });
</script>

<style>
    .step-number {
        display: inline-block;
        width: 28px;
        height: 28px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        text-align: center;
        line-height: 28px;
        margin-right: 10px;
        font-weight: bold;
    }
    .panel-primary .step-number {
        background: rgba(255,255,255,0.3);
    }
</style>