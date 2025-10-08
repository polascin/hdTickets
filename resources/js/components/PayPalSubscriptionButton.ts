/**
 * PayPal Subscription Button Component
 * 
 * Handles PayPal subscription creation, approval flows, and error handling
 * for sports event ticket subscription payments.
 */

interface PayPalSubscriber {
    name: {
        given_name: string;
        surname: string;
    };
    email_address: string;
    shipping_address: {
        address_line_1: string;
        admin_area_2: string; // City
        admin_area_1: string; // State/Province
        postal_code: string;
        country_code: string;
    };
}

interface PayPalSubscriptionData {
    plan_id: string;
    subscriber: PayPalSubscriber;
    application_context?: {
        brand_name?: string;
        locale?: string;
        shipping_preference?: 'SET_PROVIDED_ADDRESS' | 'NO_SHIPPING' | 'GET_FROM_FILE';
        user_action?: 'SUBSCRIBE_NOW' | 'CONTINUE';
        payment_method?: {
            payer_selected?: 'PAYPAL';
            payee_preferred?: 'UNRESTRICTED' | 'IMMEDIATE_PAYMENT_REQUIRED';
        };
    };
}

interface BillingInfo {
    firstName: string;
    lastName: string;
    email: string;
    address: string;
    city: string;
    state: string;
    postalCode: string;
    country: string;
}

interface PayPalButtonStyle {
    layout?: 'vertical' | 'horizontal';
    color?: 'gold' | 'blue' | 'silver' | 'black' | 'white';
    shape?: 'rect' | 'pill';
    label?: 'paypal' | 'checkout' | 'buynow' | 'pay' | 'installment' | 'subscribe';
    tagline?: boolean;
    height?: number;
}

interface PayPalApprovalData {
    subscriptionID: string;
    orderID?: string;
    payerID?: string;
}

interface PayPalError {
    name: string;
    message: string;
    details?: Array<{
        issue: string;
        description: string;
    }>;
}

interface PayPalActions {
    subscription: {
        create: (data: PayPalSubscriptionData) => Promise<string>;
    };
}

class PayPalSubscriptionButton {
    private containerId: string;
    private planId: string;
    private billingInfo: BillingInfo;
    private isRendered: boolean = false;
    private isProcessing: boolean = false;
    private onSuccess?: (subscriptionId: string) => Promise<void>;
    private onError?: (error: string) => void;
    private onCancel?: () => void;

    constructor(
        containerId: string,
        planId: string,
        billingInfo: BillingInfo,
        callbacks: {
            onSuccess?: (subscriptionId: string) => Promise<void>;
            onError?: (error: string) => void;
            onCancel?: () => void;
        } = {}
    ) {
        this.containerId = containerId;
        this.planId = planId;
        this.billingInfo = billingInfo;
        this.onSuccess = callbacks.onSuccess;
        this.onError = callbacks.onError;
        this.onCancel = callbacks.onCancel;
    }

    /**
     * Render the PayPal subscription button
     */
    public async render(style: PayPalButtonStyle = {}): Promise<void> {
        if (typeof window.paypal === 'undefined') {
            this.handleError('PayPal SDK not loaded. Please refresh the page and try again.');
            return;
        }

        if (this.isRendered) {
            this.clearButton();
        }

        const container = document.getElementById(this.containerId);
        if (!container) {
            this.handleError(`Container element ${this.containerId} not found`);
            return;
        }

        const defaultStyle: PayPalButtonStyle = {
            layout: 'vertical',
            color: 'blue',
            shape: 'rect',
            label: 'subscribe',
            height: 50,
            tagline: false,
            ...style
        };

        try {
            await window.paypal.Buttons({
                style: defaultStyle,
                createSubscription: this.createSubscription.bind(this),
                onApprove: this.onApprove.bind(this),
                onError: this.onPayPalError.bind(this),
                onCancel: this.onPayPalCancel.bind(this)
            }).render(`#${this.containerId}`);

            this.isRendered = true;
            console.log('PayPal subscription button rendered successfully');
        } catch (error) {
            console.error('Error rendering PayPal button:', error);
            this.handleError('Failed to load PayPal payment option. Please try again.');
        }
    }

