import type { ApiResponse, ApiError, User } from '@/types';

interface RequestConfig {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  headers?: Record<string, string>;
  body?: any;
  params?: Record<string, any>;
  timeout?: number;
}

class ApiClient {
  private baseURL: string;
  private token: string | null = null;
  private refreshToken: string | null = null;
  private isRefreshing = false;
  private failedQueue: Array<{
    resolve: (value?: any) => void;
    reject: (reason?: any) => void;
  }> = [];

  constructor(baseURL: string = '/api') {
    this.baseURL = baseURL;
    this.loadTokensFromStorage();
  }

  private loadTokensFromStorage() {
    if (typeof window !== 'undefined') {
      this.token = localStorage.getItem('auth_token');
      this.refreshToken = localStorage.getItem('refresh_token');
    }
  }

  private saveTokensToStorage(token: string, refreshToken?: string) {
    if (typeof window !== 'undefined') {
      localStorage.setItem('auth_token', token);
      if (refreshToken) {
        localStorage.setItem('refresh_token', refreshToken);
      }
    }
    this.token = token;
    if (refreshToken) {
      this.refreshToken = refreshToken;
    }
  }

  private clearTokensFromStorage() {
    if (typeof window !== 'undefined') {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('refresh_token');
    }
    this.token = null;
    this.refreshToken = null;
  }

  private async processQueue(error: any, token: string | null = null) {
    this.failedQueue.forEach(({ resolve, reject }) => {
      if (error) {
        reject(error);
      } else {
        resolve(token);
      }
    });
    
    this.failedQueue = [];
  }

