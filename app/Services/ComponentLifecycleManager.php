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

/**
 * Component Lifecycle Manager
 *
 * Manages the complete lifecycle of Blade, Alpine.js, and Vue.js components
 * including initialization, mounting, updating, and cleanup processes.
 */
class ComponentLifecycleManager
{
    private Collection $componentStates;

    private Collection $lifecycleHooks;

    private Collection $activeComponents;

    private array $lifecycleEvents = [
        'created',
        'mounted',
        'updated',
        'unmounted',
        'error',
        'beforeCreate',
        'beforeMount',
        'beforeUpdate',
        'beforeUnmount',
    ];

    public function __construct()
    {
        $this->componentStates = new Collection();
        $this->lifecycleHooks = new Collection();
        $this->activeComponents = new Collection();
        $this->setupLifecycleEvents();
    }

    /**
     * Register a component for lifecycle management
     */
    /**
     * Register
     */
    public function register(string $componentName, string $type, array $config = []): void
    {
        $componentState = [
            'name'                => $componentName,
            'type'                => $type,
            'state'               => 'registered',
            'config'              => $config,
            'created_at'          => now(),
            'mounted_at'          => NULL,
            'updated_at'          => NULL,
            'unmounted_at'        => NULL,
            'error_count'         => 0,
            'last_error'          => NULL,
            'performance_metrics' => [
                'creation_time' => 0,
                'mount_time'    => 0,
                'update_count'  => 0,
                'memory_usage'  => 0,
            ],
            'dependencies'      => $config['dependencies'] ?? [],
            'cleanup_callbacks' => [],
            'watchers'          => [],
            'timers'            => [],
        ];

        $this->componentStates->put($componentName, $componentState);

        $this->emitLifecycleEvent('registered', $componentName, $componentState);

        Log::info("Component registered for lifecycle management: {$componentName} ({$type})");
    }

    /**
     * Initialize component lifecycle
     */
    /**
     * Initialize
     */
    public function initialize(string $componentName, array $initData = []): void
    {
        $componentState = $this->getComponentState($componentName);

        if (! $componentState) {
            throw new InvalidArgumentException("Component not registered: {$componentName}");
        }

        $startTime = microtime(TRUE);

        try {
            // Execute before create hooks
            $this->executeHooks($componentName, 'beforeCreate', $initData);

            // Update state
            $componentState['state'] = 'initializing';
            $componentState['init_data'] = $initData;
            $this->updateComponentState($componentName, $componentState);

            // Initialize based on component type
            switch ($componentState['type']) {
                case 'blade':
                    $this->initializeBladeComponent($componentName, $componentState, $initData);

                    break;
                case 'alpine':
                    $this->initializeAlpineComponent($componentName, $componentState, $initData);

                    break;
                case 'vue':
                    $this->initializeVueComponent($componentName, $componentState, $initData);

                    break;
                default:
                    throw new InvalidArgumentException("Unknown component type: {$componentState['type']}");
            }

            // Record performance metrics
            $componentState['performance_metrics']['creation_time'] = (microtime(TRUE) - $startTime) * 1000;
            $componentState['state'] = 'created';
            $componentState['created_at'] = now();

            $this->updateComponentState($componentName, $componentState);
            $this->executeHooks($componentName, 'created', $initData);

            Log::info("Component initialized: {$componentName}");
        } catch (Exception $e) {
            $this->handleComponentError($componentName, 'initialization', $e);

            throw $e;
        }
    }

