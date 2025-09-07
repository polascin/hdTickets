<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

use function count;
use function in_array;
use function is_array;
use function is_callable;
use function is_resource;
use function is_string;
use function strlen;

/**
 * Component Communication Service
 *
 * Manages props/events communication patterns between Blade, Alpine.js, and Vue.js components
 * for the HD Tickets sports events platform.
 */
class ComponentCommunication
{
    private Collection $eventListeners;

    private Collection $propValidators;

    private array $communicationChannels = [];

    public function __construct()
    {
        $this->eventListeners = new Collection();
        $this->propValidators = new Collection();
        $this->initializeCommunicationChannels();
    }

    /**
     * Register a prop validator
     */
    /**
     * RegisterPropValidator
     */
    public function registerPropValidator(string $propName, callable $validator): void
    {
        $this->propValidators->put($propName, $validator);
    }

    /**
     * Validate props for a component
     */
    /**
     * ValidateProps
     */
    public function validateProps(string $componentName, array $props): array
    {
        $errors = [];

        foreach ($props as $propName => $propValue) {
            $validator = $this->propValidators->get($propName);

            if ($validator && !$validator($propValue)) {
                $errors[] = "Invalid prop '{$propName}' for component '{$componentName}'";
            }
        }

        // Type validation
        foreach ($props as $propName => $propValue) {
            $typeError = $this->validatePropType($propName, $propValue);
            if ($typeError) {
                $errors[] = $typeError;
            }
        }

        return $errors;
    }

    /**
     * Register event listener
     */
    /**
     * RegisterEventListener
     */
    public function registerEventListener(string $eventName, string $componentName, callable $handler): void
    {
        $key = "{$eventName}:{$componentName}";
        $this->eventListeners->put($key, $handler);

        Log::info("Event listener registered: {$eventName} for {$componentName}");
    }

    /**
     * Emit event between components
     */
    /**
     * EmitEvent
     */
    public function emitEvent(string $eventName, array $payload = [], ?string $source = NULL): void
    {
        // Log event emission
        Log::info("Event emitted: {$eventName}", ['payload' => $payload, 'source' => $source]);

        // Emit to Laravel event system
        Event::dispatch("component.{$eventName}", [$payload, $source]);

        // Call registered listeners
        $this->eventListeners->each(function ($handler, $key) use ($eventName, $payload, $source): void {
            [$listenerEvent, $componentName] = explode(':', $key);

            if ($listenerEvent === $eventName) {
                try {
                    $handler($payload, $source);
                } catch (Exception $e) {
                    Log::error("Event handler error in {$componentName}: " . $e->getMessage());
                }
            }
        });
    }

    /**
     * Create Blade to Alpine.js data binding
     */
    /**
     * CreateBladeToAlpineBinding
     */
    public function createBladeToAlpineBinding(string $bladeVariable, string $alpineProperty): string
    {
        return "x-data=\"{ {$alpineProperty}: @json({$bladeVariable}) }\"";
    }

    /**
     * Create Blade to Vue.js prop binding
     */
    /**
     * CreateBladeToVueBinding
     */
    public function createBladeToVueBinding(array $props): string
    {
        $propsString = collect($props)->map(function ($value, $key) {
            if (is_string($value)) {
                return ":{$key}=\"'{$value}'\"";
            }

            return ":{$key}=\"@json({$value})\"";
        })->implode(' ');

        return $propsString;
    }

    /**
     * Generate Alpine.js event emitter
     */
    /**
     * GenerateAlpineEventEmitter
     */
    public function generateAlpineEventEmitter(string $eventName, array $payload = []): string
    {
        $payloadJson = json_encode($payload);

        return "\$dispatch('{$eventName}', {$payloadJson})";
    }

    /**
     * Generate Alpine.js event listener
     */
    /**
     * GenerateAlpineEventListener
     */
    public function generateAlpineEventListener(string $eventName, string $handler): string
    {
        return "@{$eventName}=\"{$handler}\"";
    }

    /**
     * Generate Vue.js event emitter
     */
    /**
     * GenerateVueEventEmitter
     */
    public function generateVueEventEmitter(string $eventName, array $payload = []): string
    {
        $payloadJson = json_encode($payload);

        return "\$emit('{$eventName}', {$payloadJson})";
    }

    /**
     * Generate Vue.js event listener
     */
    /**
     * GenerateVueEventListener
     */
    public function generateVueEventListener(string $eventName, string $handler): string
    {
        return "@{$eventName}=\"{$handler}\"";
    }

