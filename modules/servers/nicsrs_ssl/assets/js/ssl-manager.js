/**
 * SSL Manager - Client Area JavaScript
 * Vendor Neutral Implementation
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

(function() {
    'use strict';

    // ==========================================
    // Global SSLManager Object
    // ==========================================
    window.SSLManager = {
        config: {
            serviceId: null,
            ajaxUrl: '',
            lang: {}
        },

        /**
         * Initialize the manager
         */
        init: function(config) {
            this.config = Object.assign(this.config, config || {});
            this.bindEvents();
            this.initCSRToggle();
            this.initDomainInputs();
        },

        /**
         * Bind global events
         */
        bindEvents: function() {
            // Form submission
            document.querySelectorAll('[data-action]').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    var action = this.getAttribute('data-action');
                    SSLManager.handleAction(action, this);
                });
            });

            // Copy to clipboard buttons
            document.querySelectorAll('[data-copy]').forEach(function(el) {
                el.addEventListener('click', function() {
                    SSLManager.copyToClipboard(this.getAttribute('data-copy'));
                });
            });
        },

        // ==========================================
        // CSR Management
        // ==========================================
        initCSRToggle: function() {
            var csrModeInputs = document.querySelectorAll('input[name="csrMode"]');
            var autoFields = document.getElementById('autoCSRFields');
            var manualField = document.getElementById('manualCSRField');

            if (!csrModeInputs.length) return;

            csrModeInputs.forEach(function(input) {
                input.addEventListener('change', function() {
                    if (this.value === 'auto') {
                        if (autoFields) autoFields.style.display = 'block';
                        if (manualField) manualField.style.display = 'none';
                    } else {
                        if (autoFields) autoFields.style.display = 'none';
                        if (manualField) manualField.style.display = 'block';
                    }
                });
            });

            // Trigger initial state
            var checked = document.querySelector('input[name="csrMode"]:checked');
            if (checked) checked.dispatchEvent(new Event('change'));
        },

        /**
         * Generate CSR via API
         */
        generateCSR: function() {
            var data = {
                cn: document.querySelector('[name="commonName"]')?.value || '',
                org: document.querySelector('[name="organization"]')?.value || '',
                ou: document.querySelector('[name="organizationalUnit"]')?.value || '',
                city: document.querySelector('[name="city"]')?.value || '',
                state: document.querySelector('[name="state"]')?.value || '',
                country: document.querySelector('[name="country"]')?.value || '',
                email: document.querySelector('[name="csrEmail"]')?.value || ''
            };

            if (!data.cn) {
                this.showAlert('error', this.config.lang.common_name_required || 'Common Name is required');
                return;
            }

            this.ajax('generateCSR', data, function(response) {
                if (response.success) {
                    var csrField = document.querySelector('[name="csr"]');
                    if (csrField) csrField.value = response.data.csr;
                    
                    // Store private key
                    var pkField = document.getElementById('privateKey');
                    if (pkField) pkField.value = response.data.privateKey;

                    SSLManager.showAlert('success', SSLManager.config.lang.csr_generated || 'CSR generated successfully');
                } else {
                    SSLManager.showAlert('error', response.message);
                }
            });
        },

        /**
         * Decode CSR
         */
        decodeCSR: function() {
            var csr = document.querySelector('[name="csr"]')?.value || '';

            if (!csr) {
                this.showAlert('error', this.config.lang.csr_required || 'Please enter CSR');
                return;
            }

            this.ajax('decodeCsr', { csr: csr }, function(response) {
                if (response.success) {
                    // Fill decoded info
                    SSLManager.fillDecodedCSR(response.data);
                    SSLManager.showAlert('success', SSLManager.config.lang.csr_decoded || 'CSR decoded successfully');
                } else {
                    SSLManager.showAlert('error', response.message);
                }
            });
        },

        fillDecodedCSR: function(data) {
            if (data.commonName) {
                var domainInput = document.querySelector('[name="domainName"]') || 
                                  document.querySelector('[name="domainName[]"]');
                if (domainInput) domainInput.value = data.commonName;
            }
        },

        // ==========================================
        // Domain Management
        // ==========================================
        initDomainInputs: function() {
            var addBtn = document.querySelector('.sslm-add-domain');
            if (addBtn) {
                addBtn.addEventListener('click', this.addDomainRow.bind(this));
            }

            // Set for all DCV method
            var setAllBtn = document.querySelector('.sslm-set-for-all');
            if (setAllBtn) {
                setAllBtn.addEventListener('click', this.setDCVForAll.bind(this));
            }
        },

        addDomainRow: function() {
            var container = document.querySelector('.sslm-domain-list');
            var template = document.querySelector('.sslm-domain-row');
            
            if (!container || !template) return;

            var count = container.querySelectorAll('.sslm-domain-row').length;
            var maxDomains = parseInt(container.dataset.maxDomains || 1);

            if (count >= maxDomains) {
                this.showAlert('warning', this.config.lang.max_domains_reached || 'Maximum domains reached');
                return;
            }

            var clone = template.cloneNode(true);
            clone.querySelector('.sslm-domain-number').textContent = count + 1;
            clone.querySelector('[name="domainName[]"]').value = '';
            
            // Add remove button
            var removeBtn = clone.querySelector('.sslm-remove-domain');
            if (removeBtn) {
                removeBtn.style.display = 'inline-block';
                removeBtn.addEventListener('click', function() {
                    clone.remove();
                    SSLManager.updateDomainNumbers();
                });
            }

            container.appendChild(clone);
        },

        updateDomainNumbers: function() {
            var rows = document.querySelectorAll('.sslm-domain-row');
            rows.forEach(function(row, index) {
                var num = row.querySelector('.sslm-domain-number');
                if (num) num.textContent = index + 1;
            });
        },

        setDCVForAll: function() {
            var firstSelect = document.querySelector('[name="dcvMethod[]"]');
            if (!firstSelect) return;

            var value = firstSelect.value;
            document.querySelectorAll('[name="dcvMethod[]"]').forEach(function(select) {
                select.value = value;
            });
        },

        // ==========================================
        // Form Actions
        // ==========================================
        handleAction: function(action, element) {
            switch(action) {
                case 'submitApply':
                    this.submitApply();
                    break;
                case 'saveDraft':
                    this.saveDraft();
                    break;
                case 'refreshStatus':
                    this.refreshStatus();
                    break;
                case 'downloadCert':
                    this.downloadCertificate(element.dataset.format || 'all');
                    break;
                case 'updateDCV':
                    this.updateDCV();
                    break;
                case 'cancelOrder':
                    this.confirmCancel();
                    break;
                case 'reissueCert':
                    this.submitReissue();
                    break;
                case 'revokeCert':
                    this.confirmRevoke();
                    break;
                default:
                    console.warn('Unknown action:', action);
            }
        },

        submitApply: function() {
            var form = document.getElementById('sslApplyForm');
            if (!form) return;

            var formData = new FormData(form);
            var data = {};
            formData.forEach(function(value, key) {
                if (key.endsWith('[]')) {
                    var k = key.slice(0, -2);
                    if (!data[k]) data[k] = [];
                    data[k].push(value);
                } else {
                    data[key] = value;
                }
            });

            // Build domainInfo array
            if (data.domainName) {
                data.domainInfo = [];
                var domains = Array.isArray(data.domainName) ? data.domainName : [data.domainName];
                var methods = Array.isArray(data.dcvMethod) ? data.dcvMethod : [data.dcvMethod];
                var emails = Array.isArray(data.dcvEmail) ? data.dcvEmail : [data.dcvEmail || ''];

                domains.forEach(function(domain, i) {
                    if (domain) {
                        data.domainInfo.push({
                            domainName: domain,
                            dcvMethod: methods[i] || 'HTTP_CSR_HASH',
                            dcvEmail: emails[i] || ''
                        });
                    }
                });
            }

            this.showLoading();

            this.ajax('submitApply', data, function(response) {
                SSLManager.hideLoading();
                
                if (response.success) {
                    SSLManager.showAlert('success', response.message || 'Request submitted successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    if (response.errors) {
                        SSLManager.showValidationErrors(response.errors);
                    } else {
                        SSLManager.showAlert('error', response.message);
                    }
                }
            });
        },

        saveDraft: function() {
            var form = document.getElementById('sslApplyForm');
            if (!form) return;

            var formData = new FormData(form);
            var data = {};
            formData.forEach(function(value, key) {
                data[key] = value;
            });

            this.ajax('saveDraft', data, function(response) {
                if (response.success) {
                    SSLManager.showAlert('success', SSLManager.config.lang.draft_saved || 'Draft saved');
                } else {
                    SSLManager.showAlert('error', response.message);
                }
            });
        },

        refreshStatus: function() {
            this.showLoading();

            this.ajax('refreshStatus', {}, function(response) {
                SSLManager.hideLoading();
                
                if (response.success) {
                    SSLManager.showAlert('success', SSLManager.config.lang.status_refreshed || 'Status refreshed');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    SSLManager.showAlert('error', response.message);
                }
            });
        },

        downloadCertificate: function(format) {
            var url = this.config.ajaxUrl + '&a=downCert&format=' + format;
            window.location.href = url;
        },

        updateDCV: function() {
            var domainInfo = [];
            document.querySelectorAll('.sslm-dcv-row').forEach(function(row) {
                domainInfo.push({
                    domainName: row.querySelector('[name="dcvDomain"]')?.value,
                    dcvMethod: row.querySelector('[name="dcvMethod"]')?.value,
                    dcvEmail: row.querySelector('[name="dcvEmail"]')?.value || ''
                });
            });

            this.ajax('batchUpdateDCV', { domainInfo: JSON.stringify(domainInfo) }, function(response) {
                if (response.success) {
                    SSLManager.showAlert('success', SSLManager.config.lang.dcv_updated || 'DCV updated');
                } else {
                    SSLManager.showAlert('error', response.message);
                }
            });
        },

        confirmCancel: function() {
            var msg = this.config.lang.sure_to_cancel || 'Are you sure you want to cancel?';
            if (confirm(msg)) {
                this.ajax('cancelOrder', {}, function(response) {
                    if (response.success) {
                        SSLManager.showAlert('success', response.message);
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        SSLManager.showAlert('error', response.message);
                    }
                });
            }
        },

        confirmRevoke: function() {
            var msg = this.config.lang.sure_to_revoke || 'Are you sure? This cannot be undone.';
            if (confirm(msg)) {
                this.ajax('revoke', {}, function(response) {
                    if (response.success) {
                        SSLManager.showAlert('success', response.message);
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        SSLManager.showAlert('error', response.message);
                    }
                });
            }
        },

        submitReissue: function() {
            var form = document.getElementById('sslReissueForm');
            if (!form) return;

            var formData = new FormData(form);
            var data = {};
            formData.forEach(function(value, key) {
                data[key] = value;
            });

            this.showLoading();

            this.ajax('submitReissue', data, function(response) {
                SSLManager.hideLoading();
                
                if (response.success) {
                    SSLManager.showAlert('success', response.message);
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    SSLManager.showAlert('error', response.message);
                }
            });
        },

        // ==========================================
        // Utility Functions
        // ==========================================
        ajax: function(action, data, callback) {
            var xhr = new XMLHttpRequest();
            var url = this.config.ajaxUrl + '&a=' + action;

            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            callback(response);
                        } catch (e) {
                            callback({ success: false, message: 'Invalid response' });
                        }
                    } else {
                        callback({ success: false, message: 'Request failed' });
                    }
                }
            };

            var params = this.serialize(data);
            xhr.send(params);
        },

        serialize: function(obj, prefix) {
            var str = [], p;
            for (p in obj) {
                if (obj.hasOwnProperty(p)) {
                    var k = prefix ? prefix + "[" + p + "]" : p,
                        v = obj[p];
                    str.push((v !== null && typeof v === "object") ?
                        this.serialize(v, k) :
                        encodeURIComponent(k) + "=" + encodeURIComponent(v));
                }
            }
            return str.join("&");
        },

        showAlert: function(type, message) {
            // Remove existing alerts
            document.querySelectorAll('.sslm-alert-toast').forEach(function(el) {
                el.remove();
            });

            var alert = document.createElement('div');
            alert.className = 'sslm-alert-toast sslm-alert--' + type;
            alert.innerHTML = '<span>' + message + '</span>';
            alert.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:12px 20px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:slideIn 0.3s ease';

            document.body.appendChild(alert);

            setTimeout(function() {
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 300);
            }, 3000);
        },

        showValidationErrors: function(errors) {
            // Clear previous errors
            document.querySelectorAll('.sslm-error-text').forEach(function(el) {
                el.remove();
            });
            document.querySelectorAll('.sslm-input--error').forEach(function(el) {
                el.classList.remove('sslm-input--error');
            });

            // Show new errors
            for (var field in errors) {
                var input = document.querySelector('[name="' + field + '"]');
                if (input) {
                    input.classList.add('sslm-input--error');
                    var errorEl = document.createElement('div');
                    errorEl.className = 'sslm-error-text';
                    errorEl.textContent = errors[field];
                    input.parentNode.appendChild(errorEl);
                }
            }

            this.showAlert('error', this.config.lang.validation_error || 'Please fix the errors below');
        },

        showLoading: function() {
            var overlay = document.createElement('div');
            overlay.id = 'sslmLoading';
            overlay.className = 'sslm-modal-overlay sslm-modal-overlay--visible';
            overlay.innerHTML = '<div class="sslm-loading"><div class="sslm-spinner"></div></div>';
            document.body.appendChild(overlay);
        },

        hideLoading: function() {
            var overlay = document.getElementById('sslmLoading');
            if (overlay) overlay.remove();
        },

        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    SSLManager.showAlert('success', SSLManager.config.lang.copied || 'Copied!');
                });
            } else {
                var textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
                this.showAlert('success', this.config.lang.copied || 'Copied!');
            }
        },

        // Modal functions
        openModal: function(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('sslm-modal-overlay--visible');
            }
        },

        closeModal: function(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('sslm-modal-overlay--visible');
            }
        }
    };

    // Auto-initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Get config from page
        var configEl = document.getElementById('sslmConfig');
        if (configEl) {
            try {
                var config = JSON.parse(configEl.textContent);
                SSLManager.init(config);
            } catch (e) {
                console.error('Failed to parse SSLManager config:', e);
            }
        }
    });

    // Add CSS for toast animation
    var style = document.createElement('style');
    style.textContent = '@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}';
    document.head.appendChild(style);

})();