    /**
     * Mount component to DOM/render system
     */
    /**
     * Mount
     */
    public function mount(string $componentName, array $mountData = []): void
    {
        $componentState = $this->getComponentState($componentName);

        if (! $componentState || $componentState['state'] !== 'created') {
            throw new InvalidArgumentException("Component must be created before mounting: {$componentName}");
        }

        $startTime = microtime(TRUE);

        try {
            // Execute before mount hooks
            $this->executeHooks($componentName, 'beforeMount', $mountData);

            // Update state
            $componentState['state'] = 'mounting';
            $this->updateComponentState($componentName, $componentState);

            // Mount based on component type
            switch ($componentState['type']) {
                case 'blade':
                    $this->mountBladeComponent($componentName, $componentState, $mountData);

                    break;
                case 'alpine':
                    $this->mountAlpineComponent($componentName, $componentState, $mountData);

                    break;
                case 'vue':
                    $this->mountVueComponent($componentName, $componentState, $mountData);

                    break;
            }

            // Record performance metrics
            $componentState['performance_metrics']['mount_time'] = (microtime(TRUE) - $startTime) * 1000;
            $componentState['state'] = 'mounted';
            $componentState['mounted_at'] = now();

            $this->updateComponentState($componentName, $componentState);
            $this->activeComponents->put($componentName, $componentState);

            $this->executeHooks($componentName, 'mounted', $mountData);

            Log::info("Component mounted: {$componentName}");
        } catch (Exception $e) {
            $this->handleComponentError($componentName, 'mounting', $e);

            throw $e;
        }
    }

    /**
     * Update component with new data
     */
    /**
     * Update
     */
    public function update(string $componentName, array $updateData = []): void
    {
        $componentState = $this->getComponentState($componentName);

        if (! $componentState || $componentState['state'] !== 'mounted') {
            throw new InvalidArgumentException("Component must be mounted before updating: {$componentName}");
        }

        try {
            // Execute before update hooks
            $this->executeHooks($componentName, 'beforeUpdate', $updateData);

            // Update state
            $componentState['state'] = 'updating';
            $componentState['performance_metrics']['update_count']++;
            $this->updateComponentState($componentName, $componentState);

            // Update based on component type
            switch ($componentState['type']) {
                case 'blade':
                    $this->updateBladeComponent($componentName, $componentState, $updateData);

                    break;
                case 'alpine':
                    $this->updateAlpineComponent($componentName, $componentState, $updateData);

                    break;
                case 'vue':
                    $this->updateVueComponent($componentName, $componentState, $updateData);

                    break;
            }

            $componentState['state'] = 'mounted';
            $componentState['updated_at'] = now();

            $this->updateComponentState($componentName, $componentState);
            $this->executeHooks($componentName, 'updated', $updateData);

            Log::debug("Component updated: {$componentName}");
        } catch (Exception $e) {
            $this->handleComponentError($componentName, 'updating', $e);

            throw $e;
        }
    }

    /**
     * Unmount component and cleanup resources
     */
    /**
     * Unmount
     */
    public function unmount(string $componentName, array $unmountData = []): void
    {
        $componentState = $this->getComponentState($componentName);

        if (! $componentState) {
            return; // Already unmounted or never mounted
        }

        try {
            // Execute before unmount hooks
            $this->executeHooks($componentName, 'beforeUnmount', $unmountData);

            // Update state
            $componentState['state'] = 'unmounting';
            $this->updateComponentState($componentName, $componentState);

            // Unmount based on component type
            switch ($componentState['type']) {
                case 'blade':
                    $this->unmountBladeComponent($componentName, $componentState, $unmountData);

                    break;
                case 'alpine':
                    $this->unmountAlpineComponent($componentName, $componentState, $unmountData);

                    break;
                case 'vue':
                    $this->unmountVueComponent($componentName, $componentState, $unmountData);

                    break;
            }

            // Execute cleanup callbacks
            foreach ($componentState['cleanup_callbacks'] as $callback) {
                try {
                    $callback($componentName, $componentState);
                } catch (Exception $e) {
                    Log::warning("Cleanup callback failed for {$componentName}: " . $e->getMessage());
                }
            }

            // Clear timers
            foreach ($componentState['timers'] as $timerId) {
                $this->clearTimer($timerId);
            }

            // Clear watchers
            foreach ($componentState['watchers'] as $watcherId) {
                $this->clearWatcher($watcherId);
            }

            $componentState['state'] = 'unmounted';
            $componentState['unmounted_at'] = now();

            $this->updateComponentState($componentName, $componentState);
            $this->activeComponents->forget($componentName);

            $this->executeHooks($componentName, 'unmounted', $unmountData);

            Log::info("Component unmounted: {$componentName}");
        } catch (Exception $e) {
            $this->handleComponentError($componentName, 'unmounting', $e);

            throw $e;
        }
    }

