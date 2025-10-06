# HD Tickets - Multi-Framework Architecture

## Overview

HD Tickets now supports **four frontend frameworks** working together seamlessly:

- **Alpine.js** - Lightweight interactivity and progressive enhancement
- **React 18+** - Complex state management and real-time monitoring dashboards  
- **Vue 3** - Interactive purchase flows and smooth transitions
- **Angular 17+** - Form-heavy admin interfaces and reactive forms

## Framework Responsibilities

### 🏔️ Alpine.js (Existing)
**Purpose**: Simple interactivity and progressive enhancement
- Toggle components, dropdowns, modals
- Form validation (client-side)
- Simple state management for UI components
- Theme switching and accessibility features

### ⚛️ React
**Purpose**: Complex state management and real-time monitoring
- **Primary Use Case**: Ticket monitoring dashboards
- Real-time ticket price updates
- Complex data visualizations (charts, graphs)
- Redux-based state management
- WebSocket integration for live updates

**Key Components**:
- `TicketMonitoringDashboard` - Main real-time dashboard
- `PriceChart` - Interactive price history charts
- `RealTimeStats` - Live statistics display
- `TicketList` - Advanced ticket listing with filtering

### 🟢 Vue 3
**Purpose**: Interactive user flows and smooth transitions
- **Primary Use Case**: Ticket purchase flow
- Multi-step purchase process
- Dynamic pricing displays
- Smooth page transitions
- Composition API for shared logic

**Key Components**:
- `TicketPurchaseFlow` - Complete purchase workflow
- `PricingDisplay` - Dynamic price calculations
- `PaymentForm` - Payment processing interface
- `PurchaseConfirmation` - Order confirmation

### 🔴 Angular
**Purpose**: Admin interfaces and complex forms
- **Primary Use Case**: Admin dashboard and management
- Reactive forms with complex validation
- User management interfaces
- Scraping configuration panels
- Data management tools

**Key Components**:
- `AdminDashboardComponent` - Admin control panel
- `UserManagementComponent` - User CRUD operations
- `ScrapingConfigComponent` - Scraper configuration
- `ReactiveFormComponent` - Advanced form builder

## Architecture Patterns

### Island Architecture
Each framework operates as an "island" that can be embedded in Laravel Blade templates:

```html
<!-- React Component -->
<div data-react-component="TicketMonitoringDashboard" 
     data-props='{"userId": "123", "autoRefresh": true}'></div>

<!-- Vue Component -->
<div data-vue-component="TicketPurchaseFlow" 
     data-props='{"ticketId": "abc-123"}'></div>

<!-- Angular Component -->
<div data-angular-component="AdminDashboardComponent" 
     data-props='{"role": "admin"}'></div>
```

### State Management

#### Shared State
- **Global Event Bus**: Cross-framework communication
- **localStorage**: Persisted user preferences
- **Custom Events**: DOM-based framework communication

#### Framework-Specific State
- **React**: Redux Toolkit for complex state management
- **Vue**: Pinia for reactive state management
- **Angular**: Services with RxJS for reactive programming
- **Alpine**: Built-in Alpine stores for simple state

### Communication Patterns

#### Inter-Framework Communication
```typescript
// Global event bus (shared)
globalEventBus.emit('ticket-selected', ticket);
globalEventBus.on('ticket-selected', (ticket) => {
  // Handle in any framework
});

// DOM events (fallback)
document.dispatchEvent(new CustomEvent('hdtickets:ticket-selected', {
  detail: { ticket }
}));
```

#### Shared Services
```typescript
// Shared API service (used by all frameworks)
import apiService from '@shared/services/api';

// Get tickets in any framework
const tickets = await apiService.getTickets(filters);
```

## File Structure

```
resources/js/frameworks/
├── shared/                    # Framework-agnostic utilities
│   ├── types/index.ts        # TypeScript interfaces
│   ├── services/api.ts       # API service
│   └── index.ts             # Shared utilities
├── react/                    # React implementation
│   ├── index.tsx            # React entry point
│   ├── store/store.ts       # Redux store
│   ├── bridge/ReactBridge.ts # React bridge
│   └── components/          # React components
├── vue/                     # Vue implementation
│   ├── index.ts            # Vue entry point
│   ├── bridge/VueBridge.ts # Vue bridge
│   └── components/         # Vue components
└── angular/                 # Angular implementation
    ├── index.ts            # Angular entry point
    ├── bridge/AngularBridge.ts # Angular bridge
    └── components/         # Angular components
```

## Development Workflow

### Development Server
```bash
# Start all frameworks
npm run dev

# Framework-specific development
npm run dev:react
npm run dev:vue
npm run dev:angular
```

### Building
```bash
# Build all frameworks
npm run build:frameworks

# Framework-specific builds
npm run build:react
npm run build:vue  
npm run build:angular
```

### Testing
```bash
# Test all frameworks
npm run test:frameworks

# Framework-specific testing
npm run test:react
npm run test:vue
npm run test:angular
```

