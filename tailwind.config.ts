import type { Config } from 'tailwindcss'

const config: Config = {
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
    './src/features/**/*.{js,ts,jsx,tsx,mdx}',
    './src/shared/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      // Enterprise Sports Brand Colors
      colors: {
        // Professional Sports League Themes
        nfl: {
          50: '#f0f7ff',
          100: '#e0efff',
          200: '#baddff',
          300: '#7cc2ff',
          400: '#36a5ff',
          500: '#0284c7', // NFL Blue
          600: '#0369a1',
          700: '#075985',
          800: '#0c4a6e',
          900: '#0f3f5c',
        },
        nba: {
          50: '#faf5ff',
          100: '#f3e8ff',
          200: '#e9d5ff',
          300: '#d8b4fe',
          400: '#c084fc',
          500: '#8b5cf6', // NBA Purple
          600: '#7c3aed',
          700: '#6d28d9',
          800: '#5b21b6',
          900: '#4c1d95',
        },
        mlb: {
          50: '#fefcf3',
          100: '#fef7e0',
          200: '#fcecb8',
          300: '#f9db84',
          400: '#f5c147',
          500: '#d97706', // MLB Orange
          600: '#c2410c',
          700: '#9a2e06',
          800: '#7c2d12',
          900: '#6c2e1f',
        },
        nhl: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6', // NHL Teal
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
        },
        mls: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e', // MLS Green
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
        
        // Enterprise Status Colors
        enterprise: {
          primary: '#0f172a',   // Deep professional navy
          secondary: '#1e293b', // Slate gray
          accent: '#3b82f6',    // Professional blue
          surface: '#f8fafc',   // Light surface
          border: '#e2e8f0',    // Light border
        },
        
        // Ticket Status Colors
        ticket: {
          available: '#10b981',
          limited: '#f59e0b', 
          soldout: '#ef4444',
          premium: '#8b5cf6',
          vip: '#f59e0b',
          presale: '#06b6d4',
        },
        
        // Platform Brand Colors
        platform: {
          stubhub: '#0070f3',
          ticketmaster: '#026cdf',
          seatgeek: '#4c84ff',
          vivid: '#ff6900',
          tickpick: '#1a73e8',
          gametime: '#00c851',
        }
      },
      
      // Professional Typography Scale
      fontFamily: {
        'sans': ['Inter', 'system-ui', 'sans-serif'],
        'display': ['Poppins', 'Inter', 'sans-serif'],
        'mono': ['JetBrains Mono', 'Fira Code', 'monospace'],
        'sport': ['Rajdhani', 'Oswald', 'sans-serif'], // Athletic typography
      },
      
      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem' }],
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem', { lineHeight: '1.5rem' }],
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
        '5xl': ['3rem', { lineHeight: '1.16' }],
        '6xl': ['3.75rem', { lineHeight: '1.12' }],
        'display-sm': ['2rem', { lineHeight: '2.5rem', letterSpacing: '-0.02em' }],
        'display-md': ['2.5rem', { lineHeight: '3rem', letterSpacing: '-0.02em' }],
        'display-lg': ['3.5rem', { lineHeight: '4rem', letterSpacing: '-0.02em' }],
        'display-xl': ['4.5rem', { lineHeight: '5rem', letterSpacing: '-0.02em' }],
      },
      
      // Professional Spacing Scale
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '100': '25rem',
        '112': '28rem',
        '128': '32rem',
      },
      
      // Enterprise Border Radius
      borderRadius: {
        'none': '0px',
        'sm': '0.125rem',
        DEFAULT: '0.375rem',
        'md': '0.5rem',
        'lg': '0.75rem',
        'xl': '1rem',
        '2xl': '1.5rem',
        '3xl': '2rem',
        'stadium': '50rem', // For stadium-like rounded elements
      },
      
      // Professional Shadow System
      boxShadow: {
        'xs': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
        'sm': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
        DEFAULT: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
        'md': '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
        'lg': '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
        'xl': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
        '2xl': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
        'inner': 'inset 0 2px 4px 0 rgb(0 0 0 / 0.05)',
        
        // Sports-themed shadows
        'field': '0 4px 20px rgb(34 197 94 / 0.15)', // Green field glow
        'court': '0 4px 20px rgb(139 92 246 / 0.15)', // Purple court glow
        'ice': '0 4px 20px rgb(20 184 166 / 0.15)',   // Teal ice glow
        'stadium': '0 8px 30px rgb(15 23 42 / 0.12)',  // Stadium depth
      },
      
      // Mobile-first Screen Sizes
      screens: {
        'xs': '320px',    // Small mobile
        'sm': '375px',    // Standard mobile
        'md': '425px',    // Large mobile
        'lg': '768px',    // Tablet
        'xl': '1024px',   // Small desktop
        '2xl': '1280px',  // Standard desktop
        '3xl': '1536px',  // Large desktop
        '4xl': '1920px',  // Ultra-wide
      },
      
      // Professional Animation System
      animation: {
        'fade-in': 'fadeIn 0.3s ease-in-out',
        'slide-up': 'slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
        'slide-down': 'slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
        'scale-in': 'scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1)',
        'bounce-in': 'bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)',
        'pulse-glow': 'pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'ticker': 'ticker 30s linear infinite',
        'field-scan': 'fieldScan 3s ease-in-out infinite',
      },
      
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideDown: {
          '0%': { transform: 'translateY(-20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        scaleIn: {
          '0%': { transform: 'scale(0.9)', opacity: '0' },
          '100%': { transform: 'scale(1)', opacity: '1' },
        },
        bounceIn: {
          '0%': { transform: 'scale(0.3)', opacity: '0' },
          '50%': { transform: 'scale(1.05)', opacity: '1' },
          '70%': { transform: 'scale(0.98)' },
          '100%': { transform: 'scale(1)' },
        },
        pulseGlow: {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.5' },
        },
        ticker: {
          '0%': { transform: 'translateX(100%)' },
          '100%': { transform: 'translateX(-100%)' },
        },
        fieldScan: {
          '0%, 100%': { transform: 'scaleX(1) scaleY(1)' },
          '50%': { transform: 'scaleX(1.02) scaleY(0.98)' },
        },
      },
      
      // Professional Gradients
      backgroundImage: {
        // Sports venue gradients
        'stadium-gradient': 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)',
        'field-gradient': 'linear-gradient(135deg, #22c55e 0%, #15803d 100%)',
        'court-gradient': 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)',
        'ice-gradient': 'linear-gradient(135deg, #14b8a6 0%, #0f766e 100%)',
        
        // Enterprise gradients
        'enterprise-primary': 'linear-gradient(135deg, #1e293b 0%, #475569 100%)',
        'enterprise-surface': 'linear-gradient(180deg, #ffffff 0%, #f8fafc 100%)',
        'enterprise-dark': 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
        
        // Status gradients
        'success-gradient': 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
        'warning-gradient': 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
        'error-gradient': 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
        'info-gradient': 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
      },
      
      // Enterprise Grid Systems
      gridTemplateColumns: {
        // Stadium seating layouts
        'stadium-sm': 'repeat(8, minmax(0, 1fr))',
        'stadium-md': 'repeat(12, minmax(0, 1fr))',
        'stadium-lg': 'repeat(16, minmax(0, 1fr))',
        
        // Dashboard layouts
        'dashboard': '240px 1fr',
        'dashboard-collapsed': '64px 1fr',
        'analytics': 'repeat(auto-fit, minmax(300px, 1fr))',
      },
      
      // Professional Z-index Scale
      zIndex: {
        'modal': '1000',
        'dropdown': '1010',
        'sticky': '1020',
        'fixed': '1030',
        'overlay': '1040',
        'popover': '1050',
        'tooltip': '1060',
        'notification': '1070',
      },
      
      // Enterprise Backdrop Filters
      backdropBlur: {
        'xs': '2px',
        'sm': '4px',
        DEFAULT: '8px',
        'md': '12px',
        'lg': '16px',
        'xl': '24px',
        '2xl': '40px',
        '3xl': '64px',
      },
      
      // Touch-friendly sizes for mobile
      width: {
        'touch': '44px',  // Minimum touch target
        'touch-lg': '56px', // Comfortable touch target
      },
      height: {
        'touch': '44px',
        'touch-lg': '56px',
        'screen-mobile': '100dvh', // Dynamic viewport height for mobile
      },
      
      // Professional aspect ratios
      aspectRatio: {
        'stadium': '16 / 9',
        'field': '4 / 3',
        'ticket': '3 / 2',
        'card': '5 / 3',
      },
    },
  },
  
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
    require('@tailwindcss/typography'),
    
    // Custom plugin for sports-themed utilities
    function({ addUtilities, theme }) {
      const newUtilities = {
        // Touch-friendly utilities
        '.touch-target': {
          minWidth: '44px',
          minHeight: '44px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          touchAction: 'manipulation',
          WebkitTapHighlightColor: 'transparent',
        },
        
        // Stadium-themed layouts
        '.stadium-layout': {
          borderRadius: '50px',
          background: 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)',
          boxShadow: '0 8px 30px rgb(15 23 42 / 0.12)',
        },
        
        // Field-themed components
        '.field-surface': {
          background: 'linear-gradient(135deg, #22c55e 0%, #15803d 100%)',
          position: 'relative',
        },
        '.field-surface::before': {
          content: '""',
          position: 'absolute',
          inset: '0',
          background: 'repeating-linear-gradient(0deg, transparent, transparent 10px, rgba(255,255,255,0.1) 10px, rgba(255,255,255,0.1) 11px)',
          pointerEvents: 'none',
        },
        
        // Professional glass effect
        '.glass-pro': {
          backgroundColor: 'rgba(255, 255, 255, 0.8)',
          backdropFilter: 'blur(12px) saturate(200%)',
          border: '1px solid rgba(255, 255, 255, 0.3)',
        },
        
        // Mobile-first safe areas
        '.safe-top': {
          paddingTop: 'max(1rem, env(safe-area-inset-top))',
        },
        '.safe-bottom': {
          paddingBottom: 'max(1rem, env(safe-area-inset-bottom))',
        },
        '.safe-left': {
          paddingLeft: 'max(1rem, env(safe-area-inset-left))',
        },
        '.safe-right': {
          paddingRight: 'max(1rem, env(safe-area-inset-right))',
        },
      }
      
      addUtilities(newUtilities)
    },
  ],
}

export default config
