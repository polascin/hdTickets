/**
 * PayPal Checkout Button Component
 * 
 * Handles PayPal one-time payment processing for sports event ticket purchases.
 * This component manages order creation, payment approval flows, and error handling
 * for individual ticket transactions (as opposed to recurring subscriptions).
 */

interface PayPalOrderRequest {
    intent: 'CAPTURE' | 'AUTHORIZE';
    purchase_units: PayPalPurchaseUnit[];
    payment_source?: PayPalPaymentSource;
    application_context?: PayPalApplicationContext;
}

interface PayPalPurchaseUnit {
    reference_id?: string;
    amount: PayPalAmount;
    payee?: PayPalPayee;
    payment_instruction?: PayPalPaymentInstruction;
    description?: string;
    custom_id?: string;
    invoice_id?: string;
    soft_descriptor?: string;
    items?: PayPalItem[];
    shipping?: PayPalShipping;
}

interface PayPalAmount {
    currency_code: string;
    value: string;
    breakdown?: PayPalAmountBreakdown;
}

interface PayPalAmountBreakdown {
    item_total?: PayPalMoney;
    shipping?: PayPalMoney;
    handling?: PayPalMoney;
    tax_total?: PayPalMoney;
    insurance?: PayPalMoney;
    shipping_discount?: PayPalMoney;
    discount?: PayPalMoney;
}

interface PayPalMoney {
    currency_code: string;
    value: string;
}

interface PayPalItem {
    name: string;
    unit_amount: PayPalMoney;
    tax?: PayPalMoney;
    quantity: string;
    description?: string;
    sku?: string;
    category?: 'DIGITAL_GOODS' | 'PHYSICAL_GOODS' | 'DONATION';
}

interface PayPalApplicationContext {
    brand_name?: string;
    locale?: string;
    landing_page?: 'LOGIN' | 'BILLING' | 'NO_PREFERENCE';
    shipping_preference?: 'GET_FROM_FILE' | 'NO_SHIPPING' | 'SET_PROVIDED_ADDRESS';
    user_action?: 'CONTINUE' | 'PAY_NOW';
    payment_method?: PayPalPaymentMethod;
    return_url?: string;
    cancel_url?: string;
}

interface PayPalPaymentMethod {
    payer_selected?: 'PAYPAL';
    payee_preferred?: 'UNRESTRICTED' | 'IMMEDIATE_PAYMENT_REQUIRED';
}

interface PayPalPaymentSource {
    paypal?: {
        experience_context?: {
            payment_method_preference?: 'UNRESTRICTED' | 'IMMEDIATE_PAYMENT_REQUIRED';
            brand_name?: string;
            locale?: string;
            landing_page?: 'LOGIN' | 'BILLING' | 'NO_PREFERENCE';
            shipping_preference?: 'GET_FROM_FILE' | 'NO_SHIPPING' | 'SET_PROVIDED_ADDRESS';
            user_action?: 'PAY_NOW' | 'CONTINUE';
        };
    };
}

interface PayPalPayee {
    email_address?: string;
    merchant_id?: string;
}

interface PayPalPaymentInstruction {
    platform_fees?: PayPalPlatformFee[];
    disbursement_mode?: 'INSTANT' | 'DELAYED';
}

interface PayPalPlatformFee {
    amount: PayPalMoney;
    payee?: PayPalPayee;
}

interface PayPalShipping {
    method?: string;
    address?: PayPalAddress;
}

interface PayPalAddress {
    address_line_1?: string;
    address_line_2?: string;
    admin_area_2?: string; // City
    admin_area_1?: string; // State
    postal_code?: string;
    country_code?: string;
}

interface TicketPurchaseData {
    ticketId: string;
    ticketTitle: string;
    quantity: number;
    unitPrice: number;
    currency: string;
    processingFeeRate: number;
    serviceFee: number;
}

interface PayPalButtonStyle {
    layout?: 'vertical' | 'horizontal';
    color?: 'gold' | 'blue' | 'silver' | 'black' | 'white';
    shape?: 'rect' | 'pill';
    label?: 'paypal' | 'checkout' | 'buynow' | 'pay';
    tagline?: boolean;
    height?: number;
}

interface PayPalApprovalData {
    orderID: string;
    payerID?: string;
    paymentID?: string;
    billingToken?: string;
    facilitatorAccessToken?: string;
}

