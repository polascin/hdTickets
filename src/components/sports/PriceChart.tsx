'use client';

import { useState, useEffect, useRef, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  LineChart, 
  Line, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  ResponsiveContainer,
  Area,
  AreaChart,
  ReferenceLine,
  Brush
} from 'recharts';
import { Card } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { Select } from '@/components/ui/Select';
import type { PricePoint, Platform, Money } from '@/types';
import { 
  TrendingUp, 
  TrendingDown, 
  DollarSign, 
  Clock, 
  Target,
  Maximize2,
  Minimize2,
  RefreshCw
} from 'lucide-react';

interface PriceChartProps {
  ticketId: string;
  priceHistory: PricePoint[];
  currentPrice: Money;
  targetPrice?: Money;
  platforms: Platform[];
  className?: string;
  variant?: 'mini' | 'standard' | 'detailed';
  onTargetPriceSet?: (price: number) => void;
}

interface ChartDataPoint {
  timestamp: string;
  price: number;
  platform: string;
  date: Date;
  formattedTime: string;
  formattedDate: string;
}

export function PriceChart({
  ticketId,
  priceHistory,
  currentPrice,
  targetPrice,
  platforms,
  className = '',
  variant = 'standard',
  onTargetPriceSet
}: PriceChartProps) {
  const [timeRange, setTimeRange] = useState<'1h' | '6h' | '24h' | '3d' | '7d' | 'all'>('24h');
  const [selectedPlatforms, setSelectedPlatforms] = useState<string[]>(platforms.map(p => p.id));
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const chartRef = useRef<HTMLDivElement>(null);

  // Filter and transform data
  const chartData = useMemo(() => {
    const now = new Date();
    const timeRanges = {
      '1h': 1000 * 60 * 60,
      '6h': 1000 * 60 * 60 * 6,
      '24h': 1000 * 60 * 60 * 24,
      '3d': 1000 * 60 * 60 * 24 * 3,
      '7d': 1000 * 60 * 60 * 24 * 7,
      'all': Infinity
    };

    const cutoffTime = timeRange === 'all' ? 0 : now.getTime() - timeRanges[timeRange];

    return priceHistory
      .filter(point => {
        const pointTime = new Date(point.timestamp).getTime();
        return pointTime >= cutoffTime && selectedPlatforms.includes(point.platform.id);
      })
      .map(point => {
        const date = new Date(point.timestamp);
        return {
          timestamp: point.timestamp,
          price: point.price.amount,
          platform: point.platform.name,
          date,
          formattedTime: date.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
          }),
          formattedDate: date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric' 
          })
        };
      })
      .sort((a, b) => a.date.getTime() - b.date.getTime());
  }, [priceHistory, timeRange, selectedPlatforms]);

  // Calculate price statistics
  const priceStats = useMemo(() => {
    if (chartData.length === 0) return null;

    const prices = chartData.map(d => d.price);
    const minPrice = Math.min(...prices);
    const maxPrice = Math.max(...prices);
    const avgPrice = prices.reduce((sum, price) => sum + price, 0) / prices.length;
    const firstPrice = prices[0];
    const lastPrice = prices[prices.length - 1];
    const priceChange = lastPrice - firstPrice;
    const priceChangePercent = (priceChange / firstPrice) * 100;

    return {
      min: minPrice,
      max: maxPrice,
      avg: avgPrice,
      change: priceChange,
      changePercent: priceChangePercent,
      trend: priceChange >= 0 ? 'up' : 'down'
    };
  }, [chartData]);

  const CustomTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      const data = payload[0].payload;
      return (
        <div className="bg-white border border-gray-200 rounded-lg shadow-lg p-3">
          <p className="font-medium text-gray-900">
            ${payload[0].value.toFixed(2)}
          </p>
          <p className="text-sm text-gray-600">{data.platform}</p>
          <p className="text-xs text-gray-500">
            {data.formattedDate} at {data.formattedTime}
          </p>
        </div>
      );
    }
    return null;
  };

  const handleRefresh = async () => {
    setIsRefreshing(true);
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    setIsRefreshing(false);
  };

  const chartHeight = variant === 'mini' ? 200 : variant === 'standard' ? 300 : 400;

  return (
    <Card className={`${className} ${isFullscreen ? 'fixed inset-4 z-50' : ''}`}>
      <div className="p-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <DollarSign className="w-5 h-5 text-green-600" />
            <div>
              <h3 className="font-semibold text-gray-900">Price History</h3>
              <p className="text-sm text-gray-600">
                Current: ${currentPrice.amount.toFixed(2)}
                {priceStats && (
                  <span className={`ml-2 ${priceStats.trend === 'up' ? 'text-red-600' : 'text-green-600'}`}>
                    {priceStats.trend === 'up' ? <TrendingUp className="w-4 h-4 inline" /> : <TrendingDown className="w-4 h-4 inline" />}
                    {Math.abs(priceStats.changePercent).toFixed(1)}%
                  </span>
                )}
              </p>
            </div>
          </div>

          <div className="flex items-center space-x-2">
            <Button
              variant="ghost"
              size="sm"
              onClick={handleRefresh}
              disabled={isRefreshing}
            >
              <RefreshCw className={`w-4 h-4 ${isRefreshing ? 'animate-spin' : ''}`} />
            </Button>
            
            {variant !== 'mini' && (
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setIsFullscreen(!isFullscreen)}
              >
                {isFullscreen ? <Minimize2 className="w-4 h-4" /> : <Maximize2 className="w-4 h-4" />}
              </Button>
            )}
          </div>
        </div>

        {variant !== 'mini' && (
          <div className="flex items-center justify-between mt-4 space-x-4">
            <div className="flex items-center space-x-2">
              <Select
                value={timeRange}
                onChange={(value) => setTimeRange(value as any)}
                options={[
                  { value: '1h', label: '1 Hour' },
                  { value: '6h', label: '6 Hours' },
                  { value: '24h', label: '24 Hours' },
                  { value: '3d', label: '3 Days' },
                  { value: '7d', label: '7 Days' },
                  { value: 'all', label: 'All Time' }
                ]}
              />
            </div>

            {priceStats && (
              <div className="flex items-center space-x-4 text-sm">
                <div className="text-center">
                  <div className="font-medium text-green-600">${priceStats.min.toFixed(2)}</div>
                  <div className="text-xs text-gray-500">Low</div>
                </div>
                <div className="text-center">
                  <div className="font-medium text-gray-900">${priceStats.avg.toFixed(2)}</div>
                  <div className="text-xs text-gray-500">Avg</div>
                </div>
                <div className="text-center">
                  <div className="font-medium text-red-600">${priceStats.max.toFixed(2)}</div>
                  <div className="text-xs text-gray-500">High</div>
                </div>
              </div>
            )}
          </div>
        )}
      </div>

      <div className="p-4">
        <div ref={chartRef} style={{ height: chartHeight }}>
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={chartData}>
              <defs>
                <linearGradient id="priceGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#3B82F6" stopOpacity={0.3}/>
                  <stop offset="95%" stopColor="#3B82F6" stopOpacity={0}/>
                </linearGradient>
              </defs>
              
              <CartesianGrid strokeDasharray="3 3" stroke="#E5E7EB" />
              <XAxis 
                dataKey="formattedTime"
                tick={{ fontSize: 12 }}
                tickLine={false}
                axisLine={false}
              />
              <YAxis 
                domain={['dataMin - 10', 'dataMax + 10']}
                tick={{ fontSize: 12 }}
                tickLine={false}
                axisLine={false}
                tickFormatter={(value) => `$${value}`}
              />
              <Tooltip content={<CustomTooltip />} />
              
              {targetPrice && (
                <ReferenceLine 
                  y={targetPrice.amount} 
                  stroke="#EF4444" 
                  strokeDasharray="5 5"
                  label={{ value: "Target", position: "topRight" }}
                />
              )}
              
              <Area
                type="monotone"
                dataKey="price"
                stroke="#3B82F6"
                strokeWidth={2}
                fill="url(#priceGradient)"
                dot={{ fill: '#3B82F6', strokeWidth: 2, r: 3 }}
                activeDot={{ r: 5, stroke: '#3B82F6', strokeWidth: 2 }}
              />
              
              {variant === 'detailed' && (
                <Brush 
                  dataKey="formattedTime" 
                  height={30} 
                  stroke="#3B82F6"
                />
              )}
            </AreaChart>
          </ResponsiveContainer>
        </div>

        {variant !== 'mini' && platforms.length > 1 && (
          <div className="mt-4 pt-4 border-t border-gray-200">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-gray-700">Platforms:</span>
              <div className="flex flex-wrap gap-2">
                {platforms.map(platform => (
                  <Badge
                    key={platform.id}
                    variant={selectedPlatforms.includes(platform.id) ? 'primary' : 'secondary'}
                    className="cursor-pointer text-xs"
                    onClick={() => {
                      if (selectedPlatforms.includes(platform.id)) {
                        setSelectedPlatforms(prev => prev.filter(id => id !== platform.id));
                      } else {
                        setSelectedPlatforms(prev => [...prev, platform.id]);
                      }
                    }}
                  >
                    {platform.name}
                  </Badge>
                ))}
              </div>
            </div>
          </div>
        )}

        {onTargetPriceSet && variant !== 'mini' && (
          <div className="mt-4 pt-4 border-t border-gray-200">
            <div className="flex items-center space-x-2">
              <Target className="w-4 h-4 text-gray-600" />
              <span className="text-sm text-gray-600">
                {targetPrice ? `Target: $${targetPrice.amount}` : 'Set price alert'}
              </span>
              <Button
                variant="outline"
                size="sm"
                onClick={() => onTargetPriceSet(currentPrice.amount * 0.9)}
              >
                {targetPrice ? 'Update' : 'Set Alert'}
              </Button>
            </div>
          </div>
        )}
      </div>
    </Card>
  );
}