    /**
     * Add lifecycle hook for a component
     */
    /**
     * AddHook
     */
    public function addHook(string $componentName, string $lifecycle, callable $callback): void
    {
        if (! in_array($lifecycle, $this->lifecycleEvents, TRUE)) {
            throw new InvalidArgumentException("Invalid lifecycle event: {$lifecycle}");
        }

        $hookKey = "{$componentName}.{$lifecycle}";

        if (! $this->lifecycleHooks->has($hookKey)) {
            $this->lifecycleHooks->put($hookKey, new Collection());
        }

        $this->lifecycleHooks->get($hookKey)->push($callback);
    }

    /**
     * Add cleanup callback for a component
     */
    /**
     * AddCleanupCallback
     */
    public function addCleanupCallback(string $componentName, callable $callback): void
    {
        $componentState = $this->getComponentState($componentName);

        if ($componentState) {
            $componentState['cleanup_callbacks'][] = $callback;
            $this->updateComponentState($componentName, $componentState);
        }
    }

    /**
     * Add watcher for component state changes
     */
    /**
     * AddWatcher
     */
    public function addWatcher(string $componentName, string $property, callable $callback): string
    {
        $watcherId = uniqid('watcher_');
        $componentState = $this->getComponentState($componentName);

        if ($componentState) {
            $componentState['watchers'][$watcherId] = [
                'property' => $property,
                'callback' => $callback,
            ];
            $this->updateComponentState($componentName, $componentState);
        }

        return $watcherId;
    }

    /**
     * Add timer for component
     */
    /**
     * AddTimer
     */
    public function addTimer(string $componentName, callable $callback, int $interval): string
    {
        $timerId = uniqid('timer_');
        $componentState = $this->getComponentState($componentName);

        if ($componentState) {
            $componentState['timers'][$timerId] = [
                'callback'   => $callback,
                'interval'   => $interval,
                'created_at' => now(),
            ];
            $this->updateComponentState($componentName, $componentState);
        }

        return $timerId;
    }

    /**
     * Handle lifecycle events
     *
     * @param mixed $event
     * @param mixed $payload
     */
    /**
     * HandleLifecycleEvent
     *
     * @param mixed $event
     * @param mixed $payload
     */
    public function handleLifecycleEvent($event, $payload): void
    {
        // This method is called by Laravel's event system
        Log::debug("Lifecycle event: {$event}", $payload);
    }

    /**
     * Get component performance metrics
     */
    /**
     * Get  performance metrics
     */
    public function getPerformanceMetrics(string $componentName): ?array
    {
        $componentState = $this->getComponentState($componentName);

        return $componentState['performance_metrics'] ?? NULL;
    }

    /**
     * Get all active components
     */
    /**
     * Get  active components
     */
    public function getActiveComponents(): Collection
    {
        return $this->activeComponents;
    }

    /**
     * Get component state summary
     */
    /**
     * Get  component summary
     */
    public function getComponentSummary(string $componentName): ?array
    {
        $componentState = $this->getComponentState($componentName);

        if (! $componentState) {
            return NULL;
        }

        return [
            'name'        => $componentState['name'],
            'type'        => $componentState['type'],
            'state'       => $componentState['state'],
            'created_at'  => $componentState['created_at'],
            'mounted_at'  => $componentState['mounted_at'],
            'updated_at'  => $componentState['updated_at'],
            'error_count' => $componentState['error_count'],
            'performance' => $componentState['performance_metrics'],
        ];
    }

