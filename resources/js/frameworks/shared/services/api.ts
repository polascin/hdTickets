/**
 * HD Tickets - Shared API Service
 * 
 * Framework-agnostic API service for sports ticket management
 * Used by React, Vue, Angular, and Alpine.js components
 */

import axios, { AxiosInstance, AxiosResponse } from 'axios';
import {
  Ticket,
  Event,
  User,
  Purchase,
  PriceAlert,
  Watchlist,
  MonitoringStats,
  ScrapingJob,
  ApiResponse,
  PaginatedResponse,
  TicketFilters
} from '../types';

class ApiService {
  private api: AxiosInstance;
  private baseURL: string;

  constructor() {
    this.baseURL = '/api/v1';
    
    this.api = axios.create({
      baseURL: this.baseURL,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    this.setupInterceptors();
  }

  private setupInterceptors(): void {
    // Request interceptor for auth token
    this.api.interceptors.request.use(
      (config) => {
        const token = this.getAuthToken();
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        
        // Add CSRF token for Laravel
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
          config.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor for error handling
    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          this.handleUnauthorized();
        }
        
        if (error.response?.status === 419) {
          // CSRF token mismatch - reload page
          window.location.reload();
        }

        return Promise.reject(this.formatError(error));
      }
    );
  }

  private getAuthToken(): string | null {
    // Try to get from localStorage (SPA mode) or meta tag (traditional)
    return localStorage.getItem('auth_token') || 
           document.querySelector('meta[name="api-token"]')?.getAttribute('content') || 
           null;
  }

  private handleUnauthorized(): void {
    localStorage.removeItem('auth_token');
    if (window.location.pathname !== '/login') {
      window.location.href = '/login';
    }
  }

  private formatError(error: any): any {
    if (error.response) {
      return {
        status: error.response.status,
        message: error.response.data?.message || 'An error occurred',
        errors: error.response.data?.errors || {},
        data: error.response.data
      };
    }
    
    return {
      message: error.message || 'Network error',
      status: 0
    };
  }

  // Ticket endpoints
  async getTickets(filters?: TicketFilters, page: number = 1, perPage: number = 20): Promise<PaginatedResponse<Ticket>> {
    const params = new URLSearchParams();
    params.append('page', page.toString());
    params.append('per_page', perPage.toString());

    if (filters) {
      if (filters.platform?.length) {
        filters.platform.forEach(p => params.append('platform[]', p));
      }
      if (filters.priceRange) {
        params.append('min_price', filters.priceRange.min.toString());
        params.append('max_price', filters.priceRange.max.toString());
      }
      if (filters.categories?.length) {
        filters.categories.forEach(c => params.append('categories[]', c));
      }
      if (filters.availability?.length) {
        filters.availability.forEach(a => params.append('availability[]', a));
      }
      if (filters.dateRange) {
        params.append('date_start', filters.dateRange.start);
        params.append('date_end', filters.dateRange.end);
      }
      if (filters.venue?.length) {
        filters.venue.forEach(v => params.append('venue[]', v));
      }
    }

    const response = await this.api.get<PaginatedResponse<Ticket>>(`/tickets?${params.toString()}`);
    return response.data;
  }

  async getTicket(id: string): Promise<ApiResponse<Ticket>> {
    const response = await this.api.get<ApiResponse<Ticket>>(`/tickets/${id}`);
    return response.data;
  }

  async searchTickets(query: string, filters?: TicketFilters): Promise<ApiResponse<Ticket[]>> {
    const response = await this.api.post<ApiResponse<Ticket[]>>('/tickets/search', {
      query,
      filters
    });
    return response.data;
  }

  // Event endpoints
  async getEvents(page: number = 1, perPage: number = 20): Promise<PaginatedResponse<Event>> {
    const response = await this.api.get<PaginatedResponse<Event>>(`/events?page=${page}&per_page=${perPage}`);
    return response.data;
  }

  async getEvent(id: string): Promise<ApiResponse<Event>> {
    const response = await this.api.get<ApiResponse<Event>>(`/events/${id}`);
    return response.data;
  }

  async getEventTickets(eventId: string, filters?: TicketFilters): Promise<ApiResponse<Ticket[]>> {
    const response = await this.api.post<ApiResponse<Ticket[]>>(`/events/${eventId}/tickets`, { filters });
    return response.data;
  }

  // Purchase endpoints
  async purchaseTicket(ticketId: string, quantity: number, paymentMethod: string): Promise<ApiResponse<Purchase>> {
    const response = await this.api.post<ApiResponse<Purchase>>('/purchases', {
      ticket_id: ticketId,
      quantity,
      payment_method: paymentMethod
    });
    return response.data;
  }

  async getPurchases(): Promise<ApiResponse<Purchase[]>> {
    const response = await this.api.get<ApiResponse<Purchase[]>>('/purchases');
    return response.data;
  }

  async getPurchase(id: string): Promise<ApiResponse<Purchase>> {
    const response = await this.api.get<ApiResponse<Purchase>>(`/purchases/${id}`);
    return response.data;
  }

  async cancelPurchase(id: string): Promise<ApiResponse<Purchase>> {
    const response = await this.api.post<ApiResponse<Purchase>>(`/purchases/${id}/cancel`);
    return response.data;
  }

  // Price Alert endpoints
  async getPriceAlerts(): Promise<ApiResponse<PriceAlert[]>> {
    const response = await this.api.get<ApiResponse<PriceAlert[]>>('/price-alerts');
    return response.data;
  }

  async createPriceAlert(eventId: string, targetPrice: number): Promise<ApiResponse<PriceAlert>> {
    const response = await this.api.post<ApiResponse<PriceAlert>>('/price-alerts', {
      event_id: eventId,
      target_price: targetPrice
    });
    return response.data;
  }

  async deletePriceAlert(id: string): Promise<ApiResponse<void>> {
    const response = await this.api.delete<ApiResponse<void>>(`/price-alerts/${id}`);
    return response.data;
  }

  // Watchlist endpoints
  async getWatchlists(): Promise<ApiResponse<Watchlist[]>> {
    const response = await this.api.get<ApiResponse<Watchlist[]>>('/watchlists');
    return response.data;
  }

  async createWatchlist(name: string, eventIds: string[]): Promise<ApiResponse<Watchlist>> {
    const response = await this.api.post<ApiResponse<Watchlist>>('/watchlists', {
      name,
      event_ids: eventIds
    });
    return response.data;
  }

  async updateWatchlist(id: string, name: string, eventIds: string[]): Promise<ApiResponse<Watchlist>> {
    const response = await this.api.put<ApiResponse<Watchlist>>(`/watchlists/${id}`, {
      name,
      event_ids: eventIds
    });
    return response.data;
  }

  async deleteWatchlist(id: string): Promise<ApiResponse<void>> {
    const response = await this.api.delete<ApiResponse<void>>(`/watchlists/${id}`);
    return response.data;
  }

  // User endpoints
  async getCurrentUser(): Promise<ApiResponse<User>> {
    const response = await this.api.get<ApiResponse<User>>('/user');
    return response.data;
  }

  async updateUserPreferences(preferences: any): Promise<ApiResponse<User>> {
    const response = await this.api.put<ApiResponse<User>>('/user/preferences', preferences);
    return response.data;
  }

  // Dashboard endpoints
  async getDashboardStats(): Promise<ApiResponse<MonitoringStats>> {
    const response = await this.api.get<ApiResponse<MonitoringStats>>('/dashboard/stats');
    return response.data;
  }

  async getRecentTickets(limit: number = 10): Promise<ApiResponse<Ticket[]>> {
    const response = await this.api.get<ApiResponse<Ticket[]>>(`/dashboard/recent-tickets?limit=${limit}`);
    return response.data;
  }

  // Admin endpoints (role-based access)
  async getScrapingJobs(): Promise<ApiResponse<ScrapingJob[]>> {
    const response = await this.api.get<ApiResponse<ScrapingJob[]>>('/admin/scraping-jobs');
    return response.data;
  }

  async startScrapingJob(platform: string): Promise<ApiResponse<ScrapingJob>> {
    const response = await this.api.post<ApiResponse<ScrapingJob>>('/admin/scraping-jobs', { platform });
    return response.data;
  }

  async getUsers(page: number = 1, perPage: number = 20): Promise<PaginatedResponse<User>> {
    const response = await this.api.get<PaginatedResponse<User>>(`/admin/users?page=${page}&per_page=${perPage}`);
    return response.data;
  }

  // Real-time data with polling fallback
  async subscribeToTicketUpdates(eventId: string, callback: (ticket: Ticket) => void): Promise<() => void> {
    // Try WebSocket first, fallback to polling
    if (window.Echo && window.Echo.channel) {
      const channel = window.Echo.channel(`event.${eventId}`);
      channel.listen('TicketUpdated', callback);
      
      return () => {
        window.Echo.leave(`event.${eventId}`);
      };
    } else {
      // Fallback to polling
      const interval = setInterval(async () => {
        try {
          const response = await this.getEventTickets(eventId);
          response.data.forEach(callback);
        } catch (error) {
          console.error('Polling error:', error);
        }
      }, 30000); // Poll every 30 seconds

      return () => clearInterval(interval);
    }
  }

  // Utility methods
  formatPrice(price: number, currency: string = 'USD'): string {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
    }).format(price);
  }

  formatDate(date: string): string {
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(date));
  }

  formatRelativeTime(date: string): string {
    const now = new Date();
    const target = new Date(date);
    const diffInSeconds = Math.floor((now.getTime() - target.getTime()) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    return `${Math.floor(diffInSeconds / 86400)} days ago`;
  }

  // Error handling utilities
  isNetworkError(error: any): boolean {
    return error.status === 0 || !error.status;
  }

  isValidationError(error: any): boolean {
    return error.status === 422 && error.errors;
  }

  getValidationErrors(error: any): Record<string, string[]> {
    return this.isValidationError(error) ? error.errors : {};
  }
}

// Singleton instance
const apiService = new ApiService();

export default apiService;
export { ApiService };