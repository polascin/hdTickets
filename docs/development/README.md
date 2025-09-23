# Development Documentation

This directory contains all documentation related to developing and maintaining the HD Tickets application.

## 📋 Contents

### Frontend & Build System
- [FRONTEND_STATUS.md](FRONTEND_STATUS.md) - Current frontend framework status and build configuration
- [CLEANUP_SUMMARY.md](CLEANUP_SUMMARY.md) - Recent cleanup operations and optimizations

### UI/UX & Layout
- [NAVIGATION_IMPROVEMENTS.md](NAVIGATION_IMPROVEMENTS.md) - Navigation system enhancements
- [LAYOUT_IMPROVEMENTS_DOCUMENTATION.md](LAYOUT_IMPROVEMENTS_DOCUMENTATION.md) - Layout system documentation

### Code Quality & Standards
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - Code style and quality standards
- [PSR_IMPLEMENTATION_REPORT.md](PSR_IMPLEMENTATION_REPORT.md) - PSR compliance implementation
- [ROUTE_MIDDLEWARE_IMPLEMENTATION.md](ROUTE_MIDDLEWARE_IMPLEMENTATION.md) - Routing and middleware patterns

### Testing & Quality Assurance
- [ACCESSIBILITY_TESTING_GUIDE.md](ACCESSIBILITY_TESTING_GUIDE.md) - Accessibility compliance testing
- [PERFORMANCE_OPTIMIZATION_GUIDE.md](PERFORMANCE_OPTIMIZATION_GUIDE.md) - Performance optimization strategies

### Dependencies & Updates
- [DEPENDENCY_UPDATE_GUIDELINES.md](DEPENDENCY_UPDATE_GUIDELINES.md) - Package management and update procedures

## 🛠️ Development Workflow

1. **Code Standards**: Follow [CODING_STANDARDS.md](CODING_STANDARDS.md)
2. **Frontend Setup**: Check [FRONTEND_STATUS.md](FRONTEND_STATUS.md) for current tech stack
3. **Performance**: Apply guidelines from [PERFORMANCE_OPTIMIZATION_GUIDE.md](PERFORMANCE_OPTIMIZATION_GUIDE.md)
4. **Accessibility**: Use [ACCESSIBILITY_TESTING_GUIDE.md](ACCESSIBILITY_TESTING_GUIDE.md) for compliance
5. **Dependencies**: Follow [DEPENDENCY_UPDATE_GUIDELINES.md](DEPENDENCY_UPDATE_GUIDELINES.md) for updates

## 🎯 Current Tech Stack

- **Backend**: Laravel 11.45.2 + PHP 8.3
- **Frontend**: Alpine.js 3.14.7 + Custom Design System (TailwindCSS removed – legacy utilities snapshot `tw-legacy.css` in use during transition)
- **Build Tool**: Vite 6.3.5
- **Database**: MySQL/MariaDB
- **Server**: Apache2 + Ubuntu 24.04 LTS

---
*Last updated: August 29, 2025*