## Component Loading Strategy

### Conditional Loading
Frameworks are loaded only when needed:

```javascript
// Auto-detect and load frameworks based on DOM elements
if (document.querySelector('[data-react-component]')) {
  import('./frameworks/react/index.tsx');
}

if (document.querySelector('[data-vue-component]')) {
  import('./frameworks/vue/index.ts');
}

if (document.querySelector('[data-angular-component]')) {
  import('./frameworks/angular/index.ts');
}
```

### Lazy Loading
Components support lazy loading for better performance:

```html
<!-- Lazy-loaded component -->
<div data-react-component="TicketMonitoringDashboard" 
     data-lazy-mount="true"></div>
```

## Bundle Optimization

### Code Splitting
- **Framework chunks**: Separate bundles for each framework
- **Vendor chunks**: Shared dependencies
- **Component chunks**: Framework-specific components
- **Shared chunk**: Common utilities and types

### Bundle Analysis
```bash
# Analyze framework bundles
npm run analyze:frameworks
npm run analyze:react
npm run analyze:vue
npm run analyze:angular
```

## Role-Based Access Control (RBAC)

All frameworks respect the existing RBAC system:

- **Admin**: Full access to Angular admin components
- **Agent**: Access to React monitoring and Vue purchase flows
- **Customer**: Access to Vue purchase flows and basic React components
- **Scraper**: Limited access to monitoring components

```typescript
// RBAC integration in components
const DashboardComponent = ({ user }) => {
  if (user.role === 'admin') {
    return <AngularAdminDashboard />;
  } else if (user.role === 'agent') {
    return <ReactMonitoringDashboard />;
  } else {
    return <BasicDashboard />;
  }
};
```

## Performance Considerations

### Bundle Size Management
- **Target**: < 500KB per framework chunk
- **Shared utilities**: Common code in shared chunk
- **Tree shaking**: Unused code elimination
- **Compression**: Gzip/Brotli compression

### Loading Strategy
- **Critical path**: Alpine.js loads first
- **Progressive enhancement**: Frameworks load as needed
- **Error boundaries**: Graceful fallbacks
- **Performance monitoring**: Bundle size tracking

## Migration Strategy

### From Alpine.js
1. **Identify complex components** that would benefit from framework migration
2. **Create framework equivalent** with same functionality
3. **Update Blade templates** to use new component
4. **Test thoroughly** for compatibility
5. **Remove Alpine.js version** when stable

### Component Migration Priority
1. **High complexity, high value**: React dashboard components
2. **Form-heavy interfaces**: Angular admin components  
3. **Interactive flows**: Vue purchase components
4. **Simple interactions**: Keep in Alpine.js

## Best Practices

### Framework Selection
- **React**: Choose for real-time data, complex state management
- **Vue**: Choose for multi-step flows, smooth animations
- **Angular**: Choose for complex forms, admin interfaces
- **Alpine**: Choose for simple interactions, progressive enhancement

### State Management
- **Use shared state** for cross-framework communication
- **Keep framework state local** when possible
- **Persist critical state** to localStorage
- **Use TypeScript** for type safety across frameworks

### Component Design
- **Props-based communication** for data flow
- **Event-based communication** for actions
- **Error boundaries** for graceful failures
- **Consistent styling** with shared design tokens

## Troubleshooting

### Common Issues

#### Framework Not Loading
- Check DOM elements have correct `data-*-component` attributes
- Verify component is registered in framework registry
- Check browser console for JavaScript errors

#### State Synchronization Issues
- Verify global event bus is working
- Check localStorage for persisted state
- Ensure bridges are initialized properly

#### Performance Issues
- Check bundle sizes with analyzer
- Verify lazy loading is working
- Monitor network requests in DevTools

### Debugging Tools
- **React DevTools**: Component inspection and state debugging
- **Vue DevTools**: Component tree and Pinia store inspection
- **Angular DevTools**: Component analysis and dependency injection
- **Browser DevTools**: Network, performance, and console debugging

## Future Enhancements

### Planned Features
- **Micro-frontend routing**: Framework-specific routes
- **Component library**: Shared component system
- **Performance monitoring**: Real-time bundle analysis
- **A/B testing**: Framework performance comparison

### Migration Roadmap
1. **Phase 1**: Core framework integration (✅ Complete)
2. **Phase 2**: Component migration from Alpine.js
3. **Phase 3**: Advanced state management integration
4. **Phase 4**: Performance optimization and monitoring
5. **Phase 5**: Full micro-frontend architecture

---

## Support

For questions about the multi-framework architecture:
1. Check this documentation first
2. Review component examples in each framework folder
3. Test with the included demo components
4. Refer to framework-specific documentation

The multi-framework architecture provides flexibility while maintaining the existing Alpine.js functionality, allowing for gradual migration and framework-specific optimizations based on use case requirements.