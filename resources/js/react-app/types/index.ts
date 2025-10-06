/**
 * HD Tickets - React Application Types
 * Comprehensive TypeScript definitions for the sports ticketing platform
 */

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'customer' | 'agent' | 'admin' | 'scraper';
  avatar?: string;
  subscription?: Subscription;
  preferences?: UserPreferences;
  createdAt: string;
  updatedAt: string;
}

export interface Subscription {
  id: number;
  plan: 'free' | 'basic' | 'premium' | 'enterprise';
  status: 'active' | 'expired' | 'cancelled';
  ticketLimit: number;
  usedTickets: number;
  expiresAt: string;
}

export interface UserPreferences {
  theme: 'light' | 'dark' | 'system';
  notifications: {
    email: boolean;
    push: boolean;
    priceAlerts: boolean;
    availabilityAlerts: boolean;
  };
  dashboard: {
    refreshInterval: number;
    defaultView: string;
  };
}

export interface SportsEvent {
  id: number;
  title: string;
  description?: string;
  sport: Sport;
  venue: Venue;
  dateTime: string;
  status: 'upcoming' | 'live' | 'ended' | 'cancelled' | 'postponed';
  category: EventCategory;
  teams?: Team[];
  league?: string;
  season?: string;
  imageUrl?: string;
  popularity: number;
  ticketsAvailable: boolean;
  minPrice?: number;
  maxPrice?: number;
  averagePrice?: number;
  totalTickets?: number;
  soldTickets?: number;
  createdAt: string;
  updatedAt: string;
}

export interface Sport {
  id: number;
  name: string;
  slug: string;
  icon?: string;
  color?: string;
}

export interface Venue {
  id: number;
  name: string;
  city: string;
  state?: string;
  country: string;
  capacity?: number;
  address?: string;
  coordinates?: {
    lat: number;
    lng: number;
  };
}

export interface Team {
  id: number;
  name: string;
  shortName?: string;
  logo?: string;
  colors?: {
    primary: string;
    secondary: string;
  };
}

export interface EventCategory {
  id: number;
  name: string;
  slug: string;
  color?: string;
}

export interface Ticket {
  id: number;
  event: SportsEvent;
  platform: TicketPlatform;
  section: string;
  row?: string;
  seat?: string;
  price: number;
  originalPrice?: number;
  currency: string;
  availability: TicketAvailability;
  quality: TicketQuality;
  features: string[];
  restrictions?: string[];
  url: string;
  lastChecked: string;
  priceHistory: PricePoint[];
  metadata?: Record<string, any>;
  createdAt: string;
  updatedAt: string;
}

export interface TicketPlatform {
  id: number;
  name: string;
  slug: string;
  logo?: string;
  baseUrl: string;
  isActive: boolean;
  reliability: number;
  averageResponseTime?: number;
}

export interface TicketAvailability {
  status: 'available' | 'limited' | 'sold_out' | 'unavailable';
  quantity?: number;
  lastUpdated: string;
}

export interface TicketQuality {
  score: number; // 0-100
  factors: {
    price: number;
    location: number;
    availability: number;
    platform: number;
  };
}

export interface PricePoint {
  price: number;
  timestamp: string;
  availability?: number;
}

export interface TicketFilter {
  sports?: number[];
  venues?: number[];
  priceRange?: {
    min: number;
    max: number;
  };
  dateRange?: {
    start: string;
    end: string;
  };
  platforms?: number[];
  availability?: TicketAvailability['status'][];
  categories?: number[];
  search?: string;
  sortBy?: 'price' | 'date' | 'popularity' | 'relevance';
  sortOrder?: 'asc' | 'desc';
}

export interface Purchase {
  id: number;
  user: User;
  tickets: Ticket[];
  totalPrice: number;
  currency: string;
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'refunded';
  paymentMethod?: string;
  transactionId?: string;
  purchaseDate: string;
  deliveryMethod?: 'digital' | 'pickup' | 'mail';
  notes?: string;
}

export interface Cart {
  items: CartItem[];
  totalPrice: number;
  totalItems: number;
  currency: string;
  expiresAt?: string;
}

export interface CartItem {
  ticket: Ticket;
  quantity: number;
  subtotal: number;
  addedAt: string;
}

export interface Notification {
  id: number;
  type: 'info' | 'success' | 'warning' | 'error';
  title: string;
  message: string;
  isRead: boolean;
  action?: {
    label: string;
    url: string;
  };
  createdAt: string;
}

export interface DashboardStats {
  totalEvents: number;
  totalTickets: number;
  totalUsers: number;
  totalPurchases: number;
  averagePrice: number;
  popularSports: Array<{
    sport: Sport;
    count: number;
  }>;
  recentActivity: Activity[];
  priceAlerts: PriceAlert[];
}

export interface Activity {
  id: number;
  type: 'ticket_added' | 'price_changed' | 'purchase_made' | 'event_added';
  description: string;
  data?: Record<string, any>;
  timestamp: string;
}

export interface PriceAlert {
  id: number;
  user: User;
  event: SportsEvent;
  targetPrice: number;
  currentPrice: number;
  isActive: boolean;
  triggeredAt?: string;
  createdAt: string;
}

// API Response Types
export interface ApiResponse<T> {
  data: T;
  meta?: {
    pagination?: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  message?: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status: number;
}

// Form Types
export interface LoginForm {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterForm {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  terms: boolean;
}

export interface EventSearchForm {
  query?: string;
  sport?: number;
  venue?: number;
  startDate?: string;
  endDate?: string;
  minPrice?: number;
  maxPrice?: number;
}

export interface PriceAlertForm {
  eventId: number;
  targetPrice: number;
  email: string;
}

// Component Props Types
export interface BaseComponentProps {
  className?: string;
  children?: React.ReactNode;
}

export interface LoadingState {
  isLoading: boolean;
  error?: string | null;
}

export interface PaginationProps {
  currentPage: number;
  totalPages: number;
  onPageChange: (page: number) => void;
  showFirstLast?: boolean;
  showPrevNext?: boolean;
}

// State Management Types
export interface RootState {
  auth: AuthState;
  events: EventsState;
  tickets: TicketsState;
  cart: CartState;
  notifications: NotificationsState;
  ui: UIState;
}

export interface AuthState extends LoadingState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
}

export interface EventsState extends LoadingState {
  events: SportsEvent[];
  currentEvent: SportsEvent | null;
  filters: TicketFilter;
  pagination: {
    currentPage: number;
    totalPages: number;
    totalItems: number;
  };
}

export interface TicketsState extends LoadingState {
  tickets: Ticket[];
  selectedTicket: Ticket | null;
  filters: TicketFilter;
  priceAlerts: PriceAlert[];
}

export interface CartState extends LoadingState {
  cart: Cart;
  isCheckingOut: boolean;
}

export interface NotificationsState extends LoadingState {
  notifications: Notification[];
  unreadCount: number;
}

export interface UIState {
  theme: 'light' | 'dark' | 'system';
  sidebarOpen: boolean;
  modalStack: string[];
  toasts: Toast[];
}

export interface Toast {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message?: string;
  duration?: number;
}

// Utility Types
export type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

export type NonNullable<T> = T extends null | undefined ? never : T;

export type Optional<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;

export type Required<T, K extends keyof T> = T & Required<Pick<T, K>>;