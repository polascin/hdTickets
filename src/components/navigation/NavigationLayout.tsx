'use client';

import { useState, useEffect } from 'react';
import { Header } from './Header';
import { MobileNavigation } from '@/components/mobile/MobileNavigation';
import { useAuth } from '@/components/auth/AuthProvider';

interface NavigationLayoutProps {
  children: React.ReactNode;
  className?: string;
}

export function NavigationLayout({ children, className = '' }: NavigationLayoutProps) {
  const { user, logout } = useAuth();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [notificationCount, setNotificationCount] = useState(3); // Mock data

  // Mock favorite teams - in real app, this would come from user preferences
  const favoriteTeams = [
    { id: '1', name: 'Lakers', sport: 'NBA' },
    { id: '2', name: 'Chiefs', sport: 'NFL' },
  ];

  const handleSearch = (query: string) => {
    // Implement search functionality
    console.log('Searching for:', query);
    // In real app: router.push(`/search?q=${encodeURIComponent(query)}`);
  };

  const handleMobileMenuToggle = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  const handleLogout = async () => {
    try {
      await logout();
    } catch (error) {
      console.error('Logout failed:', error);
    }
  };

  // Close mobile menu on window resize to desktop size
  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth >= 768) { // md breakpoint
        setIsMobileMenuOpen(false);
      }
    };

    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  return (
    <div className={`min-h-screen bg-gray-50 ${className}`}>
      {/* Desktop/Tablet Header - Always visible except on small mobile */}
      <Header
        user={user}
        onSearch={handleSearch}
        onMenuToggle={handleMobileMenuToggle}
        notificationCount={notificationCount}
        onLogout={handleLogout}
      />

      {/* Main Content Area */}
      <main className="flex-1">
        {children}
      </main>

      {/* Mobile Navigation - Only visible on mobile screens */}
      <div className="md:hidden">
        <MobileNavigation
          user={user}
          favoriteTeams={favoriteTeams}
          onLogout={handleLogout}
          notificationCount={notificationCount}
        />
      </div>

      {/* Mobile Menu Overlay (for when mobile menu button is pressed on header) */}
      {/* This is handled by the mobile menu toggle in header, but we can add additional functionality here if needed */}
    </div>
  );
}
