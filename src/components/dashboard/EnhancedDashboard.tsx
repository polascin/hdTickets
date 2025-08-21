'use client';

import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Card } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { TeamCard } from '@/components/sports/TeamCard';
import { PriceChart } from '@/components/sports/PriceChart';
import { 
  TrendingUp, 
  TrendingDown, 
  DollarSign, 
  Users, 
  Activity, 
  Bell,
  Calendar,
  MapPin,
  Clock,
  Star,
  Zap,
  Target,
  BarChart3,
  Globe,
  Wifi,
  WifiOff
} from 'lucide-react';

// Mock data - replace with real API calls
const mockMetrics = {
  totalTicketsMonitored: 12547,
  averagePrice: 234.50,
  priceChangePercent: -3.2,
  activeAlerts: 8,
  platformsOnline: 4,
  totalPlatforms: 5,
  todayRevenue: 15420,
  activeUsers: 1234
};

const mockPopularEvents = [
  {
    id: '1',
    title: 'Lakers vs Warriors',
    date: '2024-03-20',
    venue: 'Crypto.com Arena',
    sport: 'NBA',
    minPrice: 299,
    popularity: 95,
    image: '/images/events/lakers-warriors.jpg'
  },
  {
    id: '2',
    title: 'Chiefs vs Bills',
    date: '2024-03-22',
    venue: 'Arrowhead Stadium',
    sport: 'NFL',
    minPrice: 450,
    popularity: 98,
    image: '/images/events/chiefs-bills.jpg'
  },
  {
    id: '3',
    title: 'Yankees vs Red Sox',
    date: '2024-04-15',
    venue: 'Yankee Stadium',
    sport: 'MLB',
    minPrice: 125,
    popularity: 88,
    image: '/images/events/yankees-redsox.jpg'
  }
];

const mockRecentActivity = [
  {
    id: '1',
    type: 'price_drop',
    event: 'Lakers vs Warriors',
    oldPrice: 320,
    newPrice: 299,
    timestamp: new Date(Date.now() - 300000) // 5 minutes ago
  },
  {
    id: '2',
    type: 'alert_triggered',
    event: 'Chiefs vs Bills',
    targetPrice: 400,
    currentPrice: 395,
    timestamp: new Date(Date.now() - 600000) // 10 minutes ago
  },
  {
    id: '3',
    type: 'new_listing',
    event: 'Yankees vs Red Sox',
    platform: 'StubHub',
    price: 125,
    timestamp: new Date(Date.now() - 900000) // 15 minutes ago
  }
];

interface MetricCardProps {
  title: string;
  value: string | number;
  change?: number;
  icon: React.ComponentType<{ className?: string }>;
  color: string;
  description?: string;
}

