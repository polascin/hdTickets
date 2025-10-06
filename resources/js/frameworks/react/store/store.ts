/**
 * HD Tickets - React Redux Store
 * 
 * Centralized state management for React components
 * Focus: Ticket monitoring, real-time updates, complex data flows
 */

import { configureStore, createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import {
  Ticket,
  Event,
  User,
  Purchase,
  PriceAlert,
  Watchlist,
  MonitoringStats,
  TicketFilters,
  AppState,
  Notification
} from '@shared/types';
import apiService from '@shared/services/api';

// Async thunks for API calls
export const fetchTickets = createAsyncThunk(
  'tickets/fetchTickets',
  async ({ filters, page = 1, perPage = 20 }: { 
    filters?: TicketFilters; 
    page?: number; 
    perPage?: number; 
  }) => {
    const response = await apiService.getTickets(filters, page, perPage);
    return response;
  }
);

export const fetchEvents = createAsyncThunk(
  'events/fetchEvents',
  async ({ page = 1, perPage = 20 }: { page?: number; perPage?: number } = {}) => {
    const response = await apiService.getEvents(page, perPage);
    return response;
  }
);

export const purchaseTicket = createAsyncThunk(
  'purchases/purchaseTicket',
  async ({ ticketId, quantity, paymentMethod }: { 
    ticketId: string; 
    quantity: number; 
    paymentMethod: string; 
  }) => {
    const response = await apiService.purchaseTicket(ticketId, quantity, paymentMethod);
    return response;
  }
);

export const fetchDashboardStats = createAsyncThunk(
  'dashboard/fetchStats',
  async () => {
    const response = await apiService.getDashboardStats();
    return response;
  }
);

export const createPriceAlert = createAsyncThunk(
  'alerts/createAlert',
  async ({ eventId, targetPrice }: { eventId: string; targetPrice: number }) => {
    const response = await apiService.createPriceAlert(eventId, targetPrice);
    return response;
  }
);

// Tickets slice
const ticketsSlice = createSlice({
  name: 'tickets',
  initialState: {
    items: [] as Ticket[],
    selectedTicket: null as Ticket | null,
    filters: {} as TicketFilters,
    loading: false,
    error: null as string | null,
    pagination: {
      page: 1,
      totalPages: 1,
      totalItems: 0,
      perPage: 20
    }
  },
  reducers: {
    selectTicket: (state, action: PayloadAction<Ticket>) => {
      state.selectedTicket = action.payload;
    },
    clearSelectedTicket: (state) => {
      state.selectedTicket = null;
    },
    updateFilters: (state, action: PayloadAction<TicketFilters>) => {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearFilters: (state) => {
      state.filters = {};
    },
    updateTicketPrice: (state, action: PayloadAction<{ id: string; price: number }>) => {
      const ticket = state.items.find(t => t.id === action.payload.id);
      if (ticket) {
        ticket.price = action.payload.price;
        ticket.last_updated = new Date().toISOString();
      }
    },
    addTicket: (state, action: PayloadAction<Ticket>) => {
      state.items.unshift(action.payload);
    },
    removeTicket: (state, action: PayloadAction<string>) => {
      state.items = state.items.filter(t => t.id !== action.payload);
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchTickets.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTickets.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.data;
        if (action.payload.meta) {
          state.pagination = {
            page: action.payload.meta.page,
            totalPages: action.payload.meta.last_page,
            totalItems: action.payload.meta.total,
            perPage: action.payload.meta.per_page
          };
        }
      })
      .addCase(fetchTickets.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch tickets';
      });
  }
});

