<?php declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UnifiedLayout extends Component
{
    /**
     * Create a new component instance.
     *
     * @param mixed|null $headerActions
     */
    public function __construct(public string $title = 'HD Tickets', public string $subtitle = '', public bool $showSidebar = TRUE, public bool $sidebarCollapsed = FALSE, public array $breadcrumbs = [], public array $meta = [], public $headerActions = NULL)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.unified-layout');
    }
}