function MetricCard({ title, value, change, icon: Icon, color, description }: MetricCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <Card className="p-6 hover:shadow-lg transition-shadow">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-600">{title}</p>
            <p className="text-2xl font-bold text-gray-900 mt-1">
              {typeof value === 'number' ? value.toLocaleString() : value}
            </p>
            {description && (
              <p className="text-xs text-gray-500 mt-1">{description}</p>
            )}
          </div>
          <div className={`p-3 rounded-full ${color}`}>
            <Icon className="w-6 h-6 text-white" />
          </div>
        </div>
        
        {change !== undefined && (
          <div className="mt-4 flex items-center">
            <div className={`flex items-center ${change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
              {change >= 0 ? (
                <TrendingUp className="w-4 h-4 mr-1" />
              ) : (
                <TrendingDown className="w-4 h-4 mr-1" />
              )}
              <span className="text-sm font-medium">
                {Math.abs(change)}%
              </span>
            </div>
            <span className="text-sm text-gray-500 ml-2">vs last week</span>
          </div>
        )}
      </Card>
    </motion.div>
  );
}

function PopularEventsCarousel() {
  const [currentIndex, setCurrentIndex] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % mockPopularEvents.length);
    }, 5000);

    return () => clearInterval(timer);
  }, []);

  return (
    <Card className="p-6">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-semibold text-gray-900">Popular Events</h3>
        <div className="flex space-x-2">
          {mockPopularEvents.map((_, index) => (
            <button
              key={index}
              className={`w-2 h-2 rounded-full transition-colors ${
                index === currentIndex ? 'bg-blue-600' : 'bg-gray-300'
              }`}
              onClick={() => setCurrentIndex(index)}
            />
          ))}
        </div>
      </div>

      <AnimatePresence mode="wait">
        <motion.div
          key={currentIndex}
          initial={{ opacity: 0, x: 50 }}
          animate={{ opacity: 1, x: 0 }}
          exit={{ opacity: 0, x: -50 }}
          transition={{ duration: 0.3 }}
          className="space-y-4"
        >
          {mockPopularEvents.map((event, index) => (
            <motion.div
              key={event.id}
              className={`p-4 rounded-lg border transition-all ${
                index === currentIndex ? 'border-blue-200 bg-blue-50' : 'border-gray-200'
              }`}
            >
              <div className="flex items-center justify-between">
                <div className="flex-1">
                  <h4 className="font-semibold text-gray-900">{event.title}</h4>
                  <div className="flex items-center text-sm text-gray-600 mt-1">
                    <Calendar className="w-4 h-4 mr-1" />
                    {event.date}
                    <MapPin className="w-4 h-4 ml-3 mr-1" />
                    {event.venue}
                  </div>
                </div>
                <div className="text-right">
                  <Badge variant="primary" className="mb-2">
                    {event.sport}
                  </Badge>
                  <div className="text-lg font-bold text-green-600">
                    From ${event.minPrice}
                  </div>
                  <div className="flex items-center text-sm text-gray-500">
                    <Star className="w-3 h-3 mr-1 fill-yellow-400 text-yellow-400" />
                    {event.popularity}% popular
                  </div>
                </div>
              </div>
            </motion.div>
          ))}
        </motion.div>
      </AnimatePresence>
    </Card>
  );
}

function RecentActivityFeed() {
  const getActivityIcon = (type: string) => {
    switch (type) {
      case 'price_drop':
        return <TrendingDown className="w-4 h-4 text-green-600" />;
      case 'alert_triggered':
        return <Bell className="w-4 h-4 text-blue-600" />;
      case 'new_listing':
        return <Zap className="w-4 h-4 text-purple-600" />;
      default:
        return <Activity className="w-4 h-4 text-gray-600" />;
    }
  };

  const getActivityMessage = (activity: any) => {
    switch (activity.type) {
      case 'price_drop':
        return `Price dropped from $${activity.oldPrice} to $${activity.newPrice}`;
      case 'alert_triggered':
        return `Alert triggered! Price dropped to $${activity.currentPrice}`;
      case 'new_listing':
        return `New listing on ${activity.platform} for $${activity.price}`;
      default:
        return 'Unknown activity';
    }
  };

  const formatTimestamp = (timestamp: Date) => {
    const now = new Date();
    const diff = now.getTime() - timestamp.getTime();
    const minutes = Math.floor(diff / 60000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    return timestamp.toLocaleDateString();
  };

  return (
    <Card className="p-6">
      <h3 className="text-lg font-semibold text-gray-900 mb-6">Recent Activity</h3>
      
      <div className="space-y-4">
        {mockRecentActivity.map((activity) => (
          <motion.div
            key={activity.id}
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div className="flex-shrink-0 mt-1">
              {getActivityIcon(activity.type)}
            </div>
            <div className="flex-1 min-w-0">
              <p className="font-medium text-gray-900">{activity.event}</p>
              <p className="text-sm text-gray-600">{getActivityMessage(activity)}</p>
            </div>
            <div className="flex-shrink-0 text-xs text-gray-500">
              {formatTimestamp(activity.timestamp)}
            </div>
          </motion.div>
        ))}
      </div>

      <Button variant="ghost" className="w-full mt-4">
        View All Activity
      </Button>
    </Card>
  );
}

function PlatformStatusWidget() {
  const [platforms] = useState([
    { name: 'StubHub', status: 'online', responseTime: 234 },
    { name: 'Ticketmaster', status: 'online', responseTime: 187 },
    { name: 'Vivid Seats', status: 'slow', responseTime: 892 },
    { name: 'SeatGeek', status: 'offline', responseTime: 0 },
    { name: 'TicketCity', status: 'online', responseTime: 156 }
  ]);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'online': return 'text-green-600';
      case 'slow': return 'text-yellow-600';
      case 'offline': return 'text-red-600';
      default: return 'text-gray-600';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'online': return <Wifi className="w-4 h-4" />;
      case 'slow': return <Activity className="w-4 h-4" />;
      case 'offline': return <WifiOff className="w-4 h-4" />;
      default: return <Globe className="w-4 h-4" />;
    }
  };

  return (
    <Card className="p-6">
      <h3 className="text-lg font-semibold text-gray-900 mb-6">Platform Status</h3>
      
      <div className="space-y-4">
        {platforms.map((platform) => (
          <div key={platform.name} className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className={getStatusColor(platform.status)}>
                {getStatusIcon(platform.status)}
              </div>
              <span className="font-medium text-gray-900">{platform.name}</span>
            </div>
            <div className="text-right">
              <Badge 
                variant={platform.status === 'online' ? 'success' : platform.status === 'slow' ? 'warning' : 'danger'}
                className="mb-1"
              >
                {platform.status}
              </Badge>
              {platform.responseTime > 0 && (
                <div className="text-xs text-gray-500">
                  {platform.responseTime}ms
                </div>
              )}
            </div>
          </div>
        ))}
      </div>
    </Card>
  );
}

export function EnhancedDashboard() {
  return (
    <div className="space-y-8">
      {/* Metrics Overview */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Tickets Monitored"
          value={mockMetrics.totalTicketsMonitored}
          change={12.5}
          icon={Target}
          color="bg-blue-600"
          description="Across all platforms"
        />
        <MetricCard
          title="Average Price"
          value={`$${mockMetrics.averagePrice}`}
          change={mockMetrics.priceChangePercent}
          icon={DollarSign}
          color="bg-green-600"
          description="Last 7 days"
        />
        <MetricCard
          title="Active Alerts"
          value={mockMetrics.activeAlerts}
          icon={Bell}
          color="bg-purple-600"
          description="Price monitoring"
        />
        <MetricCard
          title="Platforms Online"
          value={`${mockMetrics.platformsOnline}/${mockMetrics.totalPlatforms}`}
          icon={Globe}
          color="bg-orange-600"
          description="System status"
        />
      </div>

      {/* Main Dashboard Content */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column */}
        <div className="lg:col-span-2 space-y-8">
          <PopularEventsCarousel />
          
          {/* Price Trends Chart */}
          <Card className="p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-6">Price Trends</h3>
            <div className="h-64 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg flex items-center justify-center">
              <div className="text-center">
                <BarChart3 className="w-12 h-12 text-gray-400 mx-auto mb-2" />
                <p className="text-gray-600">Interactive price chart will be rendered here</p>
              </div>
            </div>
          </Card>
        </div>

        {/* Right Column */}
        <div className="space-y-8">
          <RecentActivityFeed />
          <PlatformStatusWidget />
        </div>
      </div>
    </div>
  );
}
