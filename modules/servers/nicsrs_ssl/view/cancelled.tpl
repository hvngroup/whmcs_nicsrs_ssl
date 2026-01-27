{**
 * NicSRS SSL Module - Cancelled/Revoked/Expired Template
 * Shows certificate status for non-active certificates
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/css/ssl-manager.css">

<div class="sslm-container">
    {* Configuration for JavaScript *}
    <script type="application/json" id="sslmConfig">{literal}{{/literal}
        "serviceId": {$serviceid},
        "ajaxUrl": "clientarea.php?action=productdetails&id={$serviceid}",
        "lang": {if $lang_json}{$lang_json nofilter}{else}{ldelim}{rdelim}{/if}
    {literal}}{/literal}</script>

    {* Status Banner - Different colors based on status *}
    {if $order->status == 'Cancelled' || $order->status == 'cancelled'}
    <div class="sslm-alert sslm-alert--warning">
        <i>🚫</i>
        <div>
            <strong>{$_LANG.order_cancelled|default:'Order Cancelled'}</strong>
            <p style="margin: 5px 0 0 0;">{$_LANG.cancelled_desc|default:'This certificate order has been cancelled and is no longer active.'}</p>
        </div>
    </div>
    
    {elseif $order->status == 'Revoked' || $order->status == 'revoked'}
    <div class="sslm-alert sslm-alert--danger">
        <i>⛔</i>
        <div>
            <strong>{$_LANG.certificate_revoked|default:'Certificate Revoked'}</strong>
            <p style="margin: 5px 0 0 0;">{$_LANG.revoked_desc|default:'This certificate has been revoked and is no longer trusted by browsers.'}</p>
        </div>
    </div>
    
    {elseif $order->status == 'Expired' || $order->status == 'expired'}
    <div class="sslm-alert sslm-alert--danger">
        <i>⏰</i>
        <div>
            <strong>{$_LANG.certificate_expired|default:'Certificate Expired'}</strong>
            <p style="margin: 5px 0 0 0;">{$_LANG.expired_desc|default:'This certificate has expired and is no longer valid.'}</p>
        </div>
    </div>
    
    {elseif $order->status == 'Rejected' || $order->status == 'rejected'}
    <div class="sslm-alert sslm-alert--danger">
        <i>❌</i>
        <div>
            <strong>{$_LANG.request_rejected|default:'Request Rejected'}</strong>
            <p style="margin: 5px 0 0 0;">{$_LANG.rejected_desc|default:'Your certificate request has been rejected by the Certificate Authority.'}</p>
        </div>
    </div>
    
    {else}
    <div class="sslm-alert sslm-alert--info">
        <i>ℹ️</i>
        <div>
            <strong>{$_LANG.certificate_status|default:'Certificate Status'}</strong>
            <p style="margin: 5px 0 0 0;">{$statusMessage|default:'Certificate is not active.'}</p>
        </div>
    </div>
    {/if}

    {* Order Details Card *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>{$_LANG.order_details|default:'Order Details'}</h3>
            <span class="sslm-badge sslm-badge--{if $order->status == 'Cancelled'}warning{elseif $order->status == 'Revoked' || $order->status == 'Expired' || $order->status == 'Rejected'}danger{else}default{/if}">
                {$order->status|escape:'html'}
            </span>
        </div>
        <div class="sslm-card__body">
            <table class="sslm-table sslm-table--simple">
                <tbody>
                    <tr>
                        <th style="width: 180px;">{$_LANG.order_id|default:'Order ID'}</th>
                        <td>#{$order->id}</td>
                    </tr>
                    {if $order->remoteid}
                    <tr>
                        <th>{$_LANG.certificate_id|default:'Certificate ID'}</th>
                        <td><code>{$order->remoteid|escape:'html'}</code></td>
                    </tr>
                    {/if}
                    <tr>
                        <th>{$_LANG.certificate_type|default:'Certificate Type'}</th>
                        <td>{$productCode|escape:'html'}</td>
                    </tr>
                    {if $primaryDomain}
                    <tr>
                        <th>{$_LANG.domain|default:'Domain'}</th>
                        <td><strong>{$primaryDomain|escape:'html'}</strong></td>
                    </tr>
                    {/if}
                    <tr>
                        <th>{$_LANG.status|default:'Status'}</th>
                        <td>
                            <span class="sslm-badge sslm-badge--{if $order->status == 'Cancelled'}warning{elseif $order->status == 'Revoked' || $order->status == 'Expired' || $order->status == 'Rejected'}danger{else}default{/if}">
                                {$order->status|escape:'html'}
                            </span>
                        </td>
                    </tr>
                    {if $order->provisiondate && $order->provisiondate != '0000-00-00'}
                    <tr>
                        <th>{$_LANG.order_date|default:'Order Date'}</th>
                        <td>{$order->provisiondate|escape:'html'}</td>
                    </tr>
                    {/if}
                    {if $order->completiondate && $order->completiondate != '0000-00-00 00:00:00'}
                    <tr>
                        <th>{$_LANG.completion_date|default:'Completion Date'}</th>
                        <td>{$order->completiondate|escape:'html'}</td>
                    </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>

    {* Cancellation/Revocation Reason *}
    {if $configdata.cancel_reason || $configdata.revoke_reason || $configdata.reject_reason}
    <div class="sslm-card sslm-card--warning">
        <div class="sslm-card__header">
            <h3>📝 {$_LANG.reason|default:'Reason'}</h3>
        </div>
        <div class="sslm-card__body">
            {if $configdata.cancel_reason}
            <div class="sslm-info-box">
                <strong>{$_LANG.cancellation_reason|default:'Cancellation Reason'}:</strong>
                <p style="margin: 8px 0 0 0;">{$configdata.cancel_reason|escape:'html'}</p>
            </div>
            {/if}
            
            {if $configdata.revoke_reason}
            <div class="sslm-info-box">
                <strong>{$_LANG.revocation_reason|default:'Revocation Reason'}:</strong>
                <p style="margin: 8px 0 0 0;">{$configdata.revoke_reason|escape:'html'}</p>
            </div>
            {/if}
            
            {if $configdata.reject_reason}
            <div class="sslm-info-box">
                <strong>{$_LANG.rejection_reason|default:'Rejection Reason'}:</strong>
                <p style="margin: 8px 0 0 0;">{$configdata.reject_reason|escape:'html'}</p>
            </div>
            {/if}
        </div>
    </div>
    {/if}

    {* Next Steps / Actions *}
    {if $canReapply}
    <div class="sslm-card sslm-card--success">
        <div class="sslm-card__header">
            <h3>✨ {$_LANG.next_steps|default:'Next Steps'}</h3>
        </div>
        <div class="sslm-card__body">
            <p class="sslm-help-text">{$_LANG.reapply_message|default:'Your service is still active. You can apply for a new certificate to replace this one.'}</p>
            
            <div class="sslm-action-bar" style="border: none; margin: 16px 0 0 0; padding: 0;">
                <button type="button" class="sslm-btn sslm-btn--success" data-action="reapply">
                    🎫 {$_LANG.apply_new_certificate|default:'Apply for New Certificate'}
                </button>
            </div>
        </div>
    </div>
    {/if}

    {* Help & Support *}
    <div class="sslm-card">
        <div class="sslm-card__header">
            <h3>💬 {$_LANG.need_help|default:'Need Help?'}</h3>
        </div>
        <div class="sslm-card__body">
            <p class="sslm-help-text">{$_LANG.contact_support_message|default:'If you have any questions about this certificate or need assistance, please contact our support team.'}</p>
            
            <div class="sslm-action-bar" style="border: none; margin: 16px 0 0 0; padding: 0; justify-content: flex-start;">
                <a href="clientarea.php?action=productdetails&id={$serviceid}" class="sslm-btn sslm-btn--secondary">
                    ← {$_LANG.back|default:'Back to Overview'}
                </a>
                <a href="submitticket.php" class="sslm-btn sslm-btn--secondary">
                    📧 {$_LANG.contact_support|default:'Contact Support'}
                </a>
            </div>
        </div>
    </div>
</div>

<script src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/assets/js/ssl-manager.js"></script>