    /**
     * Create PayPal subscription
     */
    private async createSubscription(
        data: any,
        actions: PayPalActions
    ): Promise<string> {
        try {
            const subscriptionData: PayPalSubscriptionData = {
                plan_id: this.planId,
                subscriber: {
                    name: {
                        given_name: this.billingInfo.firstName,
                        surname: this.billingInfo.lastName
                    },
                    email_address: this.billingInfo.email,
                    shipping_address: {
                        address_line_1: this.billingInfo.address,
                        admin_area_2: this.billingInfo.city,
                        admin_area_1: this.billingInfo.state,
                        postal_code: this.billingInfo.postalCode,
                        country_code: this.billingInfo.country
                    }
                },
                application_context: {
                    brand_name: 'HD Tickets',
                    locale: 'en-GB', // British English as per rules
                    shipping_preference: 'SET_PROVIDED_ADDRESS',
                    user_action: 'SUBSCRIBE_NOW',
                    payment_method: {
                        payer_selected: 'PAYPAL',
                        payee_preferred: 'IMMEDIATE_PAYMENT_REQUIRED'
                    }
                }
            };

            const subscriptionId = await actions.subscription.create(subscriptionData);
            
            // Log subscription creation for analytics
            this.logEvent('paypal_subscription_created', {
                plan_id: this.planId,
                subscription_id: subscriptionId
            });

            return subscriptionId;
        } catch (error) {
            console.error('Error creating PayPal subscription:', error);
            throw new Error('Failed to create subscription. Please try again.');
        }
    }

    /**
     * Handle PayPal subscription approval
     */
    private async onApprove(data: PayPalApprovalData): Promise<void> {
        if (this.isProcessing) {
            return;
        }

        this.isProcessing = true;
        this.showLoadingState(true);

        try {
            console.log('PayPal subscription approved:', data.subscriptionID);
            
            // Log approval for analytics
            this.logEvent('paypal_subscription_approved', {
                subscription_id: data.subscriptionID,
                payer_id: data.payerID
            });

            if (this.onSuccess) {
                await this.onSuccess(data.subscriptionID);
            } else {
                // Default success handling - call backend to activate subscription
                await this.activateSubscription(data.subscriptionID);
            }
        } catch (error) {
            console.error('Error handling PayPal approval:', error);
            
            // Log error for analytics
            this.logEvent('paypal_subscription_approval_error', {
                subscription_id: data.subscriptionID,
                error: error instanceof Error ? error.message : 'Unknown error'
            });

            this.handleError(
                error instanceof Error 
                    ? error.message 
                    : 'Failed to complete subscription. Please contact support.'
            );
        } finally {
            this.isProcessing = false;
            this.showLoadingState(false);
        }
    }

    /**
     * Handle PayPal errors
     */
    private onPayPalError(err: PayPalError): void {
        console.error('PayPal error:', err);
        
        // Log error for analytics
        this.logEvent('paypal_subscription_error', {
            error_name: err.name,
            error_message: err.message,
            error_details: err.details
        });

        let errorMessage = 'PayPal payment failed. Please try again.';
        
        if (err.details && err.details.length > 0) {
            const detail = err.details[0];
            errorMessage = detail.description || errorMessage;
        }

        this.handleError(errorMessage);
    }

    /**
     * Handle PayPal cancellation
     */
    private onPayPalCancel(data: any): void {
        console.log('PayPal payment cancelled:', data);
        
        // Log cancellation for analytics
        this.logEvent('paypal_subscription_cancelled', {
            subscription_id: data.subscriptionID,
            order_id: data.orderID
        });

        if (this.onCancel) {
            this.onCancel();
        } else {
            this.showMessage('Payment was cancelled. You can try again at any time.', 'info');
        }
    }

    /**
     * Activate subscription on backend
     */
    private async activateSubscription(subscriptionId: string): Promise<void> {
        const response = await fetch('/api/v1/subscriptions/paypal/activate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                subscription_id: subscriptionId,
                billing_info: this.billingInfo
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to activate subscription');
        }

        // Redirect to success page
        if (result.redirect_url) {
            window.location.href = result.redirect_url;
        }
    }

