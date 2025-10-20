<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

use function in_array;

/**
 * Email Template Model
 *
 * Manages email templates for the HD Tickets platform.
 * Used for storing customizable email templates for various notifications.
 *
 * @property int                        $id
 * @property string                     $key        Unique template identifier
 * @property string                     $name       Display name for the template
 * @property string                     $subject    Email subject line (supports variables)
 * @property string                     $content    Email body content (HTML supported)
 * @property array                      $variables  Available template variables
 * @property bool                       $active     Whether the template is active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class EmailTemplate extends Model
{
    use HasFactory;

    /** Default template variables available across all templates */
    public const DEFAULT_VARIABLES = [
        'user_name'     => 'User\'s full name',
        'user_email'    => 'User\'s email address',
        'platform_name' => 'Platform name',
        'platform_url'  => 'Platform URL',
        'current_date'  => 'Current date',
        'current_time'  => 'Current time',
    ];

    /** Common template types */
    public const TYPE_WELCOME = 'welcome';

    public const TYPE_PRICE_ALERT = 'price_alert';

    public const TYPE_BOOKING_CONFIRMATION = 'booking_confirmation';

    public const TYPE_PASSWORD_RESET = 'password_reset';

    public const TYPE_EMAIL_VERIFICATION = 'email_verification';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'subject',
        'content',
        'variables',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variables'  => 'array',
        'active'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get only active templates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', TRUE);
    }

    /**
     * Get template by key
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Render template with provided variables
     */
    public function render(array $variables = []): array
    {
        // Merge default variables with provided variables
        $allVariables = array_merge(
            $this->getDefaultVariableValues(),
            $variables,
        );

        $subject = $this->subject;
        $content = $this->content;

        // Replace template variables
        foreach ($allVariables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $content = str_replace($placeholder, $value, $content);
        }

        return [
            'subject'        => $subject,
            'content'        => $content,
            'variables_used' => $allVariables,
        ];
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariables(): array
    {
        $templateVariables = $this->variables ?? [];

        return array_merge(self::DEFAULT_VARIABLES, $templateVariables);
    }

    /**
     * Extract variables from template content
     */
    public function extractVariables(): array
    {
        $content = $this->subject . ' ' . $this->content;
        preg_match_all('/{{(\w+)}}/', $content, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Validate template syntax
     */
    public function validateSyntax(): array
    {
        $errors = [];
        $extractedVars = $this->extractVariables();
        $availableVars = array_keys($this->getAvailableVariables());

        // Add common template variables that are typically available
        $commonVars = [
            'event_name', 'venue_name', 'event_date', 'ticket_price',
            'booking_reference', 'old_price', 'savings', 'seat_details',
        ];
        $availableVars = array_merge($availableVars, $commonVars);

        // Check for undefined variables (skip validation during seeding)
        if (! app()->runningInConsole()) {
            foreach ($extractedVars as $var) {
                if (! in_array($var, $availableVars, TRUE)) {
                    $errors[] = "Undefined variable: {{$var}}";
                }
            }
        }

        // Check for empty required fields
        if (empty($this->name)) {
            $errors[] = 'Template name is required';
        }

        if (empty($this->subject)) {
            $errors[] = 'Subject line is required';
        }

        if (empty($this->content)) {
            $errors[] = 'Content is required';
        }

        return $errors;
    }

    /**
     * Clone template with new key
     *
     * @return static
     */
    public function cloneTemplate(string $newKey, ?string $newName = NULL): self
    {
        $clone = $this->replicate();
        $clone->key = $newKey;
        $clone->name = $newName ?? $this->name . ' (Copy)';
        $clone->active = FALSE; // New clones start as inactive
        $clone->save();

        return $clone;
    }

    /**
     * Get template preview with sample data
     */
    public function getPreview(): array
    {
        $sampleData = [
            'user_name'    => 'John Doe',
            'user_email'   => 'john.doe@example.com',
            'event_name'   => 'Lakers vs Warriors',
            'ticket_price' => '$125.00',
            'venue_name'   => 'Staples Center',
            'event_date'   => 'December 25, 2024',
        ];

        return $this->render($sampleData);
    }

    /**
     * Toggle active status
     */
    public function toggle(): bool
    {
        return $this->update(['active' => ! $this->active]);
    }

    /**
     * Get default variable values
     */
    protected function getDefaultVariableValues(): array
    {
        return [
            'platform_name' => config('app.name', 'HD Tickets'),
            'platform_url'  => config('app.url'),
            'current_date'  => now()->format('F j, Y'),
            'current_time'  => now()->format('g:i A'),
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        // Set default values when creating
        static::creating(function ($model): void {
            if (NULL === $model->active) {
                $model->active = TRUE;
            }

            if (empty($model->variables)) {
                $model->variables = [];
            }
        });

        // Validate before saving
        static::saving(function ($model): void {
            $errors = $model->validateSyntax();
            if (! empty($errors)) {
                throw new InvalidArgumentException('Template validation failed: ' . implode(', ', $errors));
            }
        });
    }
}
