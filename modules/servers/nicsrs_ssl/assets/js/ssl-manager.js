/**
 * NicSRS SSL Manager - Client Area JavaScript
 * 
 * FIXED: Now uses same data format as OLD MODULE
 * - Sends data as {"data": {...}} like jQuery $.ajax
 * - Uses PHP's native array serialization format
 * 
 * @package    nicsrs_ssl
 * @version    2.0.0
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

(function() {
    'use strict';

    // Global config
    var config = window.sslmConfig || {};
    var lang = config.lang || {};

    // ========================================
    // Initialization
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        initTabs();
        initDomainManagement();
        initCSRToggle();
        initFormSubmit();
        initDCVManagement();
        initDownloadHandlers();
        initActionButtons();
        
        // Restore saved form data
        restoreFormData();
    });

    // ========================================
    // Tab Navigation
    // ========================================
    function initTabs() {
        var tabs = document.querySelectorAll('.sslm-tab');
        var panels = document.querySelectorAll('.sslm-tab-panel');
        
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var targetId = this.getAttribute('data-tab');
                
                // Update active states
                tabs.forEach(function(t) { t.classList.remove('active'); });
                panels.forEach(function(p) { p.classList.remove('active'); });
                
                this.classList.add('active');
                var targetPanel = document.getElementById(targetId);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        });
    }

    // ========================================
    // Domain Management
    // ========================================
    function initDomainManagement() {
        var addBtn = document.getElementById('addDomainBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                addDomainRow();
            });
        }
        
        // Initialize DCV options for existing rows
        var domainRows = document.querySelectorAll('.sslm-domain-row');
        domainRows.forEach(function(row) {
            var domainInput = row.querySelector('.sslm-domain-input');
            var dcvSelect = row.querySelector('.sslm-dcv-select');
            if (domainInput && dcvSelect) {
                initDomainDcvOptions(dcvSelect, domainInput.value);
            }
        });
        
        // Add event delegation for domain inputs
        var domainList = document.getElementById('domainList');
        if (domainList) {
            domainList.addEventListener('change', function(e) {
                if (e.target.classList.contains('sslm-domain-input')) {
                    var row = e.target.closest('.sslm-domain-row');
                    var dcvSelect = row.querySelector('.sslm-dcv-select');
                    if (dcvSelect) {
                        initDomainDcvOptions(dcvSelect, e.target.value);
                    }
                }
            });
            
            // Remove domain button
            domainList.addEventListener('click', function(e) {
                if (e.target.classList.contains('sslm-remove-domain') || 
                    e.target.closest('.sslm-remove-domain')) {
                    var row = e.target.closest('.sslm-domain-row');
                    var rows = domainList.querySelectorAll('.sslm-domain-row');
                    if (rows.length > 1) {
                        row.remove();
                    } else {
                        showToast(lang.min_one_domain || 'At least one domain is required', 'warning');
                    }
                }
            });
        }
    }

    function addDomainRow(data) {
        var domainList = document.getElementById('domainList');
        if (!domainList) return;
        
        var maxDomains = config.maxDomain || 1;
        var currentRows = domainList.querySelectorAll('.sslm-domain-row');
        
        if (currentRows.length >= maxDomains) {
            showToast((lang.max_domain_reached || 'Maximum domains reached') + ': ' + maxDomains, 'warning');
            return;
        }
        
        var template = document.getElementById('domainRowTemplate');
        if (!template) {
            // Fallback: clone existing row
            var existingRow = domainList.querySelector('.sslm-domain-row');
            if (existingRow) {
                var newRow = existingRow.cloneNode(true);
                var inputs = newRow.querySelectorAll('input, select');
                inputs.forEach(function(input) {
                    if (input.type !== 'hidden') {
                        input.value = '';
                    }
                });
                domainList.appendChild(newRow);
                
                // Initialize DCV options
                var dcvSelect = newRow.querySelector('.sslm-dcv-select');
                if (dcvSelect) {
                    initDomainDcvOptions(dcvSelect, '');
                }
            }
        } else {
            var clone = template.content.cloneNode(true);
            domainList.appendChild(clone);
            
            // Initialize the new row
            var newRow = domainList.lastElementChild;
            var dcvSelect = newRow.querySelector('.sslm-dcv-select');
            if (dcvSelect) {
                initDomainDcvOptions(dcvSelect, '');
            }
        }
        
        // If data provided, fill it
        if (data) {
            var newRow = domainList.lastElementChild;
            var domainInput = newRow.querySelector('.sslm-domain-input');
            var dcvSelect = newRow.querySelector('.sslm-dcv-select');
            
            if (domainInput && data.domainName) {
                domainInput.value = data.domainName;
            }
            if (dcvSelect) {
                initDomainDcvOptions(dcvSelect, data.domainName || '');
                if (data.dcvMethod) {
                    // Check if it's EMAIL method
                    if (data.dcvMethod === 'EMAIL' && data.dcvEmail) {
                        dcvSelect.value = data.dcvEmail;
                    } else {
                        dcvSelect.value = data.dcvMethod;
                    }
                }
            }
        }
    }

    function initDomainDcvOptions(select, domain) {
        if (!select) return;
        
        var currentValue = select.value;
        var other = config.other || {};
        
        // Clear existing options
        select.innerHTML = '';
        
        // DCV methods
        var methods = [
            { value: 'CNAME_CSR_HASH', label: lang.cname_csr_hash || 'DNS CNAME' },
            { value: 'HTTP_CSR_HASH', label: lang.http_csr_hash || 'HTTP File' }
        ];
        
        // Add HTTPS if supported
        if (other.supportHttps) {
            methods.push({ value: 'HTTPS_CSR_HASH', label: lang.https_csr_hash || 'HTTPS File' });
        }
        
        // Add DNS TXT
        methods.push({ value: 'DNS_CSR_HASH', label: lang.dns_csr_hash || 'DNS TXT' });
        
        // Add email options if domain provided
        if (domain && domain.trim()) {
            var baseDomain = getBaseDomain(domain);
            var emailPrefixes = ['admin', 'administrator', 'webmaster', 'hostmaster', 'postmaster'];
            
            methods.push({ value: '', label: '--- ' + (lang.email_validation || 'Email Validation') + ' ---', disabled: true });
            
            emailPrefixes.forEach(function(prefix) {
                methods.push({
                    value: prefix + '@' + baseDomain,
                    label: prefix + '@' + baseDomain,
                    isEmail: true
                });
            });
        }
        
        // Build options
        methods.forEach(function(method) {
            var option = document.createElement('option');
            option.value = method.value;
            option.textContent = method.label;
            if (method.disabled) {
                option.disabled = true;
            }
            select.appendChild(option);
        });
        
        // Restore value if exists
        if (currentValue) {
            select.value = currentValue;
        }
    }

    function getBaseDomain(domain) {
        // Remove wildcard prefix
        domain = domain.replace(/^\*\./, '');
        
        // Simple extraction - just return domain as-is for now
        // In production, you might want more sophisticated logic
        return domain;
    }

    // ========================================
    // CSR Toggle & Generation
    // ========================================
    function initCSRToggle() {
        var toggle = document.getElementById('isManualCsr');
        var csrSection = document.getElementById('csrSection');
        var autoSection = document.getElementById('autoGenSection');
        
        if (toggle) {
            toggle.addEventListener('change', function() {
                if (csrSection) {
                    csrSection.style.display = this.checked ? 'block' : 'none';
                }
                if (autoSection) {
                    autoSection.style.display = this.checked ? 'none' : 'block';
                }
            });
        }
        
        // Generate CSR button
        var generateBtn = document.getElementById('generateCsrBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', generateCSR);
        }
        
        // Decode CSR button
        var decodeBtn = document.getElementById('decodeCsrBtn');
        if (decodeBtn) {
            decodeBtn.addEventListener('click', decodeCSR);
        }
    }

    function generateCSR() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return;
        
        var domain = form.querySelector('.sslm-domain-input');
        var orgName = form.querySelector('[name="adminOrganizationName"]');
        var country = form.querySelector('[name="adminCountry"]');
        var state = form.querySelector('[name="adminProvince"]');
        var city = form.querySelector('[name="adminCity"]');
        var email = form.querySelector('[name="adminEmail"]');
        
        var data = {
            domain: domain ? domain.value : '',
            organization: orgName ? orgName.value : '',
            country: country ? country.value : 'VN',
            state: state ? state.value : '',
            city: city ? city.value : '',
            email: email ? email.value : ''
        };
        
        if (!data.domain) {
            showToast(lang.domain_required || 'Please enter a domain name first', 'error');
            return;
        }
        
        var btn = document.getElementById('generateCsrBtn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('sslm-loading');
        }
        
        ajaxRequest('generateCSR', data, function(response) {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('sslm-loading');
            }
            
            if (response.success && response.data) {
                var csrField = form.querySelector('[name="csr"]');
                var keyField = form.querySelector('[name="privateKey"]');
                
                if (csrField && response.data.csr) {
                    csrField.value = response.data.csr;
                }
                if (keyField && response.data.privateKey) {
                    keyField.value = response.data.privateKey;
                }
                
                // Show CSR section
                var toggle = document.getElementById('isManualCsr');
                if (toggle) {
                    toggle.checked = true;
                    toggle.dispatchEvent(new Event('change'));
                }
                
                showToast(lang.csr_generated || 'CSR generated successfully', 'success');
            } else {
                showToast(response.message || 'Failed to generate CSR', 'error');
            }
        });
    }

    function decodeCSR() {
        var csrField = document.querySelector('[name="csr"]');
        if (!csrField || !csrField.value.trim()) {
            showToast(lang.enter_csr || 'Please enter a CSR', 'error');
            return;
        }
        
        // Send CSR directly, not wrapped in data
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=decodeCsr', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success && response.data) {
                    var msg = (lang.csr_decoded || 'CSR Decoded') + ':\n';
                    msg += 'CN: ' + (response.data.CN || 'N/A') + '\n';
                    msg += 'O: ' + (response.data.O || 'N/A') + '\n';
                    msg += 'C: ' + (response.data.C || 'N/A');
                    alert(msg);
                } else {
                    showToast(response.message || 'Invalid CSR', 'error');
                }
            } catch (e) {
                showToast('Error decoding response', 'error');
            }
        };
        
        xhr.send('csr=' + encodeURIComponent(csrField.value));
    }

    // ========================================
    // Form Validation
    // ========================================
    function validateForm() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return false;
        
        var isValid = true;
        
        // Clear previous errors
        form.querySelectorAll('.sslm-error').forEach(function(el) {
            el.classList.remove('sslm-error');
        });
        
        // Validate domains
        var domainInputs = form.querySelectorAll('.sslm-domain-input');
        var hasDomain = false;
        domainInputs.forEach(function(input) {
            if (input.value.trim()) {
                hasDomain = true;
                // Basic domain validation
                var domainRegex = /^(\*\.)?([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/;
                if (!domainRegex.test(input.value.trim())) {
                    input.classList.add('sslm-error');
                    isValid = false;
                }
            }
        });
        
        if (!hasDomain) {
            showToast(lang.domain_required || 'At least one domain is required', 'error');
            isValid = false;
        }
        
        // Validate required contact fields
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
        var config = window.sslmConfig || {};
        var data = config.configData;
        
        console.log('Attempting to restore form data:', data);
        console.log('configData type:', typeof data);
        console.log('configData value:', data);
        
        if (!data || typeof data !== 'object') {
            console.log('No saved data to restore');
            return;
        }
        
        var form = document.getElementById('sslm-apply-form');
        if (!form) return;
        
        // Restore CSR
        if (data.csr) {
            var csrField = form.querySelector('[name="csr"]');
            if (csrField) {
                csrField.value = data.csr;
                // Show CSR section
                var toggle = document.getElementById('isManualCsr');
                if (toggle) {
                    toggle.checked = true;
                    toggle.dispatchEvent(new Event('change'));
                }
            }
        }
        
        // Restore Private Key
        if (data.privateKey) {
            var keyField = form.querySelector('[name="privateKey"]');
            if (keyField) {
                keyField.value = data.privateKey;
            }
        }
        
        // Restore Administrator info
        if (data.Administrator && Object.keys(data.Administrator).length > 0) {
            var admin = data.Administrator;
            var adminFieldMap = {
                'adminFirstName': admin.firstName,
                'adminLastName': admin.lastName,
                'adminEmail': admin.email,
                'adminPhone': admin.mobile || admin.phone,
                'adminTitle': admin.job || admin.title,
                'adminOrganizationName': admin.organation || admin.organization,
                'adminCountry': admin.country,
                'adminAddress': admin.address,
                'adminCity': admin.city,
                'adminProvince': admin.state || admin.province,
                'adminPostCode': admin.postCode || admin.postalCode
            };
            
            for (var fieldName in adminFieldMap) {
                if (adminFieldMap[fieldName]) {
                    var field = form.querySelector('[name="' + fieldName + '"]');
                    if (field) {
                        field.value = adminFieldMap[fieldName];
                    }
                }
            }
            
            console.log('Administrator info restored');
        }
        
        // Restore Organization info
        if (data.organizationInfo && Object.keys(data.organizationInfo).length > 0) {
            var org = data.organizationInfo;
            var orgFieldMap = {
                'organizationName': org.organizationName,
                'organizationAddress': org.organizationAddress,
                'organizationCity': org.organizationCity,
                'organizationCountry': org.organizationCountry,
                'organizationProvince': org.organizationState,
                'organizationPostalCode': org.organizationPostCode || org.organizationPostalCode,
                'organizationPhone': org.organizationMobile
            };
            
            for (var fieldName in orgFieldMap) {
                if (orgFieldMap[fieldName]) {
                    var field = form.querySelector('[name="' + fieldName + '"]');
                    if (field) {
                        field.value = orgFieldMap[fieldName];
                    }
                }
            }
            
            console.log('Organization info restored');
        }
        
        // Restore Domain info
        if (data.domainInfo && Array.isArray(data.domainInfo) && data.domainInfo.length > 0) {
            var domainList = document.getElementById('domainList');
            if (domainList) {
                // First, restore to existing row
                var firstRow = domainList.querySelector('.sslm-domain-row');
                if (firstRow && data.domainInfo[0]) {
                    var domainInput = firstRow.querySelector('.sslm-domain-input');
                    var dcvSelect = firstRow.querySelector('.sslm-dcv-select');
                    
                    if (domainInput) {
                        domainInput.value = data.domainInfo[0].domainName || '';
                    }
                    if (dcvSelect) {
                        // Initialize options first
                        initDomainDcvOptions(dcvSelect, data.domainInfo[0].domainName || '');
                        
                        var dcvValue = data.domainInfo[0].dcvMethod || 'CNAME_CSR_HASH';
                        var dcvEmail = data.domainInfo[0].dcvEmail || '';
                        
                        // If email validation, use the email as value
                        if (dcvValue === 'EMAIL' && dcvEmail) {
                            dcvSelect.value = dcvEmail;
                        } else {
                            dcvSelect.value = dcvValue;
                        }
                    }
                }
                
                // Add additional domain rows
                for (var i = 1; i < data.domainInfo.length; i++) {
                    addDomainRow(data.domainInfo[i]);
                }
                
                console.log('Domain info restored:', data.domainInfo.length, 'domains');
            }
        }
        
        console.log('Form data restored successfully');
    }

    // ========================================
    // Form Submit (Apply/Save Draft)
    // FIXED: Uses OLD MODULE data format
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
        
        // Collect all form data - MATCHING OLD MODULE STRUCTURE
        var domains = collectDomains();
        var contacts = collectContacts();
        
        // Get CSR value
        var csrField = form.querySelector('[name="csr"]');
        var csr = csrField ? csrField.value.trim() : '';
        
        // Get private key
        var keyField = form.querySelector('[name="privateKey"]');
        var privateKey = keyField ? keyField.value.trim() : '';
        
        // Build data object EXACTLY like OLD MODULE
        var data = {
            "server": 'other',
            "csr": csr,
            "privateKey": privateKey,
            "domainInfo": domains,
            "organizationInfo": collectOrganization(),
            "originalfromOthers": document.getElementById('isManualCsr') && 
                            document.getElementById('isManualCsr').checked ? '1' : '0',
            "Administrator": contacts
        };
        
        // Debug logging
        console.log('Form data to submit:', data);
        console.log('Domains collected:', domains);
        console.log('Contacts collected:', contacts);
        
        // Disable buttons and show loading
        if (submitBtn) submitBtn.disabled = true;
        if (saveBtn) saveBtn.disabled = true;
        
        var activeBtn = action === 'draft' ? saveBtn : submitBtn;
        if (activeBtn) activeBtn.classList.add('sslm-loading');
        
        // Build URL with step parameter
        var step = action === 'draft' ? 'savedraft' : 'applyssl';
        var ajaxUrl = config.ajaxUrl + '&step=' + step;
        
        // FIXED: Send data as "data" key - SAME AS OLD MODULE
        // Uses serializeObject to convert nested object to PHP array format
        var postData = serializeObjectForPHP(data, 'data');
        
        console.log('Serialized POST data:', postData);
        
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
            
            // Check for HTML response (error)
            if (responseText.indexOf('<!DOCTYPE') === 0 || responseText.indexOf('<html') === 0) {
                console.error('Server returned HTML instead of JSON');
                showToast('Server error occurred. Please check console.', 'error');
                return;
            }
            
            try {
                var response = JSON.parse(responseText);
                
                if (response.success) {
                    showToast(response.message || (action === 'draft' ? 
                        (lang.draft_saved || 'Draft saved') : 
                        (lang.submit_success || 'Request submitted')), 'success');
                    
                    // Reload page for submit action
                    if (action === 'submit') {
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    showToast(response.message || lang.error || 'An error occurred', 'error');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', responseText);
                showToast('Invalid server response', 'error');
            }
        };
        
        xhr.onerror = function() {
            if (submitBtn) submitBtn.disabled = false;
            if (saveBtn) saveBtn.disabled = false;
            if (activeBtn) activeBtn.classList.remove('sslm-loading');
            showToast(lang.network_error || 'Network error', 'error');
        };
        
        xhr.send(postData);
    }

    /**
     * Serialize object for PHP - converts nested object to PHP array notation
     * This mimics how jQuery $.ajax serializes nested objects
     * 
     * Example: {data: {name: "test"}} becomes "data[name]=test"
     */
    function serializeObjectForPHP(obj, prefix) {
        var pairs = [];
        
        function buildPairs(obj, currentPrefix) {
            for (var key in obj) {
                if (!obj.hasOwnProperty(key)) continue;
                
                var value = obj[key];
                var newPrefix = currentPrefix ? currentPrefix + '[' + key + ']' : key;
                
                if (value === null || value === undefined) {
                    pairs.push(encodeURIComponent(newPrefix) + '=');
                } else if (Array.isArray(value)) {
                    // Handle arrays
                    for (var i = 0; i < value.length; i++) {
                        var arrayPrefix = newPrefix + '[' + i + ']';
                        if (typeof value[i] === 'object' && value[i] !== null) {
                            buildPairs(value[i], arrayPrefix);
                        } else {
                            pairs.push(encodeURIComponent(arrayPrefix) + '=' + encodeURIComponent(value[i]));
                        }
                    }
                } else if (typeof value === 'object') {
                    buildPairs(value, newPrefix);
                } else {
                    pairs.push(encodeURIComponent(newPrefix) + '=' + encodeURIComponent(value));
                }
            }
        }
        
        // Wrap in the prefix key (e.g., "data")
        var wrapper = {};
        wrapper[prefix] = obj;
        buildPairs(wrapper, '');
        
        return pairs.join('&');
    }

    function collectDomains() {
        var domains = [];
        var rows = document.querySelectorAll('.sslm-domain-row');
        
        rows.forEach(function(row) {
            var domainInput = row.querySelector('.sslm-domain-input');
            var dcvSelect = row.querySelector('.sslm-dcv-select');
            
            if (domainInput && domainInput.value.trim()) {
                var dcvValue = dcvSelect ? dcvSelect.value : 'CNAME_CSR_HASH';
                var dcvMethod = dcvValue;
                var dcvEmail = '';
                
                // Check if it's an email value
                if (dcvValue && dcvValue.indexOf('@') !== -1) {
                    dcvMethod = 'EMAIL';
                    dcvEmail = dcvValue;
                }
                
                domains.push({
                    "domainName": domainInput.value.trim(),
                    "dcvMethod": dcvMethod,
                    "dcvEmail": dcvEmail
                });
            }
        });
        
        return domains;
    }

    function collectContacts() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return {};
        
        return {
            "job": getFieldValue(form, 'adminTitle') || 'IT Manager',
            "firstName": getFieldValue(form, 'adminFirstName'),
            "lastName": getFieldValue(form, 'adminLastName'),
            "email": getFieldValue(form, 'adminEmail'),
            "mobile": getFieldValue(form, 'adminPhone'),
            "organation": getFieldValue(form, 'adminOrganizationName'),
            "country": getFieldValue(form, 'adminCountry'),
            "address": getFieldValue(form, 'adminAddress'),
            "city": getFieldValue(form, 'adminCity'),
            "state": getFieldValue(form, 'adminProvince'),
            "postCode": getFieldValue(form, 'adminPostCode')
        };
    }

    function collectOrganization() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return {};
        
        var orgName = form.querySelector('[name="organizationName"]');
        if (!orgName || !orgName.value.trim()) {
            return {};
        }
        
        return {
            "organizationName": getFieldValue(form, 'organizationName'),
            "organizationAddress": getFieldValue(form, 'organizationAddress'),
            "organizationCity": getFieldValue(form, 'organizationCity'),
            "organizationCountry": getFieldValue(form, 'organizationCountry'),
            "organizationState": getFieldValue(form, 'organizationProvince'),
            "organizationPostCode": getFieldValue(form, 'organizationPostalCode'),
            "organizationMobile": getFieldValue(form, 'organizationPhone')
        };
    }

    function getFieldValue(form, name) {
        var field = form.querySelector('[name="' + name + '"]');
        return field ? field.value.trim() : '';
    }

    // ========================================
    // DCV Management
    // ========================================
    function initDCVManagement() {
        // Batch DCV update
        var batchDcvBtn = document.getElementById('batchDcvBtn');
        if (batchDcvBtn) {
            batchDcvBtn.addEventListener('click', batchUpdateDCV);
        }
        
        // Resend DCV email buttons
        document.querySelectorAll('.resend-dcv-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var domain = this.getAttribute('data-domain');
                resendDCVEmail(domain);
            });
        });
    }

    function batchUpdateDCV() {
        var dcvData = [];
        document.querySelectorAll('.dcv-update-row').forEach(function(row) {
            var domain = row.getAttribute('data-domain');
            var select = row.querySelector('.dcv-method-select');
            if (domain && select) {
                dcvData.push({
                    domainName: domain,
                    dcvMethod: select.value
                });
            }
        });
        
        if (dcvData.length === 0) {
            showToast('No DCV changes to update', 'warning');
            return;
        }
        
        ajaxRequest('batchUpdateDCV', dcvData, function(response) {
            if (response.success) {
                showToast(lang.dcv_updated || 'DCV methods updated', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Update failed', 'error');
            }
        });
    }

    function resendDCVEmail(domain) {
        if (!domain) return;
        
        // Send directly without data wrapper
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=resendDCVEmail', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    showToast(lang.dcv_email_sent || 'DCV email sent', 'success');
                } else {
                    showToast(response.message || 'Failed to send email', 'error');
                }
            } catch (e) {
                showToast('Invalid response', 'error');
            }
        };
        
        xhr.send('domain=' + encodeURIComponent(domain));
    }

    // ========================================
    // Download Handlers
    // ========================================
    function initDownloadHandlers() {
        document.querySelectorAll('.download-cert-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var format = this.getAttribute('data-format') || 'pem';
                downloadCertificate(format);
            });
        });
        
        // Download private key
        var downloadKeyBtn = document.getElementById('downloadKeyBtn');
        if (downloadKeyBtn) {
            downloadKeyBtn.addEventListener('click', function() {
                downloadCertificate('key');
            });
        }
    }

    function downloadCertificate(format) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=downCert', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            try {
                var response = JSON.parse(xhr.responseText);
                
                if (response.success && response.data) {
                    // Decode base64 and trigger download
                    var content = atob(response.data.content);
                    var filename = response.data.filename;
                    var mime = response.data.mime || 'application/octet-stream';
                    
                    triggerDownload(content, filename, mime);
                    
                    // Download CA bundle if available
                    if (response.data.caBundle) {
                        setTimeout(function() {
                            var caContent = atob(response.data.caBundle);
                            triggerDownload(caContent, response.data.caBundleFilename, mime);
                        }, 500);
                    }
                } else {
                    showToast(response.message || 'Download failed', 'error');
                }
            } catch (e) {
                showToast('Invalid response', 'error');
            }
        };
        
        xhr.send('format=' + encodeURIComponent(format));
    }

    function triggerDownload(content, filename, mime) {
        var blob = new Blob([content], { type: mime });
        var url = URL.createObjectURL(blob);
        
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        setTimeout(function() {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
    }

    // ========================================
    // Action Buttons (Cancel, Revoke, etc.)
    // ========================================
    function initActionButtons() {
        // Refresh status
        var refreshBtn = document.getElementById('refreshStatusBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                refreshStatus(this);
            });
        }
        
        // Cancel order
        var cancelBtn = document.getElementById('cancelOrderBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                if (confirm(lang.sure_to_cancel || 'Are you sure you want to cancel this order?')) {
                    cancelOrder();
                }
            });
        }
        
        // Revoke certificate
        var revokeBtn = document.getElementById('revokeBtn');
        if (revokeBtn) {
            revokeBtn.addEventListener('click', function() {
                if (confirm(lang.sure_to_revoke || 'Are you sure you want to revoke this certificate? This action cannot be undone.')) {
                    revokeCertificate();
                }
            });
        }
    }

    function refreshStatus(btn) {
        if (btn) {
            btn.disabled = true;
            btn.classList.add('sslm-loading');
        }
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=refreshStatus', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('sslm-loading');
            }
            
            try {
                var response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    showToast(lang.status_refreshed || 'Status refreshed', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(response.message || 'Refresh failed', 'error');
                }
            } catch (e) {
                showToast('Invalid response', 'error');
            }
        };
        
        xhr.send('');
    }

    function cancelOrder() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=cancelOrder', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            try {
                var response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    showToast(lang.cancelled_success || 'Order cancelled', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(response.message || 'Cancel failed', 'error');
                }
            } catch (e) {
                showToast('Invalid response', 'error');
            }
        };
        
        xhr.send('');
    }

    function revokeCertificate() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=revoke', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            try {
                var response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    showToast(lang.revoked_success || 'Certificate revoked', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(response.message || 'Revoke failed', 'error');
                }
            } catch (e) {
                showToast('Invalid response', 'error');
            }
        };
        
        xhr.send('');
    }

    // ========================================
    // AJAX Helper - For actions with data wrapper
    // ========================================
    function ajaxRequest(action, data, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=' + action, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            try {
                var response = JSON.parse(xhr.responseText);
                if (typeof callback === 'function') {
                    callback(response);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                if (typeof callback === 'function') {
                    callback({ success: false, message: 'Invalid response' });
                }
            }
        };
        
        xhr.onerror = function() {
            if (typeof callback === 'function') {
                callback({ success: false, message: 'Network error' });
            }
        };
        
        // Serialize data using PHP array notation
        var postData = serializeObjectForPHP(data, 'data');
        xhr.send(postData);
    }

    // ========================================
    // Toast Notifications
    // ========================================
    function showToast(message, type) {
        type = type || 'info';
        
        // Remove existing toasts
        var existing = document.querySelectorAll('.sslm-toast');
        existing.forEach(function(el) { el.remove(); });
        
        // Create toast
        var toast = document.createElement('div');
        toast.className = 'sslm-toast sslm-toast-' + type;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(function() {
            toast.classList.add('show');
        }, 10);
        
        // Auto remove
        setTimeout(function() {
            toast.classList.remove('show');
            setTimeout(function() {
                toast.remove();
            }, 300);
        }, 4000);
    }

    // Export for global access
    window.SSLManager = {
        showToast: showToast,
        refreshStatus: refreshStatus,
        saveDraft: window.saveDraft
    };

})();