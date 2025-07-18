/**
 * Login Page JavaScript
 * Handles password visibility toggle and form enhancements
 */

document.addEventListener('DOMContentLoaded', () => {
    setupFormEnhancements();
});

/**
 * Setup form enhancements
 */
function setupFormEnhancements() {
    // Auto-focus username field
    const usernameField = document.getElementById('username');
    if (usernameField) {
        usernameField.focus();
    }
    
    // Add loading state to form submission
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Enter key navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.tagName === 'INPUT') {
                const form = activeElement.closest('form');
                if (form) {
                    form.requestSubmit();
                }
            }
        }
    });
}

/**
 * Toggle password visibility
 */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleButton.className = 'fas fa-eye';
    }
}

/**
 * Handle form submission
 */
function handleFormSubmit(e) {
    const submitButton = e.target.querySelector('.login-btn');
    if (submitButton) {
        submitButton.classList.add('loading');
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
    }
}

/**
 * Show error message
 */
function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-error';
    alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    const form = document.querySelector('.login-form');
    form.parentNode.insertBefore(alertDiv, form);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

/**
 * Show success message
 */
function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success';
    alertDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    
    const form = document.querySelector('.login-form');
    form.parentNode.insertBefore(alertDiv, form);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
} 