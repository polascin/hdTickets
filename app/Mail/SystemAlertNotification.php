<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use function in_array;

class SystemAlertNotification extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $alertType;

    public $message;

    public $level;

    public $data;

    public $user;

    public $timestamp;

    /**
     * Create a new message instance.
     *
     * @param mixed      $alertType
     * @param mixed      $message
     * @param mixed      $level
     * @param mixed      $user
     * @param mixed|null $data
     */
    public function __construct($alertType, $message, $level, $user, $data = NULL)
    {
        $this->alertType = $alertType;
        $this->message = $message;
        $this->level = $level; // info, warning, error, critical
        $this->user = $user;
        $this->data = $data;
        $this->timestamp = now();
    }

    /**
     * Get the message envelope.
     */
    /**
     * Envelope
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubjectLine();
        $priority = $this->getPriority();

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            tags: ['system-alert', $this->level, $this->alertType],
            metadata: [
                'alert_type' => $this->alertType,
                'level'      => $this->level,
                'user_id'    => $this->user['id'] ?? NULL,
                'timestamp'  => $this->timestamp->toISOString(),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    /**
     * Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.system-alert-notification',
            text: 'emails.system-alert-notification-text',
            with: [
                'alertType'            => $this->alertType,
                'message'              => $this->message,
                'level'                => $this->level,
                'user'                 => $this->user,
                'data'                 => $this->data,
                'timestamp'            => $this->timestamp,
                'isCritical'           => $this->level === 'critical',
                'isError'              => $this->level === 'error',
                'isWarning'            => $this->level === 'warning',
                'levelIcon'            => $this->getLevelIcon(),
                'levelColor'           => $this->getLevelColor(),
                'actionRequired'       => $this->requiresAction(),
                'troubleshootingSteps' => $this->getTroubleshootingSteps(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    /**
     * Attachments
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Generate appropriate subject line based on alert level
     */
    /**
     * Get  subject line
     */
    private function getSubjectLine(): string
    {
        $prefix = match ($this->level) {
            'critical' => '🚨 CRITICAL ALERT',
            'error'    => '❌ ERROR ALERT',
            'warning'  => '⚠️ WARNING',
            'info'     => '📊 INFO',
            default    => '🔔 SYSTEM ALERT',
        };

        return "{$prefix}: {$this->alertType}";
    }

    /**
     * Get email priority based on alert level
     */
    /**
     * Get  priority
     */
    private function getPriority(): int
    {
        return match ($this->level) {
            'critical' => 1, // Highest priority
            'error'    => 2,
            'warning'  => 3,
            'info'     => 4,
            default    => 5, // Lowest priority
        };
    }

    /**
     * Get icon for alert level
     */
    /**
     * Get  level icon
     */
    private function getLevelIcon(): string
    {
        return match ($this->level) {
            'critical' => '🚨',
            'error'    => '❌',
            'warning'  => '⚠️',
            'info'     => '📊',
            default    => '🔔',
        };
    }

    /**
     * Get color for alert level styling
     */
    /**
     * Get  level color
     */
    private function getLevelColor(): string
    {
        return match ($this->level) {
            'critical' => '#dc2626', // Red
            'error'    => '#ef4444',
            'warning'  => '#f59e0b', // Amber
            'info'     => '#3b82f6', // Blue
            default    => '#6b7280', // Gray
        };
    }

    /**
     * Check if this alert requires immediate action
     */
    /**
     * RequiresAction
     */
    private function requiresAction(): bool
    {
        return in_array($this->level, ['critical', 'error'], TRUE)
               || in_array($this->alertType, ['platform_down', 'scraping_blocked', 'database_error'], TRUE);
    }

    /**
     * Get troubleshooting steps based on alert type
     */
    /**
     * Get  troubleshooting steps
     */
    private function getTroubleshootingSteps(): array
    {
        return match ($this->alertType) {
            'platform_down' => [
                'Check platform status page',
                'Verify network connectivity',
                'Review recent configuration changes',
                'Contact platform support if needed',
            ],
            'scraping_blocked' => [
                'Check IP address whitelist',
                'Review rate limiting settings',
                'Rotate proxy servers if applicable',
                'Update user agent strings',
            ],
            'database_error' => [
                'Check database connection',
                'Review database logs',
                'Verify disk space availability',
                'Restart database service if needed',
            ],
            'high_error_rate' => [
                'Review application logs',
                'Check server resources',
                'Monitor traffic patterns',
                'Investigate recent deployments',
            ],
            default => [
                'Review system logs',
                'Check monitoring dashboards',
                'Contact system administrator',
            ],
        };
    }
}
