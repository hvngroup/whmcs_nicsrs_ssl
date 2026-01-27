/**
 * SSL Manager JavaScript
 * Handles form interactions and AJAX submissions
 * 
 * @package    NicSRS SSL Module
 * @author     HVN GROUP
 * @version    2.0.0
 */

(function() {
    'use strict';

    // ========================================
    // Global Variables
    // ========================================
    var domainIndex = 0;
    var config = window.sslmConfig || {};
    var lang = config.lang || {};

    // ========================================
    // Initialization
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Parse config from JSON script tag if exists
        var configEl = document.getElementById('sslmConfig');
        if (configEl) {
            try {
                config = JSON.parse(configEl.textContent);
                lang = config.lang || {};
            } catch (e) {
                console.error('Config parse error:', e);
            }
        }
        
        // Also check window.sslmConfig (set by applycert.tpl)
        if (window.sslmConfig) {
            config = window.sslmConfig;
            lang = config.lang || {};
        }
        
        // Initialize all components
        initCSRToggle();
        initFormSubmit();
        initDomainRows();
        initValidation();
        initActionButtons();
        initCopyButtons();
        restoreFormData();
    });

    // ========================================
    // CSR Toggle
    // ========================================
    function initCSRToggle() {
        var toggle = document.getElementById('isManualCsr');
        var csrTextarea = document.getElementById('csrTextarea');
        
        if (toggle && csrTextarea) {
            toggle.addEventListener('change', function() {
                if (this.checked) {
                    csrTextarea.style.display = 'block';
                } else {
                    csrTextarea.style.display = 'none';
                }
            });
            
            // Restore state from configData
            if (config.configData && config.configData.csr) {
                toggle.checked = true;
                csrTextarea.style.display = 'block';
            }
        }
    }

    // ========================================
    // Domain Management
    // ========================================
    function initDomainRows() {
        var domainList = document.getElementById('domainList');
        if (!domainList) return;
        
        var rows = domainList.querySelectorAll('.sslm-domain-row');
        domainIndex = rows.length - 1;
        
        // Restore domains from configData
        if (config.configData && config.configData.domainInfo && config.configData.domainInfo.length > 1) {
            for (var i = 1; i < config.configData.domainInfo.length; i++) {
                addDomainRow(config.configData.domainInfo[i]);
            }
        }
        
        updateRemoveButtons();
    }

    window.addDomain = function() {
        addDomainRow();
    };

    function addDomainRow(data) {
        var domainList = document.getElementById('domainList');
        if (!domainList) return;
        
        domainIndex++;
        data = data || {};
        
        var row = document.createElement('div');
        row.className = 'sslm-domain-row';
        row.innerHTML = 
            '<div class="sslm-domain-col">' +
                '<input type="text" name="domains[' + domainIndex + '][name]" ' +
                       'class="sslm-input sslm-domain-input" ' +
                       'placeholder="' + (lang.enter_domain || 'Enter domain') + '" ' +
                       'value="' + (data.domainName || '') + '">' +
            '</div>' +
            '<div class="sslm-dcv-col">' +
                '<select name="domains[' + domainIndex + '][dcvMethod]" class="sslm-select sslm-dcv-select">' +
                    '<option value="CNAME_CSR_HASH"' + (data.dcvMethod === 'CNAME_CSR_HASH' ? ' selected' : '') + '>' + (lang.dns_cname || 'DNS CNAME') + '</option>' +
                    '<option value="HTTP_CSR_HASH"' + (data.dcvMethod === 'HTTP_CSR_HASH' ? ' selected' : '') + '>' + (lang.http_file || 'HTTP File') + '</option>' +
                    '<option value="HTTPS_CSR_HASH"' + (data.dcvMethod === 'HTTPS_CSR_HASH' ? ' selected' : '') + '>' + (lang.https_file || 'HTTPS File') + '</option>' +
                    '<option value="EMAIL"' + (data.dcvMethod === 'EMAIL' ? ' selected' : '') + '>' + (lang.email || 'Email') + '</option>' +
                '</select>' +
            '</div>' +
            '<div class="sslm-action-col">' +
                '<button type="button" class="sslm-btn-icon sslm-btn-remove" onclick="removeDomain(this)">' +
                    '<i class="fas fa-times"></i> ✕' +
                '</button>' +
            '</div>';
        
        domainList.appendChild(row);
        updateRemoveButtons();
    }

    window.removeDomain = function(btn) {
        var row = btn.closest('.sslm-domain-row');
        var domainList = document.getElementById('domainList');
        var rows = domainList.querySelectorAll('.sslm-domain-row');
        
        if (rows.length > 1) {
            row.remove();
            updateRemoveButtons();
        }
    };

    function updateRemoveButtons() {
        var domainList = document.getElementById('domainList');
        if (!domainList) return;
        
        var rows = domainList.querySelectorAll('.sslm-domain-row');
        var removeButtons = domainList.querySelectorAll('.sslm-btn-remove');
        
        removeButtons.forEach(function(btn, index) {
            if (rows.length > 1) {
                btn.style.display = 'flex';
            } else {
                btn.style.display = 'none';
            }
        });
        
        // Hide first row's remove button always
        if (removeButtons.length > 0) {
            removeButtons[0].style.display = 'none';
        }
    }

    // ========================================
    // Form Validation
    // ========================================
    function initValidation() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return;
        
        var inputs = form.querySelectorAll('.sslm-input, .sslm-select');
        inputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('sslm-error')) {
                    validateField(this);
                }
            });
        });
    }

    function validateField(field) {
        var value = field.value.trim();
        var name = field.name;
        var isValid = true;
        
        // Required fields
        var requiredFields = ['domains[0][name]', 'domains[0][dcvMethod]', 'adminFirstName', 'adminLastName', 'adminEmail'];
        
        if (requiredFields.indexOf(name) !== -1 && !value) {
            isValid = false;
        }
        
        // Email validation
        if (name.indexOf('Email') !== -1 && value) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
            }
        }
        
        // Domain validation
        if (name.indexOf('[name]') !== -1 && value) {
            var domainRegex = /^(\*\.)?([a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/;
            if (!domainRegex.test(value)) {
                isValid = false;
            }
        }
        
        if (isValid) {
            field.classList.remove('sslm-error');
        } else {
            field.classList.add('sslm-error');
        }
        
        return isValid;
    }

    function validateForm() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return true;
        
        var isValid = true;
        
        // Check required domain
        var domainInput = form.querySelector('.sslm-domain-input');
        if (domainInput && !domainInput.value.trim()) {
            domainInput.classList.add('sslm-error');
            isValid = false;
        }
        
        // Check admin contact fields
        var requiredFields = ['adminFirstName', 'adminLastName', 'adminEmail'];
        requiredFields.forEach(function(fieldName) {
            var field = form.querySelector('[name="' + fieldName + '"]');
            if (field && !field.value.trim()) {
                field.classList.add('sslm-error');
                isValid = false;
            }
        });
        
        // Validate email format
        var emailField = form.querySelector('[name="adminEmail"]');
        if (emailField && emailField.value.trim()) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value.trim())) {
                emailField.classList.add('sslm-error');
                isValid = false;
            }
        }
        
        return isValid;
    }

    // ========================================
    // Restore Form Data
    // ========================================
    function restoreFormData() {
        if (!config.configData) return;
        
        var form = document.getElementById('sslm-apply-form');
        if (!form) return;
        
        var data = config.configData;
        
        // Restore CSR
        if (data.csr) {
            var csrField = form.querySelector('[name="csr"]');
            if (csrField) csrField.value = data.csr;
        }
        
        // Restore admin contact
        if (data.Administrator) {
            var admin = data.Administrator;
            var fieldMap = {
                'adminFirstName': admin.firstName,
                'adminLastName': admin.lastName,
                'adminEmail': admin.email,
                'adminPhone': admin.mobile,
                'adminTitle': admin.job,
                'adminCountry': admin.country,
                'adminCity': admin.city,
                'adminAddress': admin.address,
                'adminProvince': admin.state,
                'adminPostCode': admin.postCode
            };
            
            for (var fieldName in fieldMap) {
                if (fieldMap[fieldName]) {
                    var field = form.querySelector('[name="' + fieldName + '"]');
                    if (field) field.value = fieldMap[fieldName];
                }
            }
        }
        
        // Restore organization info
        if (data.organizationInfo) {
            var org = data.organizationInfo;
            var orgFieldMap = {
                'organizationName': org.organizationName,
                'organizationAddress': org.organizationAddress,
                'organizationCity': org.organizationCity,
                'organizationCountry': org.organizationCountry,
                'organizationPostalCode': org.organizationPostalCode || org.organizationPostCode
            };
            
            for (var orgFieldName in orgFieldMap) {
                if (orgFieldMap[orgFieldName]) {
                    var orgField = form.querySelector('[name="' + orgFieldName + '"]');
                    if (orgField) orgField.value = orgFieldMap[orgFieldName];
                }
            }
        }
        
        // Restore first domain
        if (data.domainInfo && data.domainInfo.length > 0) {
            var firstDomain = data.domainInfo[0];
            var domainInput = form.querySelector('.sslm-domain-input');
            var dcvSelect = form.querySelector('.sslm-dcv-select');
            
            if (domainInput && firstDomain.domainName) {
                domainInput.value = firstDomain.domainName;
            }
            if (dcvSelect && firstDomain.dcvMethod) {
                dcvSelect.value = firstDomain.dcvMethod;
            }
        }
    }

    // ========================================
    // Form Submit (Apply/Save Draft)
    // ========================================
    function initFormSubmit() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('submit');
        });
    }

    window.saveDraft = function() {
        submitForm('draft');
    };

    function submitForm(action) {
        if (action === 'submit' && !validateForm()) {
            showToast(lang.validation_error || 'Please fill in all required fields correctly', 'error');
            return;
        }
        
        var form = document.getElementById('sslm-apply-form');
        var submitBtn = document.getElementById('submitBtn');
        var saveBtn = document.getElementById('saveBtn');
        
        // Collect all form data
        var domains = collectDomains();
        var contacts = collectContacts();
        
        // Build data object (like old module)
        var data = {
            server: 'other',
            csr: form.querySelector('[name="csr"]') ? form.querySelector('[name="csr"]').value : '',
            domainInfo: domains,
            Administrator: contacts,
            originalfromOthers: document.getElementById('isManualCsr') && document.getElementById('isManualCsr').checked ? '1' : '0'
        };
        
        // Add organization info if OV/EV
        var orgName = form.querySelector('[name="organizationName"]');
        if (orgName) {
            data.organizationInfo = {
                organizationName: orgName.value,
                organizationAddress: form.querySelector('[name="organizationAddress"]') ? form.querySelector('[name="organizationAddress"]').value : '',
                organizationCity: form.querySelector('[name="organizationCity"]') ? form.querySelector('[name="organizationCity"]').value : '',
                organizationCountry: form.querySelector('[name="organizationCountry"]') ? form.querySelector('[name="organizationCountry"]').value : '',
                organizationPostalCode: form.querySelector('[name="organizationPostalCode"]') ? form.querySelector('[name="organizationPostalCode"]').value : ''
            };
        }
        
        // Disable buttons
        if (submitBtn) submitBtn.disabled = true;
        if (saveBtn) saveBtn.disabled = true;
        
        // Show loading
        var activeBtn = action === 'draft' ? saveBtn : submitBtn;
        if (activeBtn) activeBtn.classList.add('sslm-loading');
        
        // Build URL with step parameter (like old module)
        var step = action === 'draft' ? 'savedraft' : 'applyssl';
        var ajaxUrl = config.ajaxUrl + '&step=' + step;
        
        // Submit via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            // Re-enable buttons
            if (submitBtn) submitBtn.disabled = false;
            if (saveBtn) saveBtn.disabled = false;
            if (activeBtn) activeBtn.classList.remove('sslm-loading');
            
            var responseText = xhr.responseText.trim();
            console.log('Raw response:', responseText);
            
            // Check for HTML response
            if (responseText.indexOf('<!DOCTYPE') === 0 || responseText.indexOf('<html') === 0) {
                console.error('Server returned HTML instead of JSON');
                showToast('Server error occurred. Please check console.', 'error');
                return;
            }
            
            // Try to find JSON
            var jsonStart = responseText.indexOf('{');
            var jsonEnd = responseText.lastIndexOf('}');
            if (jsonStart !== -1 && jsonEnd > jsonStart) {
                responseText = responseText.substring(jsonStart, jsonEnd + 1);
            }
            
            try {
                var response = JSON.parse(responseText);
                
                if (response.success) {
                    showToast(response.message || 'Success!', 'success');
                    if (action === 'submit') {
                        setTimeout(function() { window.location.reload(); }, 1500);
                    }
                } else {
                    showToast(response.message || 'An error occurred', 'error');
                }
            } catch (e) {
                console.error('JSON parse error:', e, responseText);
                showToast('Server response error', 'error');
            }
        };
        
        xhr.onerror = function() {
            if (submitBtn) submitBtn.disabled = false;
            if (saveBtn) saveBtn.disabled = false;
            if (activeBtn) activeBtn.classList.remove('sslm-loading');
            showToast('Network error occurred', 'error');
        };
        
        // Send data as URL-encoded (like old module with data parameter)
        xhr.send('data=' + encodeURIComponent(JSON.stringify(data)));
    }

    // ========================================
    // Data Collection
    // ========================================
    function collectDomains() {
        var domainList = document.getElementById('domainList');
        if (!domainList) return [];
        
        var domains = [];
        var rows = domainList.querySelectorAll('.sslm-domain-row');
        
        rows.forEach(function(row) {
            var domainInput = row.querySelector('.sslm-domain-input');
            var dcvSelect = row.querySelector('.sslm-dcv-select');
            
            if (domainInput && domainInput.value.trim()) {
                domains.push({
                    domainName: domainInput.value.trim(),
                    dcvMethod: dcvSelect ? dcvSelect.value : 'CNAME_CSR_HASH'
                });
            }
        });
        
        return domains;
    }

    function collectContacts() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return {};
        
        return {
            firstName: getFieldValue(form, 'adminFirstName'),
            lastName: getFieldValue(form, 'adminLastName'),
            email: getFieldValue(form, 'adminEmail'),
            mobile: getFieldValue(form, 'adminPhone'),
            job: getFieldValue(form, 'adminTitle'),
            country: getFieldValue(form, 'adminCountry'),
            city: getFieldValue(form, 'adminCity'),
            address: getFieldValue(form, 'adminAddress'),
            state: getFieldValue(form, 'adminProvince'),
            postCode: getFieldValue(form, 'adminPostCode')
        };
    }

    function getFieldValue(form, name) {
        var field = form.querySelector('[name="' + name + '"]');
        return field ? field.value.trim() : '';
    }

    // ========================================
    // Action Button Handlers (for pending/complete/manage pages)
    // ========================================
    function initActionButtons() {
        // Handle all elements with data-action attribute
        document.querySelectorAll('[data-action]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                var action = this.getAttribute('data-action');
                executeAction(action, this);
            });
        });
    }

    function executeAction(action, btn) {
        // Map frontend action names to backend step names
        var actionToStep = {
            'refreshStatus': 'refreshStatus',
            'refresh': 'refreshStatus',
            'cancelOrder': 'cancelOrder',
            'cancel': 'cancelOrder',
            'updateDCV': 'batchUpdateDCV',
            'batchUpdateDCV': 'batchUpdateDCV',
            'revokeCert': 'revoke',
            'revoke': 'revoke',
            'resendDCV': 'resendDCVEmail',
            'resendDCVEmail': 'resendDCVEmail',
            'renew': 'renew',
            'reissue': 'submitReissue',
            'downCert': 'downCert',
            'download': 'downCert'
        };
        
        var step = actionToStep[action] || action;
        
        // Confirm destructive actions
        var confirmActions = {
            'cancelOrder': lang.confirm_cancel || 'Are you sure you want to cancel this order?',
            'cancel': lang.confirm_cancel || 'Are you sure you want to cancel this order?',
            'revokeCert': lang.confirm_revoke || 'Are you sure you want to revoke this certificate? This action cannot be undone.',
            'revoke': lang.confirm_revoke || 'Are you sure you want to revoke this certificate?'
        };
        
        if (confirmActions[action]) {
            if (!confirm(confirmActions[action])) {
                return;
            }
        }
        
        // Build URL with step parameter (like old module)
        var ajaxUrl = config.ajaxUrl + '&step=' + step;
        
        // Show loading state
        if (btn) {
            btn.classList.add('sslm-loading');
            btn.disabled = true;
        }
        
        // Create XHR request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            // Remove loading state
            if (btn) {
                btn.classList.remove('sslm-loading');
                btn.disabled = false;
            }
            
            handleActionResponse(xhr.responseText, action);
        };
        
        xhr.onerror = function() {
            if (btn) {
                btn.classList.remove('sslm-loading');
                btn.disabled = false;
            }
            showToast(lang.network_error || 'Network error occurred', 'error');
        };
        
        // Collect and send action-specific data
        var postData = collectActionData(action);
        xhr.send(postData);
    }

    function collectActionData(action) {
        var data = {};
        
        // DCV Update - collect domain info
        if (action === 'updateDCV' || action === 'batchUpdateDCV') {
            var domainInfo = [];
            document.querySelectorAll('.sslm-dcv-row').forEach(function(row) {
                var domainInput = row.querySelector('[name="dcvDomain"]');
                var methodSelect = row.querySelector('[name="dcvMethod"]');
                
                if (domainInput && methodSelect) {
                    domainInfo.push({
                        domainName: domainInput.value,
                        dcvMethod: methodSelect.value
                    });
                }
            });
            data.domainInfo = domainInfo;
        }
        
        // Format like old module: data={...}
        return 'data=' + encodeURIComponent(JSON.stringify(data));
    }

    function handleActionResponse(responseText, action) {
        responseText = responseText.trim();
        
        // Check for HTML error response
        if (responseText.indexOf('<!DOCTYPE') === 0 || responseText.indexOf('<html') === 0) {
            console.error('Server returned HTML instead of JSON');
            showToast(lang.server_error || 'Server error occurred', 'error');
            return;
        }
        
        // Extract JSON from response (handle any prefix/suffix)
        var jsonStart = responseText.indexOf('{');
        var jsonEnd = responseText.lastIndexOf('}');
        if (jsonStart !== -1 && jsonEnd > jsonStart) {
            responseText = responseText.substring(jsonStart, jsonEnd + 1);
        }
        
        try {
            var response = JSON.parse(responseText);
            
            if (response.success) {
                showToast(response.message || lang.success || 'Operation successful!', 'success');
                
                // Reload page for state-changing actions
                var reloadActions = ['cancelOrder', 'cancel', 'revokeCert', 'revoke', 
                                     'updateDCV', 'batchUpdateDCV', 'refreshStatus', 
                                     'refresh', 'renew'];
                if (reloadActions.indexOf(action) !== -1) {
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                showToast(response.message || lang.error || 'An error occurred', 'error');
            }
        } catch (e) {
            console.error('JSON parse error:', e, responseText);
            showToast(lang.parse_error || 'Server response error', 'error');
        }
    }

    // ========================================
    // SSLManager Namespace (for onclick handlers in templates)
    // ========================================
    window.SSLManager = window.SSLManager || {};

    // Modal functions
    SSLManager.openModal = function(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('sslm-modal--active');
            document.body.style.overflow = 'hidden';
        }
    };

    SSLManager.closeModal = function(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('sslm-modal--active');
            document.body.style.overflow = '';
        }
    };

    // Download certificate
    SSLManager.downloadCertificate = function(format) {
        format = format || 'apache';
        
        var ajaxUrl = config.ajaxUrl + '&step=downCert';
        
        showToast(lang.downloading || 'Downloading...', 'info');
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            try {
                var responseText = xhr.responseText.trim();
                var jsonStart = responseText.indexOf('{');
                var jsonEnd = responseText.lastIndexOf('}');
                if (jsonStart !== -1 && jsonEnd > jsonStart) {
                    responseText = responseText.substring(jsonStart, jsonEnd + 1);
                }
                
                var response = JSON.parse(responseText);
                
                if (response.success && response.data) {
                    // Trigger file download
                    downloadFile(response.data.name, response.data.content);
                    showToast(lang.download_success || 'Download started!', 'success');
                } else {
                    showToast(response.message || lang.download_failed || 'Download failed', 'error');
                }
            } catch (e) {
                console.error('Download error:', e);
                showToast(lang.download_error || 'Download error occurred', 'error');
            }
        };
        
        xhr.onerror = function() {
            showToast(lang.network_error || 'Network error', 'error');
        };
        
        xhr.send('format=' + encodeURIComponent(format));
    };

    // Confirm renew
    SSLManager.confirmRenew = function() {
        if (confirm(lang.confirm_renew || 'Submit renewal request for this certificate?')) {
            executeAction('renew', null);
        }
    };

    // Confirm reissue - redirect to reissue page
    SSLManager.confirmReissue = function() {
        var baseUrl = config.ajaxUrl.split('&step=')[0];
        window.location.href = baseUrl + '&modop=custom&a=reissue';
    };

    // ========================================
    // Helper: Download File from Base64
    // ========================================
    function downloadFile(filename, base64Content) {
        try {
            // Decode base64
            var binaryString = atob(base64Content);
            var bytes = new Uint8Array(binaryString.length);
            for (var i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            
            // Create blob and download
            var blob = new Blob([bytes], { type: 'application/octet-stream' });
            var url = window.URL.createObjectURL(blob);
            
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            
            // Cleanup
            setTimeout(function() {
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }, 100);
        } catch (e) {
            console.error('Download file error:', e);
            showToast(lang.download_error || 'Failed to download file', 'error');
        }
    }

    // ========================================
    // Copy to Clipboard
    // ========================================
    function initCopyButtons() {
        document.querySelectorAll('[data-copy]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var text = this.getAttribute('data-copy');
                copyToClipboard(text);
            });
        });
    }

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showToast(lang.copied || 'Copied to clipboard!', 'success');
            }).catch(function() {
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    }

    function fallbackCopyToClipboard(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showToast(lang.copied || 'Copied!', 'success');
        } catch (e) {
            showToast(lang.copy_failed || 'Copy failed', 'error');
        }
        
        document.body.removeChild(textarea);
    }

    // ========================================
    // Toast Notification
    // ========================================
    function showToast(message, type) {
        type = type || 'info';
        
        // Remove existing toast
        var existing = document.querySelector('.sslm-toast');
        if (existing) existing.remove();
        
        // Create toast
        var toast = document.createElement('div');
        toast.className = 'sslm-toast sslm-toast--' + type;
        toast.innerHTML = '<span>' + message + '</span>';
        
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(function() {
            toast.classList.add('sslm-toast--visible');
        }, 10);
        
        // Hide toast after delay
        setTimeout(function() {
            toast.classList.remove('sslm-toast--visible');
            setTimeout(function() {
                if (toast.parentNode) toast.remove();
            }, 300);
        }, 3000);
    }

    // Expose showToast globally
    window.showToast = showToast;

})();