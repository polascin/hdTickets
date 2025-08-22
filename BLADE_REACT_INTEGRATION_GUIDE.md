# HD Tickets Blade-React Integration Guide

This guide explains how to seamlessly integrate React components within Laravel Blade templates using our unified design system and state management approach.

## 🚀 System Overview

The HD Tickets Blade-React integration provides:

1. **Shared Design Tokens**: CSS variables accessible by both Blade and React
2. **Component Bridge**: Automatic mounting and unmounting of React components
3. **State Synchronization**: Shared state between Alpine.js and React
4. **Error Boundaries**: Graceful error handling for React components
5. **Asset Optimization**: Intelligent loading of React dependencies

## 🎨 Shared Design Tokens

All design tokens are available as CSS custom properties and automatically passed to React components.

### Using Design Tokens

#### In Blade Templates
```blade
<div class="hd-card" style="padding: var(--hd-space-6); border-radius: var(--hd-radius-xl);">
    <h2 style="color: var(--hd-text-primary); font-size: var(--hd-text-xl);">
        Ticket Dashboard
    </h2>
</div>
```

#### In React Components
```jsx
function TicketCard({ designTokens, ...props }) {
    return (
        <div 
            className="hd-card"
            style={{
                padding: designTokens.space6,
                borderRadius: designTokens.radiusXl,
                color: designTokens.textPrimary
            }}
        >
            <h2 style={{ fontSize: designTokens.textXl }}>
                Ticket Dashboard
            </h2>
        </div>
    );
}
```

### Available Design Tokens

| Category | Tokens | Example Usage |
|----------|--------|---------------|
| **Colors** | `primary`, `secondary`, `success`, `warning`, `error`, `info` | `var(--hd-primary)` |
| **Spacing** | `space-1` through `space-32` | `var(--hd-space-4)` (16px) |
| **Typography** | `text-xs` through `text-6xl` | `var(--hd-text-lg)` (18px) |
| **Radius** | `radius-sm` through `radius-3xl` | `var(--hd-radius-lg)` (8px) |
| **Shadows** | `shadow-sm` through `shadow-2xl` | `var(--hd-shadow-lg)` |

## ⚛️ Embedding React Components

### Basic Component Embedding

```blade
{{-- In your Blade template --}}
<div data-react-component="TicketChart"
     data-props='{"ticketData": @json($tickets), "height": 400}'
     data-error-boundary="true">
    {{-- Fallback content while loading --}}
    <div class="loading-placeholder">Loading chart...</div>
</div>
```

### Component with Shared State

```blade
{{-- Define shared state --}}
<div data-shared-state 
     data-state-key="dashboard"
     data-initial-state='{"filters": {"status": "open"}, "selectedTickets": []}'
     style="display: none;">
</div>

{{-- Component using shared state --}}
<div data-react-component="FilterPanel"
     data-state-key="dashboard"
     data-props='{"title": "Ticket Filters"}'>
</div>

<div data-react-component="TicketList"
     data-state-key="dashboard"
     data-props='{"showPagination": true}'>
</div>
```

### Lazy Loading Components

```blade
{{-- Component loads only when visible --}}
<div data-react-component="HeavyChart"
     data-lazy-mount="true"
     data-props='{"data": @json($chartData)}'
     data-skeleton-type="card"
     data-min-height="300px">
</div>
```

## 🔄 State Management

### Alpine.js and React State Sync

#### 1. Define Shared State in Blade
```blade
<div x-data="dashboardData()" 
     data-shared-state 
     data-state-key="dashboard"
     data-initial-state='{"selectedTicket": null, "filters": {}}'>
     
    <script>
    function dashboardData() {
        return {
            selectedTicket: null,
            filters: {},
            
            selectTicket(ticket) {
                this.selectedTicket = ticket;
                // Update shared state for React components
                HDTickets.ReactBladeBridge.updateSharedState('dashboard', {
                    selectedTicket: ticket
                });
            }
        }
    }
    </script>
</div>
```

#### 2. Access State in React Components
```jsx
function TicketDetails({ sharedState, updateSharedState }) {
    const { selectedTicket, filters } = sharedState;

    const handleStatusChange = (newStatus) => {
        updateSharedState({
            selectedTicket: {
                ...selectedTicket,
                status: newStatus
            }
        });
    };

    if (!selectedTicket) {
        return <div>No ticket selected</div>;
    }

    return (
        <div className="hd-card">
            <h3>{selectedTicket.title}</h3>
            <select onChange={(e) => handleStatusChange(e.target.value)}>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    );
}
```