// Events slice
const eventsSlice = createSlice({
  name: 'events',
  initialState: {
    items: [] as Event[],
    selectedEvent: null as Event | null,
    loading: false,
    error: null as string | null,
    pagination: {
      page: 1,
      totalPages: 1,
      totalItems: 0,
      perPage: 20
    }
  },
  reducers: {
    selectEvent: (state, action: PayloadAction<Event>) => {
      state.selectedEvent = action.payload;
    },
    clearSelectedEvent: (state) => {
      state.selectedEvent = null;
    },
    updateEventTicketCount: (state, action: PayloadAction<{ id: string; count: number }>) => {
      const event = state.items.find(e => e.id === action.payload.id);
      if (event) {
        event.tickets_available = action.payload.count;
      }
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchEvents.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchEvents.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.data;
        if (action.payload.meta) {
          state.pagination = {
            page: action.payload.meta.page,
            totalPages: action.payload.meta.last_page,
            totalItems: action.payload.meta.total,
            perPage: action.payload.meta.per_page
          };
        }
      })
      .addCase(fetchEvents.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch events';
      });
  }
});

// User slice
const userSlice = createSlice({
  name: 'user',
  initialState: {
    currentUser: null as User | null,
    loading: false,
    error: null as string | null,
  },
  reducers: {
    setUser: (state, action: PayloadAction<User>) => {
      state.currentUser = action.payload;
    },
    clearUser: (state) => {
      state.currentUser = null;
    },
    updateUserPreferences: (state, action: PayloadAction<any>) => {
      if (state.currentUser) {
        state.currentUser.preferences = { ...state.currentUser.preferences, ...action.payload };
      }
    }
  }
});

// Purchases slice
const purchasesSlice = createSlice({
  name: 'purchases',
  initialState: {
    items: [] as Purchase[],
    loading: false,
    error: null as string | null,
    currentPurchase: null as Purchase | null
  },
  reducers: {
    addPurchase: (state, action: PayloadAction<Purchase>) => {
      state.items.unshift(action.payload);
    },
    updatePurchaseStatus: (state, action: PayloadAction<{ id: string; status: string }>) => {
      const purchase = state.items.find(p => p.id === action.payload.id);
      if (purchase) {
        purchase.status = action.payload.status as any;
      }
    },
    clearCurrentPurchase: (state) => {
      state.currentPurchase = null;
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(purchaseTicket.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(purchaseTicket.fulfilled, (state, action) => {
        state.loading = false;
        state.currentPurchase = action.payload.data;
        state.items.unshift(action.payload.data);
      })
      .addCase(purchaseTicket.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to purchase ticket';
      });
  }
});

// Dashboard slice
const dashboardSlice = createSlice({
  name: 'dashboard',
  initialState: {
    stats: null as MonitoringStats | null,
    loading: false,
    error: null as string | null,
    lastUpdated: null as string | null
  },
  reducers: {
    updateStats: (state, action: PayloadAction<MonitoringStats>) => {
      state.stats = action.payload;
      state.lastUpdated = new Date().toISOString();
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchDashboardStats.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchDashboardStats.fulfilled, (state, action) => {
        state.loading = false;
        state.stats = action.payload.data;
        state.lastUpdated = new Date().toISOString();
      })
      .addCase(fetchDashboardStats.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch dashboard stats';
      });
  }
});

