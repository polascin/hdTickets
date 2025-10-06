/**
 * HD Tickets - Main Layout Component
 * Responsive layout with header, sidebar, and main content area
 */

import React, { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Bars3Icon, 
  XMarkIcon,
  BellIcon,
  ShoppingCartIcon,
  UserCircleIcon
} from '@heroicons/react/24/outline';

import { useAppSelector, useAppDispatch } from '../../store/store';
import { selectSidebarOpen, selectCartItemCount, selectUnreadCount } from '../../store/store';
import { toggleSidebar, closeSidebar } from '../../store/slices/uiSlice';

import Header from './Header';
import Sidebar from './Sidebar';
import NavigationBreadcrumb from './NavigationBreadcrumb';
import QuickActions from './QuickActions';

const Layout: React.FC = () => {
  const dispatch = useAppDispatch();
  const sidebarOpen = useAppSelector(selectSidebarOpen);
  const cartItemCount = useAppSelector(selectCartItemCount);
  const unreadCount = useAppSelector(selectUnreadCount);

  const handleSidebarToggle = () => {
    dispatch(toggleSidebar());
  };

  const handleSidebarClose = () => {
    dispatch(closeSidebar());
  };

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Mobile sidebar backdrop */}
      <AnimatePresence>
        {sidebarOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={handleSidebarClose}
            className="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
          />
        )}
      </AnimatePresence>

      {/* Sidebar */}
      <Sidebar 
        isOpen={sidebarOpen}
        onClose={handleSidebarClose}
      />

      {/* Main content */}
      <div className={`flex flex-1 flex-col transition-all duration-300 ${
        sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'
      }`}>
        {/* Header */}
        <Header 
          onSidebarToggle={handleSidebarToggle}
          cartItemCount={cartItemCount}
          unreadCount={unreadCount}
        />

        {/* Main content area */}
        <main className="flex-1 pb-8">
          {/* Navigation breadcrumb */}
          <NavigationBreadcrumb />

          {/* Page content */}
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3 }}
            >
              <Outlet />
            </motion.div>
          </div>
        </main>

        {/* Quick actions - floating action menu */}
        <QuickActions />
      </div>

      {/* Mobile bottom navigation (optional) */}
      <MobileBottomNavigation />
    </div>
  );
};

// Mobile bottom navigation for better mobile UX
const MobileBottomNavigation: React.FC = () => {
  const cartItemCount = useAppSelector(selectCartItemCount);
  const unreadCount = useAppSelector(selectUnreadCount);

  return (
    <div className="fixed bottom-0 left-0 right-0 z-30 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 lg:hidden">
      <div className="grid grid-cols-4 h-16">
        <MobileNavItem 
          icon={<Bars3Icon className="h-6 w-6" />}
          label="Menu"
          href="/dashboard"
        />
        <MobileNavItem 
          icon={<BellIcon className="h-6 w-6" />}
          label="Alerts"
          href="/notifications"
          badge={unreadCount}
        />
        <MobileNavItem 
          icon={<ShoppingCartIcon className="h-6 w-6" />}
          label="Cart"
          href="/cart"
          badge={cartItemCount}
        />
        <MobileNavItem 
          icon={<UserCircleIcon className="h-6 w-6" />}
          label="Profile"
          href="/profile"
        />
      </div>
    </div>
  );
};

interface MobileNavItemProps {
  icon: React.ReactNode;
  label: string;
  href: string;
  badge?: number;
}

const MobileNavItem: React.FC<MobileNavItemProps> = ({ 
  icon, 
  label, 
  href, 
  badge 
}) => {
  return (
    <a
      href={href}
      className="flex flex-col items-center justify-center text-xs text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors relative"
    >
      <div className="relative">
        {icon}
        {badge && badge > 0 && (
          <span className="absolute -top-2 -right-2 h-5 w-5 text-xs flex items-center justify-center bg-red-500 text-white rounded-full">
            {badge > 99 ? '99+' : badge}
          </span>
        )}
      </div>
      <span className="mt-1">{label}</span>
    </a>
  );
};

export default Layout;