## 🧩 React Component Development

### Component Structure

Create React components in `/public/js/components/react/`:

```jsx
// /public/js/components/react/TicketChart.js
import React, { useState, useEffect } from 'react';

const TicketChart = ({ 
    ticketData = [], 
    height = 300, 
    designTokens, 
    sharedState, 
    updateSharedState 
}) => {
    const [chartData, setChartData] = useState([]);

    useEffect(() => {
        // Process ticket data for chart
        setChartData(processTicketData(ticketData));
    }, [ticketData]);

    // Handle shared state updates
    useEffect(() => {
        if (sharedState?.filters) {
            // Filter chart data based on shared state
            const filteredData = filterChartData(chartData, sharedState.filters);
            setChartData(filteredData);
        }
    }, [sharedState?.filters]);

    const handleChartClick = (dataPoint) => {
        // Update shared state when chart is clicked
        if (updateSharedState) {
            updateSharedState({
                selectedTicket: dataPoint.ticket
            });
        }
    };

    return (
        <div 
            className="hd-chart-container"
            style={{ 
                height: height,
                padding: designTokens?.space4 || 'var(--hd-space-4)'
            }}
        >
            {/* Chart implementation */}
            <canvas 
                onClick={handleChartClick}
                style={{
                    borderRadius: designTokens?.radiusLg || 'var(--hd-radius-lg)'
                }}
            />
        </div>
    );
};

// Static method for state updates (optional)
TicketChart.onStateUpdate = function(newState) {
    // Handle external state updates
    console.log('TicketChart received state update:', newState);
};

export default TicketChart;
```

### Component Registration

Components are automatically registered in the bridge system:

```javascript
// The bridge automatically detects components in these paths:
const componentRegistry = {
    'TicketChart': () => import('/js/components/react/TicketChart.js'),
    'FilterPanel': () => import('/js/components/react/FilterPanel.js'),
    'DataTable': () => import('/js/components/react/DataTable.js'),
    // ... more components
};
```

### Manual Component Registration

```javascript
// Register additional components manually
window.registerReactComponent('CustomChart', CustomChart);

// Or register with dynamic import
window.registerReactComponent('LazyChart', () => import('./LazyChart.js'));
```

## 🛠️ Error Handling

### Component Error Boundaries

Error boundaries are automatically applied to all React components:

```blade
{{-- Disable error boundary for a component --}}
<div data-react-component="SimpleComponent"
     data-error-boundary="false">
</div>

{{-- Custom error handling --}}
<script>
HDTickets.ReactBladeBridge.onComponentError = function(error, errorInfo, componentName) {
    console.error(`Component ${componentName} failed:`, error);
    
    // Report to error tracking
    if (window.Sentry) {
        window.Sentry.captureException(error, {
            tags: { component: componentName },
            extra: errorInfo
        });
    }
    
    // Show user notification
    HDTickets.AccessibilityUtils.announce(
        'A component failed to load. Please refresh the page.',
        'assertive'
    );
};
</script>
```

### Error Fallbacks

The system provides automatic error fallbacks:

1. **Default Fallback**: Shows error message with refresh button
2. **Custom Fallback**: Define your own error content
3. **Graceful Degradation**: Falls back to Blade template content

## 🎯 Best Practices

### Component Props

✅ **Do:**
- Use JSON for complex props: `data-props='@json($data)'`
- Keep props serializable (no functions or complex objects)
- Use shared state for component communication
- Validate props in React components

❌ **Don't:**
- Pass large objects as props (use shared state instead)
- Include functions in props JSON
- Hardcode values that should come from design tokens

### State Management

✅ **Do:**
- Use shared state for data that multiple components need
- Keep state flat and serializable
- Use Alpine.js for simple interactions
- Use React for complex UI state

❌ **Don't:**
- Duplicate state between Alpine and React
- Store non-serializable data in shared state
- Use shared state for component-specific data

### Performance

✅ **Do:**
- Use lazy mounting for heavy components: `data-lazy-mount="true"`
- Implement skeleton loaders: `data-skeleton-type="card"`
- Set minimum heights to prevent layout shift: `data-min-height="300px"`
- Use React.memo for expensive components

❌ **Don't:**
- Mount all components immediately
- Skip loading states
- Ignore layout shift prevention

## 🧪 Testing

### Unit Testing React Components

