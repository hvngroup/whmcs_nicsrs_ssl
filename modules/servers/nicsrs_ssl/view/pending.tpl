<link rel="stylesheet" href="{$WEB_ROOT}/{$MODULE_PATH}/assets/css/ssl-manager.css">

<div class="sslm-container">
    <script type="application/json" id="sslmConfig">{
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}&modop=custom",
        "lang": {$_LANG_JSON}
    }</script>

    <!-- Status Alert -->
    <div class="sslm-alert sslm-alert--warning">
        <i>⏳</i>
        <span>{$_LANG.message_des}</span>
    </div>

    <!-- Certificate Status Card -->
    <div class="sslm-card sslm-card--warning">
        <div class="sslm-card__header">
            <h3>{$_LANG.certificate_pending}</h3>
            <span class="sslm-badge sslm-badge--warning">{$status}</span>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-cert-info">
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_id}</label>
                    <span>{$remoteid|default:'N/A'}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.certificate_type}</label>
                    <span>{$productCode}</span>
                </div>
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.status}</label>
                    <span class="sslm-badge sslm-badge--warning">{$status}</span>
                </div>
                {if $applyTime}
                <div class="sslm-cert-info__item">
                    <label>{$_LANG.submit}</label>
                    <span>{$applyTime}</span>
                </div>
                {/if}
            </div>
        </div>
    </div>

    <!-- Domain Validation Status -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.domain_validation}</h3>
            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" data-action="refreshStatus">
                🔄 {$_LANG.refresh_status}
            </button>
        </div>
        <div class="sslm-card__body">
            {if $dcvStatus|@count > 0}
            <table class="sslm-table">
                <thead>
                    <tr>
                        <th>{$_LANG.domain}</th>
                        <th>{$_LANG.dcv_method}</th>
                        <th>{$_LANG.status}</th>
                        <th>{$_LANG.actions}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $dcvStatus as $dcv}
                    <tr class="sslm-dcv-row">
                        <td>
                            <strong>{$dcv.domain}</strong>
                            <input type="hidden" name="dcvDomain" value="{$dcv.domain}">
                        </td>
                        <td>
                            <select name="dcvMethod" class="sslm-select" style="width: auto;">
                                {foreach $dcvMethods as $code => $method}
                                <option value="{$code}" {if $dcv.method == $code}selected{/if}>{$method.name}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td>
                            <span class="sslm-badge sslm-badge--{$dcv.statusClass}">{$dcv.statusText}</span>
                        </td>
                        <td>
                            {if !$dcv.isVerified}
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    onclick="SSLManager.openModal('dcvModal_{$dcv@index}')">
                                {$_LANG.dcv_instructions}
                            </button>
                            {/if}
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>

            <div class="sslm-action-bar">
                <button type="button" class="sslm-btn sslm-btn--primary" data-action="updateDCV">
                    {$_LANG.update_dcv}
                </button>
            </div>
            {else}
            <p class="sslm-help-text">{$_LANG.please_wait}</p>
            {/if}
        </div>
    </div>

    <!-- DCV Instructions -->
    {if $applyReturn}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.dcv_instructions}</h3>
        </div>
        <div class="sslm-card__body">
            {if $applyReturn.DCVfileName}
            <div class="sslm-form-section">
                <h4>HTTP/HTTPS File Validation</h4>
                <table class="sslm-table sslm-dcv-table">
                    <tr>
                        <th>{$_LANG.file_path}</th>
                        <td>
                            <code>/.well-known/pki-validation/{$applyReturn.DCVfileName}</code>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="/.well-known/pki-validation/{$applyReturn.DCVfileName}" 
                                    style="margin-left: 8px;">
                                {$_LANG.copy}
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>{$_LANG.file_content}</th>
                        <td>
                            <code>{$applyReturn.DCVfileContent}</code>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="{$applyReturn.DCVfileContent}" 
                                    style="margin-left: 8px;">
                                {$_LANG.copy}
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}

            {if $applyReturn.DCVdnsHost}
            <div class="sslm-form-section">
                <h4>DNS Validation</h4>
                <table class="sslm-table sslm-dcv-table">
                    <tr>
                        <th>{$_LANG.dns_type}</th>
                        <td><code>{$applyReturn.DCVdnsType|default:'CNAME'}</code></td>
                    </tr>
                    <tr>
                        <th>{$_LANG.dns_host}</th>
                        <td>
                            <code>{$applyReturn.DCVdnsHost}</code>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="{$applyReturn.DCVdnsHost}" 
                                    style="margin-left: 8px;">
                                {$_LANG.copy}
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>{$_LANG.dns_value}</th>
                        <td>
                            <code>{$applyReturn.DCVdnsValue}</code>
                            <button type="button" class="sslm-btn sslm-btn--secondary sslm-btn--sm" 
                                    data-copy="{$applyReturn.DCVdnsValue}" 
                                    style="margin-left: 8px;">
                                {$_LANG.copy}
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
            {/if}
        </div>
    </div>
    {/if}

    <!-- Quick Actions -->
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.actions}</h3>
        </div>
        <div class="sslm-card__body">
            <div class="sslm-action-bar sslm-action-bar--centered" style="border: none; margin: 0; padding: 0;">
                <button type="button" class="sslm-btn sslm-btn--primary" data-action="refreshStatus">
                    🔄 {$_LANG.refresh_status}
                </button>
                <button type="button" class="sslm-btn sslm-btn--danger" data-action="cancelOrder">
                    ❌ {$_LANG.cancel_order}
                </button>
            </div>
        </div>
    </div>

    {if $lastRefresh}
    <p class="sslm-help-text" style="text-align: center; margin-top: 20px;">
        {$_LANG.last_refresh}: {$lastRefresh}
    </p>
    {/if}
</div>

<script src="{$WEB_ROOT}/{$MODULE_PATH}/assets/js/ssl-manager.js"></script>