    /**
     * Update billing information
     */
    public updateBillingInfo(billingInfo: Partial<BillingInfo>): void {
        this.billingInfo = { ...this.billingInfo, ...billingInfo };
        
        // Re-render if already rendered to update subscriber info
        if (this.isRendered) {
            this.render();
        }
    }

    /**
     * Update plan ID and re-render
     */
    public updatePlan(planId: string): void {
        this.planId = planId;
        
        if (this.isRendered) {
            this.render();
        }
    }

    /**
     * Clear the PayPal button
     */
    public clearButton(): void {
        const container = document.getElementById(this.containerId);
        if (container) {
            container.innerHTML = '';
        }
        this.isRendered = false;
    }

    /**
     * Show/hide loading state
     */
    private showLoadingState(show: boolean): void {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        if (show) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = `${this.containerId}-loading`;
            loadingDiv.className = 'flex items-center justify-center p-4 text-gray-600';
            loadingDiv.innerHTML = `
                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing PayPal subscription...
            `;
            container.appendChild(loadingDiv);
        } else {
            const loadingDiv = document.getElementById(`${this.containerId}-loading`);
            if (loadingDiv) {
                loadingDiv.remove();
            }
        }
    }

    /**
     * Handle errors
     */
    private handleError(message: string): void {
        if (this.onError) {
            this.onError(message);
        } else {
            this.showMessage(message, 'error');
        }
    }

    /**
     * Show messages
     */
    private showMessage(message: string, type: 'error' | 'info' | 'success' = 'info'): void {
        const errorContainer = document.getElementById(`${this.containerId.replace('-container', '')}-errors`);
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.className = type === 'error' 
                ? 'text-red-600 text-sm mt-2' 
                : type === 'success'
                ? 'text-green-600 text-sm mt-2'
                : 'text-blue-600 text-sm mt-2';
        }
    }

    /**
     * Get CSRF token
     */
    private getCSRFToken(): string {
        const token = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
        return token ? token.content : '';
    }

    /**
     * Log events for analytics
     */
    private logEvent(eventName: string, data: any): void {
        try {
            // Send to analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, data);
            }

            // Log to console in development
            if (process.env.NODE_ENV === 'development') {
                console.log(`Analytics Event: ${eventName}`, data);
            }
        } catch (error) {
            console.warn('Failed to log analytics event:', error);
        }
    }

    /**
     * Validate billing information
     */
    public validateBillingInfo(): { valid: boolean; errors: string[] } {
        const errors: string[] = [];

        if (!this.billingInfo.firstName?.trim()) {
            errors.push('First name is required');
        }

        if (!this.billingInfo.lastName?.trim()) {
            errors.push('Last name is required');
        }

        if (!this.billingInfo.email?.trim()) {
            errors.push('Email address is required');
        } else if (!this.isValidEmail(this.billingInfo.email)) {
            errors.push('Please enter a valid email address');
        }

        if (!this.billingInfo.address?.trim()) {
            errors.push('Address is required');
        }

        if (!this.billingInfo.city?.trim()) {
            errors.push('City is required');
        }

        if (!this.billingInfo.state?.trim()) {
            errors.push('State/Province is required');
        }

        if (!this.billingInfo.postalCode?.trim()) {
            errors.push('Postal code is required');
        }

        if (!this.billingInfo.country?.trim()) {
            errors.push('Country is required');
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }

    /**
     * Validate email format
     */
    private isValidEmail(email: string): boolean {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Check if PayPal SDK is loaded
     */
    public static isSDKLoaded(): boolean {
        return typeof window.paypal !== 'undefined' && window.paypal.Buttons;
    }

    /**
     * Get processing state
     */
    public isProcessingPayment(): boolean {
        return this.isProcessing;
    }

    /**
     * Destroy the component
     */
    public destroy(): void {
        this.clearButton();
        this.onSuccess = undefined;
        this.onError = undefined;
        this.onCancel = undefined;
    }
}

// Global declarations for PayPal SDK
declare global {
    interface Window {
        paypal: {
            Buttons: (config: any) => {
                render: (selector: string) => Promise<void>;
            };
        };
        gtag?: (command: string, eventName: string, data: any) => void;
    }
}

export default PayPalSubscriptionButton;
export type { BillingInfo, PayPalButtonStyle, PayPalApprovalData, PayPalError };