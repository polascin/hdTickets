import { createSlice } from '@reduxjs/toolkit';
import type { NotificationsState } from '../../types';

const initialState: NotificationsState = {
  notifications: [],
  unreadCount: 0,
  isLoading: false,
  error: null,
};

const notificationsSlice = createSlice({
  name: 'notifications',
  initialState,
  reducers: {},
});

export default notificationsSlice.reducer;