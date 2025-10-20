# HD Tickets Documentation Index

Welcome to the HD Tickets Sports Event Ticket Monitoring System documentation.

## 📚 Documentation Structure

### 🚀 Getting Started
- [README.md](README.md) - Main project overview and quick start
- [CHANGELOG.md](CHANGELOG.md) - Version history and changes
- [DOCUMENTATION_CLEANUP_SUMMARY.md](DOCUMENTATION_CLEANUP_SUMMARY.md) - Recent documentation reorganization

### 🔧 Setup & Installation
- [Setup Guide](docs/setup/) - Complete installation documentation
  - [Installation Guide](docs/setup/HD_TICKETS_LAMP_INSTALLATION.md) - LAMP stack setup
  - [SSL Configuration](docs/setup/SSL_SETUP_DOCUMENTATION.md) - SSL/TLS setup

### 👩‍💻 Development
- [Development Guides](docs/development/) - Development documentation
  - [Frontend Status](docs/development/FRONTEND_STATUS.md) - Current frontend framework status
  - [Cleanup Summary](docs/development/CLEANUP_SUMMARY.md) - Recent cleanup and optimizations
  - [Dependency Updates](docs/development/DEPENDENCY_UPDATE_GUIDELINES.md) - Package management
  - [Coding Standards](docs/development/CODING_STANDARDS.md) - Code style guidelines
  - [Performance Guide](docs/development/PERFORMANCE_OPTIMIZATION_GUIDE.md) - Performance best practices
  - [Accessibility Testing](docs/development/ACCESSIBILITY_TESTING_GUIDE.md) - Accessibility compliance

### 🏗️ Architecture
- [Architecture Documentation](docs/architecture/) - System design and patterns
  - [DDD Implementation](docs/architecture/DDD_IMPLEMENTATION.md) - Domain-Driven Design patterns
  - [Service Architecture](docs/architecture/SERVICE_CONSOLIDATION_PLAN.md) - Service layer design
  - [Security Architecture](docs/architecture/SECURITY_HARDENING_IMPLEMENTATION.md) - Security implementation
  - [Event Architecture](docs/architecture/EVENT_DRIVEN_ARCHITECTURE.md) - Event-driven patterns

### 🚀 Deployment & Production
- [Deployment Guides](docs/deployment/) - Production deployment documentation
  - [Production Monitoring](docs/deployment/PRODUCTION_MONITORING.md) - Monitoring setup
  - [Security Enhancements](docs/deployment/SECURITY_ENHANCEMENTS.md) - Production security

### 📖 API & Technical References
- [API Documentation](API_ROUTE_DOCUMENTATION.md) - REST API endpoints
- [Dashboard Routing](DASHBOARD_ROUTING_DOCUMENTATION.md) - Dashboard routing system
- [Security Guide](SECURITY.md) - Security best practices
- [WARP Documentation](WARP.md) - Advanced features and capabilities

## 🔍 Quick Navigation

### For Developers
1. Start with [README.md](README.md) for project overview
2. Follow [Installation Guide](docs/setup/HD_TICKETS_LAMP_INSTALLATION.md) for setup
3. Check [Frontend Status](docs/development/FRONTEND_STATUS.md) for current tech stack
4. Review [API Documentation](API_ROUTE_DOCUMENTATION.md) for endpoints

### For System Administrators
1. Review [SSL Configuration](docs/setup/SSL_SETUP_DOCUMENTATION.md)
2. Check [Production Monitoring](docs/deployment/PRODUCTION_MONITORING.md)
3. Follow [Security Guide](SECURITY.md)

### For Architects
1. Study [DDD Implementation](docs/architecture/DDD_IMPLEMENTATION.md)
2. Review [Service Consolidation](docs/architecture/SERVICE_CONSOLIDATION_PLAN.md)
3. Check [Security Implementation](docs/architecture/SECURITY_HARDENING_IMPLEMENTATION.md)

## 📝 Documentation Standards

- All documentation follows Markdown format
- Code examples include syntax highlighting
- Screenshots and diagrams are included where helpful
- Each document includes a table of contents for longer docs
- Last updated dates are maintained in document headers

## 🆕 Recent Updates

See [CHANGELOG.md](CHANGELOG.md) for the latest changes and updates.

---

**Last Updated:** August 29, 2025  
**Version:** 2.0.0

## UI Component Additions (v4 Design System)

### Modal (hdt-modal)

An accessible, token-driven modal dialog component replacing legacy `.hd-modal` variants.

Blade Usage:

```blade
<x-hdt.modal name="invite-users" title="Invite Users" subtitle="Send email invitations" size="lg">
  <p class="mb-4">Provide a comma-separated list of email addresses to invite.</p>
  <div class="space-y-2">
    <label class="block text-sm font-medium">Emails</label>
    <textarea class="w-full border rounded p-2 text-sm" rows="4" placeholder="user1@example.com, user2@example.com"></textarea>
  </div>
  <x-slot:footer>
    <x-ui.button variant="subtle" onclick="window.dispatchEvent(new CustomEvent('hdt:modal:close',{detail:{name:'invite-users'}}))">Cancel</x-ui.button>
    <x-ui.button>Send Invites</x-ui.button>
  </x-slot:footer>
</x-hdt.modal>

<x-ui.button onclick="window.dispatchEvent(new CustomEvent('hdt:modal:open',{detail:{name:'invite-users'}}))">Open Modal</x-ui.button>
```

