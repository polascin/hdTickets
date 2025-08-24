'use client';

import { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { useRouter, usePathname } from 'next/navigation';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { 
  Search, 
  Bell, 
  User as UserIcon,
  Settings,
  Heart,
  Shield,
  LogOut,
  ChevronDown,
  Menu,
  X,
  Home,
  Calendar,
  TrendingUp,
  BarChart3,
  Target
} from 'lucide-react';

interface User {
  id: string;
  name: string;
  email: string;
  avatar?: string;
  role: string;
}

interface HeaderProps {
  user?: User;
  onSearch?: (query: string) => void;
  onMenuToggle?: () => void;
  notificationCount?: number;
  onLogout?: () => void;
  className?: string;
}

const navigationItems = [
  {
    id: 'dashboard',
    label: 'Dashboard',
    href: '/',
    icon: Home,
  },
  {
    id: 'discover',
    label: 'Discover',
    href: '/discover',
    icon: Search,
  },
  {
    id: 'trending',
    label: 'Trending',
    href: '/trending',
    icon: TrendingUp,
  },
  {
    id: 'schedule',
    label: 'Schedule',
    href: '/schedule',
    icon: Calendar,
  },
  {
    id: 'analytics',
    label: 'Analytics',
    href: '/analytics',
    icon: BarChart3,
  },
];

export function Header({ 
  user, 
  onSearch, 
  onMenuToggle,
  notificationCount = 0,
  onLogout,
  className = '' 
}: HeaderProps) {
  const [searchQuery, setSearchQuery] = useState('');
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const [isMobileSearchOpen, setIsMobileSearchOpen] = useState(false);
  const userMenuRef = useRef<HTMLDivElement>(null);
  const router = useRouter();
  const pathname = usePathname();

  // Close user menu when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (userMenuRef.current && !userMenuRef.current.contains(event.target as Node)) {
        setIsUserMenuOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      onSearch?.(searchQuery.trim());
      setIsMobileSearchOpen(false);
    }
  };

  const handleNavigation = (href: string) => {
    router.push(href);
  };

  const getActiveNavItem = () => {
    return navigationItems.find(item => {
      if (item.href === '/') return pathname === '/';
      return pathname.startsWith(item.href);
    });
  };

  const activeItem = getActiveNavItem();

  return (
    <motion.header 
      className={`sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm ${className}`}
      initial={{ y: -100 }}
      animate={{ y: 0 }}
      transition={{ type: 'spring', stiffness: 300, damping: 30 }}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo and Brand */}
          <div className="flex items-center space-x-4">
            {/* Mobile Menu Toggle - only visible on mobile */}
            <Button
              variant="ghost"
              size="sm"
              className="md:hidden"
              onClick={onMenuToggle}
            >
              <Menu className="w-5 h-5" />
            </Button>

            {/* Brand */}
            <motion.div 
              className="flex items-center space-x-3 cursor-pointer"
              onClick={() => handleNavigation('/')}
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.98 }}
            >
              <div className="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                <Target className="w-6 h-6 text-white" />
              </div>
              <div className="hidden sm:block">
                <h1 className="text-xl font-bold text-gray-900">HD Tickets</h1>
                <p className="text-xs text-gray-500 -mt-1">Sports Monitoring</p>
              </div>
            </motion.div>
          </div>

          {/* Desktop Navigation - hidden on mobile */}
          <nav className="hidden md:flex items-center space-x-1">
            {navigationItems.map((item) => {
              const isActive = item.id === activeItem?.id;
              const Icon = item.icon;
              
              return (
                <motion.button
                  key={item.id}
                  className={`flex items-center space-x-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                    isActive 
                      ? 'bg-blue-50 text-blue-600' 
                      : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                  }`}
                  onClick={() => handleNavigation(item.href)}
                  whileHover={{ scale: 1.05 }}
                  whileTap={{ scale: 0.95 }}
                >
                  <Icon className={`w-4 h-4 ${isActive ? 'text-blue-600' : ''}`} />
                  <span>{item.label}</span>
                </motion.button>
              );
            })}
          </nav>

          {/* Right Side Actions */}
          <div className="flex items-center space-x-3">
            {/* Search - Desktop */}
            <div className="hidden lg:block relative">
              <form onSubmit={handleSearch} className="relative">
                <input
                  type="text"
                  placeholder="Search events, teams..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-80 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              </form>
            </div>

            {/* Mobile Search Toggle */}
            <Button
              variant="ghost"
              size="sm"
              className="lg:hidden"
              onClick={() => setIsMobileSearchOpen(!isMobileSearchOpen)}
            >
              {isMobileSearchOpen ? (
                <X className="w-5 h-5" />
              ) : (
                <Search className="w-5 h-5" />
              )}
            </Button>

            {/* Notifications */}
            <Button
              variant="ghost"
              size="sm"
              className="relative"
              onClick={() => router.push('/notifications')}
            >
              <Bell className="w-5 h-5" />
              {notificationCount > 0 && (
                <Badge 
                  variant="danger"
                  className="absolute -top-1 -right-1 w-5 h-5 text-xs flex items-center justify-center"
                >
                  {notificationCount > 99 ? '99+' : notificationCount}
                </Badge>
              )}
            </Button>

            {/* User Menu */}
            {user ? (
              <div className="relative" ref={userMenuRef}>
                <Button
                  variant="ghost"
                  className="flex items-center space-x-2 p-2"
                  onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
                >
                  <div className="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                    {user.avatar ? (
                      <img 
                        src={user.avatar} 
                        alt={user.name}
                        className="w-8 h-8 rounded-full object-cover"
                      />
                    ) : (
                      <UserIcon className="w-4 h-4 text-white" />
                    )}
                  </div>
                  <span className="hidden sm:block text-sm font-medium text-gray-700">
                    {user.name}
                  </span>
                  <ChevronDown className={`w-4 h-4 text-gray-400 transition-transform ${
                    isUserMenuOpen ? 'rotate-180' : ''
                  }`} />
                </Button>

                {/* User Dropdown */}
                <AnimatePresence>
                  {isUserMenuOpen && (
                    <motion.div
                      initial={{ opacity: 0, y: -10, scale: 0.95 }}
                      animate={{ opacity: 1, y: 0, scale: 1 }}
                      exit={{ opacity: 0, y: -10, scale: 0.95 }}
                      className="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                    >
                      {/* User Info */}
                      <div className="px-4 py-3 border-b border-gray-100">
                        <p className="font-medium text-gray-900">{user.name}</p>
                        <p className="text-sm text-gray-500">{user.email}</p>
                        <Badge variant="primary" className="mt-1">
                          {user.role}
                        </Badge>
                      </div>

                      {/* Menu Items */}
                      <div className="py-2">
                        <button
                          className="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                          onClick={() => {
                            router.push('/profile');
                            setIsUserMenuOpen(false);
                          }}
                        >
                          <UserIcon className="w-4 h-4 mr-3" />
                          Profile
                        </button>
                        
                        <button
                          className="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                          onClick={() => {
                            router.push('/favorites');
                            setIsUserMenuOpen(false);
                          }}
                        >
                          <Heart className="w-4 h-4 mr-3" />
                          Favorites
                        </button>
                        
                        <button
                          className="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                          onClick={() => {
                            router.push('/settings');
                            setIsUserMenuOpen(false);
                          }}
                        >
                          <Settings className="w-4 h-4 mr-3" />
                          Settings
                        </button>
                        
                        <button
                          className="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                          onClick={() => {
                            router.push('/privacy');
                            setIsUserMenuOpen(false);
                          }}
                        >
                          <Shield className="w-4 h-4 mr-3" />
                          Privacy & Security
                        </button>
                      </div>

                      {/* Logout */}
                      <div className="border-t border-gray-100 pt-2">
                        <button
                          className="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                          onClick={() => {
                            onLogout?.();
                            setIsUserMenuOpen(false);
                          }}
                        >
                          <LogOut className="w-4 h-4 mr-3" />
                          Sign Out
                        </button>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            ) : (
              <div className="flex items-center space-x-2">
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => router.push('/auth/login')}
                >
                  Sign In
                </Button>
                <Button
                  size="sm"
                  onClick={() => router.push('/auth/register')}
                >
                  Sign Up
                </Button>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Mobile Search Bar */}
      <AnimatePresence>
        {isMobileSearchOpen && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            className="lg:hidden border-t border-gray-200 bg-white"
          >
            <div className="px-4 py-3">
              <form onSubmit={handleSearch} className="relative">
                <input
                  type="text"
                  placeholder="Search events, teams..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  autoFocus
                />
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              </form>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.header>
  );
}
