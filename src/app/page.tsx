'use client';

import { useState } from 'react';
import { EnhancedDashboard } from '@/components/dashboard/EnhancedDashboard';
import { TicketDiscoveryDashboard } from '@/components/dashboard/TicketDiscoveryDashboard';
import { RealtimeMonitoringDashboard } from '@/components/dashboard/RealtimeMonitoringDashboard';
import { motion } from 'framer-motion';

export default function HomePage() {
  const [activeTab, setActiveTab] = useState<'dashboard' | 'discovery' | 'monitoring'>('dashboard');

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      <div className="container mx-auto px-4 py-6 md:py-8">
        {/* Hero Section */}
        <motion.div 
          className="text-center mb-8 md:mb-12"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
        >
          <h1 className="text-3xl md:text-5xl font-bold text-gray-900 mb-3 md:mb-4">
            Welcome to <span className="text-blue-600">HD Tickets</span>
          </h1>
          <p className="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto mb-6 md:mb-8">
            Your comprehensive sports event ticket monitoring and discovery platform.
            Track prices, discover events, and never miss the perfect ticket deal.
          </p>
        </motion.div>

        {/* Tab Navigation */}
        <motion.div 
          className="flex justify-center mb-8"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.2 }}
        >
          <nav className="flex space-x-1 bg-white rounded-xl p-1 shadow-sm border border-gray-200">
            <button
              onClick={() => setActiveTab('dashboard')}
              className={`px-4 md:px-6 py-2 md:py-3 rounded-lg font-medium transition-all duration-200 ${
                activeTab === 'dashboard'
                  ? 'bg-blue-600 text-white shadow-sm transform scale-105'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
              }`}
            >
              Dashboard
            </button>
            <button
              onClick={() => setActiveTab('discovery')}
              className={`px-4 md:px-6 py-2 md:py-3 rounded-lg font-medium transition-all duration-200 ${
                activeTab === 'discovery'
                  ? 'bg-blue-600 text-white shadow-sm transform scale-105'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
              }`}
            >
              Ticket Discovery
            </button>
            <button
              onClick={() => setActiveTab('monitoring')}
              className={`px-4 md:px-6 py-2 md:py-3 rounded-lg font-medium transition-all duration-200 ${
                activeTab === 'monitoring'
                  ? 'bg-blue-600 text-white shadow-sm transform scale-105'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
              }`}
            >
              Real-time Monitoring
            </button>
          </nav>
        </motion.div>

        {/* Content Area */}
        <motion.main
          key={activeTab}
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          exit={{ opacity: 0, x: -20 }}
          transition={{ duration: 0.4 }}
          className="mb-20 md:mb-8" // Extra bottom margin for mobile navigation
        >
          {activeTab === 'dashboard' && <EnhancedDashboard />}
          {activeTab === 'discovery' && <TicketDiscoveryDashboard />}
          {activeTab === 'monitoring' && <RealtimeMonitoringDashboard />}
        </main>
      </div>
    </div>
  );
}