Props:
- name: optional identifier for global open/close events
- open: initial boolean state
- title / subtitle: accessible labeling (automatically bound to aria-labelledby / aria-describedby)
- size: sm|md|lg|xl|full (default md)
- closeButton: show dismiss button (default true)
- escToClose: allow ESC key (default true)
- staticBackdrop: disable backdrop click close (default false)
- maxHeight: screen|content (screen enables internal scroll container)
- id: optional explicit DOM id

Events (dispatch on window):
- hdt:modal:open  { name?, id? }
- hdt:modal:close { name?, id? }

Accessibility:
- Focus returns to previously focused element on close
- Scroll on document locked while open (html.overflow-hidden)
- ESC/backdrop behavior configurable
- Console warning emitted once if legacy `.hd-modal` markup detected

Future Enhancements (planned):
- Focus sentry elements for more robust tab loop
- Density-aware padding adjustments
- Inline form validation pattern guidance

### Input (hdt-input)

Accessible text input component with unified states and optional prefix/suffix icons.

Blade Usage:
```blade
<x-hdt.input name="email" type="email" label="Email" placeholder="you@example.com" required help="We'll send a confirmation." />

<x-hdt.input name="amount" label="Amount" prefixIcon="currency-dollar" suffixIcon="information-circle" state="success" value="99" />

<x-hdt.input name="username" label="Username" :error="$errors->first('username')" />
```

Props:
- name / id: for form submission & label association
- type: input type (default text)
- label: visible label (recommend always)
- value / placeholder
- help: small explanatory text (id bound via aria-describedby)
- error: displays error text (takes priority over help; red state)
- required / disabled / readonly
- size: sm|md|lg (default md)
- state: success|warning|error|info (applies colored focus ring)
- prefixIcon / suffixIcon or <x-slot:prefix>/<x-slot:suffix>

Accessibility:
- Label is explicitly connected by for/id
- Help/error text referenced via aria-describedby
- Error state uses color plus icon slot availability (optional future) for non-color cues

Planned Enhancements:
- Add textarea variant & select wrapper
- Integrate validation icons (success/error) automatically when state provided
- Density compact variant based on global density token

### Table (hdt-table)

Semantic, accessible data table component with optional density, striped rows, hover highlighting, sticky headers, and empty state ergonomics. Styles are token-driven via `design-system-v3.css`.

Blade Usage (basic):
```blade
<x-hdt.table :columns="['Name','Price','Status']">
  <tr>
    <td>Section 101 Row A</td>
    <td>$120</td>
    <td><x-ui.badge variant="success" size="sm">Active</x-ui.badge></td>
  </tr>
</x-hdt.table>
```

Advanced (striped, hover, dense, sticky header, caption & empty):
```blade
<x-hdt.table
  :columns="['Event','Venue','Floor','Price']"
  caption="Upcoming tracked events"
  striped
  hover
  dense
  stickyHeader
  empty="No events found">
  @foreach($events as $evt)
    <tr>
      <td>{{ $evt->name }}</td>
      <td>{{ $evt->venue }}</td>
      <td>{{ $evt->floor }}</td>
      <td>${{ number_format($evt->price,2) }}</td>
    </tr>
  @endforeach
</x-hdt.table>
```

Props:
- columns: array of header labels (auto-renders thead)
- striped / hover / dense / comfortable: style modifiers
- stickyHeader: makes header row sticky inside scroll container
- caption: optional `<caption>` for context (recommended for screen readers)
- empty: string or slot displayed when there are no body rows
- id: optional DOM id (auto-generated if omitted)

Accessibility:
- Uses native `<table>` semantics, `<thead>`, `<tbody>`, `<th scope="col">`
- Caption rendered before thead for screen reader context
- Sticky header preserves column associations
- Empty state placed inside tbody in a single spanning row
- Hover & selection color changes meet contrast via token palette
- Optional JS can add `.is-focus` to row to create a visible focus outline (keyboard row navigation pattern)

Future Enhancements:
- Column sorting buttons with aria-sort
- Responsive column priority / overflow disclosure pattern
- Row selection checkboxes with select-all header cell
- Live region updates when sorting or filtering

### Navigation Accessibility Enhancements

The primary and responsive navigation components now automatically apply `aria-current="page"` to active links via updates in `nav-link.blade.php` and `responsive-nav-link.blade.php`. Sidebar links and sublinks already exposed `aria-current` bindings. This improves screen reader context for current location.

Key Improvements:
- `aria-current="page"` on active top navigation & mobile links
- Consistent 44px min-height for interactive sidebar elements (touch target)
- Focus outlines standardized to design token focus ring
- Landmark roles: main top nav `aria-label="Primary navigation"`, sidebar uses `role="menu"` for grouped items

Planned Navigation Work:
- Add skip-to-content link injection above nav (desktop & mobile)
- Provide orientation announcement when user tabs into nav region
- ARIA pattern for collapsible submenu state announcements (aria-controls w/ region labeling)
- Reduced motion friendly transitions for menu open/close animations
