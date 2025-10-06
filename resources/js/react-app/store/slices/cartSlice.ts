import { createSlice } from '@reduxjs/toolkit';
import type { CartState } from '../../types';

const initialState: CartState = {
  cart: { items: [], totalPrice: 0, totalItems: 0, currency: 'USD' },
  isCheckingOut: false,
  isLoading: false,
  error: null,
};

const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {},
});

export default cartSlice.reducer;