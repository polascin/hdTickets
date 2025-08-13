<?php declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

use function in_array;
use function is_array;
use function is_string;

class InputValidationService
{
    /** Security patterns to detect potential attacks */
    private const DANGEROUS_PATTERNS = [
        // SQL Injection patterns
        '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|UNION|SCRIPT)\b)/i',

        // XSS patterns
        '/<script[^>]*>.*?<\/script>/si',
        '/javascript:/i',
        '/on\w+\s*=/i',

        // Command injection patterns
        '/[;&|`$(){}[\]]/i',

        // Path traversal patterns
        '/\.\.[\/\\\\]/i',

        // PHP code injection patterns
        '/(<\?php|\?>)/i',

        // HTML injection patterns
        '/<iframe[^>]*>/i',
        '/<object[^>]*>/i',
        '/<embed[^>]*>/i',
        '/<form[^>]*>/i',
    ];

    /**
     * Sanitize input data recursively
     *
     * @param mixed $data
     */
    /**
     * SanitizeInput
     */
    public function sanitizeInput(array $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }

        if (! is_string($data)) {
            return $data;
        }

        // Basic sanitization
        $sanitized = trim($data);

        // Remove null bytes
        $sanitized = str_replace("\0", '', $sanitized);

        // Normalize unicode
        $sanitized = mb_convert_encoding($sanitized, 'UTF-8', 'UTF-8');