interface PayPalError {
    name: string;
    message: string;
    details?: Array<{
        issue: string;
        description: string;
        location?: string;
        value?: string;
    }>;
}

interface PayPalActions {
    order: {
        create: (orderRequest: PayPalOrderRequest) => Promise<string>;
        capture: (orderID: string) => Promise<any>;
        get: (orderID: string) => Promise<any>;
    };
}

class PayPalCheckoutButton {
    private containerId: string;
    private ticketData: TicketPurchaseData;
    private isRendered: boolean = false;
    private isProcessing: boolean = false;
    private onSuccess?: (orderID: string, details: any) => Promise<void>;
    private onError?: (error: string) => void;
    private onCancel?: () => void;
    private validateForm?: () => Promise<boolean>;

    constructor(
        containerId: string,
        ticketData: TicketPurchaseData,
        callbacks: {
            onSuccess?: (orderID: string, details: any) => Promise<void>;
            onError?: (error: string) => void;
            onCancel?: () => void;
            validateForm?: () => Promise<boolean>;
        } = {}
    ) {
        this.containerId = containerId;
        this.ticketData = ticketData;
        this.onSuccess = callbacks.onSuccess;
        this.onError = callbacks.onError;
        this.onCancel = callbacks.onCancel;
        this.validateForm = callbacks.validateForm;
    }

    /**
     * Render the PayPal checkout button
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
            label: 'pay',
            height: 50,
            tagline: false,
            ...style
        };

        try {
            await window.paypal.Buttons({
                style: defaultStyle,
                createOrder: this.createOrder.bind(this),
                onApprove: this.onApprove.bind(this),
                onError: this.onPayPalError.bind(this),
                onCancel: this.onPayPalCancel.bind(this)
            }).render(`#${this.containerId}`);

            this.isRendered = true;
            console.log('PayPal checkout button rendered successfully');
        } catch (error) {
            console.error('Error rendering PayPal button:', error);
            this.handleError('Failed to load PayPal payment option. Please try again.');
        }
    }

    /**
     * Create PayPal order for ticket purchase
     */
    private async createOrder(data: any, actions: PayPalActions): Promise<string> {
        try {
            // Validate form if validation function provided
            if (this.validateForm && !(await this.validateForm())) {
                throw new Error('Form validation failed');
            }

            const { quantity, unitPrice, currency, processingFeeRate, serviceFee } = this.ticketData;
            const subtotal = unitPrice * quantity;
            const processingFee = subtotal * processingFeeRate;
            const total = subtotal + processingFee + serviceFee;

            const orderRequest: PayPalOrderRequest = {
                intent: 'CAPTURE',
                purchase_units: [{
                    reference_id: `ticket_${this.ticketData.ticketId}`,
                    amount: {
                        currency_code: currency,
                        value: total.toFixed(2),
                        breakdown: {
                            item_total: {
                                currency_code: currency,
                                value: subtotal.toFixed(2)
                            },
                            handling: {
                                currency_code: currency,
                                value: (processingFee + serviceFee).toFixed(2)
                            }
                        }
                    },
                    items: [{
                        name: this.ticketData.ticketTitle,
                        unit_amount: {
                            currency_code: currency,
                            value: unitPrice.toFixed(2)
                        },
                        quantity: quantity.toString(),
                        description: 'Sports Event Ticket',
                        sku: `ticket_${this.ticketData.ticketId}`,
                        category: 'DIGITAL_GOODS'
                    }],
                    description: `Sports Event Ticket Purchase - ${this.ticketData.ticketTitle}`,
                    custom_id: `ticket_${this.ticketData.ticketId}`,
                    invoice_id: `HDT_${Date.now()}_${this.ticketData.ticketId}`,
                    soft_descriptor: 'HD TICKETS'
                }],
                application_context: {
                    brand_name: 'HD Tickets',
                    locale: 'en-GB', // British English as per project rules
                    landing_page: 'NO_PREFERENCE',
                    shipping_preference: 'NO_SHIPPING',
                    user_action: 'PAY_NOW',
                    payment_method: {
                        payer_selected: 'PAYPAL',
                        payee_preferred: 'IMMEDIATE_PAYMENT_REQUIRED'
                    }
                }
            };

            const orderID = await actions.order.create(orderRequest);
            
            // Log order creation for analytics
            this.logEvent('paypal_order_created', {
                order_id: orderID,
                ticket_id: this.ticketData.ticketId,
                quantity: quantity,
                amount: total.toFixed(2),
                currency: currency
            });

            return orderID;
        } catch (error) {
            console.error('Error creating PayPal order:', error);
            throw new Error('Failed to create payment order. Please try again.');
        }
    }

