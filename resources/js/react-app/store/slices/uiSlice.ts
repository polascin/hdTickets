/**
 * HD Tickets - UI Redux Slice
 * Manages UI state, theme, modals, and interface interactions
 */

import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import type { UIState, Toast } from '../../types';

// Initial state
const initialState: UIState = {
  theme: (localStorage.getItem('theme') as 'light' | 'dark' | 'system') || 'system',
  sidebarOpen: false,
  modalStack: [],
  toasts: [],
};

// UI slice
const uiSlice = createSlice({
  name: 'ui',
  initialState,
  reducers: {
    // Theme management
    setTheme: (state, action: PayloadAction<'light' | 'dark' | 'system'>) => {
      state.theme = action.payload;
      localStorage.setItem('theme', action.payload);
    },
    
    // Sidebar management
    toggleSidebar: (state) => {
      state.sidebarOpen = !state.sidebarOpen;
    },
    
    openSidebar: (state) => {
      state.sidebarOpen = true;
    },
    
    closeSidebar: (state) => {
      state.sidebarOpen = false;
    },
    
    // Modal management
    openModal: (state, action: PayloadAction<string>) => {
      state.modalStack.push(action.payload);
    },
    
    closeModal: (state, action: PayloadAction<string>) => {
      state.modalStack = state.modalStack.filter(modal => modal !== action.payload);
    },
    
    closeTopModal: (state) => {
      state.modalStack.pop();
    },
    
    closeAllModals: (state) => {
      state.modalStack = [];
    },
    
    // Toast management
    addToast: (state, action: PayloadAction<Omit<Toast, 'id'>>) => {
      const toast: Toast = {
        ...action.payload,
        id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
      };
      state.toasts.push(toast);
    },
    
    removeToast: (state, action: PayloadAction<string>) => {
      state.toasts = state.toasts.filter(toast => toast.id !== action.payload);
    },
    
    clearAllToasts: (state) => {
      state.toasts = [];
    },
  },
});

// Export actions
export const {
  setTheme,
  toggleSidebar,
  openSidebar,
  closeSidebar,
  openModal,
  closeModal,
  closeTopModal,
  closeAllModals,
  addToast,
  removeToast,
  clearAllToasts,
} = uiSlice.actions;

// Export reducer
export default uiSlice.reducer;