    /**
     * Get lifecycle statistics
     */
    /**
     * Get  lifecycle stats
     */
    public function getLifecycleStats(): array
    {
        $stats = [
            'total_components'      => $this->componentStates->count(),
            'active_components'     => $this->activeComponents->count(),
            'components_by_state'   => [],
            'components_by_type'    => [],
            'total_errors'          => 0,
            'average_creation_time' => 0,
            'average_mount_time'    => 0,
        ];

        $creationTimes = [];
        $mountTimes = [];

        foreach ($this->componentStates as $state) {
            // Count by state
            $currentState = $state['state'];
            $stats['components_by_state'][$currentState] = ($stats['components_by_state'][$currentState] ?? 0) + 1;

            // Count by type
            $type = $state['type'];
            $stats['components_by_type'][$type] = ($stats['components_by_type'][$type] ?? 0) + 1;

            // Error count
            $stats['total_errors'] += $state['error_count'];

            // Performance metrics
            if ($state['performance_metrics']['creation_time'] > 0) {
                $creationTimes[] = $state['performance_metrics']['creation_time'];
            }
            if ($state['performance_metrics']['mount_time'] > 0) {
                $mountTimes[] = $state['performance_metrics']['mount_time'];
            }
        }

        if (! empty($creationTimes)) {
            $stats['average_creation_time'] = array_sum($creationTimes) / count($creationTimes);
        }

        if (! empty($mountTimes)) {
            $stats['average_mount_time'] = array_sum($mountTimes) / count($mountTimes);
        }

        return $stats;
    }

    /**
     * Cleanup all components
     */
    /**
     * CleanupAll
     */
    public function cleanupAll(): void
    {
        foreach ($this->activeComponents->keys() as $componentName) {
            try {
                $this->unmount($componentName);
            } catch (Exception $e) {
                Log::error("Failed to cleanup component {$componentName}: " . $e->getMessage());
            }
        }

        $this->componentStates->forget($this->componentStates->keys());
        $this->lifecycleHooks->forget($this->lifecycleHooks->keys());
        $this->activeComponents->forget($this->activeComponents->keys());

        Log::info('All components cleaned up');
    }

    /**
     * Setup lifecycle event system
     */
    /**
     * Set up lifecycle events
     */
    private function setupLifecycleEvents(): void
    {
        foreach ($this->lifecycleEvents as $eventName) {
            Event::listen("component.{$eventName}", [$this, 'handleLifecycleEvent']);
        }
    }

    /**
     * Initialize Blade component
     */
    /**
     * InitializeBladeComponent
     */
    private function initializeBladeComponent(string $componentName, array $componentState, array $initData): void
    {
        // Blade components are server-side rendered, minimal initialization
        $this->emitLifecycleEvent('blade.initialized', $componentName, $initData);
    }

    /**
     * Initialize Alpine.js component
     */
    /**
     * InitializeAlpineComponent
     */
    private function initializeAlpineComponent(string $componentName, array $componentState, array $initData): void
    {
        // Alpine.js components need client-side initialization script
        $initScript = $this->generateAlpineInitScript($componentName, $componentState, $initData);
        $this->emitLifecycleEvent('alpine.initialized', $componentName, ['script' => $initScript]);
    }

    /**
     * Initialize Vue.js component
     */
    /**
     * InitializeVueComponent
     */
    private function initializeVueComponent(string $componentName, array $componentState, array $initData): void
    {
        // Vue.js components need registration with Vue instance
        $vueConfig = $this->generateVueConfig($componentName, $componentState, $initData);
        $this->emitLifecycleEvent('vue.initialized', $componentName, ['config' => $vueConfig]);
    }

    /**
     * Mount Blade component
     */
    /**
     * MountBladeComponent
     */
    private function mountBladeComponent(string $componentName, array $componentState, array $mountData): void
    {
        // Blade components are rendered server-side, emit event for client-side hooks
        $this->emitLifecycleEvent('blade.mounted', $componentName, $mountData);
    }

    /**
     * Mount Alpine.js component
     */
    /**
     * MountAlpineComponent
     */
    private function mountAlpineComponent(string $componentName, array $componentState, array $mountData): void
    {
        // Generate Alpine.js mount instructions
        $mountScript = $this->generateAlpineMountScript($componentName, $componentState, $mountData);
        $this->emitLifecycleEvent('alpine.mounted', $componentName, ['script' => $mountScript]);
    }

