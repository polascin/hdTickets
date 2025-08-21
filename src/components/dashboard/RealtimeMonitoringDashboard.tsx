'use client';

import { useState, useEffect } from 'react';
import { Card } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Switch } from '@/components/ui/Switch';
import { 
  Activity as ActivityIcon, 
  TrendingUp as TrendingUpIcon, 
  TrendingDown as TrendingDownIcon, 
  AlertCircle as AlertCircleIcon,
  CheckCircle as CheckCircleIcon,
  Clock as ClockIcon,
  DollarSign as DollarSignIcon
} from 'lucide-react';

interface PriceAlert {
  id: string;
  event: string;
  currentPrice: number;
  targetPrice: number;
  trend: 'up' | 'down' | 'stable';
  status: 'active' | 'triggered' | 'paused';
  platform: string;
}

interface PlatformStatus {
  name: string;
  status: 'online' | 'offline' | 'slow';
  lastUpdate: string;
  ticketsScraped: number;
}

const mockAlerts: PriceAlert[] = [
  {
    id: '1',
    event: 'Lakers vs Warriors',
    currentPrice: 299,
    targetPrice: 250,
    trend: 'down',
    status: 'active',
    platform: 'StubHub',
  },
  {
    id: '2',
    event: 'Chiefs vs Bills',
    currentPrice: 420,
    targetPrice: 400,
    trend: 'down',
    status: 'triggered',
    platform: 'Ticketmaster',
  },
  {
    id: '3',
    event: 'Yankees vs Red Sox',
    currentPrice: 135,
    targetPrice: 120,
    trend: 'up',
    status: 'active',
    platform: 'Vivid Seats',
  },
];

const mockPlatforms: PlatformStatus[] = [
  {
    name: 'StubHub',
    status: 'online',
    lastUpdate: '2 min ago',
    ticketsScraped: 1247,
  },
  {
    name: 'Ticketmaster',
    status: 'online',
    lastUpdate: '1 min ago',
    ticketsScraped: 892,
  },
  {
    name: 'Vivid Seats',
    status: 'slow',
    lastUpdate: '5 min ago',
    ticketsScraped: 634,
  },
  {
    name: 'SeatGeek',
    status: 'offline',
    lastUpdate: '15 min ago',
    ticketsScraped: 0,
  },
];

