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
        initCSRToggle();
        initFormSubmit();
        initDomainRows();
        initValidation();
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
        var maxDomain = config.maxDomain || 1;
        var currentRows = domainList.querySelectorAll('.sslm-domain-row').length;
        
        if (currentRows >= maxDomain) {
            showToast(lang.max_domain_reached || 'Maximum number of domains reached', 'warning');
            return;
        }
        
        domainIndex++;
        data = data || {};
        
        var row = document.createElement('div');
        row.className = 'sslm-domain-row';
        row.setAttribute('data-index', domainIndex);
        
        row.innerHTML = 
            '<div class="sslm-domain-col">' +
                '<input type="text" name="domains[' + domainIndex + '][name]" class="sslm-input sslm-domain-input" ' +
                    'placeholder="' + (lang.domain_placeholder || 'example.com') + '" ' +
                    'value="' + (data.domainName || '') + '">' +
            '</div>' +
            '<div class="sslm-dcv-col">' +
                '<select name="domains[' + domainIndex + '][dcvMethod]" class="sslm-select sslm-dcv-select">' +
                    '<option value="">' + (lang.choose || 'Choose') + '</option>' +
                    '<option value="CNAME_CSR_HASH"' + (data.dcvMethod === 'CNAME_CSR_HASH' ? ' selected' : '') + '>' + (lang.dns_cname || 'DNS CNAME') + '</option>' +
                    '<option value="HTTP_CSR_HASH"' + (data.dcvMethod === 'HTTP_CSR_HASH' ? ' selected' : '') + '>' + (lang.http_file || 'HTTP File') + '</option>' +
                    '<option value="HTTPS_CSR_HASH"' + (data.dcvMethod === 'HTTPS_CSR_HASH' ? ' selected' : '') + '>' + (lang.https_file || 'HTTPS File') + '</option>' +
                    '<option value="EMAIL"' + (data.dcvMethod === 'EMAIL' ? ' selected' : '') + '>' + (lang.email || 'Email') + '</option>' +
                '</select>' +
            '</div>' +
            '<div class="sslm-action-col">' +
                '<button type="button" class="sslm-btn-icon sslm-btn-remove" onclick="removeDomain(this)">' +
                    '<i class="fas fa-times"></i>' +
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
        
        // CSR validation
        if (name === 'csr') {
            var isManualCsr = document.getElementById('isManualCsr');
            if (isManualCsr && isManualCsr.checked && !value) {
                isValid = false;
            }
            if (value && value.indexOf('-----BEGIN CERTIFICATE REQUEST-----') === -1) {
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
        if (!form) return false;
        
        var isValid = true;
        var firstError = null;
        
        // Validate domain
        var domainInput = form.querySelector('.sslm-domain-input');
        if (domainInput && !validateField(domainInput)) {
            isValid = false;
            if (!firstError) firstError = domainInput;
        }
        
        // Validate DCV method
        var dcvSelect = form.querySelector('.sslm-dcv-select');
        if (dcvSelect && !validateField(dcvSelect)) {
            isValid = false;
            if (!firstError) firstError = dcvSelect;
        }
        
        // Validate required contact fields
        var requiredInputs = ['adminFirstName', 'adminLastName', 'adminEmail'];
        requiredInputs.forEach(function(name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (input && !validateField(input)) {
                isValid = false;
                if (!firstError) firstError = input;
            }
        });
        
        // Validate CSR if manual
        var isManualCsr = document.getElementById('isManualCsr');
        if (isManualCsr && isManualCsr.checked) {
            var csrInput = document.getElementById('csr');
            if (csrInput && !validateField(csrInput)) {
                isValid = false;
                if (!firstError) firstError = csrInput;
            }
        }
        
        // Scroll to first error
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
        
        return isValid;
    }

    // ========================================
    // Form Submit
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
        var formData = new FormData(form);
        
        // Add action type
        formData.set('action', action === 'draft' ? 'saveDraft' : 'submitApply');
        
        // Collect domains
        var domains = collectDomains();
        formData.set('domainInfo', JSON.stringify(domains));
        
        // Collect contacts
        var contacts = collectContacts();
        formData.set('Administrator', JSON.stringify(contacts));
        
        // Check CSR mode
        var isManualCsr = document.getElementById('isManualCsr');
        if (isManualCsr) {
            formData.set('originalfromOthers', isManualCsr.checked ? '1' : '0');
        }
        
        // Disable buttons
        if (submitBtn) submitBtn.disabled = true;
        if (saveBtn) saveBtn.disabled = true;
        
        // Show loading
        var activeBtn = action === 'draft' ? saveBtn : submitBtn;
        if (activeBtn) activeBtn.classList.add('sslm-loading');
        
        // Submit via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            // Re-enable buttons
            if (submitBtn) submitBtn.disabled = false;
            if (saveBtn) saveBtn.disabled = false;
            if (activeBtn) activeBtn.classList.remove('sslm-loading');
            
            var responseText = xhr.responseText.trim();
            
            // Debug: Log raw response
            console.log('Raw response:', responseText);
            
            // Check if response looks like HTML (error page)
            if (responseText.indexOf('<!DOCTYPE') === 0 || responseText.indexOf('<html') === 0 || responseText.indexOf('<br') !== -1) {
                console.error('Server returned HTML instead of JSON:', responseText.substring(0, 500));
                showToast('Server error occurred. Please check console for details.', 'error');
                return;
            }
            
            // Try to find JSON in response (sometimes there's extra output)
            var jsonStart = responseText.indexOf('{');
            var jsonEnd = responseText.lastIndexOf('}');
            
            if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
                responseText = responseText.substring(jsonStart, jsonEnd + 1);
            }
            
            try {
                var response = JSON.parse(responseText);
                
                if (response.success) {
                    showToast(response.message || (action === 'draft' ? 'Draft saved' : 'Certificate submitted successfully'), 'success');
                    
                    if (action === 'submit') {
                        // Reload page after successful submit
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    // Handle validation errors
                    if (response.errors && typeof response.errors === 'object') {
                        var errorMessages = [];
                        for (var key in response.errors) {
                            errorMessages.push(response.errors[key]);
                        }
                        showToast(errorMessages.join(', '), 'error');
                    } else {
                        showToast(response.message || 'An error occurred', 'error');
                    }
                }
            } catch (e) {
                console.error('Response parse error:', e);
                console.error('Response text:', responseText);
                showToast('Server response error. Check console for details.', 'error');
            }
        };
        
        xhr.onerror = function() {
            if (submitBtn) submitBtn.disabled = false;
            if (saveBtn) saveBtn.disabled = false;
            if (activeBtn) activeBtn.classList.remove('sslm-loading');
            showToast('Network error occurred', 'error');
        };
        
        xhr.send(formData);
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
                    dcvMethod: dcvSelect ? dcvSelect.value : '',
                    dcvEmail: ''
                });
            }
        });
        
        return domains;
    }

    function collectContacts() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return {};
        
        return {
            organation: getFieldValue(form, 'adminOrganizationName'),
            job: getFieldValue(form, 'adminTitle'),
            firstName: getFieldValue(form, 'adminFirstName'),
            lastName: getFieldValue(form, 'adminLastName'),
            email: getFieldValue(form, 'adminEmail'),
            mobile: getFieldValue(form, 'adminPhone'),
            country: getFieldValue(form, 'adminCountry'),
            address: getFieldValue(form, 'adminAddress'),
            city: getFieldValue(form, 'adminCity'),
            state: getFieldValue(form, 'adminProvince'),
            postCode: getFieldValue(form, 'adminPostcode')
        };
    }

    function getFieldValue(form, name) {
        var field = form.querySelector('[name="' + name + '"]');
        return field ? field.value.trim() : '';
    }

    // ========================================
    // Restore Form Data
    // ========================================
    function restoreFormData() {
        if (!config.configData) return;
        
        var data = config.configData;
        
        // Restore renew/purchase selection
        if (data.renewOrNot) {
            var radio = document.querySelector('input[name="renewOrNot"][value="' + data.renewOrNot + '"]');
            if (radio) radio.checked = true;
        }
    }

    // ========================================
    // Toast Notifications
    // ========================================
    function showToast(message, type) {
        type = type || 'info';
        
        // Create container if not exists
        var container = document.querySelector('.sslm-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'sslm-toast-container';
            document.body.appendChild(container);
        }
        
        // Create toast
        var toast = document.createElement('div');
        toast.className = 'sslm-toast sslm-toast-' + type;
        
        var icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        };
        
        toast.innerHTML = 
            '<i class="fas ' + (icons[type] || icons.info) + '"></i>' +
            '<span>' + message + '</span>' +
            '<span class="sslm-toast-close"><i class="fas fa-times"></i></span>';
        
        container.appendChild(toast);
        
        // Close on click
        toast.querySelector('.sslm-toast-close').addEventListener('click', function() {
            toast.remove();
        });
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    // Expose to global
    window.sslmShowToast = showToast;

})();