  private async refreshAccessToken(): Promise<string> {
    if (!this.refreshToken) {
      throw new Error('No refresh token available');
    }

    try {
      const response = await fetch(`${this.baseURL}/auth/refresh`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.refreshToken}`,
        },
      });

      if (!response.ok) {
        throw new Error('Token refresh failed');
      }

      const data = await response.json();
      this.saveTokensToStorage(data.access_token, data.refresh_token);
      
      return data.access_token;
    } catch (error) {
      this.clearTokensFromStorage();
      throw error;
    }
  }

  private buildURL(endpoint: string, params?: Record<string, any>): string {
    const url = new URL(`${this.baseURL}${endpoint}`, window.location.origin);
    
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          url.searchParams.append(key, String(value));
        }
      });
    }
    
    return url.toString();
  }

  private async makeRequest<T>(
    endpoint: string, 
    config: RequestConfig = {}
  ): Promise<ApiResponse<T>> {
    const {
      method = 'GET',
      headers = {},
      body,
      params,
      timeout = 30000,
    } = config;

    const url = this.buildURL(endpoint, params);
    
    const requestHeaders: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...headers,
    };

    if (this.token) {
      requestHeaders['Authorization'] = `Bearer ${this.token}`;
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);

    try {
      const response = await fetch(url, {
        method,
        headers: requestHeaders,
        body: body ? JSON.stringify(body) : undefined,
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      // Handle 401 Unauthorized - attempt token refresh
      if (response.status === 401 && this.token && !this.isRefreshing) {
        return this.handleTokenRefresh(endpoint, config);
      }

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({
          message: `HTTP ${response.status}: ${response.statusText}`,
          code: 'HTTP_ERROR',
          timestamp: new Date().toISOString(),
        }));

        throw {
          ...errorData,
          status: response.status,
        } as ApiError;
      }

      const data = await response.json();
      return data;
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error.name === 'AbortError') {
        throw {
          message: 'Request timeout',
          code: 'TIMEOUT_ERROR',
          timestamp: new Date().toISOString(),
        } as ApiError;
      }
      
      throw error;
    }
  }

  private async handleTokenRefresh<T>(
    endpoint: string, 
    config: RequestConfig
  ): Promise<ApiResponse<T>> {
    if (this.isRefreshing) {
      // If already refreshing, queue this request
      return new Promise((resolve, reject) => {
        this.failedQueue.push({ resolve, reject });
      }).then(() => {
        return this.makeRequest<T>(endpoint, config);
      });
    }

    this.isRefreshing = true;

    try {
      await this.refreshAccessToken();
      this.processQueue(null, this.token);
      
      // Retry the original request
      return this.makeRequest<T>(endpoint, config);
    } catch (error) {
      this.processQueue(error, null);
      this.clearTokensFromStorage();
      
      // Redirect to login or emit auth event
      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('auth:logout'));
      }
      
      throw error;
    } finally {
      this.isRefreshing = false;
    }
  }

  // Authentication methods
  async login(email: string, password: string): Promise<{ user: User; token: string; refreshToken: string }> {
    const response = await this.makeRequest<{ user: User; access_token: string; refresh_token: string }>('/auth/login', {
      method: 'POST',
      body: { email, password },
    });

    this.saveTokensToStorage(response.data.access_token, response.data.refresh_token);
    
    return {
      user: response.data.user,
      token: response.data.access_token,
      refreshToken: response.data.refresh_token,
    };
  }

  async register(userData: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }): Promise<{ user: User; token: string; refreshToken: string }> {
    const response = await this.makeRequest<{ user: User; access_token: string; refresh_token: string }>('/auth/register', {
      method: 'POST',
      body: userData,
    });

    this.saveTokensToStorage(response.data.access_token, response.data.refresh_token);
    
    return {
      user: response.data.user,
      token: response.data.access_token,
      refreshToken: response.data.refresh_token,
    };
  }

  async logout(): Promise<void> {
    try {
      await this.makeRequest('/auth/logout', { method: 'POST' });
    } catch (error) {
      // Ignore logout errors
    } finally {
      this.clearTokensFromStorage();
    }
  }

  async getUser(): Promise<User> {
    const response = await this.makeRequest<User>('/auth/me');
    return response.data;
  }

  // Generic CRUD methods
  async get<T>(endpoint: string, params?: Record<string, any>): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, { params });
  }

  async post<T>(endpoint: string, data?: any): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {
      method: 'POST',
      body: data,
    });
  }

  async put<T>(endpoint: string, data?: any): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {
      method: 'PUT',
      body: data,
    });
  }

  async patch<T>(endpoint: string, data?: any): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {
      method: 'PATCH',
      body: data,
    });
  }

  async delete<T>(endpoint: string): Promise<ApiResponse<T>> {
    return this.makeRequest<T>(endpoint, {
      method: 'DELETE',
    });
  }

  // Utility methods
  setToken(token: string, refreshToken?: string) {
    this.saveTokensToStorage(token, refreshToken);
  }

  getToken(): string | null {
    return this.token;
  }

  isAuthenticated(): boolean {
    return !!this.token;
  }

  clearAuth() {
    this.clearTokensFromStorage();
  }
}

// Create singleton instance
export const apiClient = new ApiClient();

// Export type-safe methods
export const api = {
  // Authentication
  auth: {
    login: (email: string, password: string) => apiClient.login(email, password),
    register: (userData: any) => apiClient.register(userData),
    logout: () => apiClient.logout(),
    getUser: () => apiClient.getUser(),
    setToken: (token: string, refreshToken?: string) => apiClient.setToken(token, refreshToken),
    clearAuth: () => apiClient.clearAuth(),
    isAuthenticated: () => apiClient.isAuthenticated(),
  },

  // Tickets
  tickets: {
    search: (filters: any, page = 1, perPage = 20) => 
      apiClient.get(`/tickets/search`, { ...filters, page, per_page: perPage }),
    getById: (id: string) => apiClient.get(`/tickets/${id}`),
    getPriceHistory: (id: string) => apiClient.get(`/tickets/${id}/price-history`),
  },

  // Alerts
  alerts: {
    list: (userId?: string) => apiClient.get('/alerts', userId ? { user: userId } : {}),
    create: (alertData: any) => apiClient.post('/alerts', alertData),
    update: (id: string, alertData: any) => apiClient.put(`/alerts/${id}`, alertData),
    delete: (id: string) => apiClient.delete(`/alerts/${id}`),
  },

  // Sports & Teams
  sports: {
    list: () => apiClient.get('/sports'),
    getTeams: (sportId: string) => apiClient.get(`/sports/${sportId}/teams`),
  },

  teams: {
    list: (filters?: any) => apiClient.get('/teams', filters),
    getById: (id: string) => apiClient.get(`/teams/${id}`),
    getUpcomingEvents: (id: string) => apiClient.get(`/teams/${id}/events`),
  },

  // Events
  events: {
    list: (filters?: any) => apiClient.get('/events', filters),
    getById: (id: string) => apiClient.get(`/events/${id}`),
    getTickets: (id: string) => apiClient.get(`/events/${id}/tickets`),
  },

  // Platforms
  platforms: {
    list: () => apiClient.get('/platforms'),
    getStatus: () => apiClient.get('/platforms/status'),
  },

  // User preferences
  preferences: {
    get: () => apiClient.get('/user/preferences'),
    update: (preferences: any) => apiClient.put('/user/preferences', preferences),
  },
};

export default apiClient;
