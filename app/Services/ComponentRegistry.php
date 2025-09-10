<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

use function in_array;

/**
 * Component Registry Service
 *
 * Manages registration, discovery, and lifecycle of Blade, Alpine.js, and Vue.js components
 * for the HD Tickets sports events platform.
 */
class ComponentRegistry
{
    private Collection $components;

    private Collection $loadedComponents;

    private array $componentTypes = [
        'blade' => [
            'extension' => '.blade.php',
            'basePath'  => 'resources/views/components',
            'namespace' => 'components',
        ],
        'alpine' => [
            'extension' => '.js',
            'basePath'  => 'resources/js/alpine/components',
            'namespace' => 'alpine',
        ],
        'vue' => [
            'extension' => '.vue',
            'basePath'  => 'resources/js/components',
            'namespace' => 'vue',
        ],
    ];

    public function __construct()
    {
        $this->components = new Collection();
        $this->loadedComponents = new Collection();
        $this->initializeRegistry();
    }

    /**
     * Register a component
     */
    /**
     * Register
     */
    public function register(string $name, string $type, array $config = []): void
    {
        if (! in_array($type, array_keys($this->componentTypes), TRUE)) {
            throw new InvalidArgumentException("Invalid component type: {$type}");
        }

        $component = [
            'name'         => $name,
            'type'         => $type,
            'config'       => $config,
            'path'         => $this->resolveComponentPath($name, $type),
            'dependencies' => $config['dependencies'] ?? [],
            'props'        => $config['props'] ?? [],
            'events'       => $config['events'] ?? [],
            'lazy'         => $config['lazy'] ?? FALSE,
            'priority'     => $config['priority'] ?? 0,
            'category'     => $config['category'] ?? 'general',
            'description'  => $config['description'] ?? '',
            'version'      => $config['version'] ?? '1.0.0',
            'author'       => $config['author'] ?? 'HD Tickets Team',
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        $this->components->put($name, $component);

        Log::info("Component registered: {$name} ({$type})");
    }

    /**
     * Get a component by name
     */
    /**
     * Get
     */
    public function get(string $name): ?array
    {
        return $this->components->get($name);
    }

    /**
     * Get all components of a specific type
     */
    /**
     * Get  by type
     */
    public function getByType(string $type): Collection
    {
        return $this->components->filter(fn ($component): bool => $component['type'] === $type);
    }

    /**
     * Get all components in a category
     */
    /**
     * Get  by category
     */
    public function getByCategory(string $category): Collection
    {
        return $this->components->filter(fn ($component): bool => $component['category'] === $category);
    }

    /**
     * Load a component and its dependencies
     */
    /**
     * Load
     */
    public function load(string $name): array
    {
        $component = $this->get($name);

        if (! $component) {
            throw new InvalidArgumentException("Component not found: {$name}");
        }

        // Check if already loaded
        if ($this->loadedComponents->has($name)) {
            return $this->loadedComponents->get($name);
        }

        // Load dependencies first
        foreach ($component['dependencies'] as $dependency) {
            $this->load($dependency);
        }

        // Load the component
        $loadedComponent = $this->loadComponent($component);
        $this->loadedComponents->put($name, $loadedComponent);

        Log::info("Component loaded: {$name}");

        return $loadedComponent;
    }

    /**
     * Lazy load Vue components
     */
    /**
     * LazyLoad
     */
    public function lazyLoad(string $name): array
    {
        $component = $this->get($name);

        if (! $component || $component['type'] !== 'vue') {
            throw new InvalidArgumentException("Vue component not found or not lazy loadable: {$name}");
        }

        return [
            'name'      => $name,
            'component' => "() => import('{$component['path']}')",
            'loading'   => 'LoadingComponent',
            'error'     => 'ErrorComponent',
            'delay'     => 200,
            'timeout'   => 30000,
        ];
    }

    /**
     * Component lifecycle event handlers
     */
    /**
     * OnComponentCreated
     */
    public function onComponentCreated(callable $callback): void
    {
        // Implementation for creation hooks
    }

    /**
     * OnComponentLoaded
     */
    public function onComponentLoaded(callable $callback): void
    {
        // Implementation for loading hooks
    }

    /**
     * OnComponentError
     */
    public function onComponentError(callable $callback): void
    {
        // Implementation for error hooks
    }

    /**
     * Get component statistics
     */
    /**
     * Get  stats
     */
    public function getStats(): array
    {
        return [
            'total_components'  => $this->components->count(),
            'blade_components'  => $this->getByType('blade')->count(),
            'alpine_components' => $this->getByType('alpine')->count(),
            'vue_components'    => $this->getByType('vue')->count(),
            'loaded_components' => $this->loadedComponents->count(),
            'categories'        => $this->components->groupBy('category')->keys()->toArray(),
        ];
    }

    /**
     * Validate component structure
     */
    /**
     * Validate
     */
    public function validate(string $name): array
    {
        $component = $this->get($name);

        if (! $component) {
            return ['valid' => FALSE, 'errors' => ['Component not found']];
        }

        $errors = [];

        // Check file existence
        if (! File::exists(base_path($component['path']))) {
            $errors[] = 'Component file not found';
        }

        // Check dependencies
        foreach ($component['dependencies'] as $dependency) {
            if (! $this->components->has($dependency)) {
                $errors[] = "Dependency not found: {$dependency}";
            }
        }

        return [
            'valid'  => $errors === [],
            'errors' => $errors,
        ];
    }

    /**
     * Generate component documentation
     */
    /**
     * GenerateDocs
     */
    public function generateDocs(): array
    {
        $docs = [];

        foreach ($this->components as $name => $component) {
            $docs[$name] = [
                'name'          => $name,
                'type'          => $component['type'],
                'category'      => $component['category'],
                'description'   => $component['description'],
                'props'         => $component['props'],
                'events'        => $component['events'],
                'dependencies'  => $component['dependencies'],
                'example_usage' => $this->generateUsageExample($component),
            ];
        }

        return $docs;
    }

    /**
     * Clear component cache
     */
    /**
     * ClearCache
     */
    public function clearCache(): void
    {
        $this->loadedComponents->forget($this->loadedComponents->keys());
        Log::info('Component cache cleared');
    }

    /**
     * Get all registered components
     */
    /**
     * All
     */
    public function all(): Collection
    {
        return $this->components;
    }

    /**
     * Initialize the component registry
     */
    /**
     * InitializeRegistry
     */
    private function initializeRegistry(): void
    {
        $this->discoverComponents();
        $this->establishComponentBoundaries();
        $this->setupLifecycleHooks();
    }

    /**
     * Discover components from file system
     */
    /**
     * DiscoverComponents
     */
    private function discoverComponents(): void
    {
        foreach ($this->componentTypes as $type => $config) {
            $basePath = base_path($config['basePath']);

            if (! File::exists($basePath)) {
                continue;
            }

            $files = File::allFiles($basePath);

            foreach ($files as $file) {
                if ($file->getExtension() === ltrim((string) $config['extension'], '.')) {
                    $name = $this->extractComponentName($file->getPathname(), $basePath, $config['extension']);

                    if (! $this->components->has($name)) {
                        $this->autoRegisterComponent($name, $type, $file->getPathname());
                    }
                }
            }
        }
    }

    /**
     * Auto-register discovered components
     */
    /**
     * AutoRegisterComponent
     */
    private function autoRegisterComponent(string $name, string $type, string $path): void
    {
        $config = $this->parseComponentConfig($path);
        $config['auto_discovered'] = TRUE;

        $this->register($name, $type, $config);
    }

    /**
     * Parse component configuration from file
     */
    /**
     * ParseComponentConfig
     */
    private function parseComponentConfig(string $path): array
    {
        $content = File::get($path);
        $config = [];

        // Parse component metadata from comments
        if (preg_match('/\/\*\*\s*(.*?)\*\//s', $content, $matches)) {
            $docBlock = $matches[1];

            // Extract @props
            if (preg_match_all('/@prop\s+(\w+)\s*:\s*(.+)$/m', $docBlock, $propMatches, PREG_SET_ORDER)) {
                foreach ($propMatches as $match) {
                    $config['props'][$match[1]] = trim($match[2]);
                }
            }

            // Extract @events
            if (preg_match_all('/@event\s+(\w+)\s*:\s*(.+)$/m', $docBlock, $eventMatches, PREG_SET_ORDER)) {
                foreach ($eventMatches as $match) {
                    $config['events'][$match[1]] = trim($match[2]);
                }
            }

            // Extract @category
            if (preg_match('/@category\s+(.+)$/m', $docBlock, $match)) {
                $config['category'] = trim($match[1]);
            }

            // Extract @lazy
            if (preg_match('/@lazy\s+(true|false)$/m', $docBlock, $match)) {
                $config['lazy'] = $match[1] === 'true';
            }
        }

        return $config;
    }

    /**
     * Extract component name from file path
     */
    /**
     * ExtractComponentName
     */
    private function extractComponentName(string $filePath, string $basePath, string $extension): string
    {
        $relativePath = str_replace($basePath . '/', '', $filePath);
        $name = str_replace($extension, '', $relativePath);

        // Convert slashes to dots for namespacing
        return str_replace('/', '.', $name);
    }

    /**
     * Resolve component path
     */
    /**
     * ResolveComponentPath
     */
    private function resolveComponentPath(string $name, string $type): string
    {
        $config = $this->componentTypes[$type];
        $path = str_replace('.', '/', $name);

        return $config['basePath'] . '/' . $path . $config['extension'];
    }

    /**
     * Load component content
     */
    /**
     * LoadComponent
     */
    private function loadComponent(array $component): array
    {
        $path = base_path($component['path']);

        if (! File::exists($path)) {
            throw new InvalidArgumentException("Component file not found: {$component['path']}");
        }

        $component['content'] = File::get($path);
        $component['loaded_at'] = now();

        return $component;
    }

    /**
     * Establish clear boundaries between component types
     */
    /**
     * EstablishComponentBoundaries
     */
    private function establishComponentBoundaries(): void
    {
        // Blade components: Server-side rendering, basic interactivity
        $this->setBoundary('blade', [
            'responsibilities' => [
                'Server-side HTML generation',
                'Basic form handling',
                'Static content display',
                'SEO-optimized pages',
                'Email templates',
            ],
            'restrictions' => [
                'No complex client-side state management',
                'Limited JavaScript interactions',
                'No real-time updates',
            ],
        ]);

        // Alpine.js components: Lightweight client-side interactions
        $this->setBoundary('alpine', [
            'responsibilities' => [
                'Form validation and interactions',
                'Modal and dropdown management',
                'Simple state management',
                'DOM manipulation',
                'Event handling',
            ],
            'restrictions' => [
                'No complex routing',
                'Limited component composition',
                'No virtual DOM benefits',
            ],
        ]);

        // Vue.js components: Complex interactive features
        $this->setBoundary('vue', [
            'responsibilities' => [
                'Complex dashboards',
                'Real-time data visualization',
                'Advanced form handling',
                'Route-based navigation',
                'Complex state management',
            ],
            'restrictions' => [
                'Should be lazy-loaded when possible',
                'Avoid for simple static content',
                'Consider bundle size impact',
            ],
        ]);
    }

    /**
     * Set boundary rules for component type
     */
    /**
     * Set  boundary
     */
    private function setBoundary(string $type, array $rules): void
    {
        $this->componentTypes[$type]['boundaries'] = $rules;
    }

    /**
     * Setup component lifecycle hooks
     */
    /**
     * Set up lifecycle hooks
     */
    private function setupLifecycleHooks(): void
    {
        // Component creation hooks
        $this->onComponentCreated(function (array $component): void {
            Log::info("Component created: {$component['name']}");
        });

        // Component loading hooks
        $this->onComponentLoaded(function (array $component): void {
            Log::info("Component loaded: {$component['name']}");
        });

        // Component error hooks
        $this->onComponentError(function (array $component, $error): void {
            Log::error("Component error in {$component['name']}: {$error->getMessage()}");
        });
    }

    /**
     * Generate usage example for component
     */
    /**
     * GenerateUsageExample
     */
    private function generateUsageExample(array $component): string
    {
        return match ($component['type']) {
            'blade'  => "<x-{$component['name']} />",
            'alpine' => "x-data=\"{$component['name']}()\"",
            'vue'    => "<{$component['name']}></{$component['name']}>",
            default  => '',
        };
    }
}
