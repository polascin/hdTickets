/**
 * HD Tickets - Shared TypeScript Interfaces
 * 
 * Shared types and interfaces used across React, Vue, Angular, and Alpine.js
 * These types represent the sports event ticket domain models
 */

export interface Ticket {
  id: string;
  event_id: string;
  event_name: string;
  event_date: string;
  venue: string;
  section: string;
  row?: string;
  seat?: string;
  price: number;
  original_price?: number;
  currency: string;
  seller: string;
  platform: TicketPlatform;
  availability: TicketAvailability;
  last_updated: string;
  url: string;
  images?: string[];
  categories: string[];
  is_featured?: boolean;
  discount_percentage?: number;
}

export interface Event {
  id: string;
  name: string;
  date: string;
  time?: string;
  venue: Venue;
  category: EventCategory;
  description?: string;
  image?: string;
  tickets_available: number;
  min_price: number;
  max_price: number;
  currency: string;
  status: EventStatus;
  tags: string[];
}

export interface Venue {
  id: string;
  name: string;
  address: string;
  city: string;
  country: string;
  capacity?: number;
  coordinates?: {
    lat: number;
    lng: number;
  };
}

export interface User {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  subscription?: Subscription;
  preferences?: UserPreferences;
  avatar?: string;
}

export interface Subscription {
  id: string;
  plan: SubscriptionPlan;
  status: SubscriptionStatus;
  expires_at: string;
  features: string[];
  limits: {
    ticket_purchases: number;
    alerts: number;
    watchlists: number;
  };
}

export interface PriceAlert {
  id: string;
  user_id: string;
  event_id: string;
  target_price: number;
  currency: string;
  is_active: boolean;
  created_at: string;
  triggered_at?: string;
}

export interface Watchlist {
  id: string;
  user_id: string;
  name: string;
  events: Event[];
  created_at: string;
  updated_at: string;
}

export interface Purchase {
  id: string;
  user_id: string;
  ticket_id: string;
  quantity: number;
  total_amount: number;
  currency: string;
  status: PurchaseStatus;
  payment_method: string;
  created_at: string;
  completed_at?: string;
  cancelled_at?: string;
}

export interface MonitoringStats {
  active_monitors: number;
  tickets_tracked: number;
  price_changes: number;
  alerts_sent: number;
  last_update: string;
}

export interface ScrapingJob {
  id: string;
  platform: TicketPlatform;
  status: ScrapingStatus;
  events_scraped: number;
  tickets_found: number;
  started_at: string;
  completed_at?: string;
  error_message?: string;
}

// Enums
export enum TicketPlatform {
  TICKETMASTER = 'ticketmaster',
  VIAGOGO = 'viagogo',
  STUBHUB = 'stubhub',
  SEETICKETS = 'seetickets',
  EVENTBRITE = 'eventbrite',
  OTHER = 'other'
}

export enum TicketAvailability {
  AVAILABLE = 'available',
  SOLD_OUT = 'sold_out',
  LIMITED = 'limited',
  UNKNOWN = 'unknown'
}

export enum EventCategory {
  SPORTS = 'sports',
  CONCERTS = 'concerts',
  THEATER = 'theater',
  COMEDY = 'comedy',
  FESTIVALS = 'festivals',
  OTHER = 'other'
}

export enum EventStatus {
  UPCOMING = 'upcoming',
  LIVE = 'live',
  CANCELLED = 'cancelled',
  POSTPONED = 'postponed',
  COMPLETED = 'completed'
}

export enum UserRole {
  ADMIN = 'admin',
  AGENT = 'agent',
  CUSTOMER = 'customer',
  SCRAPER = 'scraper'
}

export enum SubscriptionPlan {
  FREE = 'free',
  BASIC = 'basic',
  PREMIUM = 'premium',
  ENTERPRISE = 'enterprise'
}

export enum SubscriptionStatus {
  ACTIVE = 'active',
  EXPIRED = 'expired',
  CANCELLED = 'cancelled',
  SUSPENDED = 'suspended'
}

export enum PurchaseStatus {
  PENDING = 'pending',
  PROCESSING = 'processing',
  COMPLETED = 'completed',
  FAILED = 'failed',
  CANCELLED = 'cancelled',
  REFUNDED = 'refunded'
}

export enum ScrapingStatus {
  QUEUED = 'queued',
  RUNNING = 'running',
  COMPLETED = 'completed',
  FAILED = 'failed',
  CANCELLED = 'cancelled'
}

// Component Props Interfaces
export interface TicketListProps {
  tickets: Ticket[];
  loading?: boolean;
  error?: string;
  onTicketSelect?: (ticket: Ticket) => void;
  onTicketPurchase?: (ticket: Ticket, quantity: number) => void;
  filters?: TicketFilters;
  onFiltersChange?: (filters: TicketFilters) => void;
}

export interface TicketFilters {
  platform?: TicketPlatform[];
  priceRange?: {
    min: number;
    max: number;
  };
  categories?: string[];
  availability?: TicketAvailability[];
  dateRange?: {
    start: string;
    end: string;
  };
  venue?: string[];
}

export interface DashboardProps {
  user: User;
  stats: MonitoringStats;
  recentTickets?: Ticket[];
  priceAlerts?: PriceAlert[];
  watchlists?: Watchlist[];
}

export interface FormFieldProps {
  name: string;
  label: string;
  type: 'text' | 'email' | 'password' | 'number' | 'date' | 'select' | 'textarea';
  value: any;
  onChange: (value: any) => void;
  required?: boolean;
  disabled?: boolean;
  error?: string;
  placeholder?: string;
  options?: { label: string; value: any }[];
}

// API Response Types
export interface ApiResponse<T> {
  data: T;
  message?: string;
  errors?: Record<string, string[]>;
  meta?: {
    total: number;
    page: number;
    per_page: number;
    last_page: number;
  };
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  meta: {
    total: number;
    page: number;
    per_page: number;
    last_page: number;
  };
}

// Event Handler Types
export type EventHandler<T = any> = (data: T) => void;
export type AsyncEventHandler<T = any> = (data: T) => Promise<void>;

// State Management Types
export interface AppState {
  user: User | null;
  tickets: Ticket[];
  events: Event[];
  loading: boolean;
  error: string | null;
  filters: TicketFilters;
  selectedTicket: Ticket | null;
  cart: CartItem[];
  notifications: Notification[];
}

export interface CartItem {
  ticket: Ticket;
  quantity: number;
  selected_at: string;
}

export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  message: string;
  created_at: string;
  read: boolean;
}

// User Preferences
export interface UserPreferences {
  currency: string;
  timezone: string;
  notifications: {
    email: boolean;
    push: boolean;
    sms: boolean;
  };
  display: {
    theme: 'light' | 'dark' | 'auto';
    language: string;
    items_per_page: number;
  };
  alerts: {
    price_drops: boolean;
    new_tickets: boolean;
    event_reminders: boolean;
  };
}

// Framework Bridge Types
export interface FrameworkBridge {
  mountComponent: (element: HTMLElement, componentName: string, props?: any) => Promise<void>;
  unmountComponent: (element: HTMLElement) => void;
  updateProps: (element: HTMLElement, props: any) => void;
  getSharedState: (key: string) => any;
  updateSharedState: (key: string, data: any) => void;
}

export interface ComponentConfig {
  name: string;
  framework: 'react' | 'vue' | 'angular' | 'alpine';
  lazy?: boolean;
  props?: Record<string, any>;
  errorBoundary?: boolean;
}

// Utility Types
export type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

export type Optional<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;

export type RequiredFields<T, K extends keyof T> = T & Required<Pick<T, K>>;