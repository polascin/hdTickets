/**
 * HD Tickets - Redux Store Configuration
 * Modern state management with Redux Toolkit
 */

import { configureStore, combineReducers } from '@reduxjs/toolkit';
import { TypedUseSelectorHook, useDispatch, useSelector } from 'react-redux';

// Slice imports
import authSlice from './slices/authSlice';
import eventsSlice from './slices/eventsSlice';
import ticketsSlice from './slices/ticketsSlice';
import cartSlice from './slices/cartSlice';
import notificationsSlice from './slices/notificationsSlice';
import uiSlice from './slices/uiSlice';

// Types
import type { RootState } from '../types';

// Root reducer
const rootReducer = combineReducers({
  auth: authSlice,
  events: eventsSlice,
  tickets: ticketsSlice,
  cart: cartSlice,
  notifications: notificationsSlice,
  ui: uiSlice,
});

// Store configuration
export const store = configureStore({
  reducer: rootReducer,
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: [
          'persist/PERSIST',
          'persist/REHYDRATE',
          'persist/PAUSE',
          'persist/PURGE',
          'persist/REGISTER',
        ],
        ignoredActionsPaths: ['meta.arg', 'payload.timestamp'],
        ignoredPaths: ['items.dates'],
      },
      thunk: {
        extraArgument: {
          api: null, // Will be set up with API instance
        },
      },
    }),
  devTools: process.env.NODE_ENV !== 'production',
});

// Export types
export type AppDispatch = typeof store.dispatch;
export type AppThunk<ReturnType = void> = (
  dispatch: AppDispatch,
  getState: () => RootState
) => ReturnType;

// Typed hooks
export const useAppDispatch = (): AppDispatch => useDispatch<AppDispatch>();
export const useAppSelector: TypedUseSelectorHook<RootState> = useSelector;

// Selectors
export const selectIsAuthenticated = (state: RootState) => state.auth.isAuthenticated;
export const selectCurrentUser = (state: RootState) => state.auth.user;
export const selectAuthLoading = (state: RootState) => state.auth.isLoading;
export const selectAuthError = (state: RootState) => state.auth.error;

export const selectEvents = (state: RootState) => state.events.events;
export const selectCurrentEvent = (state: RootState) => state.events.currentEvent;
export const selectEventsLoading = (state: RootState) => state.events.isLoading;
export const selectEventsFilters = (state: RootState) => state.events.filters;
export const selectEventsPagination = (state: RootState) => state.events.pagination;

export const selectTickets = (state: RootState) => state.tickets.tickets;
export const selectSelectedTicket = (state: RootState) => state.tickets.selectedTicket;
export const selectTicketsLoading = (state: RootState) => state.tickets.isLoading;
export const selectTicketsFilters = (state: RootState) => state.tickets.filters;
export const selectPriceAlerts = (state: RootState) => state.tickets.priceAlerts;

export const selectCartItems = (state: RootState) => state.cart.cart.items;
export const selectCartTotal = (state: RootState) => state.cart.cart.totalPrice;
export const selectCartItemCount = (state: RootState) => state.cart.cart.totalItems;
export const selectIsCheckingOut = (state: RootState) => state.cart.isCheckingOut;

export const selectNotifications = (state: RootState) => state.notifications.notifications;
export const selectUnreadNotifications = (state: RootState) => 
  state.notifications.notifications.filter(n => !n.isRead);
export const selectUnreadCount = (state: RootState) => state.notifications.unreadCount;

export const selectTheme = (state: RootState) => state.ui.theme;
export const selectSidebarOpen = (state: RootState) => state.ui.sidebarOpen;
export const selectToasts = (state: RootState) => state.ui.toasts;

// Export store
export default store;