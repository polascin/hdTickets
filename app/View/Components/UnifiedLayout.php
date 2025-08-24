<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UnifiedLayout extends Component
{
    public string $title;
    public string $subtitle;
    public bool $showSidebar;
    public bool $sidebarCollapsed;
    public array $breadcrumbs;
    public array $meta;
    public $headerActions;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $title = 'HD Tickets',
        string $subtitle = '',
        bool $showSidebar = true,
        bool $sidebarCollapsed = false,
        array $breadcrumbs = [],
        array $meta = [],
        $headerActions = null
    ) {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->showSidebar = $showSidebar;
        $this->sidebarCollapsed = $sidebarCollapsed;
        $this->breadcrumbs = $breadcrumbs;
        $this->meta = $meta;
        $this->headerActions = $headerActions;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.unified-layout');
    }
}
