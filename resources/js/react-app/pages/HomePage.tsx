/**
 * HD Tickets - Home Page
 * Modern sports ticketing platform homepage with hero section and featured events
 */

import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  MagnifyingGlassIcon,
  CalendarDaysIcon,
  MapPinIcon,
  TicketIcon,
  TrendingUpIcon,
  FireIcon,
  SparklesIcon
} from '@heroicons/react/24/outline';
import { format } from 'date-fns';

import { useAppSelector } from '../store/store';
import { SportsEvent, Sport } from '../types';

import SearchBar from '../components/features/SearchBar';
import EventCard from '../components/events/EventCard';
import SportCategoryCard from '../components/sports/SportCategoryCard';
import StatsCard from '../components/ui/StatsCard';
import Button from '../components/ui/Button';
import LoadingSpinner from '../components/ui/LoadingSpinner';

const HomePage: React.FC = () => {
  const navigate = useNavigate();
  const [featuredEvents, setFeaturedEvents] = useState<SportsEvent[]>([]);
  const [popularSports, setPopularSports] = useState<Sport[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Mock data - replace with actual API calls
  useEffect(() => {
    // Simulate API loading
    const timer = setTimeout(() => {
      setFeaturedEvents(mockFeaturedEvents);
      setPopularSports(mockPopularSports);
      setIsLoading(false);
    }, 1000);

    return () => clearTimeout(timer);
  }, []);

  const handleSearch = (query: string, filters: any) => {
    navigate('/events', { 
      state: { 
        search: query, 
        filters 
      } 
    });
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <HeroSection onSearch={handleSearch} />

      {/* Statistics Section */}
      <StatsSection />

      {/* Popular Sports Categories */}
      <SportsSection sports={popularSports} />

      {/* Featured Events */}
      <FeaturedEventsSection events={featuredEvents} />

      {/* How It Works Section */}
      <HowItWorksSection />

      {/* CTA Section */}
      <CTASection />
    </div>
  );
};

// Hero Section Component
const HeroSection: React.FC<{ onSearch: (query: string, filters: any) => void }> = ({ onSearch }) => {
  return (
    <section className="relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white overflow-hidden">
      {/* Background pattern */}
      <div className="absolute inset-0 bg-[url('/images/hero-pattern.svg')] opacity-10"></div>
      
      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          className="text-center"
        >
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 bg-gradient-to-r from-white to-blue-200 bg-clip-text text-transparent">
            Find Your Perfect
            <br />
            <span className="text-yellow-400">Sports Tickets</span>
          </h1>
          
          <p className="text-xl md:text-2xl mb-12 text-blue-100 max-w-3xl mx-auto leading-relaxed">
            Discover the best deals on sports event tickets from multiple platforms. 
            Compare prices, track availability, and never miss your favorite games.
          </p>

          {/* Search Bar */}
          <div className="max-w-2xl mx-auto mb-12">
            <SearchBar 
              onSearch={onSearch}
              placeholder="Search for teams, events, or venues..."
              className="bg-white/95 backdrop-blur-sm"
            />
          </div>

          {/* Quick action buttons */}
          <div className="flex flex-wrap gap-4 justify-center">
            <Button 
              variant="primary" 
              size="lg"
              onClick={() => onSearch('', {})}
              className="bg-white text-blue-900 hover:bg-blue-50"
            >
              <TicketIcon className="h-5 w-5 mr-2" />
              Browse All Events
            </Button>
            <Button 
              variant="outline" 
              size="lg"
              className="border-white text-white hover:bg-white hover:text-blue-900"
            >
              <TrendingUpIcon className="h-5 w-5 mr-2" />
              Price Alerts
            </Button>
          </div>
        </motion.div>
      </div>

      {/* Floating elements */}
      <motion.div
        animate={{ 
          y: [0, -10, 0],
          rotate: [0, 5, 0]
        }}
        transition={{ 
          duration: 6,
          repeat: Infinity,
          ease: "easeInOut"
        }}
        className="absolute top-20 right-10 w-20 h-20 bg-yellow-400/20 rounded-full blur-xl"
      />
      <motion.div
        animate={{ 
          y: [0, 15, 0],
          rotate: [0, -3, 0]
        }}
        transition={{ 
          duration: 8,
          repeat: Infinity,
          ease: "easeInOut"
        }}
        className="absolute bottom-20 left-10 w-32 h-32 bg-blue-400/20 rounded-full blur-xl"
      />
    </section>
  );
};

// Statistics Section
const StatsSection: React.FC = () => {
  const stats = [
    { label: 'Events Monitored', value: '50K+', icon: CalendarDaysIcon, color: 'blue' },
    { label: 'Ticket Platforms', value: '25+', icon: TicketIcon, color: 'green' },
    { label: 'Price Comparisons', value: '1M+', icon: TrendingUpIcon, color: 'purple' },
    { label: 'Happy Customers', value: '100K+', icon: SparklesIcon, color: 'yellow' },
  ];

  return (
    <section className="py-16 bg-white dark:bg-gray-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
          {stats.map((stat, index) => (
            <motion.div
              key={stat.label}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              viewport={{ once: true }}
            >
              <StatsCard {...stat} />
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
};

// Sports Categories Section
const SportsSection: React.FC<{ sports: Sport[] }> = ({ sports }) => {
  return (
    <section className="py-16 bg-gray-50 dark:bg-gray-900">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="text-center mb-12"
        >
          <h2 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
            Popular Sports
          </h2>
          <p className="text-xl text-gray-600 dark:text-gray-300">
            Find tickets for your favorite sports and teams
          </p>
        </motion.div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
          {sports.map((sport, index) => (
            <motion.div
              key={sport.id}
              initial={{ opacity: 0, scale: 0.8 }}
              whileInView={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.5, delay: index * 0.1 }}
              viewport={{ once: true }}
            >
              <SportCategoryCard sport={sport} />
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
};

// Featured Events Section
const FeaturedEventsSection: React.FC<{ events: SportsEvent[] }> = ({ events }) => {
  return (
    <section className="py-16 bg-white dark:bg-gray-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="text-center mb-12"
        >
          <div className="flex items-center justify-center mb-4">
            <FireIcon className="h-8 w-8 text-red-500 mr-2" />
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
              Trending Events
            </h2>
          </div>
          <p className="text-xl text-gray-600 dark:text-gray-300">
            Don't miss these popular upcoming events
          </p>
        </motion.div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {events.slice(0, 6).map((event, index) => (
            <motion.div
              key={event.id}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              viewport={{ once: true }}
            >
              <EventCard event={event} />
            </motion.div>
          ))}
        </div>

        <div className="text-center mt-12">
          <Button 
            variant="primary" 
            size="lg"
            onClick={() => window.location.href = '/events'}
          >
            View All Events
          </Button>
        </div>
      </div>
    </section>
  );
};

// How It Works Section
const HowItWorksSection: React.FC = () => {
  const steps = [
    {
      step: 1,
      title: 'Search Events',
      description: 'Find your favorite teams and events using our powerful search',
      icon: MagnifyingGlassIcon,
    },
    {
      step: 2,
      title: 'Compare Prices',
      description: 'We compare prices across multiple ticket platforms automatically',
      icon: TrendingUpIcon,
    },
    {
      step: 3,
      title: 'Set Alerts',
      description: 'Get notified when ticket prices drop or availability changes',
      icon: CalendarDaysIcon,
    },
    {
      step: 4,
      title: 'Buy Tickets',
      description: 'Purchase directly from the best platform at the best price',
      icon: TicketIcon,
    },
  ];

  return (
    <section className="py-16 bg-gray-50 dark:bg-gray-900">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="text-center mb-16"
        >
          <h2 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
            How It Works
          </h2>
          <p className="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            Getting the best sports tickets is easy with our platform
          </p>
        </motion.div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {steps.map((step, index) => (
            <motion.div
              key={step.step}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.2 }}
              viewport={{ once: true }}
              className="relative text-center"
            >
              {/* Connection line */}
              {index < steps.length - 1 && (
                <div className="hidden lg:block absolute top-12 left-full w-full h-px bg-gradient-to-r from-blue-500 to-transparent -translate-x-1/2 z-0" />
              )}
              
              <div className="relative z-10 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                <div className="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                  <step.icon className="h-8 w-8 text-white" />
                </div>
                <div className="absolute -top-3 -right-3 w-8 h-8 bg-yellow-400 text-black rounded-full flex items-center justify-center font-bold text-sm">
                  {step.step}
                </div>
                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                  {step.title}
                </h3>
                <p className="text-gray-600 dark:text-gray-300">
                  {step.description}
                </p>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
};

// CTA Section
const CTASection: React.FC = () => {
  return (
    <section className="py-16 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
      <div className="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          viewport={{ once: true }}
        >
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Ready to Find Your Next Game?
          </h2>
          <p className="text-xl mb-8 text-blue-100">
            Join thousands of sports fans who never miss out on the best ticket deals
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button 
              variant="primary" 
              size="lg"
              className="bg-white text-blue-600 hover:bg-blue-50"
            >
              Start Searching Now
            </Button>
            <Button 
              variant="outline" 
              size="lg"
              className="border-white text-white hover:bg-white hover:text-blue-600"
            >
              Learn More
            </Button>
          </div>
        </motion.div>
      </div>
    </section>
  );
};

// Mock data - replace with real API calls
const mockFeaturedEvents: SportsEvent[] = [
  // Add mock events data here
];

const mockPopularSports: Sport[] = [
  // Add mock sports data here  
];

export default HomePage;