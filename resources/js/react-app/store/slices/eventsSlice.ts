import { createSlice } from '@reduxjs/toolkit';
import type { EventsState } from '../../types';

const initialState: EventsState = {
  events: [],
  currentEvent: null,
  filters: {},
  pagination: { currentPage: 1, totalPages: 1, totalItems: 0 },
  isLoading: false,
  error: null,
};

const eventsSlice = createSlice({
  name: 'events',
  initialState,
  reducers: {},
});

export default eventsSlice.reducer;