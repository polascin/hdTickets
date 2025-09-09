<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PublicRegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

/**
 * Public Registration Validation Controller
 * 
 * Provides real-time validation feedback for registration fields
 * without performing any side effects (no user creation).
 */
class PublicRegistrationValidationController extends Controller
{
    /**
     * Validate registration fields in real-time
     * 
     * This endpoint mirrors the validation rules from PublicRegistrationRequest
     * to provide immediate feedback without creating users or side effects.
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            // Get the validation rules from PublicRegistrationRequest
            $registrationRequest = new PublicRegistrationRequest();
            $rules = $registrationRequest->rules();
            $messages = $registrationRequest->messages();
            $attributes = $registrationRequest->attributes();

            // Only validate fields that were actually sent
            $fieldsToValidate = array_intersect_key($rules, $request->all());
            
            // Create validator instance
            $validator = Validator::make($request->all(), $fieldsToValidate, $messages, $attributes);

            // Custom validation for specific use cases
            $this->addCustomValidations($validator, $request);

            // Run validation
            $validator->validate();

            // If validation passes, return success with field-specific feedback
            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
                'fields' => $this->getFieldValidationFeedback($request, $fieldsToValidate),
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors in a structured format
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'fields' => $this->getFieldValidationFeedback($request, $fieldsToValidate ?? [], $e->errors()),
                'timestamp' => now()->toISOString(),
            ], 422);

        } catch (\Exception $e) {
            // Handle unexpected errors gracefully
            return response()->json([
                'success' => false,
                'message' => 'Validation service temporarily unavailable',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal error',
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Check if email is already taken
     */
    public function checkEmailAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->input('email')));
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'success' => true,
            'available' => !$exists,
            'email' => $email,
            'message' => $exists 
                ? 'This email address is already registered' 
                : 'Email address is available',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get password strength assessment
     */
    public function checkPasswordStrength(Request $request): JsonResponse
    {
        $password = $request->input('password', '');
        
        $assessment = $this->assessPasswordStrength($password);
        
        return response()->json([
            'success' => true,
            'password_length' => strlen($password),
            'strength_score' => $assessment['score'],
            'strength_label' => $assessment['label'],
            'requirements_met' => $assessment['requirements_met'],
            'requirements_missing' => $assessment['requirements_missing'],
            'suggestions' => $assessment['suggestions'],
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Add custom validations beyond basic rules
     */
    private function addCustomValidations(\Illuminate\Validation\Validator $validator, Request $request): void
    {
        // Add email uniqueness check if email is being validated
        if ($request->has('email')) {
            $validator->sometimes('email', 'unique:users,email', function ($input) use ($request) {
                return $request->has('email') && !empty($request->input('email'));
            });
        }

        // Add password confirmation check if both password fields are present
        if ($request->has('password') && $request->has('password_confirmation')) {
            $validator->sometimes('password_confirmation', 'same:password', function ($input) use ($request) {
                return $request->has('password') && $request->has('password_confirmation');
            });
        }

        // Phone format validation enhancement
        if ($request->has('phone') && !empty($request->input('phone'))) {
            $validator->sometimes('phone', function ($attribute, $value, $fail) {
                // More detailed E.164 format validation
                if (!preg_match('/^\+?[1-9]\d{1,14}$/', $value)) {
                    $fail('Please enter a valid phone number with country code (e.g., +1234567890).');
                }
            });
        }
    }

    /**
     * Get field-specific validation feedback
     */
    private function getFieldValidationFeedback(Request $request, array $rules, array $errors = []): array
    {
        $feedback = [];

        foreach (array_keys($rules) as $field) {
            $value = $request->input($field);
            $hasError = isset($errors[$field]);

            $feedback[$field] = [
                'value' => $value,
                'valid' => !$hasError,
                'error' => $hasError ? $errors[$field][0] : null,
                'touched' => !empty($value),
            ];

            // Add field-specific enhancements
            switch ($field) {
                case 'email':
                    $feedback[$field]['normalized'] = strtolower(trim($value ?? ''));
                    $feedback[$field]['format_valid'] = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                    break;

                case 'phone':
                    if (!empty($value)) {
                        $cleaned = preg_replace('/[^\d+]/', '', $value);
                        $feedback[$field]['cleaned'] = $cleaned;
                        $feedback[$field]['format_valid'] = preg_match('/^\+?[1-9]\d{1,14}$/', $cleaned);
                    }
                    break;

                case 'password':
                    if (!empty($value)) {
                        $assessment = $this->assessPasswordStrength($value);
                        $feedback[$field]['strength'] = $assessment;
                    }
                    break;
            }
        }

        return $feedback;
    }

    /**
     * Assess password strength based on Laravel's default requirements
     */
    private function assessPasswordStrength(string $password): array
    {
        $requirements = [
            'min_length' => strlen($password) >= 8,
            'has_lowercase' => preg_match('/[a-z]/', $password),
            'has_uppercase' => preg_match('/[A-Z]/', $password),
            'has_numbers' => preg_match('/[0-9]/', $password),
            'has_special' => preg_match('/[^A-Za-z0-9]/', $password),
        ];

        $metCount = array_sum($requirements);
        $totalRequirements = count($requirements);

        // Calculate score (0-100)
        $score = ($metCount / $totalRequirements) * 100;

        // Determine label
        if ($score < 40) {
            $label = 'weak';
        } elseif ($score < 80) {
            $label = 'medium';
        } else {
            $label = 'strong';
        }

        // Get missing requirements
        $missing = [];
        $suggestions = [];

        if (!$requirements['min_length']) {
            $missing[] = 'At least 8 characters';
            $suggestions[] = 'Use at least 8 characters';
        }
        if (!$requirements['has_lowercase']) {
            $missing[] = 'One lowercase letter';
            $suggestions[] = 'Include lowercase letters (a-z)';
        }
        if (!$requirements['has_uppercase']) {
            $missing[] = 'One uppercase letter';
            $suggestions[] = 'Include uppercase letters (A-Z)';
        }
        if (!$requirements['has_numbers']) {
            $missing[] = 'One number';
            $suggestions[] = 'Include numbers (0-9)';
        }
        if (!$requirements['has_special']) {
            $missing[] = 'One special character';
            $suggestions[] = 'Include special characters (!@#$%^&*)';
        }

        return [
            'score' => (int) round($score),
            'label' => $label,
            'requirements_met' => array_keys(array_filter($requirements)),
            'requirements_missing' => $missing,
            'suggestions' => $suggestions,
            'requirements_detail' => $requirements,
        ];
    }
}