export function RealtimeMonitoringDashboard() {
  const [alerts, setAlerts] = useState(mockAlerts);
  const [platforms] = useState(mockPlatforms);
  const [isMonitoring, setIsMonitoring] = useState(true);
  const [currentTime, setCurrentTime] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentTime(new Date());
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'online': 
      case 'active':
        return 'text-green-600 bg-green-100';
      case 'slow':
      case 'paused':
        return 'text-yellow-600 bg-yellow-100';
      case 'offline':
        return 'text-red-600 bg-red-100';
      case 'triggered':
        return 'text-blue-600 bg-blue-100';
      default:
        return 'text-gray-600 bg-gray-100';
    }
  };

  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'up':
        return <TrendingUpIcon className="h-4 w-4 text-red-500" />;
      case 'down':
        return <TrendingDownIcon className="h-4 w-4 text-green-500" />;
      default:
        return <ActivityIcon className="h-4 w-4 text-gray-500" />;
    }
  };

  const activeAlerts = alerts.filter(alert => alert.status === 'active').length;
  const triggeredAlerts = alerts.filter(alert => alert.status === 'triggered').length;
  const onlinePlatforms = platforms.filter(platform => platform.status === 'online').length;
  const totalTicketsScraped = platforms.reduce((sum, platform) => sum + platform.ticketsScraped, 0);

  return (
    <div className="space-y-6">
      {/* Status Header */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card className="p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <AlertCircleIcon className="h-8 w-8 text-blue-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Active Alerts</p>
              <p className="text-2xl font-bold text-gray-900">{activeAlerts}</p>
            </div>
          </div>
        </Card>

        <Card className="p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <CheckCircleIcon className="h-8 w-8 text-green-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Triggered Today</p>
              <p className="text-2xl font-bold text-gray-900">{triggeredAlerts}</p>
            </div>
          </div>
        </Card>

        <Card className="p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <ActivityIcon className="h-8 w-8 text-purple-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Platforms Online</p>
              <p className="text-2xl font-bold text-gray-900">{onlinePlatforms}/4</p>
            </div>
          </div>
        </Card>

        <Card className="p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <DollarSignIcon className="h-8 w-8 text-yellow-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Tickets Monitored</p>
              <p className="text-2xl font-bold text-gray-900">{totalTicketsScraped.toLocaleString()}</p>
            </div>
          </div>
        </Card>
      </div>

      {/* Monitoring Controls */}
      <Card className="p-6">
        <div className="flex items-center justify-between">
          <div>
            <h3 className="text-lg font-medium text-gray-900">Real-time Monitoring</h3>
            <p className="text-sm text-gray-600">
              Last updated: {currentTime.toLocaleTimeString()}
            </p>
          </div>
          <div className="flex items-center space-x-4">
            <span className="text-sm text-gray-600">
              {isMonitoring ? 'Monitoring Active' : 'Monitoring Paused'}
            </span>
            <Switch
              checked={isMonitoring}
              onChange={setIsMonitoring}
            />
          </div>
        </div>
      </Card>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Price Alerts */}
        <Card className="p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Price Alerts</h3>
          <div className="space-y-4">
            {alerts.map((alert) => (
              <div key={alert.id} className="border rounded-lg p-4">
                <div className="flex items-center justify-between mb-2">
                  <h4 className="font-medium text-gray-900">{alert.event}</h4>
                  <Badge className={getStatusColor(alert.status)}>
                    {alert.status}
                  </Badge>
                </div>
                
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="text-gray-600">Current:</span>
                    <div className="flex items-center">
                      <span className="font-medium">${alert.currentPrice}</span>
                      {getTrendIcon(alert.trend)}
                    </div>
                  </div>
                  <div>
                    <span className="text-gray-600">Target:</span>
                    <div className="font-medium">${alert.targetPrice}</div>
                  </div>
                </div>
                
                <div className="mt-2 text-xs text-gray-500">
                  {alert.platform}
                </div>
              </div>
            ))}
          </div>
        </Card>

        {/* Platform Status */}
        <Card className="p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Platform Status</h3>
          <div className="space-y-4">
            {platforms.map((platform) => (
              <div key={platform.name} className="flex items-center justify-between p-4 border rounded-lg">
                <div>
                  <h4 className="font-medium text-gray-900">{platform.name}</h4>
                  <div className="flex items-center text-sm text-gray-600">
                    <ClockIcon className="h-4 w-4 mr-1" />
                    {platform.lastUpdate}
                  </div>
                </div>
                
                <div className="text-right">
                  <Badge className={getStatusColor(platform.status)}>
                    {platform.status}
                  </Badge>
                  <div className="text-sm text-gray-600 mt-1">
                    {platform.ticketsScraped.toLocaleString()} tickets
                  </div>
                </div>
              </div>
            ))}
          </div>
        </Card>
      </div>

      {/* Recent Activity */}
      <Card className="p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
        <div className="space-y-3">
          <div className="flex items-center text-sm">
            <div className="flex-shrink-0 w-2 h-2 bg-green-400 rounded-full mr-3"></div>
            <span className="text-gray-600">Price alert triggered for Chiefs vs Bills - Target price reached</span>
            <span className="ml-auto text-gray-400">2 min ago</span>
          </div>
          <div className="flex items-center text-sm">
            <div className="flex-shrink-0 w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
            <span className="text-gray-600">Started monitoring Lakers vs Warriors on StubHub</span>
            <span className="ml-auto text-gray-400">5 min ago</span>
          </div>
          <div className="flex items-center text-sm">
            <div className="flex-shrink-0 w-2 h-2 bg-yellow-400 rounded-full mr-3"></div>
            <span className="text-gray-600">SeatGeek platform experiencing delays</span>
            <span className="ml-auto text-gray-400">8 min ago</span>
          </div>
          <div className="flex items-center text-sm">
            <div className="flex-shrink-0 w-2 h-2 bg-green-400 rounded-full mr-3"></div>
            <span className="text-gray-600">Successfully scraped 1,247 tickets from Ticketmaster</span>
            <span className="ml-auto text-gray-400">12 min ago</span>
          </div>
        </div>
      </Card>
    </div>
  );
}