    /**
     * Handle PayPal payment approval
     */
    private async onApprove(data: PayPalApprovalData, actions: PayPalActions): Promise<void> {
        if (this.isProcessing) {
            return;
        }

        this.isProcessing = true;
        this.showLoadingState(true);

        try {
            console.log('PayPal payment approved:', data.orderID);
            
            // Capture the payment
            const details = await actions.order.capture(data.orderID);
            
            // Log approval for analytics
            this.logEvent('paypal_payment_approved', {
                order_id: data.orderID,
                payer_id: data.payerID,
                ticket_id: this.ticketData.ticketId,
                capture_id: details.purchase_units?.[0]?.payments?.captures?.[0]?.id
            });

            if (this.onSuccess) {
                await this.onSuccess(data.orderID, details);
            } else {
                // Default success handling - call backend to process purchase
                await this.processTicketPurchase(data.orderID, details);
            }
        } catch (error) {
            console.error('Error handling PayPal approval:', error);
            
            // Log error for analytics
            this.logEvent('paypal_payment_approval_error', {
                order_id: data.orderID,
                error: error instanceof Error ? error.message : 'Unknown error'
            });

            this.handleError(
                error instanceof Error 
                    ? error.message 
                    : 'Failed to complete payment. Please contact support.'
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
        this.logEvent('paypal_payment_error', {
            error_name: err.name,
            error_message: err.message,
            error_details: err.details,
            ticket_id: this.ticketData.ticketId
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
        this.logEvent('paypal_payment_cancelled', {
            order_id: data.orderID,
            ticket_id: this.ticketData.ticketId
        });

        if (this.onCancel) {
            this.onCancel();
        } else {
            this.showMessage('Payment was cancelled. You can try again at any time.', 'info');
        }
    }

    /**
     * Process ticket purchase on backend after PayPal approval
     */
    private async processTicketPurchase(orderID: string, paypalDetails: any): Promise<void> {
        const captureID = paypalDetails.purchase_units?.[0]?.payments?.captures?.[0]?.id;
        const payerInfo = paypalDetails.payer;

        const response = await fetch(`/api/v1/tickets/${this.ticketData.ticketId}/purchase`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                payment_method: 'paypal',
                paypal_order_id: orderID,
                paypal_capture_id: captureID,
                quantity: this.ticketData.quantity,
                payer_info: payerInfo
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to process ticket purchase');
        }

        // Redirect to success page
        if (result.redirect_url) {
            window.location.href = result.redirect_url;
        }
    }

    /**
     * Update ticket data and refresh pricing
     */
    public updateTicketData(ticketData: Partial<TicketPurchaseData>): void {
        this.ticketData = { ...this.ticketData, ...ticketData };
        
        // Re-render if already rendered to update pricing
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
            loadingDiv.className = 'flex items-center justify-center p-4 text-gray-600 bg-gray-50 rounded-lg border-2 border-gray-200';
            loadingDiv.innerHTML = `
                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing PayPal payment...
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
     * Validate ticket purchase data
     */
    public validateTicketData(): { valid: boolean; errors: string[] } {
        const errors: string[] = [];

        if (!this.ticketData.ticketId) {
            errors.push('Ticket ID is required');
        }

        if (!this.ticketData.ticketTitle?.trim()) {
            errors.push('Ticket title is required');
        }

        if (!this.ticketData.quantity || this.ticketData.quantity < 1) {
            errors.push('Valid quantity is required');
        }

        if (!this.ticketData.unitPrice || this.ticketData.unitPrice <= 0) {
            errors.push('Valid ticket price is required');
        }

        if (!this.ticketData.currency?.trim()) {
            errors.push('Currency is required');
        }

        return {
            valid: errors.length === 0,
            errors
        };
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
        this.validateForm = undefined;
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

export default PayPalCheckoutButton;
export type { 
    TicketPurchaseData, 
    PayPalButtonStyle, 
    PayPalApprovalData, 
    PayPalError,
    PayPalOrderRequest,
    PayPalPurchaseUnit,
    PayPalAmount
};