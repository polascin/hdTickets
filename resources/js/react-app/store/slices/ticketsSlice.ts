import { createSlice } from '@reduxjs/toolkit';
import type { TicketsState } from '../../types';

const initialState: TicketsState = {
  tickets: [],
  selectedTicket: null,
  filters: {},
  priceAlerts: [],
  isLoading: false,
  error: null,
};

const ticketsSlice = createSlice({
  name: 'tickets',
  initialState,
  reducers: {},
});

export default ticketsSlice.reducer;