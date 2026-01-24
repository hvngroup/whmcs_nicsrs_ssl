/**
 * NicSRS SSL Client Area JavaScript
 * Handles client-side interactions for SSL certificate management
 * 
 * @package    WHMCS
 * @author     HVN GROUP
 * @version    2.0.0
 */

(function(window, $) {
    'use strict';

    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.error('NicSRS SSL: jQuery is required');
        return;
    }

    /**
     * NicSRS SSL Module
     */
    var NicsrsSSL = {
        
        // Configuration
        config: {
            serviceId: null,
            baseUrl: '',
            dcvInstructions: [],
            lang: {
                refreshing: 'Refreshing...',
                saving: 'Saving...',
                downloading: 'Preparing download...',
                success: 'Success',
                error: 'Error',
                copied: 'Copied to clipboard!',
                confirm_reissue: 'Are you sure you want to reissue this certificate?'
            }
        },

        /**
         * Initialize the module
         * @param {Object} options Configuration options
         */
        init: function(options) {
            this.config = $.extend(true, this.config, options);
            this.bindEvents();
            console.log('NicSRS SSL initialized for service:', this.config.serviceId);
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;

            // DCV method change handler
            $(document).on('change', '.dcv-method-select', function() {
                var domain = $(this).data('domain');
                var method = $(this).val();
                self.onDcvMethodChange(domain, method);
            });

            // Form submissions
            $(document).on('submit', '#reissueForm', function(e) {
                e.preventDefault();
                self.submitReissue();
            });
        },

        // ========================================
        // AJAX Helpers
        // ========================================

        /**
         * Make AJAX request
         * @param {string} action Action name
         * @param {Object} data Request data
         * @param {Function} callback Success callback
         * @param {Function} errorCallback Error callback
         */
        ajax: function(action, data, callback, errorCallback) {
            var self = this;
            
            data = data || {};
            data.step = action;
            data.serviceid = this.config.serviceId;

            $.ajax({
                url: this.config.baseUrl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 1 || response.status === '1') {
                        if (typeof callback === 'function') {
                            callback(response);
                        }
                    } else {
                        var msg = response.msg || response.error || 'Unknown error';
                        if (Array.isArray(response.error)) {
                            msg = response.error.join(', ');
                        }
                        self.showError(msg);
                        if (typeof errorCallback === 'function') {
                            errorCallback(response);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Network error: ' + error);
                    if (typeof errorCallback === 'function') {
                        errorCallback({error: error});
                    }
                }
            });
        },

        // ========================================
        // Certificate Actions
        // ========================================

        /**
         * Refresh certificate status
         */
        refreshStatus: function() {
            var self = this;
            var btn = $('#btnRefresh, #btnRefreshStatus');
            var originalHtml = btn.html();

            btn.prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin"></i> ' + this.config.lang.refreshing);

            this.ajax('refreshStatus', {}, function(response) {
                self.showSuccess('Status refreshed');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }, function() {
                btn.prop('disabled', false).html(originalHtml);
            });
        },

        /**
         * Show download options modal
         */
        showDownloadOptions: function() {
            $('#downloadModal').modal('show');
        },

        /**
         * Download certificate
         */
        downloadCertificate: function() {
            var self = this;
            var btn = $('#downloadModal .btn-success');
            var originalHtml = btn.html();

            btn.prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin"></i> ' + this.config.lang.downloading);

            this.ajax('downCert', {}, function(response) {
                if (response.data && response.data.downloadUrl) {
                    window.location.href = response.data.downloadUrl;
                }
                $('#downloadModal').modal('hide');
                btn.prop('disabled', false).html(originalHtml);
            }, function() {
                btn.prop('disabled', false).html(originalHtml);
            });
        },

        /**
         * Show reissue modal
         */
        showReissueModal: function() {
            $('#reissueForm')[0].reset();
            $('#reissueModal').modal('show');
        },

        /**
         * Submit reissue request
         */
        submitReissue: function() {
            var self = this;
            var csr = $('#reissueCsr').val().trim();
            var privateKey = $('#reissuePrivateKey').val().trim();

            if (!csr) {
                this.showError('CSR is required');
                return;
            }

            if (!confirm(this.config.lang.confirm_reissue)) {
                return;
            }

            var btn = $('#reissueModal .btn-warning');
            var originalHtml = btn.html();

            btn.prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            var data = {
                data: JSON.stringify({
                    csr: csr,
                    privateKey: privateKey
                })
            };

            this.ajax('reissueCertificate', data, function(response) {
                self.showSuccess('Reissue request submitted successfully');
                $('#reissueModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }, function() {
                btn.prop('disabled', false).html(originalHtml);
            });
        },

        // ========================================
        // DCV Actions
        // ========================================

        /**
         * Handle DCV method change
         * @param {string} domain Domain name
         * @param {string} method New DCV method
         */
        onDcvMethodChange: function(domain, method) {
            // If EMAIL selected, show email options
            if (method === 'EMAIL') {
                this.loadDcvEmails(domain);
            }
        },

        /**
         * Load DCV email options
         * @param {string} domain Domain name
         */
        loadDcvEmails: function(domain) {
            var self = this;

            this.ajax('getDcvEmails', {domain: domain}, function(response) {
                if (response.data && response.data.emails) {
                    self.showEmailOptions(domain, response.data.emails);
                }
            });
        },

        /**
         * Show email options for domain
         * @param {string} domain Domain name
         * @param {Array} emails Available emails
         */
        showEmailOptions: function(domain, emails) {
            var select = $('.dcv-method-select[data-domain="' + domain + '"]');
            var container = select.parent();
            
            // Remove existing email select
            container.find('.dcv-email-select').remove();

            if (emails.length > 0) {
                var emailSelect = $('<select class="form-control input-sm dcv-email-select" style="margin-top: 5px;">');
                emailSelect.append('<option value="">Select email...</option>');
                
                $.each(emails, function(i, email) {
                    emailSelect.append('<option value="' + email + '">' + email + '</option>');
                });

                container.append(emailSelect);
            }
        },

        /**
         * Save DCV method changes
         */
        saveDcvMethods: function() {
            var self = this;
            var domainInfo = [];

            $('#dcvTable tbody tr').each(function() {
                var domain = $(this).data('domain');
                var method = $(this).find('.dcv-method-select').val();
                var email = $(this).find('.dcv-email-select').val() || '';

                if (domain && method) {
                    domainInfo.push({
                        domainName: domain,
                        dcvMethod: method === 'EMAIL' && email ? email : method
                    });
                }
            });

            if (domainInfo.length === 0) {
                this.showError('No domains to update');
                return;
            }

            var btn = $('#btnSaveDcv');
            var originalHtml = btn.html();

            btn.prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin"></i> ' + this.config.lang.saving);

            this.ajax('batchUpdateDCV', {
                data: JSON.stringify({domainInfo: domainInfo})
            }, function(response) {
                self.showSuccess('DCV methods updated successfully');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }, function() {
                btn.prop('disabled', false).html(originalHtml);
            });
        },

        /**
         * Show DCV details modal
         * @param {string} domain Domain name
         */
        showDcvDetails: function(domain) {
            var instructions = this.config.dcvInstructions || [];
            var dcv = null;

            for (var i = 0; i < instructions.length; i++) {
                if (instructions[i].domain === domain) {
                    dcv = instructions[i];
                    break;
                }
            }

            if (!dcv) {
                this.showError('DCV details not found');
                return;
            }

            var content = '<h5>' + domain + '</h5>';
            content += '<p><strong>Method:</strong> ' + dcv.methodName + '</p>';
            
            if (dcv.details) {
                if (dcv.method === 'EMAIL') {
                    content += '<p><strong>Email:</strong> ' + (dcv.details.email || 'Not selected') + '</p>';
                } else if (dcv.method.indexOf('HTTP') !== -1) {
                    content += '<p><strong>File Path:</strong><br><code>' + (dcv.details.filePath || '') + '</code></p>';
                    content += '<p><strong>File Content:</strong><br><code>' + (dcv.details.fileContent || '') + '</code></p>';
                } else if (dcv.method.indexOf('DNS') !== -1 || dcv.method.indexOf('CNAME') !== -1) {
                    content += '<p><strong>DNS Host:</strong><br><code>' + (dcv.details.dnsHost || '') + '</code></p>';
                    content += '<p><strong>DNS Value:</strong><br><code>' + (dcv.details.dnsValue || '') + '</code></p>';
                }
            }

            $('#dcvDetailsContent').html(content);
            $('#dcvDetailsModal').modal('show');
        },

        // ========================================
        // Cancel/Revoke Actions
        // ========================================

        /**
         * Show cancel confirmation
         */
        confirmCancel: function() {
            $('#cancelModal').modal('show');
        },

        /**
         * Cancel certificate order
         */
        cancelOrder: function() {
            var self = this;
            var reason = $('#cancelReason').val().trim();
            var btn = $('#cancelModal .btn-danger');
            var originalHtml = btn.html();

            btn.prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin"></i> Cancelling...');

            this.ajax('cancelOrder', {reason: reason}, function(response) {
                self.showSuccess('Order cancelled successfully');
                $('#cancelModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }, function() {
                btn.prop('disabled', false).html(originalHtml);
            });
        },

        // ========================================
        // Utility Functions
        // ========================================

        /**
         * Copy text to clipboard
         * @param {string} text Text to copy
         */
        copyToClipboard: function(text) {
            var self = this;
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    self.showSuccess(self.config.lang.copied);
                }).catch(function() {
                    self.fallbackCopy(text);
                });
            } else {
                this.fallbackCopy(text);
            }
        },

        /**
         * Fallback copy method
         * @param {string} text Text to copy
         */
        fallbackCopy: function(text) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                this.showSuccess(this.config.lang.copied);
            } catch (err) {
                this.showError('Failed to copy');
            }
            
            document.body.removeChild(textarea);
        },

        /**
         * Show success message
         * @param {string} message Message to display
         */
        showSuccess: function(message) {
            this.showAlert(message, 'success');
        },

        /**
         * Show error message
         * @param {string} message Message to display
         */
        showError: function(message) {
            this.showAlert(message, 'danger');
        },

        /**
         * Show alert message
         * @param {string} message Message to display
         * @param {string} type Alert type (success, danger, warning, info)
         */
        showAlert: function(message, type) {
            var container = $('.nicsrs-alert-container');
            
            if (container.length === 0) {
                container = $('<div class="nicsrs-alert-container"></div>');
                $('body').append(container);
            }

            var icon = type === 'success' ? 'check-circle' : 
                       type === 'danger' ? 'exclamation-circle' : 
                       type === 'warning' ? 'exclamation-triangle' : 'info-circle';

            var alert = $('<div class="alert alert-' + type + ' alert-dismissible">' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                '<i class="fa fa-' + icon + '"></i> ' + message +
                '</div>');

            container.append(alert);

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                alert.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Export to global scope
    window.NicsrsSSL = NicsrsSSL;

})(window, jQuery);