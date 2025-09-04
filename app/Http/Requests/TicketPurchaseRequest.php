<?php declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TicketPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        $user = Auth::user();
        $maxQuantity = 10;

        // Adjust max quantity based on user role and remaining allowance
        if ($user && $user->isCustomer()) {
            $subscription = $user->subscription;
            if ($subscription) {
                $monthlyLimit = $subscription->plan->ticket_limit ?? config('subscription.default_ticket_limit', 100);
                $currentUsage = $user->getMonthlyTicketUsage();
                $remainingAllowance = max(0, $monthlyLimit - $currentUsage);
                $maxQuantity = min($maxQuantity, $remainingAllowance);
            }
        }

        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $maxQuantity,
            ],
            'method' => [
                'sometimes',
                'string',
                'in:automated,manual,api',
            ],
            'preferred_payment' => [
                'sometimes',
                'string',
                'max:50',
            ],
            'seat_preferences' => [
                'sometimes',
                'array',
            ],
            'seat_preferences.section' => [
                'sometimes',
                'string',
                'max:50',
            ],
            'seat_preferences.row' => [
                'sometimes',
                'string',
                'max:20',
            ],
            'seat_preferences.seat_type' => [
                'sometimes',
                'string',
                'in:standard,premium,vip,accessible',
            ],
            'seat_preferences.accessibility_needs' => [
                'sometimes',
                'boolean',
            ],
            'special_requests' => [
                'sometimes',
                'string',
                'max:500',
            ],
            'accept_terms' => [
                'required',
                'accepted',
            ],
            'confirm_purchase' => [
                'required',
                'accepted',
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'quantity.required'             => 'Please specify the number of tickets you want to purchase.',
            'quantity.integer'              => 'Quantity must be a valid number.',
            'quantity.min'                  => 'You must purchase at least 1 ticket.',
            'quantity.max'                  => 'You cannot purchase more than the maximum allowed quantity.',
            'method.in'                     => 'Invalid purchase method selected.',
            'seat_preferences.section.max'  => 'Section preference cannot exceed 50 characters.',
            'seat_preferences.row.max'      => 'Row preference cannot exceed 20 characters.',
            'seat_preferences.seat_type.in' => 'Invalid seat type selected.',
            'special_requests.max'          => 'Special requests cannot exceed 500 characters.',
            'accept_terms.required'         => 'You must accept the terms and conditions to proceed.',
            'accept_terms.accepted'         => 'You must accept the terms and conditions to proceed.',
            'confirm_purchase.required'     => 'You must confirm your purchase to proceed.',
            'confirm_purchase.accepted'     => 'You must confirm your purchase to proceed.',
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'quantity'                   => 'ticket quantity',
            'method'                     => 'purchase method',
            'preferred_payment'          => 'payment preference',
            'seat_preferences.section'   => 'section preference',
            'seat_preferences.row'       => 'row preference',
            'seat_preferences.seat_type' => 'seat type',
            'special_requests'           => 'special requests',
            'accept_terms'               => 'terms acceptance',
            'confirm_purchase'           => 'purchase confirmation',
        ];
    }

    /**
     * Configure the validator instance
     *
     * @param mixed $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = Auth::user();
            $ticket = $this->route('ticket');

            if (! $ticket) {
                return;
            }

            // Additional business logic validation
            $this->validateTicketAvailability($validator, $ticket);
            $this->validateUserLimits($validator, $user, $ticket);
            $this->validateEventTiming($validator, $ticket);
        });
    }

    /**
     * Get validated and processed data
     */
    public function getProcessedData(): array
    {
        $validated = $this->validated();

        return [
            'quantity'         => $validated['quantity'],
            'purchase_options' => [
                'method'            => $validated['method'] ?? 'automated',
                'preferred_payment' => $validated['preferred_payment'] ?? NULL,
                'seat_preferences'  => $validated['seat_preferences'] ?? [],
                'special_requests'  => $validated['special_requests'] ?? NULL,
                'user_agent'        => $this->userAgent(),
                'ip_address'        => $this->ip(),
                'timestamp'         => now()->toISOString(),
            ],
        ];
    }

    /**
     * Validate ticket availability
     *
     * @param mixed $validator
     * @param mixed $ticket
     */
    private function validateTicketAvailability($validator, $ticket): void
    {
        if (! $ticket->is_available) {
            $validator->errors()->add('ticket', 'This ticket is no longer available for purchase.');

            return;
        }

        $requestedQuantity = $this->input('quantity', 1);

        if ($ticket->available_quantity && $requestedQuantity > $ticket->available_quantity) {
            $validator->errors()->add(
                'quantity',
                "Only {$ticket->available_quantity} tickets are available, but you requested {$requestedQuantity}.",
            );
        }
    }

    /**
     * Validate user purchase limits
     *
     * @param mixed $validator
     * @param mixed $user
     * @param mixed $ticket
     */
    private function validateUserLimits($validator, $user, $ticket): void
    {
        if (! $user) {
            $validator->errors()->add('user', 'Authentication required for ticket purchase.');

            return;
        }

        // Check role permissions
        if ($user->role === 'scraper') {
            $validator->errors()->add('user', 'Scraper accounts cannot purchase tickets.');

            return;
        }

        // Check subscription limits for customers
        if ($user->isCustomer()) {
            $subscription = $user->subscription;

            if (! $subscription) {
                $validator->errors()->add('subscription', 'An active subscription is required to purchase tickets.');

                return;
            }

            if (! $subscription->isActive() && ! $subscription->isInFreeTrial()) {
                $validator->errors()->add(
                    'subscription',
                    'Your subscription is not active. Please renew to continue purchasing tickets.',
                );

                return;
            }

            // Check monthly limits
            $monthlyLimit = $subscription->plan->ticket_limit ?? config('subscription.default_ticket_limit', 100);
            $currentUsage = $user->getMonthlyTicketUsage();
            $requestedQuantity = $this->input('quantity', 1);

            if (($currentUsage + $requestedQuantity) > $monthlyLimit) {
                $remaining = max(0, $monthlyLimit - $currentUsage);
                $validator->errors()->add(
                    'quantity',
                    "This purchase would exceed your monthly limit of {$monthlyLimit} tickets. " .
                    "You have {$remaining} tickets remaining this month.",
                );
            }
        }
    }

    /**
     * Validate event timing
     *
     * @param mixed $validator
     * @param mixed $ticket
     */
    private function validateEventTiming($validator, $ticket): void
    {
        if ($ticket->event_date && $ticket->event_date->isPast()) {
            $validator->errors()->add(
                'ticket',
                'Cannot purchase tickets for events that have already occurred.',
            );
        }

        // Optional: Check if purchase is too close to event time
        if ($ticket->event_date && $ticket->event_date->diffInHours(now()) < 2) {
            $validator->errors()->add(
                'ticket',
                'Cannot purchase tickets less than 2 hours before the event starts.',
            );
        }
    }
}