    /**
     * Mount Vue.js component
     */
    /**
     * MountVueComponent
     */
    private function mountVueComponent(string $componentName, array $componentState, array $mountData): void
    {
        // Generate Vue.js mount instructions
        $mountConfig = $this->generateVueMountConfig($componentName, $componentState, $mountData);
        $this->emitLifecycleEvent('vue.mounted', $componentName, ['config' => $mountConfig]);
    }

    /**
     * Update Blade component
     */
    /**
     * UpdateBladeComponent
     */
    private function updateBladeComponent(string $componentName, array $componentState, array $updateData): void
    {
        // Blade components need re-rendering on server side for updates
        $this->emitLifecycleEvent('blade.updated', $componentName, $updateData);
    }

    /**
     * Update Alpine.js component
     */
    /**
     * UpdateAlpineComponent
     */
    private function updateAlpineComponent(string $componentName, array $componentState, array $updateData): void
    {
        // Generate Alpine.js update script
        $updateScript = $this->generateAlpineUpdateScript($componentName, $componentState, $updateData);
        $this->emitLifecycleEvent('alpine.updated', $componentName, ['script' => $updateScript]);
    }

    /**
     * Update Vue.js component
     */
    /**
     * UpdateVueComponent
     */
    private function updateVueComponent(string $componentName, array $componentState, array $updateData): void
    {
        // Generate Vue.js update instructions
        $updateConfig = $this->generateVueUpdateConfig($componentName, $componentState, $updateData);
        $this->emitLifecycleEvent('vue.updated', $componentName, ['config' => $updateConfig]);
    }

    /**
     * Unmount Blade component
     */
    /**
     * UnmountBladeComponent
     */
    private function unmountBladeComponent(string $componentName, array $componentState, array $unmountData): void
    {
        $this->emitLifecycleEvent('blade.unmounted', $componentName, $unmountData);
    }

    /**
     * Unmount Alpine.js component
     */
    /**
     * UnmountAlpineComponent
     */
    private function unmountAlpineComponent(string $componentName, array $componentState, array $unmountData): void
    {
        $unmountScript = $this->generateAlpineUnmountScript($componentName, $componentState, $unmountData);
        $this->emitLifecycleEvent('alpine.unmounted', $componentName, ['script' => $unmountScript]);
    }

    /**
     * Unmount Vue.js component
     */
    /**
     * UnmountVueComponent
     */
    private function unmountVueComponent(string $componentName, array $componentState, array $unmountData): void
    {
        $unmountConfig = $this->generateVueUnmountConfig($componentName, $componentState, $unmountData);
        $this->emitLifecycleEvent('vue.unmounted', $componentName, ['config' => $unmountConfig]);
    }

    /**
     * Generate Alpine.js initialization script
     */
    /**
     * GenerateAlpineInitScript
     */
    private function generateAlpineInitScript(string $componentName, array $componentState, array $initData): string
    {
        $initDataJson = json_encode($initData);

        return "Alpine.data('{$componentName}', () => ({$initDataJson}));";
    }

    /**
     * Generate Alpine.js mount script
     */
    /**
     * GenerateAlpineMountScript
     */
    private function generateAlpineMountScript(string $componentName, array $componentState, array $mountData): string
    {
        $selector = $componentState['config']['selector'] ?? "[x-data='{$componentName}']";

        return "document.querySelectorAll('{$selector}').forEach(el => Alpine.initTree(el));";
    }

    /**
     * Generate Alpine.js update script
     */
    /**
     * GenerateAlpineUpdateScript
     */
    private function generateAlpineUpdateScript(string $componentName, array $componentState, array $updateData): string
    {
        $updateDataJson = json_encode($updateData);
        $selector = $componentState['config']['selector'] ?? "[x-data='{$componentName}']";

        return "document.querySelectorAll('{$selector}').forEach(el => Object.assign(el.__x.\$data, {$updateDataJson}));";
    }