// Price alerts slice
const alertsSlice = createSlice({
  name: 'alerts',
  initialState: {
    items: [] as PriceAlert[],
    loading: false,
    error: null as string | null
  },
  reducers: {
    addAlert: (state, action: PayloadAction<PriceAlert>) => {
      state.items.unshift(action.payload);
    },
    removeAlert: (state, action: PayloadAction<string>) => {
      state.items = state.items.filter(a => a.id !== action.payload);
    },
    updateAlert: (state, action: PayloadAction<PriceAlert>) => {
      const index = state.items.findIndex(a => a.id === action.payload.id);
      if (index !== -1) {
        state.items[index] = action.payload;
      }
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(createPriceAlert.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(createPriceAlert.fulfilled, (state, action) => {
        state.loading = false;
        state.items.unshift(action.payload.data);
      })
      .addCase(createPriceAlert.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to create price alert';
      });
  }
});

// Notifications slice
const notificationsSlice = createSlice({
  name: 'notifications',
  initialState: {
    items: [] as Notification[],
    unreadCount: 0
  },
  reducers: {
    addNotification: (state, action: PayloadAction<Omit<Notification, 'id' | 'created_at'>>) => {
      const notification: Notification = {
        ...action.payload,
        id: Date.now().toString(),
        created_at: new Date().toISOString(),
        read: false
      };
      state.items.unshift(notification);
      if (!notification.read) {
        state.unreadCount++;
      }
    },
    markAsRead: (state, action: PayloadAction<string>) => {
      const notification = state.items.find(n => n.id === action.payload);
      if (notification && !notification.read) {
        notification.read = true;
        state.unreadCount = Math.max(0, state.unreadCount - 1);
      }
    },
    markAllAsRead: (state) => {
      state.items.forEach(n => n.read = true);
      state.unreadCount = 0;
    },
    removeNotification: (state, action: PayloadAction<string>) => {
      const index = state.items.findIndex(n => n.id === action.payload);
      if (index !== -1) {
        const notification = state.items[index];
        if (!notification.read) {
          state.unreadCount = Math.max(0, state.unreadCount - 1);
        }
        state.items.splice(index, 1);
      }
    },
    clearAllNotifications: (state) => {
      state.items = [];
      state.unreadCount = 0;
    }
  }
});

// Configure store
export const store = configureStore({
  reducer: {
    tickets: ticketsSlice.reducer,
    events: eventsSlice.reducer,
    user: userSlice.reducer,
    purchases: purchasesSlice.reducer,
    dashboard: dashboardSlice.reducer,
    alerts: alertsSlice.reducer,
    notifications: notificationsSlice.reducer
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        // Ignore these action types for serializable check
        ignoredActions: ['persist/PERSIST', 'persist/REHYDRATE'],
      },
    }),
  devTools: process.env.NODE_ENV !== 'production',
});

// Export types
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

// Export action creators
export const ticketsActions = ticketsSlice.actions;
export const eventsActions = eventsSlice.actions;
export const userActions = userSlice.actions;
export const purchasesActions = purchasesSlice.actions;
export const dashboardActions = dashboardSlice.actions;
export const alertsActions = alertsSlice.actions;
export const notificationsActions = notificationsSlice.actions;

// Selectors
export const selectTickets = (state: RootState) => state.tickets.items;
export const selectSelectedTicket = (state: RootState) => state.tickets.selectedTicket;
export const selectTicketFilters = (state: RootState) => state.tickets.filters;
export const selectTicketsLoading = (state: RootState) => state.tickets.loading;
export const selectTicketsError = (state: RootState) => state.tickets.error;

export const selectEvents = (state: RootState) => state.events.items;
export const selectSelectedEvent = (state: RootState) => state.events.selectedEvent;
export const selectEventsLoading = (state: RootState) => state.events.loading;

export const selectCurrentUser = (state: RootState) => state.user.currentUser;
export const selectUserLoading = (state: RootState) => state.user.loading;

export const selectPurchases = (state: RootState) => state.purchases.items;
export const selectCurrentPurchase = (state: RootState) => state.purchases.currentPurchase;
export const selectPurchasesLoading = (state: RootState) => state.purchases.loading;

export const selectDashboardStats = (state: RootState) => state.dashboard.stats;
export const selectDashboardLoading = (state: RootState) => state.dashboard.loading;
export const selectDashboardLastUpdated = (state: RootState) => state.dashboard.lastUpdated;

export const selectPriceAlerts = (state: RootState) => state.alerts.items;
export const selectAlertsLoading = (state: RootState) => state.alerts.loading;

export const selectNotifications = (state: RootState) => state.notifications.items;
export const selectUnreadNotificationCount = (state: RootState) => state.notifications.unreadCount;

// Note: Async thunks are already exported above