    /**
     * Create communication bridge between different component types
     */
    /**
     * CreateCommunicationBridge
     */
    public function createCommunicationBridge(string $sourceType, string $targetType): array
    {
        $channelKey = "{$sourceType}-to-{$targetType}";
        $channel = $this->communicationChannels[$channelKey] ?? NULL;

        if (!$channel) {
            throw new InvalidArgumentException("Communication channel not supported: {$channelKey}");
        }

        return [
            'channel'  => $channelKey,
            'method'   => $channel['method'],
            'pattern'  => $channel['pattern'],
            'examples' => $this->generateCommunicationExamples($sourceType, $targetType),
        ];
    }

    /**
     * Validate communication between Blade and Alpine
     */
    /**
     * ValidateBladeToAlpine
     */
    public function validateBladeToAlpine(array $data): bool
    {
        // Ensure data can be JSON serialized
        try {
            json_encode($data);

            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Validate communication between Blade and Vue
     */
    /**
     * ValidateBladeToVue
     */
    public function validateBladeToVue(array $data): bool
    {
        // Check for Vue-compatible data types
        foreach ($data as $key => $value) {
            if (is_resource($value) || is_callable($value)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Validate communication between Alpine components
     */
    /**
     * ValidateAlpineToAlpine
     */
    public function validateAlpineToAlpine(array $eventData): bool
    {
        // Alpine events should have simple, serializable data
        return is_array($eventData) && json_encode($eventData) !== FALSE;
    }

    /**
     * Validate communication between Alpine and Vue
     */
    /**
     * ValidateAlpineToVue
     */
    public function validateAlpineToVue(array $eventData): bool
    {
        // Check event data structure
        return isset($eventData['type']) && isset($eventData['payload']);
    }

    /**
     * Validate communication between Vue components
     */
    /**
     * ValidateVueToVue
     */
    public function validateVueToVue(array $eventData): bool
    {
        // Vue events are handled internally, always valid if properly structured
        return TRUE;
    }

    /**
     * Validate communication between Vue and Alpine
     */
    /**
     * ValidateVueToAlpine
     */
    public function validateVueToAlpine(array $eventData): bool
    {
        // Check for DOM event compatibility
        return is_array($eventData) && !empty($eventData);
    }

    /**
     * Get communication patterns documentation
     */
    /**
     * Get  communication patterns
     */
    public function getCommunicationPatterns(): array
    {
        return [
            'blade_to_alpine' => [
                'description' => 'Server-side data injection into Alpine.js components',
                'use_cases'   => [
                    'Initial component state',
                    'Server-generated configuration',
                    'User authentication data',
                ],
                'examples' => [
                    'basic'         => '<div x-data="{ tickets: @json($tickets) }">',
                    'with_config'   => '<div x-data="ticketManager(@json($config))">',
                    'authenticated' => '<div x-data="dashboard()" x-init="setUser(@json($user))">',
                ],
            ],
            'alpine_to_vue' => [
                'description' => 'Alpine.js to Vue.js communication via custom events',
                'use_cases'   => [
                    'Simple component triggers complex component',
                    'Legacy Alpine code with new Vue features',
                    'Progressive enhancement',
                ],
                'examples' => [
                    'event_dispatch' => '$dispatch("ticket-selected", { id: ticketId })',
                    'dom_event'      => 'document.dispatchEvent(new CustomEvent("data-changed"))',
                    'shared_store'   => 'Alpine.store("shared").updateTickets(tickets)',
                ],
            ],
            'vue_to_alpine' => [
                'description' => 'Vue.js to Alpine.js communication for legacy integration',
                'use_cases'   => [
                    'Vue dashboard controlling Alpine widgets',
                    'Complex component updating simple indicators',
                    'Migration scenarios',
                ],
                'examples' => [
                    'dom_event'    => 'document.dispatchEvent(new CustomEvent("vue-updated"))',
                    'direct_call'  => 'document.querySelector("[x-data]").__x.$data.update()',
                    'global_state' => 'window.appState.tickets = this.tickets',
                ],
            ],
        ];
    }

    /**
     * Create event channel for real-time communication
     */
    /**
     * CreateEventChannel
     */
    public function createEventChannel(string $channelName): array
    {
        return [
            'name' => $channelName,
            'emit' => function ($eventName, $data) use ($channelName): void {
                $this->emitEvent("{$channelName}.{$eventName}", $data);
            },
            'listen' => function ($eventName, $handler) use ($channelName): void {
                $this->registerEventListener("{$channelName}.{$eventName}", $channelName, $handler);
            },
        ];
    }

    /**
     * Get component communication statistics
     */
    /**
     * Get  stats
     */
    public function getStats(): array
    {
        return [
            'registered_listeners'   => $this->eventListeners->count(),
            'prop_validators'        => $this->propValidators->count(),
            'communication_channels' => count($this->communicationChannels),
            'supported_patterns'     => array_keys($this->communicationChannels),
        ];
    }

    /**
     * Initialize communication channels
     */
    /**
     * InitializeCommunicationChannels
     */
    private function initializeCommunicationChannels(): void
    {
        $this->communicationChannels = [
            'blade-to-alpine' => [
                'method'    => 'data-attributes',
                'pattern'   => 'server-to-client',
                'validator' => [$this, 'validateBladeToAlpine'],
            ],
            'blade-to-vue' => [
                'method'    => 'data-props',
                'pattern'   => 'server-to-client',
                'validator' => [$this, 'validateBladeToVue'],
            ],
            'alpine-to-alpine' => [
                'method'    => 'alpine-events',
                'pattern'   => 'client-to-client',
                'validator' => [$this, 'validateAlpineToAlpine'],
            ],
            'alpine-to-vue' => [
                'method'    => 'custom-events',
                'pattern'   => 'client-to-client',
                'validator' => [$this, 'validateAlpineToVue'],
            ],
            'vue-to-vue' => [
                'method'    => 'vue-events',
                'pattern'   => 'client-to-client',
                'validator' => [$this, 'validateVueToVue'],
            ],
            'vue-to-alpine' => [
                'method'    => 'dom-events',
                'pattern'   => 'client-to-client',
                'validator' => [$this, 'validateVueToAlpine'],
            ],
        ];
    }

    /**
     * Validate prop type
     *
     * @param mixed $propValue
     */
    /**
     * ValidatePropType
     *
     * @param mixed $propValue
     */
    private function validatePropType(string $propName, $propValue): ?string
    {
        // Common sport events platform prop types
        $typeValidations = [
            'ticket_id'           => fn ($v) => is_string($v) && preg_match('/^[A-Z0-9\-]{8,20}$/', $v),
            'event_id'            => fn ($v) => is_string($v) && preg_match('/^EVT-[0-9]{6}$/', $v),
            'price'               => fn ($v) => is_numeric($v) && $v >= 0,
            'venue'               => fn ($v) => is_string($v) && strlen($v) >= 2,
            'date'                => fn ($v) => is_string($v) && strtotime($v) !== FALSE,
            'sport_category'      => fn ($v) => in_array($v, ['football', 'rugby', 'cricket', 'tennis', 'other'], TRUE),
            'availability_status' => fn ($v) => in_array($v, ['available', 'limited', 'sold_out', 'on_hold'], TRUE),
            'platform_source'     => fn ($v) => in_array($v, ['ticketmaster', 'stubhub', 'seatgeek', 'official'], TRUE),
        ];

        if (isset($typeValidations[$propName])) {
            $validator = $typeValidations[$propName];
            if (!$validator($propValue)) {
                return "Prop '{$propName}' has invalid type or format";
            }
        }

        return NULL;
    }

    /**
     * Generate communication examples
     */
    /**
     * GenerateCommunicationExamples
     */
    private function generateCommunicationExamples(string $sourceType, string $targetType): array
    {
        $examples = [];

        switch ("{$sourceType}-to-{$targetType}") {
            case 'blade-to-alpine':
                $examples = [
                    'data_binding' => '<div x-data="{ ticketData: @json($ticket) }">',
                    'prop_passing' => '<div x-data="ticketCard()" data-ticket-id="{{ $ticket->id }}">',
                    'event_setup'  => '<div @ticket-selected="handleTicketSelection">',
                ];

                break;
            case 'blade-to-vue':
                $examples = [
                    'prop_binding'   => '<ticket-dashboard :initial-tickets="@json($tickets)" :user-role="{{ auth()->user()->role }}">',
                    'data_injection' => '<div id="vue-app" data-initial-state="@json($initialState)">',
                    'config_passing' => '<dashboard-component :config="@json($dashboardConfig)">',
                ];

                break;
            case 'alpine-to-vue':
                $examples = [
                    'custom_event' => 'this.$dispatch("ticket-updated", { ticketId: this.ticketId });',
                    'dom_event'    => 'document.dispatchEvent(new CustomEvent("alpine-data-changed"));',
                    'shared_state' => 'Alpine.store("tickets").update(newTickets);',
                ];

                break;
            case 'vue-to-alpine':
                $examples = [
                    'dom_event'      => 'document.dispatchEvent(new CustomEvent("vue-state-changed"));',
                    'element_method' => 'this.$refs.alpineComponent.refreshData();',
                    'global_state'   => 'window.sharedState.tickets = this.tickets;',
                ];

                break;
        }

        return $examples;
    }
}
