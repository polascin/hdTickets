'use client';

import { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence, PanInfo } from 'framer-motion';
import { useRouter, usePathname } from 'next/navigation';
import { Card } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import type { User, Sport, Team } from '@/types';
import { 
  Home, 
  Search, 
  Bell, 
  User as UserIcon,
  Menu,
  X,
  ChevronRight,
  Star,
  Settings,
  TrendingUp,
  Calendar,
  Filter,
  Heart,
  Shield,
  LogOut
} from 'lucide-react';

interface MobileNavigationProps {
  user?: User;
  favoriteTeams?: Team[];
  onLogout?: () => void;
  notificationCount?: number;
}

interface NavItem {
  id: string;
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  href: string;
  badge?: number;
  requiresAuth?: boolean;
  sports?: Sport[];
}

const navigationItems: NavItem[] = [
  {
    id: 'home',
    label: 'Dashboard',
    icon: Home,
    href: '/',
  },
  {
    id: 'discover',
    label: 'Discover',
    icon: Search,
    href: '/discover',
  },
  {
    id: 'alerts',
    label: 'Alerts',
    icon: Bell,
    href: '/alerts',
    requiresAuth: true,
  },
  {
    id: 'trending',
    label: 'Trending',
    icon: TrendingUp,
    href: '/trending',
  },
  {
    id: 'schedule',
    label: 'Schedule',
    icon: Calendar,
    href: '/schedule',
  },
];

const sportCategories = [
  { id: 'nfl', name: 'NFL', color: '#013369', icon: '🏈' },
  { id: 'nba', name: 'NBA', color: '#C8102E', icon: '🏀' },
  { id: 'mlb', name: 'MLB', color: '#132448', icon: '⚾' },
  { id: 'nhl', name: 'NHL', color: '#000000', icon: '🏒' },
  { id: 'mls', name: 'MLS', color: '#69BE28', icon: '⚽' },
  { id: 'ncaa', name: 'NCAA', color: '#FF6B35', icon: '🎓' },
];

