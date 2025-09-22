<?php declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Event\Events\SportsEventCreatedFromEmail;
use App\Domain\Event\Events\SportsEventUpdatedFromEmail;
use App\Domain\Event\Models\SportsEvent;
use App\Domain\Ticket\Events\TicketCreatedFromEmail;
use App\Domain\Ticket\Models\Ticket;
use App\Services\Email\EmailParsingService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function count;

/**
 * Process Sports Event Email Job
 *
 * Queue job that processes individual sports event emails to extract
 * ticket and event information for the HD Tickets system.
 */
class ProcessSportsEventEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** The number of times the job may be attempted. */
    public int $tries = 3;

    /** The maximum number of seconds the job can run. */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $emailData)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(EmailParsingService $parsingService): void
    {
        $uid = $this->emailData['uid'] ?? 'unknown';
        $connection = $this->emailData['connection'] ?? 'unknown';
        $platform = $this->emailData['platform'] ?? 'unknown';

        Log::info('Processing sports event email', [
            'uid'        => $uid,
            'connection' => $connection,
            'platform'   => $platform,
            'job_id'     => $this->job->getJobId(),
        ]);

        try {
            // Parse email content to extract sports event information
            $parsedData = $parsingService->parseEmailContent($this->emailData);

            // Process extracted sports events
            if (! empty($parsedData['sports_events'])) {
                foreach ($parsedData['sports_events'] as $eventData) {
                    $this->processSportsEvent($eventData, $parsedData);
                }
            }

            // Process extracted tickets
            if (! empty($parsedData['tickets'])) {
                foreach ($parsedData['tickets'] as $ticketData) {
                    $this->processTicket($ticketData, $parsedData);
                }
            }

            // Store email processing record
            $this->storeEmailProcessingRecord($parsedData);

            Log::info('Sports event email processed successfully', [
                'uid'                 => $uid,
                'connection'          => $connection,
                'platform'            => $platform,
                'sports_events_count' => count($parsedData['sports_events']),
                'tickets_count'       => count($parsedData['tickets']),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to process sports event email', [
                'uid'        => $uid,
                'connection' => $connection,
                'platform'   => $platform,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('Sports event email processing job failed permanently', [
            'uid'        => $this->emailData['uid'] ?? 'unknown',
            'connection' => $this->emailData['connection'] ?? 'unknown',
            'platform'   => $this->emailData['platform'] ?? 'unknown',
            'error'      => $exception->getMessage(),
            'attempts'   => $this->attempts(),
        ]);

        // Could send notification to administrators about failed job
    }

    /**
     * Process individual sports event
     *
     * @param array $eventData  Extracted event data
     * @param array $parsedData Complete parsed data
     */
    private function processSportsEvent(array $eventData, array $parsedData): void
    {
        try {
            // Check if sports event already exists
            $existingEvent = SportsEvent::where('name', $eventData['name'])
                ->where('source_platform', $eventData['source_platform'])
                ->first();

            if ($existingEvent) {
                // Update existing event with new information
                $this->updateExistingSportsEvent($existingEvent, $eventData, $parsedData);
            } else {
                // Create new sports event
                $this->createNewSportsEvent($eventData, $parsedData);
            }
        } catch (Exception $e) {
            Log::error('Failed to process sports event', [
                'event_name' => $eventData['name'] ?? 'unknown',
                'platform'   => $eventData['source_platform'] ?? 'unknown',
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create new sports event
     *
     * @param array $eventData  Event data
     * @param array $parsedData Complete parsed data
     */
    private function createNewSportsEvent(array $eventData, array $parsedData): void
    {
        $sportsEvent = SportsEvent::create([
            'name'            => $eventData['name'],
            'slug'            => $this->generateSlug($eventData['name']),
            'category'        => $eventData['category'] ?? 'unknown',
            'source_platform' => $eventData['source_platform'],
            'venue'           => $eventData['venue'] ?? NULL,
            'event_date'      => $eventData['event_date'] ?? NULL,
            'description'     => $this->generateDescription($eventData),
            'metadata'        => [
                'email_source' => [
                    'uid'          => $parsedData['email_uid'],
                    'connection'   => $parsedData['connection'],
                    'platform'     => $parsedData['platform'],
                    'processed_at' => $parsedData['processed_at'],
                ],
                'original_data'    => $eventData,
                'parsing_metadata' => $parsedData['metadata'] ?? [],
            ],
            'status'             => 'active',
            'created_from_email' => TRUE,
        ]);

        Log::info('New sports event created from email', [
            'event_id'   => $sportsEvent->id,
            'event_name' => $sportsEvent->name,
            'platform'   => $sportsEvent->source_platform,
        ]);

        // Trigger event for other parts of the system
        event(new SportsEventCreatedFromEmail($sportsEvent, $parsedData));
    }

    /**
     * Update existing sports event
     *
     * @param SportsEvent $event      Existing event
     * @param array       $eventData  New event data
     * @param array       $parsedData Complete parsed data
     */
    private function updateExistingSportsEvent(SportsEvent $event, array $eventData, array $parsedData): void
    {
        $updated = FALSE;
        $updates = [];

        // Update venue if not set or different
        if (empty($event->venue) && ! empty($eventData['venue'])) {
            $updates['venue'] = $eventData['venue'];
            $updated = TRUE;
        }

        // Update event date if not set or different
        if (empty($event->event_date) && ! empty($eventData['event_date'])) {
            $updates['event_date'] = $eventData['event_date'];
            $updated = TRUE;
        }

        // Update metadata with new email information
        $metadata = $event->metadata ?? [];
        $metadata['email_sources'] ??= [];
        $metadata['email_sources'][] = [
            'uid'          => $parsedData['email_uid'],
            'connection'   => $parsedData['connection'],
            'platform'     => $parsedData['platform'],
            'processed_at' => $parsedData['processed_at'],
        ];
        $updates['metadata'] = $metadata;

        // Update last seen timestamp
        $updates['last_seen_at'] = now();
        $event->update($updates);
        Log::info('Sports event updated from email', [
            'event_id'   => $event->id,
            'event_name' => $event->name,
            'platform'   => $eventData['source_platform'],
            'updates'    => array_keys($updates),
        ]);
        // Trigger event for other parts of the system
        event(new SportsEventUpdatedFromEmail($event, $eventData, $parsedData));
    }

    /**
     * Process individual ticket
     *
     * @param array $ticketData Extracted ticket data
     * @param array $parsedData Complete parsed data
     */
    private function processTicket(array $ticketData, array $parsedData): void
    {
        try {
            // Find associated sports event if available
            $sportsEvent = NULL;
            if (! empty($parsedData['sports_events'])) {
                $eventName = $parsedData['sports_events'][0]['name'] ?? NULL;
                if ($eventName) {
                    $sportsEvent = SportsEvent::where('name', $eventName)
                        ->where('source_platform', $ticketData['source_platform'])
                        ->first();
                }
            }

            // Create ticket record
            $ticket = Ticket::create([
                'sports_event_id'     => $sportsEvent?->id,
                'title'               => $this->generateTicketTitle($ticketData, $sportsEvent),
                'price'               => $ticketData['price'],
                'source_platform'     => $ticketData['source_platform'],
                'availability_status' => $ticketData['availability_status'] ?? 'available',
                'section'             => $ticketData['section'] ?? NULL,
                'row'                 => $ticketData['row'] ?? NULL,
                'seat_type'           => $ticketData['seat_type'] ?? NULL,
                'is_deal'             => $ticketData['is_deal'] ?? FALSE,
                'metadata'            => [
                    'email_source' => [
                        'uid'          => $parsedData['email_uid'],
                        'connection'   => $parsedData['connection'],
                        'platform'     => $parsedData['platform'],
                        'processed_at' => $parsedData['processed_at'],
                    ],
                    'original_data'    => $ticketData,
                    'parsing_metadata' => $parsedData['metadata'] ?? [],
                ],
                'created_from_email' => TRUE,
                'expires_at'         => now()->addDays(7), // Tickets from emails expire after 7 days
            ]);

            Log::info('New ticket created from email', [
                'ticket_id'       => $ticket->id,
                'price'           => $ticket->price,
                'platform'        => $ticket->source_platform,
                'sports_event_id' => $ticket->sports_event_id,
            ]);

            // Trigger event for other parts of the system
            event(new TicketCreatedFromEmail($ticket, $parsedData));
        } catch (Exception $e) {
            Log::error('Failed to process ticket', [
                'ticket_data' => $ticketData,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store email processing record for audit trail
     *
     * @param array $parsedData Parsed data
     */
    private function storeEmailProcessingRecord(array $parsedData): void
    {
        try {
            // Store in email processing log table (would need to create this table)
            // This is for audit trail and debugging purposes

            Log::info('Email processing completed', [
                'uid'                     => $parsedData['email_uid'],
                'connection'              => $parsedData['connection'],
                'platform'                => $parsedData['platform'],
                'sports_events_processed' => count($parsedData['sports_events']),
                'tickets_processed'       => count($parsedData['tickets']),
                'has_parsing_error'       => isset($parsedData['parsing_error']),
                'processing_metadata'     => $parsedData['metadata'] ?? [],
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to store email processing record', [
                'error' => $e->getMessage(),
                'uid'   => $parsedData['email_uid'] ?? 'unknown',
            ]);
        }
    }

    /**
     * Generate slug from event name
     *
     * @param string $name Event name
     *
     * @return string Slug
     */
    private function generateSlug(string $name): string
    {
        return Str::slug($name) . '-' . uniqid();
    }

    /**
     * Generate description from event data
     *
     * @param array $eventData Event data
     *
     * @return string Description
     */
    private function generateDescription(array $eventData): string
    {
        $description = 'Sports event automatically detected from email notification.';

        if (! empty($eventData['venue'])) {
            $description .= ' Taking place at ' . $eventData['venue'] . '.';
        }

        if (! empty($eventData['event_date'])) {
            $description .= ' Scheduled for ' . $eventData['event_date'] . '.';
        }

        return $description . (' Source: ' . ucfirst($eventData['source_platform'] ?? 'unknown platform') . '.');
    }

    /**
     * Generate ticket title
     *
     * @param array            $ticketData  Ticket data
     * @param SportsEvent|null $sportsEvent Associated event
     *
     * @return string Ticket title
     */
    private function generateTicketTitle(array $ticketData, ?SportsEvent $sportsEvent = NULL): string
    {
        $title = $sportsEvent instanceof SportsEvent ? $sportsEvent->name : 'Sports Event Ticket';

        if (! empty($ticketData['section'])) {
            $title .= ' - Section ' . $ticketData['section'];
        }

        if (! empty($ticketData['row'])) {
            $title .= ', Row ' . $ticketData['row'];
        }

        return $title;
    }
}
