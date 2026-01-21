/**
 * NicSRS SSL Admin - Main JavaScript
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 * @version    1.2.0
 */

(function($) {
    'use strict';

    // Namespace
    window.NicsrsAdmin = window.NicsrsAdmin || {};

    // =========================================================================
    // CORE UTILITY FUNCTIONS
    // =========================================================================

    /**
     * Show loading overlay
     * @param {string} message Loading message
     */
    NicsrsAdmin.showLoading = function(message) {
        message = message || 'Loading...';
        
        if ($('#nicsrs-loading').length === 0) {
            $('body').append(
                '<div id="nicsrs-loading" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;">' +
                '<div style="text-align:center;">' +
                '<i class="fa fa-spinner fa-spin fa-3x text-primary"></i>' +
                '<p style="margin-top:15px;" class="loading-message">' + message + '</p>' +
                '</div></div>'
            );
        } else {
            $('#nicsrs-loading .loading-message').text(message);
            $('#nicsrs-loading').show();
        }
    };

    /**
     * Hide loading overlay
     */
    NicsrsAdmin.hideLoading = function() {
        $('#nicsrs-loading').hide();
    };

    /**
     * Show toast notification
     * @param {string} message Message to display
     * @param {string} type Type: success, error, warning, info
     * @param {number} duration Duration in ms
     */
    NicsrsAdmin.toast = function(message, type, duration) {
        type = type || 'info';
        duration = duration || 3000;

        var icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        };

        var colors = {
            success: '#52c41a',
            error: '#ff4d4f',
            warning: '#faad14',
            info: '#1890ff'
        };

        var $toast = $(
            '<div class="nicsrs-toast" style="position:fixed;top:20px;right:20px;z-index:9999;background:#fff;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,0.15);padding:16px 20px;display:flex;align-items:center;max-width:400px;animation:slideIn 0.3s ease;">' +
            '<i class="fa ' + icons[type] + '" style="color:' + colors[type] + ';font-size:20px;margin-right:12px;"></i>' +
            '<span>' + NicsrsAdmin.escapeHtml(message) + '</span>' +
            '</div>'
        );

        // Add animation style
        if ($('#nicsrs-toast-style').length === 0) {
            $('head').append(
                '<style id="nicsrs-toast-style">' +
                '@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }' +
                '</style>'
            );
        }

        $('body').append($toast);

        setTimeout(function() {
            $toast.css({
                animation: 'slideIn 0.3s ease reverse',
                animationFillMode: 'forwards'
            });
            setTimeout(function() {
                $toast.remove();
            }, 300);
        }, duration);
    };

    /**
     * Confirm dialog
     * @param {string} message Confirmation message
     * @param {function} onConfirm Callback on confirm
     * @param {function} onCancel Callback on cancel
     */
    NicsrsAdmin.confirm = function(message, onConfirm, onCancel) {
        if ($('#nicsrs-confirm-modal').length === 0) {
            $('body').append(
                '<div class="modal fade" id="nicsrs-confirm-modal" tabindex="-1">' +
                '<div class="modal-dialog modal-sm">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
                '<h4 class="modal-title">Confirm</h4>' +
                '</div>' +
                '<div class="modal-body"><p class="confirm-message"></p></div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' +
                '<button type="button" class="btn btn-primary confirm-yes">Yes</button>' +
                '</div></div></div></div>'
            );
        }

        var $modal = $('#nicsrs-confirm-modal');
        $modal.find('.confirm-message').text(message);
        
        $modal.find('.confirm-yes').off('click').on('click', function() {
            $modal.modal('hide');
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });

        $modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
            if (typeof onCancel === 'function') {
                onCancel();
            }
        });

        $modal.modal('show');
    };

    /**
     * AJAX request wrapper
     * @param {object} options Request options
     */
    NicsrsAdmin.ajax = function(options) {
        var defaults = {
            method: 'POST',
            dataType: 'json',
            beforeSend: function() {
                if (options.loading !== false) {
                    NicsrsAdmin.showLoading(options.loadingMessage || 'Processing...');
                }
            },
            complete: function() {
                NicsrsAdmin.hideLoading();
            },
            success: function(response) {
                if (response.status === 1 || response.success) {
                    if (options.successMessage !== false) {
                        NicsrsAdmin.toast(response.msg || response.message || options.successMessage || 'Success', 'success');
                    }
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess(response);
                    }
                } else {
                    NicsrsAdmin.toast(response.error || response.message || 'An error occurred', 'error');
                    if (typeof options.onError === 'function') {
                        options.onError(response);
                    }
                }
            },
            error: function(xhr, status, error) {
                NicsrsAdmin.toast('Request failed: ' + error, 'error');
                if (typeof options.onError === 'function') {
                    options.onError({ success: false, message: error });
                }
            }
        };

        $.ajax($.extend({}, defaults, options));
    };

    /**
     * Format number with commas
     * @param {number} num Number to format
     * @returns {string} Formatted number
     */
    NicsrsAdmin.formatNumber = function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    /**
     * Escape HTML to prevent XSS
     * @param {string} text Text to escape
     * @returns {string} Escaped text
     */
    NicsrsAdmin.escapeHtml = function(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    /**
     * Copy text to clipboard
     * @param {string} text Text to copy
     * @param {boolean} showToast Show success toast
     */
    NicsrsAdmin.copyToClipboard = function(text, showToast) {
        showToast = showToast !== false;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (showToast) {
                    NicsrsAdmin.toast('Copied to clipboard', 'success', 1500);
                }
            }).catch(function() {
                NicsrsAdmin.fallbackCopy(text, showToast);
            });
        } else {
            NicsrsAdmin.fallbackCopy(text, showToast);
        }
    };

    /**
     * Fallback copy method for older browsers
     * @param {string} text Text to copy
     * @param {boolean} showToast Show success toast
     */
    NicsrsAdmin.fallbackCopy = function(text, showToast) {
        var $temp = $('<textarea>');
        $temp.css({
            position: 'fixed',
            opacity: 0
        });
        $('body').append($temp);
        $temp.val(text).select();
        
        try {
            document.execCommand('copy');
            if (showToast) {
                NicsrsAdmin.toast('Copied to clipboard', 'success', 1500);
            }
        } catch (err) {
            NicsrsAdmin.toast('Failed to copy', 'error');
        }
        
        $temp.remove();
    };

    /**
     * Initialize tooltips
     */
    NicsrsAdmin.initTooltips = function() {
        $('[data-toggle="tooltip"]').tooltip();
    };

    /**
     * Initialize popovers
     */
    NicsrsAdmin.initPopovers = function() {
        $('[data-toggle="popover"]').popover();
    };

    // =========================================================================
    // ORDER DETAIL PAGE FUNCTIONS
    // =========================================================================

    /**
     * Initialize order detail page
     */
    NicsrsAdmin.initOrderDetail = function() {
        if (!$('.nicsrs-order-detail').length) return;

        var orderId = $('#orderId').val() || 0;
        var moduleLink = $('#moduleLink').val() || '';

        // Store in namespace for access
        NicsrsAdmin.orderDetail = {
            orderId: orderId,
            moduleLink: moduleLink
        };

        // Bind all handlers
        NicsrsAdmin.bindOrderActions();
        NicsrsAdmin.bindDownloadButtons();
        NicsrsAdmin.bindCopyButtons();
        NicsrsAdmin.bindResendDcvButtons();
    };

    /**
     * Bind order action buttons (refresh, cancel, revoke, etc.)
     */
    NicsrsAdmin.bindOrderActions = function() {
        $('.btn-action').off('click').on('click', function() {
            var $btn = $(this);
            var action = $btn.data('action');
            
            if ($btn.prop('disabled')) return;
            
            switch (action) {
                case 'refresh_status':
                    NicsrsAdmin.refreshOrderStatus($btn);
                    break;
                case 'cancel':
                    NicsrsAdmin.confirmOrderAction('cancel', 'Cancel Order', 
                        'Are you sure you want to cancel this order?', $btn, true);
                    break;
                case 'revoke':
                    NicsrsAdmin.confirmOrderAction('revoke', 'Revoke Certificate', 
                        'Are you sure you want to revoke this certificate? This action cannot be undone.', $btn, true);
                    break;
                case 'reissue':
                    NicsrsAdmin.confirmOrderAction('reissue', 'Reissue Certificate', 
                        'Are you sure you want to reissue this certificate?', $btn, false);
                    break;
                case 'renew':
                    NicsrsAdmin.confirmOrderAction('renew', 'Renew Certificate', 
                        'Are you sure you want to renew this certificate?', $btn, false);
                    break;
            }
        });
    };

    /**
     * Refresh order status from API
     * @param {jQuery} $btn Button element
     */
    NicsrsAdmin.refreshOrderStatus = function($btn) {
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Refreshing...');
        
        $.ajax({
            url: NicsrsAdmin.orderDetail.moduleLink,
            type: 'POST',
            data: {
                action: 'orders',
                ajax_action: 'refresh_status',
                order_id: NicsrsAdmin.orderDetail.orderId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 1) {
                    NicsrsAdmin.toast('Status refreshed successfully', 'success');
                    
                    // Reload page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    NicsrsAdmin.toast(response.error || 'Failed to refresh status', 'error');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                NicsrsAdmin.toast('Network error occurred', 'error');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    };

    /**
     * Confirm and execute order action
     * @param {string} action Action name
     * @param {string} title Dialog title
     * @param {string} message Confirmation message
     * @param {jQuery} $btn Button element
     * @param {boolean} requireReason Require reason input
     */
    NicsrsAdmin.confirmOrderAction = function(action, title, message, $btn, requireReason) {
        if (!confirm(title + '\n\n' + message)) return;
        
        var reason = '';
        if (requireReason) {
            reason = prompt('Please enter a reason (optional):') || '';
        }
        
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: NicsrsAdmin.orderDetail.moduleLink,
            type: 'POST',
            data: {
                action: 'orders',
                ajax_action: action,
                order_id: NicsrsAdmin.orderDetail.orderId,
                reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 1) {
                    NicsrsAdmin.toast(response.msg || 'Action completed successfully', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    NicsrsAdmin.toast(response.error || 'Action failed', 'error');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                NicsrsAdmin.toast('Network error occurred', 'error');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    };

    /**
     * Bind download buttons
     */
    NicsrsAdmin.bindDownloadButtons = function() {
        // Download CSR
        $('.btn-download-csr').off('click').on('click', function() {
            var csrData = $('#csrData').val();
            if (!csrData) {
                NicsrsAdmin.toast('CSR not available', 'error');
                return;
            }
            
            try {
                var content = atob(csrData);
                NicsrsAdmin.downloadFile(content, 'certificate.csr', 'application/x-pem-file');
                NicsrsAdmin.toast('CSR download started', 'success');
            } catch (e) {
                NicsrsAdmin.toast('Failed to decode CSR', 'error');
            }
        });
        
        // Download Certificate (various formats)
        $('.btn-download-cert').off('click').on('click', function() {
            var $btn = $(this);
            var format = $btn.data('format') || 'zip';
            
            NicsrsAdmin.downloadCertificate(format, $btn);
        });
    };

    /**
     * Download certificate in specified format
     * @param {string} format Format: zip, jks, pkcs12, pem
     * @param {jQuery} $btn Button element
     */
    NicsrsAdmin.downloadCertificate = function(format, $btn) {
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Preparing...');
        
        $.ajax({
            url: NicsrsAdmin.orderDetail.moduleLink,
            type: 'POST',
            data: {
                action: 'orders',
                ajax_action: 'download_cert',
                order_id: NicsrsAdmin.orderDetail.orderId,
                format: format
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 1 && response.data) {
                    var data = response.data;
                    
                    try {
                        // Decode and download
                        var content = atob(data.content);
                        var blob = new Blob([NicsrsAdmin.stringToArrayBuffer(content)], { type: data.mime });
                        var url = URL.createObjectURL(blob);
                        
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = data.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(url);
                        
                        // Show password notification for JKS/PKCS12
                        if (data.password) {
                            NicsrsAdmin.showPasswordModal(data.format, data.password);
                        } else if (data.jks_password || data.pkcs12_password) {
                            NicsrsAdmin.showPasswordsModal(data.jks_password, data.pkcs12_password);
                        }
                        
                        NicsrsAdmin.toast('Download started', 'success');
                    } catch (e) {
                        NicsrsAdmin.toast('Failed to process download: ' + e.message, 'error');
                    }
                } else {
                    NicsrsAdmin.toast(response.error || 'Download failed', 'error');
                }
                
                $btn.prop('disabled', false).html(originalHtml);
            },
            error: function() {
                NicsrsAdmin.toast('Network error occurred', 'error');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    };

    /**
     * Convert string to ArrayBuffer (for binary download)
     * @param {string} str String to convert
     * @returns {ArrayBuffer}
     */
    NicsrsAdmin.stringToArrayBuffer = function(str) {
        var buf = new ArrayBuffer(str.length);
        var bufView = new Uint8Array(buf);
        for (var i = 0; i < str.length; i++) {
            bufView[i] = str.charCodeAt(i);
        }
        return buf;
    };

    /**
     * Download file helper
     * @param {string} content File content
     * @param {string} filename Filename
     * @param {string} mimeType MIME type
     */
    NicsrsAdmin.downloadFile = function(content, filename, mimeType) {
        var blob = new Blob([content], { type: mimeType });
        var url = URL.createObjectURL(blob);
        
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    };

    /**
     * Show password modal for JKS/PKCS12 downloads
     * @param {string} format Format name (jks, pkcs12)
     * @param {string} password Password
     */
    NicsrsAdmin.showPasswordModal = function(format, password) {
        var formatName = format === 'jks' ? 'JKS (Tomcat)' : 'PKCS12/PFX (IIS)';
        var html = '<div class="modal fade" id="passwordModal" tabindex="-1">' +
            '<div class="modal-dialog modal-sm">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '<h4 class="modal-title"><i class="fa fa-key"></i> ' + formatName + ' Password</h4>' +
            '</div>' +
            '<div class="modal-body text-center">' +
            '<p>Use this password to import the certificate:</p>' +
            '<div class="input-group">' +
            '<input type="text" class="form-control text-center" value="' + NicsrsAdmin.escapeHtml(password) + '" readonly id="modalPassword">' +
            '<span class="input-group-btn">' +
            '<button class="btn btn-default" type="button" onclick="NicsrsAdmin.copyToClipboard($(\'#modalPassword\').val())"><i class="fa fa-copy"></i></button>' +
            '</span>' +
            '</div>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>' +
            '</div>' +
            '</div></div></div>';
        
        // Remove existing modal
        $('#passwordModal').remove();
        
        // Add and show modal
        $('body').append(html);
        $('#passwordModal').modal('show');
    };

    /**
     * Show passwords modal (for ZIP downloads with multiple formats)
     * @param {string} jksPassword JKS password
     * @param {string} pkcs12Password PKCS12 password
     */
    NicsrsAdmin.showPasswordsModal = function(jksPassword, pkcs12Password) {
        if (!jksPassword && !pkcs12Password) return;
        
        var html = '<div class="modal fade" id="passwordsModal" tabindex="-1">' +
            '<div class="modal-dialog modal-sm">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '<h4 class="modal-title"><i class="fa fa-key"></i> Certificate Passwords</h4>' +
            '</div>' +
            '<div class="modal-body">' +
            '<p>Use these passwords to import the certificates:</p>';
        
        if (jksPassword) {
            html += '<div class="form-group">' +
                '<label><i class="fa fa-coffee"></i> JKS (Tomcat):</label>' +
                '<div class="input-group">' +
                '<input type="text" class="form-control" value="' + NicsrsAdmin.escapeHtml(jksPassword) + '" readonly id="modalJksPassword">' +
                '<span class="input-group-btn">' +
                '<button class="btn btn-default" type="button" onclick="NicsrsAdmin.copyToClipboard($(\'#modalJksPassword\').val())"><i class="fa fa-copy"></i></button>' +
                '</span>' +
                '</div></div>';
        }
        
        if (pkcs12Password) {
            html += '<div class="form-group">' +
                '<label><i class="fa fa-windows"></i> PFX (IIS/Windows):</label>' +
                '<div class="input-group">' +
                '<input type="text" class="form-control" value="' + NicsrsAdmin.escapeHtml(pkcs12Password) + '" readonly id="modalPkcs12Password">' +
                '<span class="input-group-btn">' +
                '<button class="btn btn-default" type="button" onclick="NicsrsAdmin.copyToClipboard($(\'#modalPkcs12Password\').val())"><i class="fa fa-copy"></i></button>' +
                '</span>' +
                '</div></div>';
        }
        
        html += '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>' +
            '</div></div></div></div>';
        
        // Remove existing modal
        $('#passwordsModal').remove();
        
        // Add and show modal
        $('body').append(html);
        $('#passwordsModal').modal('show');
    };

    /**
     * Bind copy buttons
     */
    NicsrsAdmin.bindCopyButtons = function() {
        $('.btn-copy').off('click').on('click', function() {
            var targetId = $(this).data('target');
            var $el = $('#' + targetId);
            var text = $el.is('input, textarea') ? $el.val() : $el.text();
            NicsrsAdmin.copyToClipboard(text);
        });
    };

    /**
     * Bind resend DCV buttons
     */
    NicsrsAdmin.bindResendDcvButtons = function() {
        $('.btn-resend-dcv').off('click').on('click', function() {
            var $btn = $(this);
            var domain = $btn.data('domain');
            
            if (!confirm('Resend DCV validation email for ' + domain + '?')) return;
            
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: NicsrsAdmin.orderDetail.moduleLink,
                type: 'POST',
                data: {
                    action: 'orders',
                    ajax_action: 'resend_dcv',
                    order_id: NicsrsAdmin.orderDetail.orderId,
                    domain: domain
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1) {
                        NicsrsAdmin.toast('DCV email resent for ' + domain, 'success');
                    } else {
                        NicsrsAdmin.toast(response.error || 'Failed to resend DCV', 'error');
                    }
                    $btn.prop('disabled', false).html(originalHtml);
                },
                error: function() {
                    NicsrsAdmin.toast('Network error', 'error');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    };

    // =========================================================================
    // ORDERS LIST PAGE FUNCTIONS
    // =========================================================================

    /**
     * Initialize orders list page
     */
    NicsrsAdmin.initOrdersList = function() {
        if (!$('.nicsrs-orders-list').length) return;

        // Bind bulk actions
        NicsrsAdmin.bindBulkActions();
        
        // Bind inline actions
        NicsrsAdmin.bindInlineActions();
    };

    /**
     * Bind bulk action handlers
     */
    NicsrsAdmin.bindBulkActions = function() {
        $('#bulkActionBtn').off('click').on('click', function() {
            var action = $('#bulkAction').val();
            if (!action) {
                NicsrsAdmin.toast('Please select an action', 'warning');
                return;
            }
            
            var selectedIds = [];
            $('.order-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                NicsrsAdmin.toast('Please select at least one order', 'warning');
                return;
            }
            
            NicsrsAdmin.confirm(
                'Are you sure you want to ' + action + ' ' + selectedIds.length + ' order(s)?',
                function() {
                    NicsrsAdmin.executeBulkAction(action, selectedIds);
                }
            );
        });
    };

    /**
     * Execute bulk action
     * @param {string} action Action name
     * @param {array} orderIds Order IDs
     */
    NicsrsAdmin.executeBulkAction = function(action, orderIds) {
        NicsrsAdmin.showLoading('Processing ' + orderIds.length + ' orders...');
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                ajax_action: 'bulk_' + action,
                order_ids: orderIds
            },
            dataType: 'json',
            success: function(response) {
                NicsrsAdmin.hideLoading();
                if (response.status === 1) {
                    NicsrsAdmin.toast(response.msg || 'Bulk action completed', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    NicsrsAdmin.toast(response.error || 'Bulk action failed', 'error');
                }
            },
            error: function() {
                NicsrsAdmin.hideLoading();
                NicsrsAdmin.toast('Network error occurred', 'error');
            }
        });
    };

    /**
     * Bind inline action handlers (dropdown menu actions)
     */
    NicsrsAdmin.bindInlineActions = function() {
        $('.btn-inline-action').off('click').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var action = $btn.data('action');
            var orderId = $btn.data('order-id');
            
            switch (action) {
                case 'refresh':
                    NicsrsAdmin.inlineRefresh(orderId, $btn);
                    break;
                case 'cancel':
                case 'revoke':
                    NicsrsAdmin.inlineActionWithReason(action, orderId, $btn);
                    break;
            }
        });
    };

    /**
     * Inline refresh status
     * @param {int} orderId Order ID
     * @param {jQuery} $btn Button element
     */
    NicsrsAdmin.inlineRefresh = function(orderId, $btn) {
        var $row = $btn.closest('tr');
        var $statusCell = $row.find('.status-cell');
        var originalStatus = $statusCell.html();
        
        $statusCell.html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                ajax_action: 'refresh_status',
                order_id: orderId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 1) {
                    NicsrsAdmin.toast('Status refreshed', 'success');
                    location.reload();
                } else {
                    NicsrsAdmin.toast(response.error || 'Refresh failed', 'error');
                    $statusCell.html(originalStatus);
                }
            },
            error: function() {
                NicsrsAdmin.toast('Network error', 'error');
                $statusCell.html(originalStatus);
            }
        });
    };

    /**
     * Inline action with reason
     * @param {string} action Action name
     * @param {int} orderId Order ID
     * @param {jQuery} $btn Button element
     */
    NicsrsAdmin.inlineActionWithReason = function(action, orderId, $btn) {
        var actionName = action.charAt(0).toUpperCase() + action.slice(1);
        
        if (!confirm(actionName + ' this order?')) return;
        
        var reason = prompt('Please enter a reason (optional):') || '';
        
        NicsrsAdmin.showLoading('Processing...');
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                ajax_action: action,
                order_id: orderId,
                reason: reason
            },
            dataType: 'json',
            success: function(response) {
                NicsrsAdmin.hideLoading();
                if (response.status === 1) {
                    NicsrsAdmin.toast(actionName + ' successful', 'success');
                    location.reload();
                } else {
                    NicsrsAdmin.toast(response.error || actionName + ' failed', 'error');
                }
            },
            error: function() {
                NicsrsAdmin.hideLoading();
                NicsrsAdmin.toast('Network error', 'error');
            }
        });
    };

    // =========================================================================
    // DOCUMENT READY - INITIALIZATION
    // =========================================================================

    $(document).ready(function() {
        // Core initializations
        NicsrsAdmin.initTooltips();
        NicsrsAdmin.initPopovers();

        // Page-specific initializations
        NicsrsAdmin.initOrderDetail();
        NicsrsAdmin.initOrdersList();

        // Auto-hide alerts after 5 seconds
        $('.nicsrs-content .alert').not('.alert-permanent').delay(5000).fadeOut(500);

        // Confirm for dangerous actions
        $(document).on('click', '[data-confirm]', function(e) {
            var message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Copy to clipboard (data attribute)
        $(document).on('click', '[data-copy]', function() {
            var text = $(this).data('copy');
            NicsrsAdmin.copyToClipboard(text);
        });

        // Auto-submit form on filter change
        $(document).on('change', '.auto-submit', function() {
            $(this).closest('form').submit();
        });

        // Select all checkboxes
        $(document).on('change', '.select-all', function() {
            var isChecked = $(this).prop('checked');
            $(this).closest('table').find('tbody input[type="checkbox"]').prop('checked', isChecked);
        });

        // Disable submit button on form submit to prevent double submit
        $(document).on('submit', 'form', function() {
            $(this).find('button[type="submit"]').prop('disabled', true);
        });

        // Collapsible panels toggle icon
        $(document).on('click', '[data-toggle="collapse"]', function() {
            var $icon = $(this).find('.fa-chevron-down, .fa-chevron-up');
            if ($icon.length) {
                $icon.toggleClass('fa-chevron-down fa-chevron-up');
            }
        });
    });

})(jQuery);