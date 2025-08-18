// Core Types
export interface User {
  id: string;
  email: string;
  name: string;
  role: 'admin' | 'agent' | 'customer';
  avatar?: string;
  preferences: UserPreferences;
  createdAt: string;
  updatedAt: string;
}

export interface UserPreferences {
  favoriteTeams: Team[];
  preferredSports: Sport[];
  priceAlerts: boolean;
  emailNotifications: boolean;
  pushNotifications: boolean;
  darkMode: boolean;
  language: string;
  currency: string;
  timezone: string;
}

// Sports & Teams
export interface Sport {
  id: string;
  name: string;
  code: 'NFL' | 'NBA' | 'MLB' | 'NHL' | 'MLS' | 'NCAA' | 'OTHER';
  season: Season;
  isActive: boolean;
}

export interface League {
  id: string;
  name: string;
  sport: Sport;
  country: string;
  season: Season;
  teams: Team[];
}

export interface Team {
  id: string;
  name: string;
  city: string;
  abbreviation: string;
  sport: Sport;
  league: League;
  colors: {
    primary: string;
    secondary: string;
    accent?: string;
  };
  logo?: string;
  venue?: Venue;
}

export interface Season {
  id: string;
  year: number;
  startDate: string;
  endDate: string;
  type: 'regular' | 'playoffs' | 'preseason' | 'offseason';
}

// Venues & Events
export interface Venue {
  id: string;
  name: string;
  city: string;
  state: string;
  country: string;
  capacity: number;
  address: string;
  coordinates: {
    latitude: number;
    longitude: number;
  };
  timezone: string;
  image?: string;
}

export interface Event {
  id: string;
  title: string;
  description?: string;
  sport: Sport;
  homeTeam: Team;
  awayTeam: Team;
  venue: Venue;
  dateTime: string;
  status: EventStatus;
  importance: 'low' | 'medium' | 'high' | 'playoff';
  tickets: Ticket[];
  metadata: EventMetadata;
}

export interface EventMetadata {
  season: Season;
  week?: number;
  gameNumber?: number;
  series?: string;
  weather?: WeatherInfo;
  attendance?: number;
  broadcastInfo?: BroadcastInfo;
}

export interface WeatherInfo {
  temperature: number;
  conditions: string;
  humidity: number;
  windSpeed: number;
}

export interface BroadcastInfo {
  tv?: string[];
  radio?: string[];
  streaming?: string[];
}

export type EventStatus = 
  | 'scheduled' 
  | 'delayed' 
  | 'in_progress' 
  | 'finished' 
  | 'cancelled' 
  | 'postponed';

// Tickets & Pricing
export interface Ticket {
  id: string;
  event: Event;
  platform: Platform;
  section: string;
  row?: string;
  seat?: string;
  quantity: number;
  price: Money;
  originalPrice?: Money;
  fees: Money;
  totalPrice: Money;
  availability: TicketAvailability;
  quality: TicketQuality;
  features: TicketFeature[];
  lastUpdated: string;
  url: string;
  metadata: TicketMetadata;
}

export interface Money {
  amount: number;
  currency: string;
}

export interface TicketMetadata {
  priceHistory: PricePoint[];
  competitorPrices: CompetitorPrice[];
  demandScore: number;
  valueScore: number;
  recommendations: string[];
}

export interface PricePoint {
  price: Money;
  timestamp: string;
  platform: Platform;
}

export interface CompetitorPrice {
  platform: Platform;
  price: Money;
  availability: TicketAvailability;
  timestamp: string;
}

export type TicketAvailability = 
  | 'available' 
  | 'limited' 
  | 'few_left' 
  | 'sold_out' 
  | 'not_available';

export type TicketQuality = 
  | 'premium' 
  | 'standard' 
  | 'budget' 
  | 'obstructed';

export interface TicketFeature {
  type: 'parking' | 'food' | 'vip' | 'club_access' | 'meet_greet' | 'merchandise';
  name: string;
  description: string;
  included: boolean;
  additionalCost?: Money;
}

// Platforms & Monitoring
export interface Platform {
  id: string;
  name: string;
  type: 'primary' | 'secondary' | 'resale';
  url: string;
  logo?: string;
  status: PlatformStatus;
  reliability: number; // 0-100
  averageResponseTime: number; // ms
  lastScrapeTime: string;
  supportedSports: Sport[];
  fees: PlatformFees;
}