    /**
     * Generate Alpine.js unmount script
     */
    /**
     * GenerateAlpineUnmountScript
     */
    private function generateAlpineUnmountScript(string $componentName, array $componentState, array $unmountData): string
    {
        $selector = $componentState['config']['selector'] ?? "[x-data='{$componentName}']";

        return "document.querySelectorAll('{$selector}').forEach(el => el.remove());";
    }

    /**
     * Generate Vue.js configuration
     */
    /**
     * GenerateVueConfig
     */
    private function generateVueConfig(string $componentName, array $componentState, array $initData): array
    {
        return [
            'name'   => $componentName,
            'data'   => $initData,
            'config' => $componentState['config'],
        ];
    }

    /**
     * Generate Vue.js mount configuration
     */
    /**
     * GenerateVueMountConfig
     */
    private function generateVueMountConfig(string $componentName, array $componentState, array $mountData): array
    {
        return [
            'component' => $componentName,
            'target'    => $componentState['config']['target'] ?? "#{$componentName}",
            'props'     => $mountData,
        ];
    }

    /**
     * Generate Vue.js update configuration
     */
    /**
     * GenerateVueUpdateConfig
     */
    private function generateVueUpdateConfig(string $componentName, array $componentState, array $updateData): array
    {
        return [
            'component' => $componentName,
            'updates'   => $updateData,
        ];
    }

    /**
     * Generate Vue.js unmount configuration
     */
    /**
     * GenerateVueUnmountConfig
     */
    private function generateVueUnmountConfig(string $componentName, array $componentState, array $unmountData): array
    {
        return [
            'component' => $componentName,
            'target'    => $componentState['config']['target'] ?? "#{$componentName}",
        ];
    }

    /**
     * Execute hooks for a specific lifecycle event
     */
    /**
     * ExecuteHooks
     */
    private function executeHooks(string $componentName, string $lifecycle, array $data = []): void
    {
        $hookKey = "{$componentName}.{$lifecycle}";
        $hooks = $this->lifecycleHooks->get($hookKey);

        if ($hooks) {
            foreach ($hooks as $hook) {
                try {
                    $hook($componentName, $data);
                } catch (Exception $e) {
                    Log::error("Hook execution failed for {$componentName}:{$lifecycle} - " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Handle component errors
     */
    /**
     * HandleComponentError
     */
    private function handleComponentError(string $componentName, string $phase, Exception $error): void
    {
        $componentState = $this->getComponentState($componentName);

        if ($componentState) {
            $componentState['error_count']++;
            $componentState['last_error'] = [
                'phase'     => $phase,
                'message'   => $error->getMessage(),
                'timestamp' => now(),
            ];
            $componentState['state'] = 'error';

            $this->updateComponentState($componentName, $componentState);
        }

        $this->executeHooks($componentName, 'error', [
            'phase' => $phase,
            'error' => $error->getMessage(),
        ]);

        Log::error("Component error in {$componentName} during {$phase}: " . $error->getMessage());
    }

    /**
     * Emit lifecycle event
     */
    /**
     * EmitLifecycleEvent
     */
    private function emitLifecycleEvent(string $eventName, string $componentName, array $data = []): void
    {
        Event::dispatch("component.{$eventName}", [
            'component' => $componentName,
            'data'      => $data,
            'timestamp' => now(),
        ]);
    }

    /**
     * Get component state
     */
    /**
     * Get  component state
     */
    private function getComponentState(string $componentName): ?array
    {
        return $this->componentStates->get($componentName);
    }

    /**
     * Update component state
     */
    /**
     * UpdateComponentState
     */
    private function updateComponentState(string $componentName, array $state): void
    {
        $this->componentStates->put($componentName, $state);
    }

    /**
     * Clear timer
     */
    /**
     * ClearTimer
     */
    private function clearTimer(string $timerId): void
    {
        // Implementation depends on how timers are handled in client-side
        Log::debug("Clearing timer: {$timerId}");
    }

    /**
     * Clear watcher
     */
    /**
     * ClearWatcher
     */
    private function clearWatcher(string $watcherId): void
    {
        // Implementation depends on how watchers are handled in client-side
        Log::debug("Clearing watcher: {$watcherId}");
    }
}
