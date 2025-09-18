/**
 * Loading Manager Component
 * Provides loading states and AJAX request management
 */
export class LoadingManager {
  static activeRequests = new Set();
  static defaultOptions = {
    showSpinner: true,
    disableForm: true,
    spinnerClass: 'loading-spinner',
    loadingText: 'Processing...'
  };

  static async wrapAjax(element, requestPromise, options = {}) {
    const config = { ...this.defaultOptions, ...options };
    const requestId = this.generateRequestId();

    try {
      this.startLoading(element, config, requestId);
      const result = await requestPromise;
      return result;
    } catch (error) {
      this.handleError(element, error);
      throw error;
    } finally {
      this.stopLoading(element, config, requestId);
    }
  }

  static startLoading(element, config, requestId) {
    this.activeRequests.add(requestId);

    if (config.disableForm && element.tagName === 'FORM') {
      this.disableForm(element);
    }

    if (config.showSpinner) {
      this.showSpinner(element, config);
    }

    element.setAttribute('data-loading', 'true');
    element.setAttribute('data-request-id', requestId);
  }

  static stopLoading(element, config, requestId) {
    this.activeRequests.delete(requestId);

    if (config.disableForm && element.tagName === 'FORM') {
      this.enableForm(element);
    }

    if (config.showSpinner) {
      this.hideSpinner(element, config);
    }

    element.removeAttribute('data-loading');
    element.removeAttribute('data-request-id');
  }

  static disableForm(form) {
    const fields = form.querySelectorAll('input, select, textarea, button');
    fields.forEach(field => {
      field.disabled = true;
      field.setAttribute('data-was-disabled', field.disabled);
    });
  }

  static enableForm(form) {
    const fields = form.querySelectorAll('input, select, textarea, button');
    fields.forEach(field => {
      const wasDisabled = field.getAttribute('data-was-disabled') === 'true';
      if (!wasDisabled) {
        field.disabled = false;
      }
      field.removeAttribute('data-was-disabled');
    });
  }

  static showSpinner(element, config) {
    let spinner = element.querySelector(`.${config.spinnerClass}`);

    if (!spinner) {
      spinner = document.createElement('div');
      spinner.className = config.spinnerClass;
      spinner.innerHTML = `
                <div class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ${config.loadingText}
                </div>
            `;

      // Position spinner
      if (element.tagName === 'FORM') {
        const submitButton = element.querySelector('button[type="submit"]');
        if (submitButton) {
          submitButton.appendChild(spinner);
        } else {
          element.appendChild(spinner);
        }
      } else {
        element.appendChild(spinner);
      }
    }

    spinner.style.display = 'inline-flex';
  }

  static hideSpinner(element, config) {
    const spinner = element.querySelector(`.${config.spinnerClass}`);
    if (spinner) {
      spinner.style.display = 'none';
    }
  }

  static handleError(element, error) {
    console.error('LoadingManager: Request failed', error);

    // Show error message if container exists
    const errorContainer = element.querySelector('.form-errors') ||
      element.parentNode.querySelector('.form-errors');

    if (errorContainer) {
      const errorDiv = document.createElement('div');
      errorDiv.className = 'alert alert-danger';
      errorDiv.textContent = error.message || 'An error occurred. Please try again.';
      errorContainer.appendChild(errorDiv);

      // Auto-hide after 5 seconds
      setTimeout(() => errorDiv.remove(), 5000);
    }
  }

  static generateRequestId() {
    return 'req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }

  static isLoading(element) {
    return element.getAttribute('data-loading') === 'true';
  }

  static cancelAll() {
    this.activeRequests.clear();
    document.querySelectorAll('[data-loading="true"]').forEach(element => {
      const requestId = element.getAttribute('data-request-id');
      if (requestId) {
        this.stopLoading(element, this.defaultOptions, requestId);
      }
    });
  }
}

// Global AJAX error handler
window.addEventListener('unhandledrejection', (event) => {
  if (event.reason && event.reason.name === 'AbortError') {
    // Don't show error for aborted requests
    event.preventDefault();
  }
});

// Make available globally
window.LoadingManager = LoadingManager;
