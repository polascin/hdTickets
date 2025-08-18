import { useQuery } from '@tanstack/react-query'
import axios from 'axios'

interface SearchFilters {
  query: string
  sports: string[]
  priceRange: [number, number]
  dateRange: {
    start: Date | null
    end: Date | null
  }
  location: {
    city?: string
    radius?: number
    coordinates?: [number, number]
  }
  availability: 'all' | 'available' | 'limited'
  platforms: string[]
  sortBy: 'date' | 'price' | 'popularity' | 'distance' | 'relevance'
  sortOrder: 'asc' | 'desc'
}

interface TicketEvent {
  id: string
  title: string
  sport: 'nfl' | 'nba' | 'mlb' | 'nhl' | 'mls' | 'tennis' | 'other'
  date: Date
  venue: {
    name: string
    city: string
    state: string
    coordinates: [number, number]
  }
  teams: {
    home: string
    away?: string
  }
  prices: {
    min: number
    max: number
    average: number
    lastUpdated: Date
  }
  availability: {
    total: number
    available: number
    platforms: string[]
  }
  popularity: number
  trending: boolean
  image?: string
  categories: string[]
}

const fetchTicketEvents = async (filters: SearchFilters): Promise<TicketEvent[]> => {
  // In a real application, this would make API calls to your Laravel backend
  // For now, we'll return mock data
  
  // Simulate API delay
  await new Promise(resolve => setTimeout(resolve, 500))
  
  // Mock data for development
  const mockEvents: TicketEvent[] = [
    {
      id: '1',
      title: 'Lakers vs Warriors',
      sport: 'nba',
      date: new Date(Date.now() + 86400000 * 7), // 7 days from now
      venue: {
        name: 'Crypto.com Arena',
        city: 'Los Angeles',
        state: 'CA',
        coordinates: [34.043, -118.267],
      },
      teams: {
        home: 'Los Angeles Lakers',
        away: 'Golden State Warriors',
      },
      prices: {
        min: 85,
        max: 850,
        average: 285,
        lastUpdated: new Date(),
      },
      availability: {
        total: 1200,
        available: 345,
        platforms: ['StubHub', 'Ticketmaster', 'SeatGeek'],
      },
      popularity: 92,
      trending: true,
      image: '/images/lakers-vs-warriors.jpg',
      categories: ['NBA', 'Basketball', 'Playoff'],
    },
    {
      id: '2',
      title: 'Chiefs vs Bills',
      sport: 'nfl',
      date: new Date(Date.now() + 86400000 * 14), // 14 days from now
      venue: {
        name: 'Arrowhead Stadium',
        city: 'Kansas City',
        state: 'MO',
        coordinates: [39.049, -94.484],
      },
      teams: {
        home: 'Kansas City Chiefs',
        away: 'Buffalo Bills',
      },
      prices: {
        min: 125,
        max: 1200,
        average: 385,
        lastUpdated: new Date(),
      },
      availability: {
        total: 800,
        available: 156,
        platforms: ['StubHub', 'Ticketmaster', 'NFL Ticket Exchange'],
      },
      popularity: 88,
      trending: true,
      image: '/images/chiefs-vs-bills.jpg',
      categories: ['NFL', 'Football', 'AFC Championship'],
    },
    {
      id: '3',
      title: 'Dodgers vs Padres',
      sport: 'mlb',
      date: new Date(Date.now() + 86400000 * 3), // 3 days from now
      venue: {
        name: 'Dodger Stadium',
        city: 'Los Angeles',
        state: 'CA',
        coordinates: [34.074, -118.240],
      },
      teams: {
        home: 'Los Angeles Dodgers',
        away: 'San Diego Padres',
      },
      prices: {
        min: 45,
        max: 450,
        average: 125,
        lastUpdated: new Date(),
      },
      availability: {
        total: 2500,
        available: 1230,
        platforms: ['StubHub', 'Ticketmaster', 'SeatGeek', 'MLB.com'],
      },
      popularity: 75,
      trending: false,
      image: '/images/dodgers-vs-padres.jpg',
      categories: ['MLB', 'Baseball', 'NL West'],
    },
    // Add more mock events as needed
  ]
  
  // Apply basic filtering for demonstration
  let filteredEvents = mockEvents.filter(event => {
    // Sports filter
    if (filters.sports.length > 0 && !filters.sports.includes(event.sport)) {
      return false
    }
    
    // Query filter (simple text search)
    if (filters.query) {
      const query = filters.query.toLowerCase()
      const searchText = `${event.title} ${event.teams.home} ${event.teams.away || ''} ${event.venue.name} ${event.venue.city}`.toLowerCase()
      if (!searchText.includes(query)) {
        return false
      }
    }
    
    // Price range filter
    if (event.prices.min > filters.priceRange[1] || event.prices.max < filters.priceRange[0]) {
      return false
    }
    
    // Date range filter
    if (filters.dateRange.start && event.date < filters.dateRange.start) {
      return false
    }
    if (filters.dateRange.end && event.date > filters.dateRange.end) {
      return false
    }
    
    return true
  })
  
  // Apply sorting
  filteredEvents.sort((a, b) => {
    const direction = filters.sortOrder === 'asc' ? 1 : -1
    
    switch (filters.sortBy) {
      case 'date':
        return direction * (a.date.getTime() - b.date.getTime())
      case 'price':
        return direction * (a.prices.min - b.prices.min)
      case 'popularity':
        return direction * (a.popularity - b.popularity)
      default: // relevance
        return direction * (a.popularity - b.popularity)
    }
  })
  
  return filteredEvents
}

export const useTicketSearch = (filters: SearchFilters) => {
  return useQuery({
    queryKey: ['ticket-search', filters],
    queryFn: () => fetchTicketEvents(filters),
    staleTime: 5 * 60 * 1000, // 5 minutes
    gcTime: 10 * 60 * 1000, // 10 minutes
    retry: 2,
    refetchOnWindowFocus: true,
  })
}

export default useTicketSearch
