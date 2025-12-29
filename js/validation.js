/* ============================================
   TASKMASTER - VALIDATION MODULE
   Author: Your Name
   Version: 2.0
   Description: Client-side form validation
   ============================================ */

/**
 * Validation Module
 * Handles all form validation logic
 */
const Validator = (function() {
    'use strict';

    // ==========================================
    // VALIDATION RULES
    // ==========================================
    const rules = {
        required: {
            validate: (value) => value.trim().length > 0,
            message: 'This field is required'
        },
        minLength: {
            validate: (value, min) => value.trim().length >= min,
            message: (min) => `Must be at least ${min} characters`
        },
        maxLength: {
            validate: (value, max) => value.trim().length <= max,
            message: (max) => `Must be no more than ${max} characters`
        },
        noSpecialChars: {
            validate: (value) => /^[a-zA-Z0-9\s\-_.,!?'"\u0600-\u06FF]+$/.test(value),
            message: 'Contains invalid characters'
        },
        validDate: {
            validate: (value) => {
                if (!value) return true; // Optional field
                const date = new Date(value);
                return !isNaN(date.getTime());
            },
            message: 'Please enter a valid date'
        },
        futureDate: {
            validate: (value) => {
                if (!value) return true; // Optional field
                const inputDate = new Date(value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return inputDate >= today;
            },
            message: 'Date cannot be in the past'
        },
        notEmpty: {
            validate: (value) => value !== null && value !== undefined && value !== '',
            message: 'Please select an option'
        }
    };

    // ==========================================
    // VALIDATION FUNCTIONS
    // ==========================================

    /**
     * Validate a single field
     * @param {string} value - The field value
     * @param {Array} validations - Array of validation rules to apply
     * @returns {Object} - { isValid: boolean, errors: Array }
     */
    function validateField(value, validations) {
        const errors = [];
        
        validations.forEach(validation => {
            const ruleName = typeof validation === 'string' ? validation : validation.rule;
            const param = typeof validation === 'object' ? validation.param : null;
            
            if (rules[ruleName]) {
                const isValid = param 
                    ? rules[ruleName].validate(value, param)
                    : rules[ruleName].validate(value);
                
                if (!isValid) {
                    const message = typeof rules[ruleName].message === 'function'
                        ? rules[ruleName].message(param)
                        : rules[ruleName].message;
                    errors.push(message);
                }
            }
        });

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Validate task title
     * @param {string} title - Task title
     * @returns {Object} - Validation result
     */
    function validateTaskTitle(title) {
        return validateField(title, [
            'required',
            { rule: 'minLength', param: 2 },
            { rule: 'maxLength', param: 100 }
        ]);
    }

    /**
     * Validate task description
     * @param {string} description - Task description
     * @returns {Object} - Validation result
     */
    function validateTaskDescription(description) {
        if (!description || description.trim() === '') {
            return { isValid: true, errors: [] };
        }
        
        return validateField(description, [
            { rule: 'maxLength', param: 500 }
        ]);
    }

    /**
     * Validate task due date
     * @param {string} date - Due date string
     * @returns {Object} - Validation result
     */
    function validateDueDate(date) {
        return validateField(date, [
            'validDate'
        ]);
    }

    /**
     * Validate task priority
     * @param {string} priority - Priority value
     * @returns {Object} - Validation result
     */
    function validatePriority(priority) {
        const validPriorities = ['low', 'medium', 'high'];
        return {
            isValid: validPriorities.includes(priority),
            errors: validPriorities.includes(priority) ? [] : ['Invalid priority selected']
        };
    }

    /**
     * Validate task category
     * @param {string} category - Category value
     * @returns {Object} - Validation result
     */
    function validateCategory(category) {
        const validCategories = ['personal', 'work', 'study', 'shopping'];
        return {
            isValid: validCategories.includes(category),
            errors: validCategories.includes(category) ? [] : ['Invalid category selected']
        };
    }

    /**
     * Validate entire task form
     * @param {Object} taskData - Task data object
     * @returns {Object} - { isValid: boolean, errors: Object }
     */
    function validateTaskForm(taskData) {
        const errors = {};
        let isValid = true;

        // Validate title
        const titleValidation = validateTaskTitle(taskData.title);
        if (!titleValidation.isValid) {
            errors.title = titleValidation.errors;
            isValid = false;
        }

        // Validate description (if provided)
        if (taskData.description) {
            const descValidation = validateTaskDescription(taskData.description);
            if (!descValidation.isValid) {
                errors.description = descValidation.errors;
                isValid = false;
            }
        }

        // Validate priority
        const priorityValidation = validatePriority(taskData.priority);
        if (!priorityValidation.isValid) {
            errors.priority = priorityValidation.errors;
            isValid = false;
        }

        // Validate category
        const categoryValidation = validateCategory(taskData.category);
        if (!categoryValidation.isValid) {
            errors.category = categoryValidation.errors;
            isValid = false;
        }

        // Validate due date (if provided)
        if (taskData.dueDate) {
            const dateValidation = validateDueDate(taskData.dueDate);
            if (!dateValidation.isValid) {
                errors.dueDate = dateValidation.errors;
                isValid = false;
            }
        }

        return { isValid, errors };
    }

    // ==========================================
    // UI FEEDBACK FUNCTIONS
    // ==========================================

    /**
     * Show error state on input field
     * @param {jQuery} $input - jQuery input element
     * @param {string} message - Error message
     */
    function showError($input, message) {
        $input.addClass('error').removeClass('success');
        
        // Find or create error message element
        let $errorEl = $input.siblings('.error-message');
        if ($errorEl.length === 0) {
            $errorEl = $('<span class="error-message"></span>');
            $input.after($errorEl);
        }
        
        $errorEl.text(message).addClass('visible');
        
        // Add shake animation
        $input.addClass('animate-shake');
        setTimeout(() => $input.removeClass('animate-shake'), 500);
    }

    /**
     * Show success state on input field
     * @param {jQuery} $input - jQuery input element
     */
    function showSuccess($input) {
        $input.addClass('success').removeClass('error');
        $input.siblings('.error-message').removeClass('visible').text('');
    }

    /**
     * Clear all validation states
     * @param {jQuery} $form - jQuery form element
     */
    function clearValidation($form) {
        $form.find('input, select, textarea')
            .removeClass('error success');
        $form.find('.error-message')
            .removeClass('visible')
            .text('');
    }

    /**
     * Real-time validation on input
     * @param {jQuery} $input - jQuery input element
     * @param {Function} validationFn - Validation function to apply
     */
    function setupRealTimeValidation($input, validationFn) {
        $input.on('blur', function() {
            const value = $(this).val();
            const result = validationFn(value);
            
            if (result.isValid) {
                showSuccess($(this));
            } else {
                showError($(this), result.errors[0]);
            }
        });

        $input.on('input', function() {
            // Clear error state while typing
            $(this).removeClass('error');
            $(this).siblings('.error-message').removeClass('visible');
        });
    }

    // ==========================================
    // SANITIZATION FUNCTIONS
    // ==========================================

    /**
     * Sanitize string input (prevent XSS)
     * @param {string} str - Input string
     * @returns {string} - Sanitized string
     */
    function sanitizeString(str) {
        if (typeof str !== 'string') return '';
        
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#x27;',
            '/': '&#x2F;',
        };
        
        return str.replace(/[&<>"'/]/g, char => map[char]);
    }

    /**
     * Sanitize task data object
     * @param {Object} taskData - Task data
     * @returns {Object} - Sanitized task data
     */
    function sanitizeTaskData(taskData) {
        return {
            title: sanitizeString(taskData.title.trim()),
            description: taskData.description ? sanitizeString(taskData.description.trim()) : '',
            priority: sanitizeString(taskData.priority),
            category: sanitizeString(taskData.category),
            dueDate: taskData.dueDate || null
        };
    }

    // ==========================================
    // PUBLIC API
    // ==========================================
    return {
        validateField,
        validateTaskTitle,
        validateTaskDescription,
        validateDueDate,
        validatePriority,
        validateCategory,
        validateTaskForm,
        showError,
        showSuccess,
        clearValidation,
        setupRealTimeValidation,
        sanitizeString,
        sanitizeTaskData,
        rules
    };

})();

// Make Validator available globally
window.Validator = Validator;