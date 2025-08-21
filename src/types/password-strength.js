/**
 * Password Strength Indicator Component
 * Uses zxcvbn library for password strength evaluation
 */

import zxcvbn from 'zxcvbn';

export class PasswordStrengthIndicator {
    constructor(inputSelector, options = {}) {
        this.input = document.querySelector(inputSelector);
        this.options = {
            containerClass: 'password-strength-container',
            strengthBarClass: 'password-strength-bar',
            strengthTextClass: 'password-strength-text',
            requirementsClass: 'password-requirements',
            showRequirements: true,
            showEstimations: true,
            userInputs: [], // Array of user-specific inputs to avoid (name, email, etc.)
            ...options
        };
        
        this.strengthLevels = [
            { label: 'Very Weak', color: '#ff4444', bgColor: '#ffebee' },
            { label: 'Weak', color: '#ff8800', bgColor: '#fff3e0' },
            { label: 'Fair', color: '#ffcc02', bgColor: '#fffde7' },
            { label: 'Good', color: '#88cc00', bgColor: '#f1f8e9' },
            { label: 'Strong', color: '#00aa00', bgColor: '#e8f5e8' }
        ];
        
        this.requirements = [
            { test: (pwd) => pwd.length >= 8, text: 'At least 8 characters' },
            { test: (pwd) => /[a-z]/.test(pwd), text: 'One lowercase letter' },
            { test: (pwd) => /[A-Z]/.test(pwd), text: 'One uppercase letter' },
            { test: (pwd) => /\d/.test(pwd), text: 'One number' },
            { test: (pwd) => /[!@#$%^&*(),.?":{}|<>]/.test(pwd), text: 'One special character' },
            { test: (pwd) => pwd.length >= 12, text: 'At least 12 characters (recommended)' }
        ];

        this.init();
    }

    init() {
        if (!this.input) {
            console.error('Password input not found');
            return;
        }

        this.createUI();
        this.bindEvents();
    }

    createUI() {
        // Create main container
        this.container = document.createElement('div');
        this.container.className = this.options.containerClass;
        this.container.innerHTML = `
            <div class="${this.options.strengthBarClass}" style="display: none;">
                <div class="strength-bar-fill" style="width: 0%; transition: all 0.3s ease;"></div>
            </div>
            <div class="${this.options.strengthTextClass}" style="display: none;"></div>
            ${this.options.showRequirements ? this.createRequirementsHTML() : ''}
            ${this.options.showEstimations ? '<div class="password-estimations" style="display: none;"></div>' : ''}
        `;

        // Insert after the password input
        this.input.parentNode.insertBefore(this.container, this.input.nextSibling);
        
        // Get references to created elements
        this.strengthBar = this.container.querySelector(`.${this.options.strengthBarClass}`);
        this.strengthBarFill = this.strengthBar.querySelector('.strength-bar-fill');
        this.strengthText = this.container.querySelector(`.${this.options.strengthTextClass}`);
        this.requirementsContainer = this.container.querySelector(`.${this.options.requirementsClass}`);
        this.estimationsContainer = this.container.querySelector('.password-estimations');

        this.applyStyles();
    }

    createRequirementsHTML() {
        return `
            <div class="${this.options.requirementsClass}" style="margin-top: 8px;">
                <div class="requirements-title" style="font-size: 12px; color: #666; margin-bottom: 4px;">Password Requirements:</div>
                ${this.requirements.map((req, index) => `
                    <div class="requirement-item" data-requirement="${index}" style="display: flex; align-items: center; font-size: 12px; margin-bottom: 2px;">
                        <span class="requirement-icon" style="margin-right: 6px; width: 12px;">
                            <svg width="12" height="12" viewBox="0 0 12 12" style="display: none;">
                                <path d="M10 3L4.5 8.5L2 6" stroke="#00aa00" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <svg width="12" height="12" viewBox="0 0 12 12" style="display: inline;">
                                <circle cx="6" cy="6" r="3" fill="#ccc"/>
                            </svg>
                        </span>
                        <span class="requirement-text">${req.text}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    applyStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .${this.options.containerClass} {
                margin-top: 8px;
            }
            
            .${this.options.strengthBarClass} {
                height: 6px;
                background-color: #e0e0e0;
                border-radius: 3px;
                overflow: hidden;
                margin-bottom: 6px;
            }
            
            .strength-bar-fill {
                height: 100%;
                border-radius: 3px;
                transition: all 0.3s ease;
            }
            
            .${this.options.strengthTextClass} {
                font-size: 12px;
                font-weight: 500;
                margin-bottom: 8px;
            }
            
            .requirement-item.met .requirement-text {
                color: #00aa00;
                text-decoration: line-through;
            }
            
            .requirement-item.met .requirement-icon svg:first-child {
                display: inline !important;
            }
            
            .requirement-item.met .requirement-icon svg:last-child {
                display: none !important;
            }
            
            .password-estimations {
                font-size: 11px;
                color: #666;
                margin-top: 6px;
                padding: 6px;
                background-color: #f8f9fa;
                border-radius: 4px;
            }
            
            .estimation-item {
                margin-bottom: 2px;
            }
            
            .feedback-warning {
                color: #ff8800;
                margin-bottom: 4px;
            }
            
            .feedback-suggestion {
                color: #666;
                font-style: italic;
            }
        `;
        
        if (!document.querySelector('#password-strength-styles')) {
            style.id = 'password-strength-styles';
            document.head.appendChild(style);
        }
    }

    bindEvents() {
        this.input.addEventListener('input', (e) => {
            this.updateStrength(e.target.value);
        });

        this.input.addEventListener('focus', () => {
            if (this.options.showRequirements) {
                this.requirementsContainer.style.display = 'block';
            }
        });

        this.input.addEventListener('blur', () => {
            if (this.input.value === '' && this.options.showRequirements) {
                this.requirementsContainer.style.display = 'none';
            }
        });
    }

    updateStrength(password) {
        if (!password) {
            this.hideIndicators();
            return;
        }

        // Show indicators when there's input
        this.showIndicators();

        // Use zxcvbn to evaluate password strength
        const result = zxcvbn(password, this.options.userInputs);
        
        // Update strength bar and text
        this.updateStrengthDisplay(result);
        
        // Update requirements
        if (this.options.showRequirements) {
            this.updateRequirements(password);
        }
        
        // Update estimations
        if (this.options.showEstimations) {
            this.updateEstimations(result);
        }

        // Dispatch custom event
        this.input.dispatchEvent(new CustomEvent('passwordStrengthUpdate', {
            detail: {
                score: result.score,
                strength: this.strengthLevels[result.score].label,
                feedback: result.feedback,
                crackTimeDisplay: result.crack_times_display,
                requirementsMet: this.getRequirementsMet(password)
            }
        }));
    }

    updateStrengthDisplay(result) {
        const level = this.strengthLevels[result.score];
        const percentage = ((result.score + 1) / 5) * 100;
        
        // Update bar
        this.strengthBarFill.style.width = `${percentage}%`;
        this.strengthBarFill.style.backgroundColor = level.color;
        
        // Update text
        this.strengthText.textContent = level.label;
        this.strengthText.style.color = level.color;
    }

    updateRequirements(password) {
        this.requirements.forEach((req, index) => {
            const item = this.requirementsContainer.querySelector(`[data-requirement="${index}"]`);
            const isMet = req.test(password);
            
            if (isMet) {
                item.classList.add('met');
            } else {
                item.classList.remove('met');
            }
        });
    }

    updateEstimations(result) {
        const estimations = [];
        
        if (result.feedback.warning) {
            estimations.push(`<div class="feedback-warning">⚠️ ${result.feedback.warning}</div>`);
        }
        
        if (result.feedback.suggestions.length > 0) {
            estimations.push(`<div class="feedback-suggestion">💡 ${result.feedback.suggestions.join(' ')}</div>`);
        }
        
        estimations.push(`<div class="estimation-item"><strong>Time to crack:</strong> ${result.crack_times_display.offline_slow_hashing_1e4_per_second}</div>`);
        
        if (result.guesses_log10) {
            estimations.push(`<div class="estimation-item"><strong>Guesses needed:</strong> ~10^${Math.round(result.guesses_log10)}</div>`);
        }

        this.estimationsContainer.innerHTML = estimations.join('');
    }

    showIndicators() {
        this.strengthBar.style.display = 'block';
        this.strengthText.style.display = 'block';
        if (this.options.showEstimations) {
            this.estimationsContainer.style.display = 'block';
        }
    }

    hideIndicators() {
        this.strengthBar.style.display = 'none';
        this.strengthText.style.display = 'none';
        this.strengthBarFill.style.width = '0%';
        if (this.options.showEstimations) {
            this.estimationsContainer.style.display = 'none';
        }
        if (this.options.showRequirements) {
            this.requirementsContainer.style.display = 'none';
            // Reset requirements
            this.requirements.forEach((req, index) => {
                const item = this.requirementsContainer.querySelector(`[data-requirement="${index}"]`);
                item.classList.remove('met');
            });
        }
    }

    getRequirementsMet(password) {
        return this.requirements.map(req => req.test(password));
    }

    getStrengthScore(password) {
        if (!password) return 0;
        return zxcvbn(password, this.options.userInputs).score;
    }

    setUserInputs(inputs) {
        this.options.userInputs = inputs;
    }

    destroy() {
        if (this.container && this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
    }
}

// Auto-initialize if password field exists
document.addEventListener('DOMContentLoaded', function() {
    // Look for password inputs that should have strength indicators
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength-indicator]');
    
    passwordInputs.forEach(input => {
        const userInputs = [];
        
        // Try to gather user-specific inputs to avoid
        const nameInput = document.querySelector('input[name="name"]');
        const emailInput = document.querySelector('input[name="email"]');
        const usernameInput = document.querySelector('input[name="username"]');
        
        if (nameInput && nameInput.value) userInputs.push(nameInput.value);
        if (emailInput && emailInput.value) userInputs.push(emailInput.value.split('@')[0]);
        if (usernameInput && usernameInput.value) userInputs.push(usernameInput.value);
        
        new PasswordStrengthIndicator(`#${input.id}`, {
            userInputs: userInputs,
            showRequirements: input.dataset.showRequirements !== 'false',
            showEstimations: input.dataset.showEstimations !== 'false'
        });
    });
});

export default PasswordStrengthIndicator;
