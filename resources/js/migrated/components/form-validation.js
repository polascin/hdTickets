/**
 * Form Validator Component
 * Provides real-time form validation with custom error messages
 */
export class FormValidator {
  constructor(form, options = {}) {
    this.form = form;
    this.options = {
      errorContainer: null,
      customMessages: {},
      validateOnInput: true,
      validateOnBlur: true,
      ...options
    };

    this.errors = new Map();
    this.init();
  }

  init() {
    if (this.options.validateOnInput) {
      this.form.addEventListener('input', this.handleInput.bind(this));
    }

    if (this.options.validateOnBlur) {
      this.form.addEventListener('blur', this.handleBlur.bind(this), true);
    }

    this.form.addEventListener('submit', this.handleSubmit.bind(this));
  }

  handleInput(e) {
    const field = e.target;
    if (field.name) {
      this.validateField(field);
    }
  }

  handleBlur(e) {
    const field = e.target;
    if (field.name && this.errors.has(field.name)) {
      this.validateField(field);
    }
  }

  handleSubmit(e) {
    if (!this.validateForm()) {
      e.preventDefault();
      this.showErrors();
    }
  }

  validateField(field) {
    const rules = this.getFieldRules(field);
    const errors = [];

    for (const rule of rules) {
      const result = this.applyRule(field, rule);
      if (result !== true) {
        errors.push(result);
      }
    }

    if (errors.length > 0) {
      this.errors.set(field.name, errors);
      this.markFieldInvalid(field, errors[0]);
    } else {
      this.errors.delete(field.name);
      this.markFieldValid(field);
    }

    return errors.length === 0;
  }

  validateForm() {
    let isValid = true;
    const fields = this.form.querySelectorAll('input, select, textarea');

    fields.forEach(field => {
      if (field.name && !this.validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  getFieldRules(field) {
    const rules = [];

    if (field.required) {
      rules.push('required');
    }

    if (field.type === 'email') {
      rules.push('email');
    }

    if (field.type === 'password' && field.name === 'password') {
      rules.push('password-strength');
    }

    if (field.name === 'password_confirmation') {
      rules.push('confirm');
    }

    return rules;
  }

  applyRule(field, rule) {
    switch (rule) {
      case 'required':
        return field.value.trim() ? true : this.getMessage(field.name + '.required') || 'This field is required';

      case 'email': {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(field.value) ? true : this.getMessage(field.name + '.email') || 'Please enter a valid email address';
      }

      case 'password-strength': {
        const password = field.value;
        if (password.length < 8) return this.getMessage(field.name + '.password-strength') || 'Password must be at least 8 characters long';
        if (!/[A-Z]/.test(password)) return 'Password must contain at least one uppercase letter';
        if (!/[a-z]/.test(password)) return 'Password must contain at least one lowercase letter';
        if (!/\d/.test(password)) return 'Password must contain at least one number';
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) return 'Password must contain at least one special character';
        return true;
      }

      case 'confirm': {
        const passwordField = this.form.querySelector('input[name="password"]');
        return passwordField && field.value === passwordField.value ? true :
          this.getMessage(field.name + '.confirm') || 'Password confirmation does not match';
      }

      default:
        return true;
    }
  }

  getMessage(key) {
    return this.options.customMessages[key] || null;
  }

  markFieldInvalid(field, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');

    let errorElement = field.parentNode.querySelector('.field-error-message');
    if (!errorElement) {
      errorElement = document.createElement('div');
      errorElement.className = 'field-error-message';
      field.parentNode.appendChild(errorElement);
    }
    errorElement.textContent = message;
  }

  markFieldValid(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');

    const errorElement = field.parentNode.querySelector('.field-error-message');
    if (errorElement) {
      errorElement.remove();
    }
  }

  showErrors() {
    if (this.options.errorContainer && this.errors.size > 0) {
      const errorHtml = Array.from(this.errors.values())
        .flat()
        .map(error => `<div class="alert alert-danger">${error}</div>`)
        .join('');

      this.options.errorContainer.innerHTML = errorHtml;
    }
  }

  clearErrors() {
    this.errors.clear();
    this.form.querySelectorAll('.is-invalid, .is-valid').forEach(field => {
      field.classList.remove('is-invalid', 'is-valid');
    });

    this.form.querySelectorAll('.field-error-message').forEach(el => el.remove());

    if (this.options.errorContainer) {
      this.options.errorContainer.innerHTML = '';
    }
  }
}

// Make available globally if needed
window.FormValidator = FormValidator;
