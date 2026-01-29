/**
 * NicSRS SSL Module - Client Area JavaScript
 * Handles form interactions, AJAX requests, and UI updates
 * 
 * FIXED v2.0.2:
 * - DCV Email options now appear directly in dcvMethod dropdown
 * - No separate email dropdown needed
 * - Email options auto-update when domain changes
 * 
 * @package    nicsrs_ssl
 * @version    2.0.2
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

(function() {
    'use strict';

    // Wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        var config = window.sslmConfig || {};
        var lang = config.lang || {};
        
        console.log('SSL Manager v2.0.2 initializing...', config);
        
        // Initialize components
        initDomainHandlers();
        initCSRHandlers();
        initFormSubmit();
        restoreFormData();
        
        // Initialize email options for existing domains
        initDCVEmailOptions();
        
        console.log('SSL Manager initialized');
    }

    // ========================================
    // Domain Management
    // ========================================
    function initDomainHandlers() {
        var config = window.sslmConfig || {};
        
        // Add domain button
        var addBtn = document.getElementById('addDomainBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                var domainList = document.getElementById('domainList');
                var currentCount = domainList.querySelectorAll('.sslm-domain-row').length;
                
                if (currentCount >= config.maxDomains) {
                    showToast(config.lang.max_domains_reached || 'Maximum domains reached', 'warning');
                    return;
                }
                
                addDomainRow();
            });
        }
        
        // Remove domain handlers (delegated)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.sslm-remove-domain')) {
                var row = e.target.closest('.sslm-domain-row');
                if (row) {
                    removeDomainRow(row);
                }
            }
        });
        
        // Domain input change handlers - update DCV email options when domain changes
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('sslm-domain-input')) {
                // Debounce the update
                clearTimeout(e.target._dcvTimeout);
                e.target._dcvTimeout = setTimeout(function() {
                    var row = e.target.closest('.sslm-domain-row');
                    if (row) {
                        updateDCVEmailOptions(row, e.target.value);
                    }
                }, 300);
            }
        });
        
        // Also update on blur for immediate feedback
        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('sslm-domain-input')) {
                var row = e.target.closest('.sslm-domain-row');
                if (row && e.target.value) {
                    updateDCVEmailOptions(row, e.target.value);
                }
            }
        }, true);
    }

    /**
     * Initialize DCV email options for all existing domain rows
     */
    function initDCVEmailOptions() {
        var rows = document.querySelectorAll('#domainList .sslm-domain-row');
        rows.forEach(function(row) {
            var domainInput = row.querySelector('.sslm-domain-input');
            if (domainInput && domainInput.value) {
                var dcvSelect = row.querySelector('.sslm-dcv-select');
                var currentValue = dcvSelect ? dcvSelect.value : '';
                updateDCVEmailOptions(row, domainInput.value, currentValue);
            }
        });
    }

    /**
     * Update DCV email options in the dcvMethod dropdown
     * 
     * @param {HTMLElement} row - The domain row element
     * @param {string} domain - The domain name
     * @param {string} selectedValue - Currently selected value to preserve
     */
    function updateDCVEmailOptions(row, domain, selectedValue) {
        var dcvSelect = row.querySelector('.sslm-dcv-select');
        if (!dcvSelect || !domain) return;
        
        // Get current selected value if not provided
        if (selectedValue === undefined) {
            selectedValue = dcvSelect.value;
        }
        
        // Clean domain (remove wildcard prefix)
        var cleanDomain = domain.replace(/^\*\./, '').trim();
        if (!cleanDomain) return;
        
        // Find or create email optgroup
        var emailOptgroup = dcvSelect.querySelector('optgroup.dcv-email-options');
        if (!emailOptgroup) {
            emailOptgroup = document.createElement('optgroup');
            emailOptgroup.label = 'Email Validation';
            emailOptgroup.className = 'dcv-email-options';
            dcvSelect.appendChild(emailOptgroup);
        }
        
        // Clear existing email options
        emailOptgroup.innerHTML = '';
        
        // Generate standard DCV email addresses
        var prefixes = ['admin', 'administrator', 'webmaster', 'hostmaster', 'postmaster'];
        var emails = [];
        
        prefixes.forEach(function(prefix) {
            emails.push(prefix + '@' + cleanDomain);
        });
        
        // If subdomain, also add parent domain emails
        var parts = cleanDomain.split('.');
        if (parts.length > 2) {
            var parentDomain = parts.slice(1).join('.');
            prefixes.forEach(function(prefix) {
                var email = prefix + '@' + parentDomain;
                if (emails.indexOf(email) === -1) {
                    emails.push(email);
                }
            });
        }
        
        // Add email options to optgroup
        emails.forEach(function(email) {
            var option = document.createElement('option');
            option.value = email;
            option.text = email;
            if (selectedValue === email) {
                option.selected = true;
            }
            emailOptgroup.appendChild(option);
        });
        
        // If selected value is an email not in our list, add it
        if (selectedValue && selectedValue.indexOf('@') !== -1 && emails.indexOf(selectedValue) === -1) {
            var customOption = document.createElement('option');
            customOption.value = selectedValue;
            customOption.text = selectedValue;
            customOption.selected = true;
            emailOptgroup.insertBefore(customOption, emailOptgroup.firstChild);
        }
    }

    function addDomainRow(data) {
        var template = document.getElementById('domainRowTemplate');
        var domainList = document.getElementById('domainList');
        
        if (!template || !domainList) return;
        
        var clone = template.content.cloneNode(true);
        var row = clone.querySelector('.sslm-domain-row');
        
        // Set domain number
        var currentCount = domainList.querySelectorAll('.sslm-domain-row').length;
        var numberSpan = row.querySelector('.sslm-domain-number');
        if (numberSpan) {
            numberSpan.textContent = currentCount + 1;
        }
        
        // Set data index
        row.setAttribute('data-index', currentCount);
        
        // Pre-fill data if provided
        if (data) {
            var domainInput = row.querySelector('.sslm-domain-input');
            var dcvSelect = row.querySelector('.sslm-dcv-select');
            
            if (domainInput && data.domainName) {
                domainInput.value = data.domainName;
            }
            
            // After adding to DOM, update email options and set value
            domainList.appendChild(clone);
            
            // Update email options for this domain
            var addedRow = domainList.querySelector('.sslm-domain-row[data-index="' + currentCount + '"]');
            if (addedRow && data.domainName) {
                updateDCVEmailOptions(addedRow, data.domainName, data.dcvMethod);
            }
            
            updateDomainNumbers();
            return;
        }
        
        domainList.appendChild(clone);
        updateDomainNumbers();
    }

    function removeDomainRow(row) {
        var domainList = document.getElementById('domainList');
        var rows = domainList.querySelectorAll('.sslm-domain-row');
        
        // Don't remove if it's the only row
        if (rows.length <= 1) {
            showToast('At least one domain is required', 'warning');
            return;
        }
        
        row.remove();
        updateDomainNumbers();
    }

    function updateDomainNumbers() {
        var rows = document.querySelectorAll('#domainList .sslm-domain-row');
        rows.forEach(function(row, index) {
            var numberSpan = row.querySelector('.sslm-domain-number');
            if (numberSpan) {
                numberSpan.textContent = index + 1;
            }
            row.setAttribute('data-index', index);
            
            // First row can't be removed
            var removeBtn = row.querySelector('.sslm-remove-domain');
            if (removeBtn) {
                removeBtn.style.visibility = index === 0 ? 'hidden' : 'visible';
            }
        });
    }

    // ========================================
    // CSR Handlers
    // ========================================
    function initCSRHandlers() {
        // CSR toggle
        var csrToggle = document.getElementById('isManualCsr');
        if (csrToggle) {
            csrToggle.addEventListener('change', function() {
                var csrSection = document.getElementById('csrSection');
                var autoSection = document.getElementById('autoGenSection');
                
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
        var config = window.sslmConfig || {};
        var lang = config.lang || {};
        
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
        var config = window.sslmConfig || {};
        var lang = config.lang || {};
        
        var csrField = document.querySelector('[name="csr"]');
        if (!csrField || !csrField.value.trim()) {
            showToast(lang.enter_csr || 'Please enter a CSR', 'error');
            return;
        }
        
        var btn = document.getElementById('decodeCsrBtn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('sslm-loading');
        }
        
        // Send CSR directly
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl + '&step=decodeCsr', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('sslm-loading');
            }
            
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success && response.data) {
                    displayCSRInfo(response.data);
                    showToast(lang.csr_decoded || 'CSR decoded successfully', 'success');
                } else {
                    showToast(response.message || lang.invalid_csr || 'Invalid CSR', 'error');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                showToast('Error decoding response', 'error');
            }
        };
        
        xhr.onerror = function() {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('sslm-loading');
            }
            showToast(lang.network_error || 'Network error', 'error');
        };
        
        xhr.send('csr=' + encodeURIComponent(csrField.value));
    }

    function displayCSRInfo(data) {
        var resultDiv = document.getElementById('csrDecodeResult');
        if (!resultDiv) return;
        
        // Update individual fields
        var fields = {
            'csrCN': data.CN || data.commonName || '-',
            'csrO': data.O || data.organization || '-',
            'csrC': data.C || data.country || '-',
            'csrST': data.ST || data.state || '-',
            'csrL': data.L || data.locality || '-',
            'csrKeySize': data.keySize ? data.keySize + ' bits' : '-',
            'csrKeyType': data.keyType || '-'
        };
        
        for (var id in fields) {
            var el = document.getElementById(id);
            if (el) {
                el.textContent = fields[id];
            }
        }
        
        // Show the result div
        resultDiv.style.display = 'block';
        
        // Auto-fill first domain if empty
        var firstDomainInput = document.querySelector('#domainList .sslm-domain-input');
        if (firstDomainInput && !firstDomainInput.value && (data.CN || data.commonName)) {
            var cn = data.CN || data.commonName;
            firstDomainInput.value = cn;
            // Update email options for this domain
            var row = firstDomainInput.closest('.sslm-domain-row');
            if (row) {
                updateDCVEmailOptions(row, cn);
            }
        }
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
        var config = window.sslmConfig || {};
        var lang = config.lang || {};
        
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
        
        // Get CSR value
        var csrField = form.querySelector('[name="csr"]');
        var csr = csrField ? csrField.value.trim() : '';
        
        // Get private key
        var keyField = form.querySelector('[name="privateKey"]');
        var privateKey = keyField ? keyField.value.trim() : '';
        
        // Get isRenew value
        var isRenewValue = '0';
        var isRenewChecked = form.querySelector('input[name="isRenew"]:checked');
        if (isRenewChecked) {
            isRenewValue = isRenewChecked.value;
        }
        
        // Build data object - SAME FORMAT AS OLD MODULE
        var data = {
            "server": 'other',
            "csr": csr,
            "privateKey": privateKey,
            "domainInfo": domains,
            "organizationInfo": collectOrganization(),
            "originalfromOthers": isRenewValue,
            "isRenew": isRenewValue,
            "Administrator": contacts
        };
        
        console.log('Form data to submit:', data);
        
        // Disable buttons and show loading
        if (submitBtn) submitBtn.disabled = true;
        if (saveBtn) saveBtn.disabled = true;
        
        var activeBtn = action === 'draft' ? saveBtn : submitBtn;
        if (activeBtn) activeBtn.classList.add('sslm-loading');
        
        // Build URL with step parameter
        var step = action === 'draft' ? 'savedraft' : 'applyssl';
        var ajaxUrl = config.ajaxUrl + '&step=' + step;
        
        // Send data as "data" key
        var postData = serializeObjectForPHP(data, 'data');
        
        // Submit via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (submitBtn) submitBtn.disabled = false;
            if (saveBtn) saveBtn.disabled = false;
            if (activeBtn) activeBtn.classList.remove('sslm-loading');
            
            var responseText = xhr.responseText.trim();
            console.log('Raw response:', responseText);
            
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
     * Collect domains - dcvMethod contains the email directly if email validation
     */
    function collectDomains() {
        var domains = [];
        var rows = document.querySelectorAll('#domainList .sslm-domain-row');
        
        rows.forEach(function(row) {
            var domainInput = row.querySelector('.sslm-domain-input');
            var dcvSelect = row.querySelector('.sslm-dcv-select');
            
            if (domainInput && domainInput.value.trim()) {
                var dcvValue = dcvSelect ? dcvSelect.value : 'CNAME_CSR_HASH';
                
                var domainData = {
                    domainName: domainInput.value.trim(),
                    dcvMethod: dcvValue
                };
                
                // Note: If dcvMethod contains '@', it's an email
                // The old module and API handle this - email in dcvMethod field means EMAIL validation
                
                domains.push(domainData);
            }
        });
        
        return domains;
    }

    function collectContacts() {
        var form = document.getElementById('sslm-apply-form');
        if (!form) return {};
        
        return {
            organation: getValue(form, 'adminOrganizationName'),
            job: getValue(form, 'adminTitle'),
            firstName: getValue(form, 'adminFirstName'),
            lastName: getValue(form, 'adminLastName'),
            email: getValue(form, 'adminEmail'),
            mobile: getValue(form, 'adminPhone'),
            country: getValue(form, 'adminCountry'),
            address: getValue(form, 'adminAddress'),
            city: getValue(form, 'adminCity'),
            state: getValue(form, 'adminProvince'),
            postCode: getValue(form, 'adminPostCode')
        };
    }

    function collectOrganization() {
        var form = document.getElementById('sslm-apply-form');
        var orgPart = document.getElementById('organizationPart');
        
        if (!form || !orgPart) return {};
        
        return {
            organizationName: getValue(form, 'organizationName'),
            organizationAddress: getValue(form, 'organizationAddress'),
            organizationCity: getValue(form, 'organizationCity'),
            organizationState: getValue(form, 'organizationState'),
            organizationCountry: getValue(form, 'organizationCountry'),
            organizationPostCode: getValue(form, 'organizationPostCode'),
            organizationMobile: getValue(form, 'organizationMobile'),
            organizationRegNumber: getValue(form, 'organizationRegNumber')
        };
    }

    function getValue(form, name) {
        var el = form.querySelector('[name="' + name + '"]');
        return el ? el.value.trim() : '';
    }

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
                    value.forEach(function(item, index) {
                        if (typeof item === 'object' && item !== null) {
                            buildPairs(item, newPrefix + '[' + index + ']');
                        } else {
                            pairs.push(encodeURIComponent(newPrefix + '[' + index + ']') + '=' + encodeURIComponent(item));
                        }
                    });
                } else if (typeof value === 'object') {
                    buildPairs(value, newPrefix);
                } else {
                    pairs.push(encodeURIComponent(newPrefix) + '=' + encodeURIComponent(value));
                }
            }
        }
        
        buildPairs(obj, prefix);
        return pairs.join('&');
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
                var domainRegex = /^(\*\.)?([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/;
                if (!domainRegex.test(input.value.trim())) {
                    input.classList.add('sslm-error');
                    isValid = false;
                }
            }
        });
        
        if (!hasDomain) {
            var config = window.sslmConfig || {};
            showToast(config.lang.domain_required || 'At least one domain is required', 'error');
            isValid = false;
        }
        
        // Validate DCV method selected
        var dcvSelects = form.querySelectorAll('.sslm-dcv-select');
        dcvSelects.forEach(function(select) {
            var row = select.closest('.sslm-domain-row');
            var domainInput = row ? row.querySelector('.sslm-domain-input') : null;
            if (domainInput && domainInput.value.trim() && !select.value) {
                select.classList.add('sslm-error');
                isValid = false;
            }
        });
        
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
        
        if (!data || typeof data !== 'object') {
            console.log('No saved data to restore');
            return;
        }
        
        var form = document.getElementById('sslm-apply-form');
        if (!form) return;
        
        // Restore isRenew
        var isRenewValue = data.originalfromOthers || data.isRenew || '0';
        var isRenewRadio = form.querySelector('input[name="isRenew"][value="' + isRenewValue + '"]');
        if (isRenewRadio) {
            isRenewRadio.checked = true;
        }
        
        // Restore CSR
        if (data.csr) {
            var csrField = form.querySelector('[name="csr"]');
            if (csrField) {
                csrField.value = data.csr;
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
            var fieldMap = {
                'adminOrganizationName': admin.organation || '',
                'adminTitle': admin.job || '',
                'adminFirstName': admin.firstName || '',
                'adminLastName': admin.lastName || '',
                'adminEmail': admin.email || '',
                'adminPhone': admin.mobile || '',
                'adminCountry': admin.country || '',
                'adminAddress': admin.address || '',
                'adminCity': admin.city || '',
                'adminProvince': admin.state || '',
                'adminPostCode': admin.postCode || ''
            };
            
            for (var fieldName in fieldMap) {
                var field = form.querySelector('[name="' + fieldName + '"]');
                if (field && fieldMap[fieldName]) {
                    field.value = fieldMap[fieldName];
                }
            }
        }
        
        // Restore Organization info
        if (data.organizationInfo && Object.keys(data.organizationInfo).length > 0) {
            var org = data.organizationInfo;
            var orgFields = ['organizationName', 'organizationAddress', 'organizationCity', 
                           'organizationState', 'organizationCountry', 'organizationPostCode', 
                           'organizationMobile', 'organizationRegNumber'];
            
            orgFields.forEach(function(fieldName) {
                var field = form.querySelector('[name="' + fieldName + '"]');
                if (field && org[fieldName]) {
                    field.value = org[fieldName];
                }
            });
        }
        
        console.log('Form data restored successfully');
    }

    // ========================================
    // DCV Method Change - NEW v2.0.3
    // ========================================
    var currentDCVDomain = null;
    
    function openChangeDCVModal(domain, currentMethod) {
        currentDCVDomain = domain;
        
        var modal = document.getElementById('dcvChangeModal');
        if (!modal) {
            createDCVChangeModal();
            modal = document.getElementById('dcvChangeModal');
        }
        
        // Set domain name
        var domainLabel = modal.querySelector('.dcv-domain-label');
        if (domainLabel) {
            domainLabel.textContent = domain;
        }
        
        // Reset and set current method
        var methodSelect = modal.querySelector('#newDcvMethod');
        if (methodSelect) {
            methodSelect.value = '';
            
            // Mark current method
            Array.from(methodSelect.options).forEach(function(opt) {
                if (opt.value === currentMethod) {
                    opt.textContent = opt.textContent.replace(' (Current)', '') + ' (Current)';
                } else {
                    opt.textContent = opt.textContent.replace(' (Current)', '');
                }
            });
        }
        
        // Generate email options
        updateModalDCVEmails(domain);
        
        // Show modal
        modal.classList.add('show');
    }
    
    function closeChangeDCVModal() {
        var modal = document.getElementById('dcvChangeModal');
        if (modal) {
            modal.classList.remove('show');
        }
        currentDCVDomain = null;
    }
    
    function createDCVChangeModal() {
        var config = window.sslmConfig || {};
        var lang = config.lang || {};
        
        var modalHtml = `
            <div id="dcvChangeModal" class="sslm-modal-overlay">
                <div class="sslm-modal">
                    <div class="sslm-modal-header">
                        <h3><i class="fas fa-exchange-alt"></i> ${lang.change_dcv_method || 'Change DCV Method'}</h3>
                        <button type="button" class="sslm-modal-close" onclick="SSLManager.closeChangeDCVModal()">&times;</button>
                    </div>
                    <div class="sslm-modal-body">
                        <div class="sslm-form-group">
                            <label>${lang.domain || 'Domain'}</label>
                            <div class="dcv-domain-label" style="font-weight:600;color:var(--sslm-primary);font-size:16px;"></div>
                        </div>
                        <div class="sslm-form-group">
                            <label>${lang.new_dcv_method || 'New DCV Method'} <span class="required">*</span></label>
                            <select id="newDcvMethod" class="sslm-select" onchange="SSLManager.onDCVMethodChange(this.value)">
                                <option value="">${lang.please_choose || '-- Select Method --'}</option>
                                <optgroup label="${lang.file_dns_validation || 'File/DNS Validation'}">
                                    <option value="HTTP_CSR_HASH">${lang.http_file || 'HTTP File Validation'}</option>
                                    <option value="HTTPS_CSR_HASH">${lang.https_file || 'HTTPS File Validation'}</option>
                                    <option value="CNAME_CSR_HASH">${lang.dns_cname || 'DNS CNAME Validation'}</option>
                                </optgroup>
                                <optgroup label="${lang.email_validation || 'Email Validation'}">
                                    <option value="EMAIL">${lang.email || 'Email Validation'}</option>
                                </optgroup>
                            </select>
                        </div>
                        <div id="dcvEmailSection" class="sslm-form-group" style="display:none;">
                            <label>${lang.dcv_email || 'DCV Email'} <span class="required">*</span></label>
                            <select id="newDcvEmail" class="sslm-select">
                                <option value="">${lang.please_choose || '-- Select Email --'}</option>
                            </select>
                            <p class="sslm-help-text">${lang.dcv_email_note || 'Select an email address to receive the validation email.'}</p>
                        </div>
                        <div class="sslm-alert sslm-alert-info" style="margin-top:16px;">
                            <i class="fas fa-info-circle"></i>
                            <div>${lang.dcv_change_note || 'After changing the DCV method, you will need to complete the new validation process.'}</div>
                        </div>
                    </div>
                    <div class="sslm-modal-footer">
                        <button type="button" class="sslm-btn sslm-btn-secondary" onclick="SSLManager.closeChangeDCVModal()">
                            ${lang.cancel || 'Cancel'}
                        </button>
                        <button type="button" id="confirmDcvChangeBtn" class="sslm-btn sslm-btn-primary" onclick="SSLManager.confirmChangeDCV()">
                            <i class="fas fa-check"></i> ${lang.confirm_change || 'Confirm Change'}
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Close on overlay click
        var modal = document.getElementById('dcvChangeModal');
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeChangeDCVModal();
            }
        });
    }
    
    function onDCVMethodChange(method) {
        var emailSection = document.getElementById('dcvEmailSection');
        if (emailSection) {
            emailSection.style.display = (method === 'EMAIL') ? 'block' : 'none';
        }
    }
    
    function updateModalDCVEmails(domain) {
        var emailSelect = document.getElementById('newDcvEmail');
        if (!emailSelect) return;
        
        // Clear existing options (except first)
        while (emailSelect.options.length > 1) {
            emailSelect.remove(1);
        }
        
        // Get base domain
        var baseDomain = domain.replace(/^\*\./, '');
        var parts = baseDomain.split('.');
        
        // Standard prefixes
        var prefixes = ['admin', 'administrator', 'hostmaster', 'postmaster', 'webmaster'];
        var emails = [];
        
        prefixes.forEach(function(prefix) {
            emails.push(prefix + '@' + baseDomain);
        });
        
        // If subdomain, add parent domain emails
        if (parts.length > 2) {
            var parentDomain = parts.slice(1).join('.');
            prefixes.forEach(function(prefix) {
                emails.push(prefix + '@' + parentDomain);
            });
        }
        
        // Add options
        emails.forEach(function(email) {
            var opt = document.createElement('option');
            opt.value = email;
            opt.textContent = email;
            emailSelect.appendChild(opt);
        });
    }
    
    function confirmChangeDCV() {
        if (!currentDCVDomain) return;
        
        var methodSelect = document.getElementById('newDcvMethod');
        var emailSelect = document.getElementById('newDcvEmail');
        var newMethod = methodSelect ? methodSelect.value : '';
        var newEmail = emailSelect ? emailSelect.value : '';
        
        if (!newMethod) {
            showToast('Please select a DCV method', 'error');
            return;
        }
        
        if (newMethod === 'EMAIL' && !newEmail) {
            showToast('Please select a DCV email', 'error');
            return;
        }
        
        var config = window.sslmConfig || {};
        var lang = config.lang || {};
        var btn = document.getElementById('confirmDcvChangeBtn');
        
        if (btn) {
            btn.disabled = true;
            btn.classList.add('sslm-loading');
        }
        
        // Build data
        var dcvData = {
            domains: [{
                domainName: currentDCVDomain,
                dcvMethod: newMethod === 'EMAIL' ? 'EMAIL' : newMethod,
                dcvEmail: newMethod === 'EMAIL' ? newEmail : ''
            }]
        };
        
        ajaxRequest('batchUpdateDCV', dcvData, function(response) {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('sslm-loading');
            }
            
            if (response.success) {
                showToast(lang.dcv_changed || 'DCV method changed successfully', 'success');
                closeChangeDCVModal();
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(response.message || lang.error || 'Failed to change DCV method', 'error');
            }
        });
    }
    
    // ========================================
    // Copy to Clipboard - Enhanced
    // ========================================
    function copyToClipboard(text, btn) {
        var copyText = function() {
            // Fallback for older browsers
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        };
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyFeedback(btn);
            }).catch(function() {
                copyText();
                showCopyFeedback(btn);
            });
        } else {
            copyText();
            showCopyFeedback(btn);
        }
    }
    
    function showCopyFeedback(btn) {
        if (btn) {
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.add('copied');
            
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('copied');
            }, 2000);
        }
        
        showToast('Copied to clipboard!', 'success');
    }
    
    // ========================================
    // Loading Overlay
    // ========================================
    function showLoading(message) {
        var overlay = document.getElementById('sslmLoadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sslmLoadingOverlay';
            overlay.className = 'sslm-loading-overlay';
            overlay.innerHTML = `
                <div class="sslm-loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="loading-text">${message || 'Loading...'}</p>
                </div>
            `;
            document.body.appendChild(overlay);
        } else {
            var textEl = overlay.querySelector('.loading-text');
            if (textEl) {
                textEl.textContent = message || 'Loading...';
            }
            overlay.style.display = 'flex';
        }
    }
    
    function hideLoading() {
        var overlay = document.getElementById('sslmLoadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }    

    // ========================================
    // AJAX Helper
    // ========================================
    function ajaxRequest(action, data, callback) {
        var config = window.sslmConfig || {};
        
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
        
        var postData = serializeObjectForPHP(data, 'data');
        xhr.send(postData);
    }

    // ========================================
    // Status Refresh
    // ========================================
    function refreshStatus() {
        var config = window.sslmConfig || {};
        
        var btn = document.getElementById('refreshStatusBtn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('sslm-loading');
        }
        
        ajaxRequest('refreshStatus', {}, function(response) {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('sslm-loading');
            }
            
            if (response.success) {
                showToast('Status refreshed', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(response.message || 'Failed to refresh status', 'error');
            }
        });
    }

    // ========================================
    // Toast Notifications
    // ========================================
    function showToast(message, type) {
        type = type || 'info';
        
        var existing = document.querySelectorAll('.sslm-toast');
        existing.forEach(function(el) { el.remove(); });
        
        var toast = document.createElement('div');
        toast.className = 'sslm-toast sslm-toast-' + type;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(function() {
            toast.classList.remove('show');
            setTimeout(function() {
                toast.remove();
            }, 300);
        }, 4000);
    }

    // ========================================
    // Export for global access
    // ========================================
    window.SSLManager = {
        showToast: showToast,
        refreshStatus: refreshStatus,
        saveDraft: window.saveDraft,
        updateDCVEmailOptions: updateDCVEmailOptions,
        openChangeDCVModal: openChangeDCVModal,
        closeChangeDCVModal: closeChangeDCVModal,
        confirmChangeDCV: confirmChangeDCV,
        onDCVMethodChange: onDCVMethodChange,
        copyToClipboard: copyToClipboard,
        showLoading: showLoading,
        hideLoading: hideLoading
    };

})();