        // Remove control characters except newlines and tabs
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sanitized);
    }

    /**
     * Validate and sanitize ticket data for sports events
     */
    /**
     * ValidateTicketData
     */
    public function validateTicketData(array $data): array
    {
        $rules = [
            'title'                => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\.,!@#$%&*()_+={}[\]:";\'<>?,.\\/]+$/',
            'description'          => 'required|string|max:5000',
            'price'                => 'required|numeric|min:0|max:99999.99',
            'event_date'           => 'required|date|after:now',
            'venue'                => 'required|string|max:255',
            'sport_type'           => 'required|string|in:football,basketball,baseball,soccer,tennis,cricket,rugby,motorsport,other',
            'category'             => 'required|string|max:100',
            'quantity'             => 'required|integer|min:1|max:100',
            'section'              => 'nullable|string|max:50',
            'row'                  => 'nullable|string|max:10',
            'seat_numbers'         => 'nullable|array',
            'seat_numbers.*'       => 'string|max:10',
            'metadata'             => 'nullable|array',
            'metadata.platform'    => 'nullable|string|max:50',
            'metadata.external_id' => 'nullable|string|max:100',
            'metadata.scraped_at'  => 'nullable|date',
        ];

        $messages = [
            'title.regex'      => 'The ticket title contains invalid characters.',
            'sport_type.in'    => 'Invalid sport type for events monitoring system.',
            'price.max'        => 'Ticket price cannot exceed £99,999.99.',
            'event_date.after' => 'Event date must be in the future.',
            'quantity.max'     => 'Maximum 100 tickets can be processed at once.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $this->sanitizeValidatedData($validator->validated());
    }

    /**
     * Validate user input with security checks
     */
    /**
     * ValidateUserInput
     */
    public function validateUserInput(array $data, array $rules): array
    {
        // First, sanitize all input
        $sanitizedData = $this->sanitizeInput($data);

        // Check for dangerous patterns
        $this->detectDangerousPatterns($sanitizedData);

        // Apply validation rules
        $validator = Validator::make($sanitizedData, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Sanitize HTML content while preserving safe formatting
     */
    /**
     * SanitizeHtmlContent
     */
    public function sanitizeHtmlContent(string $content): string
    {
        // Use HTMLPurifier for comprehensive HTML sanitization
        $config = HTMLPurifier_Config::createDefault();

        // Allow only safe HTML tags for sports event descriptions
        $config->set('HTML.Allowed', 'p,br,strong,em,u,ol,ul,li,h3,h4,h5,h6');

        // Disable JavaScript and dangerous attributes
        $config->set('HTML.ForbiddenAttributes', 'style,onclick,onload,onerror');

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($content);
    }

    /**
     * Validate API request data with rate limiting considerations
     */
    /**
     * ValidateApiRequest
     */
    public function validateApiRequest(array $data, string $endpoint): array
    {
        $baseRules = [
            'api_key'   => 'required|string|size:40',
            'timestamp' => 'required|integer|min:' . (time() - 300) . '|max:' . (time() + 60),
        ];

        // Endpoint-specific rules for sports events monitoring
        $endpointRules = match ($endpoint) {
            'scrape-tickets' => [
                'platform'           => 'required|string|in:ticketmaster,stubhub,viagogo,seetickets',
                'search_query'       => 'required|string|max:200',
                'max_results'        => 'integer|min:1|max:1000',
                'filters'            => 'array',
                'filters.sport_type' => 'string|in:football,basketball,baseball,soccer,tennis,cricket,rugby,motorsport',
                'filters.date_from'  => 'date|after:now',
                'filters.date_to'    => 'date|after:filters.date_from',
                'filters.price_min'  => 'numeric|min:0',
                'filters.price_max'  => 'numeric|min:filters.price_min|max:99999',
            ],
            'purchase-tickets' => [
                'ticket_ids'      => 'required|array|min:1|max:10',
                'ticket_ids.*'    => 'integer|exists:scraped_tickets,id',
                'purchase_method' => 'required|string|in:automated,manual',
                'max_price'       => 'required|numeric|min:0|max:10000',
                'quantity'        => 'required|integer|min:1|max:10',
            ],
            'analytics' => [
                'date_range'   => 'required|string|in:7d,30d,90d,1y',
                'metrics'      => 'array|min:1',
                'metrics.*'    => 'string|in:price_trends,availability,popular_events,platform_performance',
                'sport_filter' => 'nullable|string|in:football,basketball,baseball,soccer,tennis,cricket,rugby,motorsport',
            ],
            default => [],
        };

        $rules = array_merge($baseRules, $endpointRules);

        return $this->validateUserInput($data, $rules);
    }

    /**
     * Generate secure slug for sports events
     */
    /**
     * GenerateSecureSlug
     */
    public function generateSecureSlug(string $title, string $sport = ''): string
    {
        $slug = Str::slug($title);

        if ($sport) {
            $slug = Str::slug($sport) . '-' . $slug;
        }

        // Ensure slug is not too long and contains no dangerous characters
        $slug = substr($slug, 0, 100);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        return $slug ?: 'event-' . uniqid();
    }

    /**
     * Validate file upload for ticket attachments
     */
    /**
     * ValidateFileUpload
     */
    public function validateFileUpload(array $file): array
    {
        $rules = [
            'file' => [
                'required',
                'file',
                'max:5120', // 5MB max
                'mimes:pdf,jpg,jpeg,png,gif',
                'dimensions:max_width=2048,max_height=2048',
            ],
        ];

        $validator = Validator::make(['file' => $file], $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Sanitize search query for ticket searching
     */
    /**
     * SanitizeSearchQuery
     */
    public function sanitizeSearchQuery(string $query): string
    {
        // Remove dangerous characters but keep sports-related terms
        $sanitized = preg_replace('/[^\w\s\-\.,&]/', '', $query);

        // Limit length
        $sanitized = substr($sanitized, 0, 200);

        // Trim and normalize spaces
        return trim(preg_replace('/\s+/', ' ', $sanitized));
    }

    /**
     * Detect potentially dangerous patterns in input
     *
     * @param mixed $data
     */
    /**
     * DetectDangerousPatterns
     */
    private function detectDangerousPatterns(array $data): void
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                $this->detectDangerousPatterns($value);
            }

            return;
        }

        if (! is_string($data)) {
            return;
        }

        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $data)) {
                throw new InvalidArgumentException('Input contains potentially dangerous content and has been rejected for security reasons.');
            }
        }
    }

    /**
     * Sanitize validated data for safe storage
     */
    /**
     * SanitizeValidatedData
     */
    private function sanitizeValidatedData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Additional sanitization for specific fields
                if (in_array($key, ['title', 'venue', 'category'], TRUE)) {
                    $data[$key] = strip_tags($value);
                } elseif ($key === 'description') {
                    $data[$key] = $this->sanitizeHtmlContent($value);
                } else {
                    $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeValidatedData($value);
            }
        }

        return $data;
    }
}