export function MobileNavigation({ 
  user, 
  favoriteTeams = [], 
  onLogout,
  notificationCount = 0 
}: MobileNavigationProps) {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [activeSection, setActiveSection] = useState<'main' | 'sports' | 'profile'>('main');
  const [dragStartY, setDragStartY] = useState(0);
  const menuRef = useRef<HTMLDivElement>(null);
  const router = useRouter();
  const pathname = usePathname();

  // Close menu on route change
  useEffect(() => {
    setIsMenuOpen(false);
  }, [pathname]);

  // Handle swipe to close
  const handleDragEnd = (event: MouseEvent | TouchEvent, info: PanInfo) => {
    if (info.offset.y > 100 && info.velocity.y > 0) {
      setIsMenuOpen(false);
    }
  };

  const handleNavigation = (href: string) => {
    router.push(href);
    setIsMenuOpen(false);
  };

  const handlePullToRefresh = async () => {
    // Implement pull-to-refresh logic
    window.location.reload();
  };

  // Get active nav item
  const activeNavItem = navigationItems.find(item => {
    if (item.href === '/') return pathname === '/';
    return pathname.startsWith(item.href);
  });

  return (
    <>
      {/* Bottom Navigation Bar */}
      <motion.div
        className="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 safe-area-pb"
        initial={{ y: 100 }}
        animate={{ y: 0 }}
        transition={{ type: 'spring', stiffness: 300, damping: 30 }}
      >
        <div className="flex items-center justify-around px-2 py-2">
          {navigationItems.slice(0, 4).map((item) => {
            const isActive = item.id === activeNavItem?.id;
            const Icon = item.icon;
            
            return (
              <motion.button
                key={item.id}
                className={`relative flex flex-col items-center px-3 py-2 rounded-lg min-w-0 flex-1 ${
                  isActive 
                    ? 'text-blue-600 bg-blue-50' 
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                }`}
                onClick={() => handleNavigation(item.href)}
                whileTap={{ scale: 0.95 }}
              >
                <Icon className={`w-5 h-5 ${isActive ? 'text-blue-600' : ''}`} />
                <span className={`text-xs mt-1 font-medium truncate ${
                  isActive ? 'text-blue-600' : 'text-gray-600'
                }`}>
                  {item.label}
                </span>
                
                {item.badge && item.badge > 0 && (
                  <Badge 
                    variant="danger" 
                    className="absolute -top-1 -right-1 w-5 h-5 text-xs flex items-center justify-center"
                  >
                    {item.badge > 99 ? '99+' : item.badge}
                  </Badge>
                )}
              </motion.button>
            );
          })}
          
          {/* Menu Button */}
          <motion.button
            className="flex flex-col items-center px-3 py-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 min-w-0 flex-1"
            onClick={() => setIsMenuOpen(true)}
            whileTap={{ scale: 0.95 }}
          >
            <Menu className="w-5 h-5" />
            <span className="text-xs mt-1 font-medium">Menu</span>
            {notificationCount > 0 && (
              <Badge 
                variant="danger" 
                className="absolute -top-1 -right-1 w-5 h-5 text-xs flex items-center justify-center"
              >
                {notificationCount > 99 ? '99+' : notificationCount}
              </Badge>
            )}
          </motion.button>
        </div>
      </motion.div>

      {/* Full Screen Menu Overlay */}
      <AnimatePresence>
        {isMenuOpen && (
          <>
            {/* Backdrop */}
            <motion.div
              className="fixed inset-0 bg-black bg-opacity-50 z-50"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setIsMenuOpen(false)}
            />
            
            {/* Menu Panel */}
            <motion.div
              ref={menuRef}
              className="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-2xl max-h-[90vh] overflow-hidden"
              initial={{ y: '100%' }}
              animate={{ y: 0 }}
              exit={{ y: '100%' }}
              transition={{ type: 'spring', stiffness: 300, damping: 30 }}
              drag="y"
              dragConstraints={{ top: 0, bottom: 0 }}
              onDragEnd={handleDragEnd}
            >
              {/* Handle Bar */}
              <div className="w-12 h-1 bg-gray-300 rounded-full mx-auto mt-3 mb-6" />
              
              {/* Menu Content */}
              <div className="px-6 pb-safe">
                <AnimatePresence mode="wait">
                  {activeSection === 'main' && (
                    <motion.div
                      key="main"
                      initial={{ opacity: 0, x: -20 }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{ opacity: 0, x: 20 }}
                      className="space-y-6"
                    >
                      {/* User Section */}
                      {user ? (
                        <div className="flex items-center space-x-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl">
                          <div className="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            {user.avatar ? (
                              <img src={user.avatar} alt={user.name} className="w-12 h-12 rounded-full" />
                            ) : (
                              <UserIcon className="w-6 h-6 text-white" />
                            )}
                          </div>
                          <div className="flex-1">
                            <h3 className="font-semibold text-gray-900">{user.name}</h3>
                            <p className="text-sm text-gray-600">{user.email}</p>
                          </div>
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setActiveSection('profile')}
                          >
                            <ChevronRight className="w-4 h-4" />
                          </Button>
                        </div>
                      ) : (
                        <div className="p-4 bg-gray-50 rounded-xl">
                          <h3 className="font-semibold text-gray-900 mb-2">Sign In</h3>
                          <p className="text-sm text-gray-600 mb-4">
                            Access your alerts, favorites, and personalized recommendations
                          </p>
                          <Button className="w-full">Sign In</Button>
                        </div>
                      )}

                      {/* Sports Categories */}
                      <div>
                        <div className="flex items-center justify-between mb-4">
                          <h3 className="text-lg font-semibold text-gray-900">Sports</h3>
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setActiveSection('sports')}
                          >
                            <ChevronRight className="w-4 h-4" />
                          </Button>
                        </div>
                        <div className="grid grid-cols-3 gap-3">
                          {sportCategories.slice(0, 6).map((sport) => (
                            <motion.button
                              key={sport.id}
                              className="p-4 bg-gray-50 rounded-xl text-center hover:bg-gray-100"
                              onClick={() => handleNavigation(`/sports/${sport.id}`)}
                              whileTap={{ scale: 0.95 }}
                            >
                              <div className="text-2xl mb-2">{sport.icon}</div>
                              <div className="text-sm font-medium text-gray-900">{sport.name}</div>
                            </motion.button>
                          ))}
                        </div>
                      </div>

                      {/* Quick Actions */}
                      <div>
                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div className="space-y-2">
                          {navigationItems.slice(4).map((item) => {
                            const Icon = item.icon;
                            return (
                              <motion.button
                                key={item.id}
                                className="w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50"
                                onClick={() => handleNavigation(item.href)}
                                whileTap={{ scale: 0.98 }}
                              >
                                <Icon className="w-5 h-5 text-gray-600" />
                                <span className="flex-1 text-left font-medium text-gray-900">
                                  {item.label}
                                </span>
                                <ChevronRight className="w-4 h-4 text-gray-400" />
                              </motion.button>
                            );
                          })}
                        </div>
                      </div>
                    </motion.div>
                  )}

                  {activeSection === 'sports' && (
                    <motion.div
                      key="sports"
                      initial={{ opacity: 0, x: 20 }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{ opacity: 0, x: -20 }}
                      className="space-y-6"
                    >
                      <div className="flex items-center space-x-3">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => setActiveSection('main')}
                        >
                          <ChevronRight className="w-4 h-4 transform rotate-180" />
                        </Button>
                        <h2 className="text-xl font-bold text-gray-900">Sports</h2>
                      </div>

                      <div className="grid grid-cols-2 gap-4">
                        {sportCategories.map((sport) => (
                          <motion.button
                            key={sport.id}
                            className="p-6 bg-gray-50 rounded-xl text-center hover:bg-gray-100"
                            style={{ 
                              backgroundColor: `${sport.color}10`,
                              borderColor: sport.color,
                              borderWidth: '1px'
                            }}
                            onClick={() => handleNavigation(`/sports/${sport.id}`)}
                            whileTap={{ scale: 0.95 }}
                          >
                            <div className="text-3xl mb-3">{sport.icon}</div>
                            <div className="font-semibold text-gray-900">{sport.name}</div>
                          </motion.button>
                        ))}
                      </div>
                    </motion.div>
                  )}

                  {activeSection === 'profile' && user && (
                    <motion.div
                      key="profile"
                      initial={{ opacity: 0, x: 20 }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{ opacity: 0, x: -20 }}
                      className="space-y-6"
                    >
                      <div className="flex items-center space-x-3">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => setActiveSection('main')}
                        >
                          <ChevronRight className="w-4 h-4 transform rotate-180" />
                        </Button>
                        <h2 className="text-xl font-bold text-gray-900">Profile</h2>
                      </div>

                      <div className="space-y-3">
                        <Button
                          variant="ghost"
                          className="w-full justify-start"
                          onClick={() => handleNavigation('/profile/favorites')}
                        >
                          <Heart className="w-5 h-5 mr-3" />
                          Favorite Teams
                        </Button>
                        
                        <Button
                          variant="ghost"
                          className="w-full justify-start"
                          onClick={() => handleNavigation('/profile/settings')}
                        >
                          <Settings className="w-5 h-5 mr-3" />
                          Settings
                        </Button>
                        
                        <Button
                          variant="ghost"
                          className="w-full justify-start"
                          onClick={() => handleNavigation('/profile/privacy')}
                        >
                          <Shield className="w-5 h-5 mr-3" />
                          Privacy & Security
                        </Button>
                        
                        <hr className="my-4" />
                        
                        <Button
                          variant="ghost"
                          className="w-full justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
                          onClick={() => {
                            onLogout?.();
                            setIsMenuOpen(false);
                          }}
                        >
                          <LogOut className="w-5 h-5 mr-3" />
                          Sign Out
                        </Button>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>

      {/* Safe area spacer */}
      <div className="h-20" />
    </>
  );
}