```javascript
// Test with design tokens and shared state
import { render, screen } from '@testing-library/react';
import TicketChart from './TicketChart';

const mockDesignTokens = {
    space4: '1rem',
    radiusLg: '0.5rem',
    primary: '#3b82f6'
};

const mockSharedState = {
    filters: { status: 'open' },
    selectedTicket: null
};

test('TicketChart renders with design tokens', () => {
    render(
        <TicketChart 
            ticketData={[]}
            designTokens={mockDesignTokens}
            sharedState={mockSharedState}
        />
    );
    
    expect(screen.getByRole('img')).toBeInTheDocument();
});
```

### Integration Testing

```javascript
// Test Blade-React integration
describe('Blade-React Integration', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div data-react-component="TicketChart"
                 data-props='{"height": 400}'
                 data-state-key="test">
            </div>
        `;
    });

    test('mounts React component in Blade template', async () => {
        // Initialize bridge
        HDTickets.ReactBladeBridge.init();
        
        // Wait for component to mount
        await waitFor(() => {
            expect(document.querySelector('.hd-chart-container')).toBeInTheDocument();
        });
    });
});
```

## 🔍 Debugging

### Debug Mode

Enable React-Blade debugging:

```javascript
// Enable logging for all bridge operations
HDTickets.ReactBladeBridge.config.enableLogging = true;

// View mounted components
console.log('Mounted components:', HDTickets.ReactBladeBridge.components);

// View shared state
console.log('Shared state:', HDTickets.ReactBladeBridge.getSharedState('dashboard'));
```

### Common Issues

#### Component Not Mounting

1. Check component is registered in bridge
2. Verify React/ReactDOM are loaded
3. Check browser console for errors
4. Ensure `data-react-component` attribute is correct

#### State Not Syncing

1. Verify `data-state-key` matches between elements
2. Check shared state initialization
3. Ensure state updates are serializable
4. Verify Alpine.js is loaded and initialized

#### Design Tokens Not Working

1. Check `shared-design-tokens.css` is loaded
2. Verify CSS custom properties are defined
3. Check browser support for CSS variables
4. Ensure proper token naming convention

## 📚 Examples

### Complete Dashboard Example

```blade
{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div x-data="dashboardData()" class="space-y-6">
    {{-- Shared state definition --}}
    <div data-shared-state 
         data-state-key="dashboard"
         data-initial-state='{"filters": {"status": "open"}, "selectedTicket": null}'
         style="display: none;">
    </div>

    {{-- Header with Alpine.js --}}
    <header class="hd-card">
        <h1 class="hd-text-2xl hd-font-bold">Ticket Dashboard</h1>
        <div class="flex space-x-4 mt-4">
            <button @click="setFilter('status', 'open')" 
                    :class="{'hd-btn-primary': filters.status === 'open'}"
                    class="hd-btn">Open Tickets</button>
            <button @click="setFilter('status', 'closed')" 
                    :class="{'hd-btn-primary': filters.status === 'closed'}"
                    class="hd-btn">Closed Tickets</button>
        </div>
    </header>

    {{-- React Components --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart Component --}}
        <div data-react-component="TicketChart"
             data-state-key="dashboard"
             data-props='{"ticketData": @json($tickets), "height": 400}'
             data-lazy-mount="true"
             data-skeleton-type="card">
        </div>

        {{-- Filter Panel --}}
        <div data-react-component="FilterPanel"
             data-state-key="dashboard"
             data-props='{"categories": @json($categories)}'>
        </div>
    </div>

    {{-- Data Table --}}
    <div data-react-component="DataTable"
         data-state-key="dashboard"
         data-props='{"columns": @json($columns), "data": @json($tickets)}'
         data-min-height="400px">
    </div>
</div>

<script>
function dashboardData() {
    return {
        filters: { status: 'open' },
        selectedTicket: null,

        setFilter(key, value) {
            this.filters[key] = value;
            
            // Update shared state for React components
            HDTickets.ReactBladeBridge.updateSharedState('dashboard', {
                filters: this.filters
            });
        },

        init() {
            // Listen for React component updates
            document.addEventListener('componentUpdated', (event) => {
                if (event.detail.stateKey === 'dashboard') {
                    this.selectedTicket = event.detail.data.selectedTicket;
                }
            });
        }
    }
}
</script>
@endsection
```

This integration system provides a powerful way to combine the strengths of Laravel Blade templates with React components while maintaining consistency through shared design tokens and state management.
