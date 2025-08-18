# HD Tickets - React Platform

Professional sports event ticket monitoring and discovery platform built with React, Next.js, and TypeScript.

## 🚀 Features

- **Modern React Architecture**: Built with React 18, Next.js 15, and TypeScript for type safety
- **Sports-Focused Design**: Enterprise-grade UI with sports-themed color schemes and components
- **Real-time Monitoring**: Live ticket price tracking with WebSocket support
- **Advanced Search & Filtering**: Intelligent ticket discovery with faceted search
- **Mobile-First Design**: Responsive design optimized for all devices
- **PWA Ready**: Progressive Web App with offline capabilities

## 🛠 Tech Stack

### Frontend
- **React 18** - Latest React with concurrent features
- **Next.js 15** - Full-stack React framework with App Router
- **TypeScript** - Type safety and better developer experience
- **Tailwind CSS v4** - Utility-first CSS with sports-themed design system
- **Framer Motion** - Professional animations and transitions
- **Lucide React** - Beautiful, customizable icons

### State Management & Data
- **React Query** - Server state management and caching
- **Zustand** - Lightweight client state management
- **React Hook Form** - Performant form handling
- **Zod** - Schema validation

### Development Tools
- **ESLint** - Code linting with React/TypeScript rules
- **Prettier** - Code formatting with Tailwind plugin
- **Husky** - Git hooks for code quality
- **Storybook** - Component development and documentation

### Backend Integration
- **Laravel API** - Existing Laravel backend for data and business logic
- **Socket.io** - Real-time WebSocket connections

## 📁 Project Structure

```
src/
├── app/                    # Next.js App Router pages
│   ├── layout.tsx         # Root layout component
│   ├── page.tsx           # Home page
│   └── globals.css        # Global styles
├── components/            # React components
│   ├── ui/               # Reusable UI components
│   │   ├── Card.tsx
│   │   ├── Button.tsx
│   │   ├── Input.tsx
│   │   └── ...
│   └── dashboard/        # Dashboard-specific components
│       ├── TicketDiscoveryDashboard.tsx
│       └── RealtimeMonitoringDashboard.tsx
├── hooks/                # Custom React hooks
│   ├── api/             # API-related hooks
│   ├── auth/            # Authentication hooks
│   └── ui/              # UI interaction hooks
├── lib/                  # Utility functions and configurations
│   ├── utils/           # General utilities
│   ├── api/             # API client configurations
│   └── validation/      # Schema validation
└── types/               # TypeScript type definitions
```

## 🏃‍♂️ Getting Started

### Prerequisites
- Node.js 18+ 
- npm 9+
- Laravel backend running on port 8000

### Installation

1. **Install dependencies**:
   ```bash
   npm install
   ```

2. **Start development server**:
   ```bash
   npm run dev
   ```
   The app will be available at `http://localhost:3000`

3. **Build for production**:
   ```bash
   npm run build
   npm start
   ```

### Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run start` - Start production server
- `npm run lint` - Run ESLint
- `npm run type-check` - Run TypeScript checks
- `npm run test` - Run tests with Vitest

## 🎨 Design System

The platform features a comprehensive design system with:

- **Sports League Colors**: NFL, NBA, MLB, NHL, MLS themed color palettes
- **Enterprise Components**: Professional UI components for data-heavy interfaces
- **Responsive Grid**: Mobile-first layout system
- **Accessibility**: WCAG compliant components with proper ARIA support

## 🔄 Real-time Features

- **Live Price Tracking**: Monitor ticket prices across multiple platforms
- **WebSocket Integration**: Real-time updates without page refresh
- **Push Notifications**: Instant alerts for price changes
- **Background Sync**: Offline-first with service worker support

## 📱 Mobile Experience

- **Touch-Optimized**: Gestures and interactions designed for mobile
- **Progressive Web App**: Installable on mobile devices
- **Offline Support**: Core functionality available without internet
- **Performance Optimized**: Fast loading and smooth interactions

## 🔐 Security

- **Content Security Policy**: Configured headers for XSS protection
- **Type Safety**: TypeScript for compile-time error checking
- **Input Validation**: Zod schemas for data validation
- **Secure Headers**: Security-first Next.js configuration

## 🚀 Deployment

The application is configured for deployment on various platforms:

- **Vercel**: Zero-config deployment with Next.js
- **Docker**: Containerized deployment with standalone output
- **Traditional Hosting**: Static export support

## 🔧 Development

### Code Quality
- ESLint with React/TypeScript rules
- Prettier for consistent formatting
- Husky for pre-commit hooks
- TypeScript strict mode enabled

### Testing
- Vitest for unit testing
- React Testing Library for component testing
- Jest DOM for DOM testing utilities

## 📊 Performance

- **Bundle Analysis**: Built-in bundle analyzer
- **Code Splitting**: Automatic route-based splitting
- **Image Optimization**: Next.js Image component with AVIF/WebP
- **PWA Optimizations**: Service worker caching strategies

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Run linting and type checking
6. Submit a pull request

## 📄 License

This project is proprietary software for HD Tickets platform.

---

## 🔄 Migration from Vue/Laravel

This React platform replaces the previous Vue.js frontend while maintaining:
- ✅ Laravel backend API compatibility
- ✅ Existing database structure
- ✅ Sports-focused functionality
- ✅ Real-time monitoring capabilities
- ✅ Mobile-responsive design

### What's New
- 🆕 Modern React 18 with concurrent features
- 🆕 TypeScript for better developer experience
- 🆕 Enhanced component architecture
- 🆕 Improved performance and bundle size
- 🆕 Advanced PWA capabilities
- 🆕 Enterprise-grade design system
