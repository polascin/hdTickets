'use client';

import { useState } from 'react';
import { Card } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { Select } from '@/components/ui/Select';
import { Badge } from '@/components/ui/Badge';
import { Search as SearchIcon, Filter as FilterIcon, Map as MapIcon, List as ListIcon, Grid3X3 as GridIcon } from 'lucide-react';

interface Ticket {
  id: string;
  event: string;
  sport: string;
  venue: string;
  date: string;
  price: number;
  platform: string;
  availability: 'available' | 'limited' | 'sold-out';
  section: string;
}

const mockTickets: Ticket[] = [
  {
    id: '1',
    event: 'Lakers vs Warriors',
    sport: 'NBA',
    venue: 'Crypto.com Arena',
    date: '2024-03-15',
    price: 299,
    platform: 'StubHub',
    availability: 'available',
    section: 'Section 101',
  },
  {
    id: '2',
    event: 'Chiefs vs Bills',
    sport: 'NFL',
    venue: 'Arrowhead Stadium',
    date: '2024-03-20',
    price: 450,
    platform: 'Ticketmaster',
    availability: 'limited',
    section: 'Section 200',
  },
  {
    id: '3',
    event: 'Yankees vs Red Sox',
    sport: 'MLB',
    venue: 'Yankee Stadium',
    date: '2024-04-10',
    price: 125,
    platform: 'Vivid Seats',
    availability: 'available',
    section: 'Bleachers',
  },
];

export function TicketDiscoveryDashboard() {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedSport, setSelectedSport] = useState('all');
  const [viewMode, setViewMode] = useState<'grid' | 'list' | 'map'>('grid');
  const [sortBy, setSortBy] = useState('price');

  const filteredTickets = mockTickets.filter(ticket => {
    const matchesSearch = ticket.event.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         ticket.venue.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesSport = selectedSport === 'all' || ticket.sport === selectedSport;
    return matchesSearch && matchesSport;
  });

  const getAvailabilityColor = (availability: string) => {
    switch (availability) {
      case 'available': return 'text-green-600 bg-green-100';
      case 'limited': return 'text-yellow-600 bg-yellow-100';
      case 'sold-out': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getSportColor = (sport: string) => {
    switch (sport) {
      case 'NBA': return 'text-orange-600 bg-orange-100';
      case 'NFL': return 'text-blue-600 bg-blue-100';
      case 'MLB': return 'text-red-600 bg-red-100';
      case 'NHL': return 'text-purple-600 bg-purple-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  return (
    <div className="space-y-6">
      {/* Search and Filter Header */}
      <Card className="p-6">
        <div className="flex flex-col lg:flex-row gap-4">
          <div className="flex-1">
            <div className="relative">
              <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5" />
              <Input
                placeholder="Search events, teams, or venues..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>
          
          <div className="flex gap-3">
            <Select
              value={selectedSport}
              onChange={(value) => setSelectedSport(value)}
              options={[
                { value: 'all', label: 'All Sports' },
                { value: 'NBA', label: 'Basketball' },
                { value: 'NFL', label: 'Football' },
                { value: 'MLB', label: 'Baseball' },
                { value: 'NHL', label: 'Hockey' },
              ]}
            />
            
            <Select
              value={sortBy}
              onChange={(value) => setSortBy(value)}
              options={[
                { value: 'price', label: 'Price' },
                { value: 'date', label: 'Date' },
                { value: 'popularity', label: 'Popularity' },
              ]}
            />
            
            <div className="flex border rounded-lg">
              <Button
                variant={viewMode === 'grid' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setViewMode('grid')}
                className="rounded-r-none"
              >
                <GridIcon className="h-4 w-4" />
              </Button>
              <Button
                variant={viewMode === 'list' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setViewMode('list')}
                className="rounded-none border-x-0"
              >
                <ListIcon className="h-4 w-4" />
              </Button>
              <Button
                variant={viewMode === 'map' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setViewMode('map')}
                className="rounded-l-none"
              >
                <MapIcon className="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </Card>

      {/* Results */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredTickets.map((ticket) => (
          <Card key={ticket.id} className="p-6 hover:shadow-lg transition-shadow">
            <div className="space-y-4">
              <div className="flex justify-between items-start">
                <div>
                  <h3 className="font-semibold text-lg text-gray-900">{ticket.event}</h3>
                  <p className="text-gray-600">{ticket.venue}</p>
                </div>
                <Badge className={getSportColor(ticket.sport)}>
                  {ticket.sport}
                </Badge>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-gray-600">Date:</span>
                  <span className="font-medium">{ticket.date}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Section:</span>
                  <span className="font-medium">{ticket.section}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Platform:</span>
                  <span className="font-medium">{ticket.platform}</span>
                </div>
              </div>
              
              <div className="flex justify-between items-center">
                <div className="text-2xl font-bold text-green-600">
                  ${ticket.price}
                </div>
                <Badge className={getAvailabilityColor(ticket.availability)}>
                  {ticket.availability}
                </Badge>
              </div>
              
              <Button className="w-full" disabled={ticket.availability === 'sold-out'}>
                {ticket.availability === 'sold-out' ? 'Sold Out' : 'View Details'}
              </Button>
            </div>
          </Card>
        ))}
      </div>

      {filteredTickets.length === 0 && (
        <Card className="p-12 text-center">
          <div className="text-gray-500">
            <SearchIcon className="h-12 w-12 mx-auto mb-4 opacity-50" />
            <h3 className="text-lg font-medium mb-2">No tickets found</h3>
            <p>Try adjusting your search criteria or filters</p>
          </div>
        </Card>
      )}
    </div>
  );
}