export interface PlatformFees {
  serviceFee: number; // percentage
  processingFee: Money;
  deliveryFee: Money;
}

export type PlatformStatus = 
  | 'online' 
  | 'offline' 
  | 'slow' 
  | 'maintenance' 
  | 'error';

// Alerts & Monitoring
export interface PriceAlert {
  id: string;
  user: User;
  event: Event;
  targetPrice: Money;
  currentPrice: Money;
  condition: AlertCondition;
  status: AlertStatus;
  platforms: Platform[];
  createdAt: string;
  updatedAt: string;
  triggeredAt?: string;
  notifications: NotificationChannel[];
}

export type AlertCondition = 
  | 'below' 
  | 'above' 
  | 'change_percent' 
  | 'available';

export type AlertStatus = 
  | 'active' 
  | 'triggered' 
  | 'paused' 
  | 'expired' 
  | 'deleted';

export type NotificationChannel = 
  | 'email' 
  | 'push' 
  | 'sms' 
  | 'webhook';

// Search & Filtering
export interface SearchFilters {
  query?: string;
  sports?: string[];
  teams?: string[];
  venues?: string[];
  cities?: string[];
  dateRange?: DateRange;
  priceRange?: PriceRange;
  platforms?: string[];
  availability?: TicketAvailability[];
  quality?: TicketQuality[];
  features?: string[];
  sortBy?: SortOption;
  sortOrder?: 'asc' | 'desc';
}

export interface DateRange {
  start: string;
  end: string;
}

export interface PriceRange {
  min: number;
  max: number;
  currency: string;
}

export type SortOption = 
  | 'price' 
  | 'date' 
  | 'popularity' 
  | 'distance' 
  | 'value_score' 
  | 'demand_score';

// API Types
export interface ApiResponse<T> {
  data: T;
  meta: ApiMeta;
  links?: ApiLinks;
}

export interface ApiMeta {
  total: number;
  page: number;
  perPage: number;
  totalPages: number;
  hasNextPage: boolean;
  hasPreviousPage: boolean;
}

export interface ApiLinks {
  first: string;
  last: string;
  prev?: string;
  next?: string;
}

export interface ApiError {
  message: string;
  code: string;
  details?: Record<string, any>;
  timestamp: string;
}

// WebSocket Types
export interface WebSocketMessage {
  type: WebSocketMessageType;
  data: any;
  timestamp: string;
  id: string;
}

export type WebSocketMessageType = 
  | 'price_update' 
  | 'alert_triggered' 
  | 'platform_status' 
  | 'new_tickets' 
  | 'system_message';

export interface PriceUpdateMessage {
  ticketId: string;
  newPrice: Money;
  oldPrice: Money;
  platform: Platform;
  timestamp: string;
}

export interface AlertTriggeredMessage {
  alertId: string;
  userId: string;
  event: Event;
  triggerPrice: Money;
  currentPrice: Money;
  timestamp: string;
}

// Component Props Types
export interface BaseComponentProps {
  className?: string;
  children?: React.ReactNode;
  'data-testid'?: string;
}

export interface TableColumn<T> {
  key: keyof T;
  title: string;
  sortable?: boolean;
  width?: string;
  render?: (value: any, record: T) => React.ReactNode;
}

export interface SelectOption {
  value: string;
  label: string;
  disabled?: boolean;
  icon?: React.ReactNode;
}

// State Management Types
export interface AppState {
  user: User | null;
  auth: AuthState;
  tickets: TicketState;
  alerts: AlertState;
  ui: UIState;
}

export interface AuthState {
  isAuthenticated: boolean;
  isLoading: boolean;
  token: string | null;
  refreshToken: string | null;
  expiresAt: string | null;
}

export interface TicketState {
  searchResults: Ticket[];
  filters: SearchFilters;
  isLoading: boolean;
  error: string | null;
  pagination: ApiMeta;
}

export interface AlertState {
  alerts: PriceAlert[];
  isLoading: boolean;
  error: string | null;
}

export interface UIState {
  theme: 'light' | 'dark';
  sidebar: {
    isOpen: boolean;
    width: number;
  };
  notifications: Notification[];
  modals: Record<string, boolean>;
}

export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  duration?: number;
  actions?: NotificationAction[];
  timestamp: string;
}

export interface NotificationAction {
  label: string;
  action: () => void;
  style?: 'primary' | 'secondary';
}
