/**
 * Shared Form Utilities
 * Global utilities for form validation, API handling, and common operations
 * Used across all forms in the biosecsamoasys application
 */

// FormUtils object - main utility container
const FormUtils = {
    // Show message with type styling
    showMessage: (elementId, message, type = 'info') => {
        const el = document.getElementById(elementId);
        if (!el) {
            console.warn(`Message element with id "${elementId}" not found`);
            return;
        }

        el.textContent = message;
        el.className = `message message-${type}`;
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                el.style.display = 'none';
            }, 5000);
        }
    },

    // Hide message element
    hideMessage: (elementId) => {
        const el = document.getElementById(elementId);
        if (el) {
            el.style.display = 'none';
        }
    },

    // Show loading state on button
    showLoading: (buttonElement, message = 'Submitting...') => {
        if (!buttonElement) return;

        buttonElement.disabled = true;
        buttonElement.dataset.originalText = buttonElement.textContent;
        buttonElement.textContent = message;
    },

    // Hide loading state on button
    hideLoading: (buttonElement) => {
        if (!buttonElement) return;

        buttonElement.disabled = false;
        buttonElement.textContent = buttonElement.dataset.originalText || 'Submit';
    },

    // Handle API errors and display user-friendly message
    handleApiError: (error, elementId, customMessage = null) => {
        console.error('API Error:', error);
        const message = customMessage || 'An error occurred. Please check your connection and try again.';
        FormUtils.showMessage(elementId, message, 'error');
    },

    // Validate CSRF token exists in form
    validateCsrfToken: (formElement) => {
        const csrfToken = formElement.querySelector('[name="csrf_token"]')?.value;
        if (!csrfToken) {
            console.error('CSRF token not found in form');
            return false;
        }
        return true;
    },

    // Safely get element by ID (with optional caching)
    getElement: (id, cache) => {
        if (cache && cache.has(id)) {
            return cache.get(id);
        }
        const element = document.getElementById(id);
        if (cache && element) {
            cache.set(id, element);
        }
        return element;
    },

    // Format date to YYYY-MM-DD
    formatDate: (date) => {
        try {
            return date.toISOString().split('T')[0];
        } catch (error) {
            console.error('Error formatting date:', error);
            return '';
        }
    },

    // Set today's date for date input
    setTodayDate: (inputId) => {
        const input = document.getElementById(inputId);
        if (input) {
            const today = new Date();
            input.value = FormUtils.formatDate(today);
        }
    },

    // Validate required field
    validateRequired: (fieldId, errorElementId, errorMessage = 'This field is required') => {
        const field = document.getElementById(fieldId);
        const errorEl = document.getElementById(errorElementId);

        if (!field) return false;

        const isValid = field.value && field.value.trim() !== '';

        if (errorEl) {
            errorEl.textContent = isValid ? '' : errorMessage;
            errorEl.style.display = isValid ? 'none' : 'block';
        }

        // Set ARIA attributes for accessibility
        field.setAttribute('aria-invalid', !isValid);

        return isValid;
    },

    // Reset form and reload default values
    resetForm: (formId, options = {}) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.reset();

        // Set today's date if option enabled
        if (options.setTodayDate && options.dateFieldId) {
            FormUtils.setTodayDate(options.dateFieldId);
        }

        // Clear validation messages
        if (options.clearMessages) {
            const errorElements = form.querySelectorAll('.validation-error, .message');
            errorElements.forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });
        }
    },

    // Confirm before action
    confirmAction: (message) => {
        return confirm(message);
    },

    // Debounce function for search inputs
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Check if element is visible
    isVisible: (element) => {
        return element && element.offsetParent !== null;
    },

    // Handle form submission with duplicate prevention
    handleFormSubmission: async (formId, apiEndpoint, options = {}) => {
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Form with id "${formId}" not found`);
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const messageElementId = options.messageElementId || `${formId.replace('form', '')}Message`;

        // Prevent duplicate submission
        if (submitBtn && submitBtn.disabled) {
            FormUtils.showMessage(messageElementId, 'Form is already being submitted, please wait...', 'info');
            return;
        }

        try {
            // Show loading state
            FormUtils.showLoading(submitBtn);
            FormUtils.hideMessage(messageElementId);

            // Create FormData
            const formData = new FormData(form);

            // Add custom data if provided
            if (options.additionalData) {
                Object.entries(options.additionalData).forEach(([key, value]) => {
                    formData.set(key, value);
                });
            }

            // Submit to API
            const response = await fetch(apiEndpoint, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                FormUtils.showMessage(messageElementId, data.message || 'Success!', 'success');

                // Reset form if option enabled
                if (options.resetForm) {
                    FormUtils.resetForm(formId, options.resetOptions);
                }

                // Reload data if callback provided
                if (options.onSuccess) {
                    options.onSuccess(data);
                }

                // Scroll to message
                const messageEl = document.getElementById(messageElementId);
                if (messageEl) {
                    messageEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            } else {
                FormUtils.showMessage(messageElementId, 'Error: ' + (data.message || 'Unknown error'), 'error');

                if (options.onError) {
                    options.onError(data);
                }
            }

            return data;
        } catch (error) {
            FormUtils.handleApiError(error, messageElementId, options.errorMessage);

            if (options.onError) {
                options.onError(error);
            }
        } finally {
            // Always hide loading state
            FormUtils.hideLoading(submitBtn);
        }
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormUtils;
}
