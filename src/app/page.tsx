'use client';

import { useState } from 'react';
import { TicketDiscoveryDashboard } from '@/features/TicketDiscovery/TicketDiscoveryDashboard';
import { RealTimeMonitoringDashboard } from '@/features/RealTimeMonitoring/RealTimeMonitoringDashboard';

export default function HomePage() {
  const [activeTab, setActiveTab] = useState<'discovery' | 'monitoring'>('discovery');

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8">
        <header className="mb-8">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-4xl font-bold text-gray-900 mb-2">
                HD Tickets
              </h1>
              <p className="text-lg text-gray-600">
                Professional Sports Event Ticket Monitoring Platform
              </p>
            </div>
            
            <nav className="flex space-x-1 bg-white rounded-lg p-1 shadow-sm">
              <button
                onClick={() => setActiveTab('discovery')}
                className={`px-6 py-2 rounded-md font-medium transition-colors ${
                  activeTab === 'discovery'
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                }`}
              >
                Ticket Discovery
              </button>
              <button
                onClick={() => setActiveTab('monitoring')}
                className={`px-6 py-2 rounded-md font-medium transition-colors ${
                  activeTab === 'monitoring'
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                }`}
              >
                Real-time Monitoring
              </button>
            </nav>
          </div>
        </header>

        <main>
          {activeTab === 'discovery' && <TicketDiscoveryDashboard />}
          {activeTab === 'monitoring' && <RealTimeMonitoringDashboard />}
        </main>
      </div>
    </div>
  );
}
