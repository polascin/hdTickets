<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UserPreferencesRequest
 *
 * Validates comprehensive user preferences data including sports, teams, venues,
 * pricing settings, location preferences, and advanced configuration options
 * for the HD Tickets sports event monitoring platform.
 */
class UserPreferencesRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Top-level preferences object
            'preferences' => 'required|array',

            // Sports preferences
            'preferences.sports' => 'sometimes|array',
            'preferences.sports.*' => [
                'string',
                'max:50',
                Rule::in([
                    'football', 'basketball', 'baseball', 'hockey', 'soccer', 
                    'tennis', 'golf', 'racing', 'wrestling', 'boxing', 'mma', 'other'
                ])
            ],

            // Teams preferences (handled separately via API endpoints)
            'preferences.teams' => 'sometimes|array',

            // Venues preferences (handled separately via API endpoints)
            'preferences.venues' => 'sometimes|array',

            // Pricing preferences
            'preferences.pricing' => 'sometimes|array',
            'preferences.pricing.budgetMin' => 'sometimes|numeric|min:0|max:10000',
            'preferences.pricing.budgetMax' => 'sometimes|numeric|min:0|max:50000|gte:preferences.pricing.budgetMin',
            'preferences.pricing.alertThresholds' => 'sometimes|array',
            'preferences.pricing.alertThresholds.small' => 'sometimes|integer|min:1|max:20',
            'preferences.pricing.alertThresholds.medium' => 'sometimes|integer|min:10|max:40',
            'preferences.pricing.alertThresholds.large' => 'sometimes|integer|min:20|max:70',
            'preferences.pricing.strategy' => [
                'sometimes',
                'string',
                Rule::in(['budget', 'balanced', 'premium'])
            ],

            // Location preferences
            'preferences.location' => 'sometimes|array',
            'preferences.location.primary' => 'sometimes|string|max:255',
            'preferences.location.secondary' => 'sometimes|array',
            'preferences.location.secondary.*' => 'string|max:255',
            'preferences.location.maxDistance' => 'sometimes|integer|min:0|max:1000',

            // Advanced preferences
            'preferences.advanced' => 'sometimes|array',
            'preferences.advanced.alertFrequency' => [
                'sometimes',
                'string',
                Rule::in(['real-time', 'hourly', 'daily', 'weekly'])
            ],
            'preferences.advanced.monitoringWindow' => 'sometimes|array',
            'preferences.advanced.monitoringWindow.days' => 'sometimes|integer|min:1|max:365',
            'preferences.advanced.monitoringWindow.hours' => 'sometimes|integer|min:1|max:72',
            
            // Data collection preferences
            'preferences.advanced.dataCollection' => 'sometimes|array',
            'preferences.advanced.dataCollection.analytics' => 'sometimes|boolean',
            'preferences.advanced.dataCollection.personalization' => 'sometimes|boolean',
            'preferences.advanced.dataCollection.marketing' => 'sometimes|boolean',
            
            // Automation preferences
            'preferences.advanced.automation' => 'sometimes|array',
            'preferences.advanced.automation.autoBookmark' => 'sometimes|boolean',
            'preferences.advanced.automation.autoAlert' => 'sometimes|boolean',
            'preferences.advanced.automation.smartSuggestions' => 'sometimes|boolean',

            // Legacy support for direct preference updates
            'category' => 'sometimes|string|max:50',
            'key' => 'sometimes|string|max:100',
            'value' => 'nullable',
            'data_type' => [
                'sometimes',
                'string',
                Rule::in(['string', 'boolean', 'integer', 'float', 'json', 'array'])
            ],

            // Team/venue search parameters
            'q' => 'sometimes|string|min:2|max:100',
            'query' => 'sometimes|string|min:2|max:100',
            'sport' => 'sometimes|string|max:50',
            'city' => 'sometimes|string|max:100',
            'limit' => 'sometimes|integer|min:1|max:50',

            // Batch operations
            'team_ids' => 'sometimes|array',
            'team_ids.*' => 'integer|exists:teams,id',
            'venue_ids' => 'sometimes|array', 
            'venue_ids.*' => 'integer|exists:venues,id',
            
            // Reset operations
            'categories' => 'sometimes|array',
            'categories.*' => [
                'string',
                Rule::in(['sports', 'teams', 'venues', 'pricing', 'location', 'advanced'])
            ],
            
            // Export/Import operations
            'format' => [
                'sometimes',
                'string', 
                Rule::in(['json', 'csv'])
            ],
            'overwrite' => 'sometimes|boolean'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'preferences.required' => 'Preferences data is required.',
            'preferences.array' => 'Preferences must be a valid data structure.',
            
            // Sports validation messages
            'preferences.sports.array' => 'Sports preferences must be a list.',
            'preferences.sports.*.in' => 'Invalid sport selection. Please choose from the available options.',
            
            // Pricing validation messages  
            'preferences.pricing.budgetMin.numeric' => 'Minimum budget must be a valid number.',
            'preferences.pricing.budgetMin.min' => 'Minimum budget cannot be negative.',
            'preferences.pricing.budgetMax.numeric' => 'Maximum budget must be a valid number.',
            'preferences.pricing.budgetMax.gte' => 'Maximum budget must be greater than or equal to minimum budget.',
            'preferences.pricing.budgetMax.max' => 'Maximum budget cannot exceed $50,000.',
            'preferences.pricing.strategy.in' => 'Invalid pricing strategy. Choose from budget, balanced, or premium.',
            
            // Alert threshold messages
            'preferences.pricing.alertThresholds.small.min' => 'Small price drop threshold must be at least 1%.',
            'preferences.pricing.alertThresholds.small.max' => 'Small price drop threshold cannot exceed 20%.',
            'preferences.pricing.alertThresholds.medium.min' => 'Medium price drop threshold must be at least 10%.',
            'preferences.pricing.alertThresholds.medium.max' => 'Medium price drop threshold cannot exceed 40%.',
            'preferences.pricing.alertThresholds.large.min' => 'Large price drop threshold must be at least 20%.',
            'preferences.pricing.alertThresholds.large.max' => 'Large price drop threshold cannot exceed 70%.',
            
            // Location validation messages
            'preferences.location.primary.max' => 'Primary location cannot exceed 255 characters.',
            'preferences.location.secondary.array' => 'Secondary locations must be a list.',
            'preferences.location.secondary.*.max' => 'Each secondary location cannot exceed 255 characters.',
            'preferences.location.maxDistance.min' => 'Maximum travel distance cannot be negative.',
            'preferences.location.maxDistance.max' => 'Maximum travel distance cannot exceed 1000 miles.',
            
            // Advanced settings messages
            'preferences.advanced.alertFrequency.in' => 'Invalid alert frequency. Choose from real-time, hourly, daily, or weekly.',
            'preferences.advanced.monitoringWindow.days.min' => 'Monitoring window must be at least 1 day.',
            'preferences.advanced.monitoringWindow.days.max' => 'Monitoring window cannot exceed 365 days.',
            'preferences.advanced.monitoringWindow.hours.min' => 'Hours before event must be at least 1.',
            'preferences.advanced.monitoringWindow.hours.max' => 'Hours before event cannot exceed 72.',
            
            // Search validation messages
            'q.min' => 'Search query must be at least 2 characters.',
            'q.max' => 'Search query cannot exceed 100 characters.',
            'query.min' => 'Search query must be at least 2 characters.',
            'query.max' => 'Search query cannot exceed 100 characters.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 50 results.',
            
            // Entity existence messages
            'team_ids.*.exists' => 'One or more selected teams do not exist.',
            'venue_ids.*.exists' => 'One or more selected venues do not exist.',
        ];
    }

    /**
     * Get custom attributes for validation errors.
     */
    public function attributes(): array
    {
        return [
            'preferences.sports' => 'favorite sports',
            'preferences.pricing.budgetMin' => 'minimum budget',
            'preferences.pricing.budgetMax' => 'maximum budget',
            'preferences.pricing.strategy' => 'pricing strategy',
            'preferences.pricing.alertThresholds.small' => 'small price drop threshold',
            'preferences.pricing.alertThresholds.medium' => 'medium price drop threshold',
            'preferences.pricing.alertThresholds.large' => 'large price drop threshold',
            'preferences.location.primary' => 'primary location',
            'preferences.location.secondary' => 'secondary locations',
            'preferences.location.maxDistance' => 'maximum travel distance',
            'preferences.advanced.alertFrequency' => 'alert frequency',
            'preferences.advanced.monitoringWindow.days' => 'monitoring window (days)',
            'preferences.advanced.monitoringWindow.hours' => 'hours before event',
            'preferences.advanced.dataCollection.analytics' => 'analytics data collection',
            'preferences.advanced.dataCollection.personalization' => 'personalization features',
            'preferences.advanced.dataCollection.marketing' => 'marketing communications',
            'preferences.advanced.automation.autoBookmark' => 'auto-bookmark events',
            'preferences.advanced.automation.autoAlert' => 'auto-create alerts',
            'preferences.advanced.automation.smartSuggestions' => 'smart suggestions',
            'q' => 'search query',
            'query' => 'search query',
            'sport' => 'sport filter',
            'city' => 'city filter',
            'limit' => 'results limit',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation logic
            $this->validatePricingConsistency($validator);
            $this->validateAlertThresholdConsistency($validator);
            $this->validateLocationPreferences($validator);
            $this->validateMonitoringWindow($validator);
        });
    }

    /**
     * Validate pricing preferences consistency.
     */
    private function validatePricingConsistency($validator): void
    {
        $pricing = $this->input('preferences.pricing', []);
        
        if (isset($pricing['budgetMin'], $pricing['budgetMax'])) {
            if ($pricing['budgetMin'] > $pricing['budgetMax']) {
                $validator->errors()->add(
                    'preferences.pricing.budgetMin',
                    'Minimum budget cannot be greater than maximum budget.'
                );
            }
            
            // Warn if budget range is very narrow
            if (($pricing['budgetMax'] - $pricing['budgetMin']) < 10) {
                $validator->errors()->add(
                    'preferences.pricing',
                    'Budget range is very narrow. Consider widening the range for better ticket options.'
                );
            }
        }
    }

    /**
     * Validate alert threshold consistency.
     */
    private function validateAlertThresholdConsistency($validator): void
    {
        $thresholds = $this->input('preferences.pricing.alertThresholds', []);
        
        if (count($thresholds) >= 2) {
            $small = $thresholds['small'] ?? 5;
            $medium = $thresholds['medium'] ?? 15;
            $large = $thresholds['large'] ?? 30;
            
            if ($small >= $medium) {
                $validator->errors()->add(
                    'preferences.pricing.alertThresholds.medium',
                    'Medium price drop threshold must be greater than small threshold.'
                );
            }
            
            if ($medium >= $large) {
                $validator->errors()->add(
                    'preferences.pricing.alertThresholds.large',
                    'Large price drop threshold must be greater than medium threshold.'
                );
            }
        }
    }

    /**
     * Validate location preferences.
     */
    private function validateLocationPreferences($validator): void
    {
        $location = $this->input('preferences.location', []);
        
        // Check for duplicate secondary locations
        if (isset($location['secondary']) && is_array($location['secondary'])) {
            $secondary = array_filter($location['secondary']); // Remove empty values
            if (count($secondary) !== count(array_unique($secondary))) {
                $validator->errors()->add(
                    'preferences.location.secondary',
                    'Duplicate secondary locations are not allowed.'
                );
            }
            
            // Check if primary location is in secondary list
            if (isset($location['primary']) && in_array($location['primary'], $secondary)) {
                $validator->errors()->add(
                    'preferences.location.secondary',
                    'Secondary locations cannot include your primary location.'
                );
            }
        }
    }

    /**
     * Validate monitoring window settings.
     */
    private function validateMonitoringWindow($validator): void
    {
        $window = $this->input('preferences.advanced.monitoringWindow', []);
        
        if (isset($window['days'], $window['hours'])) {
            // Validate reasonable monitoring window
            $totalHours = ($window['days'] * 24) + $window['hours'];
            
            if ($totalHours > (365 * 24)) { // More than a year
                $validator->errors()->add(
                    'preferences.advanced.monitoringWindow',
                    'Monitoring window cannot exceed one year total.'
                );
            }
            
            if ($totalHours < 1) { // Less than 1 hour
                $validator->errors()->add(
                    'preferences.advanced.monitoringWindow',
                    'Monitoring window must be at least 1 hour total.'
                );
            }
        }
    }

    /**
     * Get validated and sanitized preference data.
     */
    public function getValidatedPreferences(): array
    {
        $validated = $this->validated();
        $preferences = $validated['preferences'] ?? [];
        
        // Sanitize and normalize preference data
        return $this->sanitizePreferences($preferences);
    }

    /**
     * Sanitize preference data.
     */
    private function sanitizePreferences(array $preferences): array
    {
        // Remove empty arrays and null values
        $preferences = array_filter($preferences, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });

        // Sanitize sports array
        if (isset($preferences['sports'])) {
            $preferences['sports'] = array_unique(array_filter($preferences['sports']));
        }

        // Sanitize location secondary array
        if (isset($preferences['location']['secondary'])) {
            $preferences['location']['secondary'] = array_unique(
                array_filter($preferences['location']['secondary'], 'trim')
            );
        }

        // Ensure pricing thresholds are properly ordered
        if (isset($preferences['pricing']['alertThresholds'])) {
            $thresholds = &$preferences['pricing']['alertThresholds'];
            if (isset($thresholds['small'], $thresholds['medium'], $thresholds['large'])) {
                // Ensure proper ordering
                if ($thresholds['small'] >= $thresholds['medium']) {
                    $thresholds['medium'] = $thresholds['small'] + 5;
                }
                if ($thresholds['medium'] >= $thresholds['large']) {
                    $thresholds['large'] = $thresholds['medium'] + 10;
                }
            }
        }

        return $preferences;
    }
}
