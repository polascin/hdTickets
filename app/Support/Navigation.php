<?php declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Navigation
{
    protected array $config;

    protected ?object $user;

    public function __construct()
    {
        $this->config = Config::get('ui.navigation', []);
        $this->user = Auth::user();
    }

    /**
     * Get navigation items for the current user's role
     */
    public function getMenuForRole(string $role = NULL): Collection
    {
        $role = $role ?? $this->getCurrentUserRole();

        if (!isset($this->config['menus'][$role])) {
            return collect();
        }

        $menuItems = $this->config['menus'][$role]['items'] ?? [];

        return collect($menuItems)
            ->filter(fn ($item) => $this->canSeeItem($item))
            ->map(fn ($item) => $this->processMenuItem($item))
            ->values();
    }

    /**
     * Get user menu items
     */
    public function getUserMenu(): Collection
    {
        $userMenuItems = $this->config['user_menu'] ?? [];

        return collect($userMenuItems)
            ->filter(fn ($item) => $this->canSeeItem($item))
            ->map(fn ($item) => $this->processUserMenuItem($item))
            ->values();
    }

    /**
     * Get quick actions for the current user
     */
    public function getQuickActions($user = NULL): Collection
    {
        $quickActions = $this->config['quick_actions'] ?? [];

        return collect($quickActions)
            ->filter(fn ($action) => $this->canSeeItem($action))
            ->map(fn ($action, $key) => array_merge($action, ['id' => $key]))
            ->values();
    }

    /**
     * Get user menu items for the header dropdown
     */
    public function getUserMenuItems($user = NULL): Collection
    {
        return $this->getUserMenu();
    }

    /**
     * Get current user role (public method)
     */
    public function getCurrentUserRole(): string
    {
        if (!$this->user) {
            return 'guest';
        }

        // Determine role based on user properties
        if (method_exists($this->user, 'hasRole')) {
            if ($this->user->hasRole('admin')) {
                return 'admin';
            }
            if ($this->user->hasRole('agent')) {
                return 'agent';
            }

            return 'customer';
        }

        // Fallback to property-based role detection
        if ($this->user->is_admin ?? FALSE) {
            return 'admin';
        }

        if ($this->user->is_agent ?? FALSE) {
            return 'agent';
        }

        return 'customer';
    }

    /**
     * Check if current user can see a navigation item
     */
    public function canSeeItem(array $item): bool
    {
        if (!isset($item['permissions'])) {
            return TRUE;
        }

        $userRole = $this->getCurrentUserRole();

        return in_array($userRole, $item['permissions']);
    }

    /**
     * Check if a navigation item is currently active
     */
    public function isActive(array $item): bool
    {
        if (!isset($item['active_patterns'])) {
            // Fall back to checking current route name
            if (isset($item['route']) && Route::currentRouteName() === $item['route']) {
                return TRUE;
            }

            return FALSE;
        }

        $currentPath = request()->path();

        foreach ($item['active_patterns'] as $pattern) {
            if (Str::is($pattern, $currentPath)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get badge data for a navigation item
     */
    public function getBadge(string $badgeKey): ?array
    {
        if (!$badgeKey || !isset($this->config['badges'][$badgeKey])) {
            return NULL;
        }

        $badgeConfig = $this->config['badges'][$badgeKey];

        // Ensure badge config has required fields
        if (!isset($badgeConfig['source']) || !isset($badgeConfig['type'])) {
            return NULL;
        }

        $value = $this->getBadgeValue($badgeConfig['source']);

        if ($value === NULL || $value === 0) {
            return NULL;
        }

        $variant = $this->getBadgeVariant($badgeConfig, $value);

        // Ensure we always have a valid variant
        $validVariants = ['default', 'primary', 'secondary', 'success', 'warning', 'error', 'info'];
        if (!in_array($variant, $validVariants)) {
            $variant = 'default';
        }

        return [
            'type'      => $badgeConfig['type'],
            'value'     => $this->formatBadgeValue($value, $badgeConfig),
            'variant'   => $variant,
            'raw_value' => $value,
        ];
    }

    /**
     * Get icon SVG for a given icon key
     */
    public function getIcon(string $iconKey): ?string
    {
        return $this->config['icons'][$iconKey] ?? NULL;
    }

    /**
     * Get responsive navigation configuration
     */
    public function getResponsiveConfig(): array
    {
        return $this->config['responsive'] ?? [];
    }

    /**
     * Get navigation defaults
     */
    public function getDefaults(): array
    {
        return $this->config['defaults'] ?? [];
    }

    /**
     * Check if sidebar should be collapsed by default
     */
    public function shouldCollapseByDefault(): bool
    {
        $responsive = $this->getResponsiveConfig();

        return $responsive['desktop']['default_collapsed'] ?? FALSE;
    }

    /**
     * Process a menu item and add computed properties
     */
    protected function processMenuItem(array $item): array
    {
        $processedItem = $item;

        // Add active state
        $processedItem['is_active'] = $this->isActive($item);

        // Add badge data
        if (isset($item['badge'])) {
            $processedItem['badge_data'] = $this->getBadge($item['badge']);
        }

        // Add icon SVG
        if (isset($item['icon'])) {
            $processedItem['icon_svg'] = $this->getIcon($item['icon']);
        }

        // Add URL
        if (isset($item['route'])) {
            try {
                $processedItem['url'] = route($item['route']);
            } catch (\Exception $e) {
                $processedItem['url'] = '#';
            }
        }

        // Process children recursively
        if (isset($item['children']) && is_array($item['children'])) {
            $processedItem['children'] = collect($item['children'])
                ->filter(fn ($child) => $this->canSeeItem($child))
                ->map(fn ($child) => $this->processMenuItem($child))
                ->values()
                ->toArray();

            // Check if any children are active
            $processedItem['has_active_child'] = collect($processedItem['children'])
                ->some(fn ($child) => $child['is_active'] ?? FALSE);
        }

        return $processedItem;
    }

    /**
     * Get badge value from source
     */
    protected function getBadgeValue(string $source): mixed
    {
        if (!$this->user) {
            return NULL;
        }

        // Handle special system sources
        if (str_starts_with($source, 'system.')) {
            // For now, return null for system sources until they're implemented
            return NULL;
        }

        // Handle dot notation for nested properties
        $parts = explode('.', $source);
        $value = $this->user;

        foreach ($parts as $part) {
            if (is_object($value)) {
                if (isset($value->$part)) {
                    $value = $value->$part;
                } elseif (method_exists($value, $part)) {
                    $value = $value->$part();
                } else {
                    return NULL;
                }
            } elseif (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return NULL;
            }
        }

        return $value;
    }

    /**
     * Format badge value according to configuration
     */
    protected function formatBadgeValue(mixed $value, array $config): string
    {
        if ($config['type'] === 'count') {
            $maxDisplay = $config['max_display'] ?? 99;

            return $value > $maxDisplay ? "{$maxDisplay}+" : (string) $value;
        }

        if ($config['type'] === 'status') {
            return ucfirst($value);
        }

        return (string) $value;
    }

    /**
     * Get badge variant based on configuration and value
     */
    protected function getBadgeVariant(array $config, mixed $value): string
    {
        if (($config['type'] ?? '') === 'status' && isset($config['variants']) && is_array($config['variants']) && isset($config['variants'][$value])) {
            $statusVariant = $config['variants'][$value];
            // Ensure the status variant is valid
            if (is_string($statusVariant)) {
                return $statusVariant;
            }
        }

        return $config['variant'] ?? 'default';
    }

    /**
     * Process a user menu item with proper handling
     */
    protected function processUserMenuItem(array $item): array
    {
        $processedItem = $item;

        // Add icon SVG
        if (isset($item['icon'])) {
            $processedItem['icon_svg'] = $this->getIcon($item['icon']);
        }

        // Add URL from route
        if (isset($item['route'])) {
            try {
                $processedItem['url'] = route($item['route']);
            } catch (\Exception $e) {
                $processedItem['url'] = '#';
            }
        }

        // Handle logout action
        if ($item['id'] === 'logout') {
            $processedItem['type'] = 'action';
            $processedItem['action'] = 'handleLogout';
        } else {
            $processedItem['type'] = 'link';
        }

        return $processedItem;
    }

    /**
     * Get breadcrumbs for the current route
     */
    public function getBreadcrumbs(): Collection
    {
        $currentRoute = Route::currentRouteName();
        $breadcrumbs = collect();

        // Find the current menu item across all roles
        foreach ($this->config['menus'] as $roleMenu) {
            $found = $this->findMenuItemByRoute($roleMenu['items'], $currentRoute);
            if ($found) {
                $breadcrumbs = $this->buildBreadcrumbTrail($found['path']);

                break;
            }
        }

        return $breadcrumbs;
    }

    /**
     * Find menu item by route name recursively
     */
    protected function findMenuItemByRoute(array $items, string $routeName, array $path = []): ?array
    {
        foreach ($items as $item) {
            $currentPath = array_merge($path, [$item]);

            if (($item['route'] ?? NULL) === $routeName) {
                return ['item' => $item, 'path' => $currentPath];
            }

            if (isset($item['children'])) {
                $found = $this->findMenuItemByRoute($item['children'], $routeName, $currentPath);
                if ($found) {
                    return $found;
                }
            }
        }

        return NULL;
    }

    /**
     * Build breadcrumb trail from path
     */
    protected function buildBreadcrumbTrail(array $path): Collection
    {
        return collect($path)->map(function ($item, $index) use ($path) {
            $processedItem = $this->processMenuItem($item);
            $processedItem['is_last'] = $index === count($path) - 1;

            return $processedItem;
        });
    }

    /**
     * Check if navigation should show icons
     */
    public function shouldShowIcons(): bool
    {
        return $this->config['defaults']['show_icons'] ?? TRUE;
    }

    /**
     * Check if navigation should show badges
     */
    public function shouldShowBadges(): bool
    {
        return $this->config['defaults']['show_badges'] ?? TRUE;
    }

    /**
     * Check if navigation should animate transitions
     */
    public function shouldAnimateTransitions(): bool
    {
        return $this->config['defaults']['animate_transitions'] ?? TRUE;
    }
}
