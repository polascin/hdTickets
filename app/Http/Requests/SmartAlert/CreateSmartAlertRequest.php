<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartAlert;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function count;
use function in_array;
use function is_array;

/**
 * Create Smart Alert Request
 *
 * Validates data for creating new intelligent ticket alerts
 * inspired by TicketScoutie's smart alert system.
 */
class CreateSmartAlertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'alert_type'  => [
                'required',
                'string',
                Rule::in([
                    'price_drop',
                    'availability',
                    'instant_deal',
                    'price_comparison',
                    'venue_alert',
                    'league_alert',
                    'keyword_alert',
                ]),
            ],
            'trigger_conditions'    => ['required', 'array'],
            'notification_channels' => [
                'required',
                'array',
                'min:1',
            ],
            'notification_channels.*' => [
                'string',
                Rule::in(['email', 'sms', 'push', 'webhook']),
            ],
            'notification_settings' => ['nullable', 'array'],
            'is_active'             => ['nullable', 'boolean'],
            'priority'              => [
                'nullable',
                'string',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
            'cooldown_minutes'     => ['nullable', 'integer', 'min:1', 'max:1440'], // Max 24 hours
            'max_triggers_per_day' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required'                  => 'Alert name is required.',
            'name.max'                       => 'Alert name cannot be longer than 255 characters.',
            'alert_type.required'            => 'Alert type is required.',
            'alert_type.in'                  => 'Invalid alert type selected.',
            'trigger_conditions.required'    => 'Trigger conditions are required.',
            'trigger_conditions.array'       => 'Trigger conditions must be a valid array.',
            'notification_channels.required' => 'At least one notification channel is required.',
            'notification_channels.min'      => 'At least one notification channel must be selected.',
            'notification_channels.*.in'     => 'Invalid notification channel selected.',
            'priority.in'                    => 'Invalid priority level selected.',
            'cooldown_minutes.min'           => 'Cooldown must be at least 1 minute.',
            'cooldown_minutes.max'           => 'Cooldown cannot exceed 24 hours (1440 minutes).',
            'max_triggers_per_day.min'       => 'Maximum triggers per day must be at least 1.',
            'max_triggers_per_day.max'       => 'Maximum triggers per day cannot exceed 100.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param mixed $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validateTriggerConditions($validator);
            $this->validateNotificationSettings($validator);
        });
    }

    /**
     * Validate trigger conditions based on alert type
     *
     * @param mixed $validator
     */
    private function validateTriggerConditions($validator): void
    {
        $alertType = $this->input('alert_type');
        $conditions = $this->input('trigger_conditions', []);

        switch ($alertType) {
            case 'price_drop':
                $this->validatePriceDropConditions($validator, $conditions);

                break;
            case 'availability':
                $this->validateAvailabilityConditions($validator, $conditions);

                break;
            case 'instant_deal':
                $this->validateInstantDealConditions($validator, $conditions);

                break;
            case 'price_comparison':
                $this->validatePriceComparisonConditions($validator, $conditions);

                break;
            case 'venue_alert':
                $this->validateVenueConditions($validator, $conditions);

                break;
            case 'league_alert':
                $this->validateLeagueConditions($validator, $conditions);

                break;
            case 'keyword_alert':
                $this->validateKeywordConditions($validator, $conditions);

                break;
        }
    }

    /**
     * Validate price drop conditions
     *
     * @param mixed $validator
     */
    private function validatePriceDropConditions($validator, array $conditions): void
    {
        if (isset($conditions['price_threshold'])
            && (! is_numeric($conditions['price_threshold']) || $conditions['price_threshold'] < 0)) {
            $validator->errors()->add('trigger_conditions.price_threshold', 'Price threshold must be a valid positive number.');
        }

        if (isset($conditions['percentage_drop'])
            && (! is_numeric($conditions['percentage_drop']) || $conditions['percentage_drop'] < 0 || $conditions['percentage_drop'] > 100)) {
            $validator->errors()->add('trigger_conditions.percentage_drop', 'Percentage drop must be between 0 and 100.');
        }
    }

    /**
     * Validate availability conditions
     *
     * @param mixed $validator
     */
    private function validateAvailabilityConditions($validator, array $conditions): void
    {
        if (isset($conditions['event_keywords']) && ! is_array($conditions['event_keywords'])) {
            $validator->errors()->add('trigger_conditions.event_keywords', 'Event keywords must be an array.');
        }

        if (isset($conditions['venue_keywords']) && ! is_array($conditions['venue_keywords'])) {
            $validator->errors()->add('trigger_conditions.venue_keywords', 'Venue keywords must be an array.');
        }

        if (isset($conditions['date_range'])) {
            if (! is_array($conditions['date_range'])) {
                $validator->errors()->add('trigger_conditions.date_range', 'Date range must be an array.');
            } else {
                if (isset($conditions['date_range']['start'])
                    && $conditions['date_range']['start']
                    && ! strtotime($conditions['date_range']['start'])) {
                    $validator->errors()->add('trigger_conditions.date_range.start', 'Invalid start date format.');
                }

                if (isset($conditions['date_range']['end'])
                    && $conditions['date_range']['end']
                    && ! strtotime($conditions['date_range']['end'])) {
                    $validator->errors()->add('trigger_conditions.date_range.end', 'Invalid end date format.');
                }
            }
        }
    }

    /**
     * Validate instant deal conditions
     *
     * @param mixed $validator
     */
    private function validateInstantDealConditions($validator, array $conditions): void
    {
        if (isset($conditions['discount_percentage'])
            && (! is_numeric($conditions['discount_percentage']) || $conditions['discount_percentage'] < 0 || $conditions['discount_percentage'] > 100)) {
            $validator->errors()->add('trigger_conditions.discount_percentage', 'Discount percentage must be between 0 and 100.');
        }
    }

    /**
     * Validate price comparison conditions
     *
     * @param mixed $validator
     */
    private function validatePriceComparisonConditions($validator, array $conditions): void
    {
        if (! isset($conditions['platforms']) || ! is_array($conditions['platforms']) || count($conditions['platforms']) < 2) {
            $validator->errors()->add('trigger_conditions.platforms', 'At least 2 platforms must be selected for price comparison.');
        }

        if (isset($conditions['price_difference_threshold'])
            && (! is_numeric($conditions['price_difference_threshold']) || $conditions['price_difference_threshold'] < 0)) {
            $validator->errors()->add('trigger_conditions.price_difference_threshold', 'Price difference threshold must be a positive number.');
        }
    }

    /**
     * Validate venue conditions
     *
     * @param mixed $validator
     */
    private function validateVenueConditions($validator, array $conditions): void
    {
        if (! isset($conditions['venues']) || ! is_array($conditions['venues']) || empty($conditions['venues'])) {
            $validator->errors()->add('trigger_conditions.venues', 'At least one venue must be specified.');
        }
    }

    /**
     * Validate league conditions
     *
     * @param mixed $validator
     */
    private function validateLeagueConditions($validator, array $conditions): void
    {
        if (! isset($conditions['leagues']) || ! is_array($conditions['leagues']) || empty($conditions['leagues'])) {
            $validator->errors()->add('trigger_conditions.leagues', 'At least one league must be specified.');
        }
    }

    /**
     * Validate keyword conditions
     *
     * @param mixed $validator
     */
    private function validateKeywordConditions($validator, array $conditions): void
    {
        if (! isset($conditions['keywords']) || ! is_array($conditions['keywords']) || empty($conditions['keywords'])) {
            $validator->errors()->add('trigger_conditions.keywords', 'At least one keyword must be specified.');
        }
    }

    /**
     * Validate notification settings
     *
     * @param mixed $validator
     */
    private function validateNotificationSettings($validator): void
    {
        $channels = $this->input('notification_channels', []);
        $settings = $this->input('notification_settings', []);

        // Validate SMS settings if SMS channel is selected
        if (in_array('sms', $channels, TRUE) && isset($settings['sms'])) {
            if (isset($settings['sms']['phone_number'])
                && ! preg_match('/^\+[1-9]\d{1,14}$/', $settings['sms']['phone_number'])) {
                $validator->errors()->add('notification_settings.sms.phone_number', 'Invalid phone number format. Use international format (+1234567890).');
            }
        }

        // Validate webhook settings if webhook channel is selected
        if (in_array('webhook', $channels, TRUE) && isset($settings['webhook'])) {
            if (isset($settings['webhook']['url'])
                && ! filter_var($settings['webhook']['url'], FILTER_VALIDATE_URL)) {
                $validator->errors()->add('notification_settings.webhook.url', 'Invalid webhook URL format.');
            }
        }
    }
}
