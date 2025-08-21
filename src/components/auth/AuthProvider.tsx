'use client';

import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { api } from '@/lib/api/client';
import type { User, AuthState } from '@/types';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (userData: RegisterData) => Promise<void>;
  logout: () => Promise<void>;
  updateUser: (userData: Partial<User>) => void;
  refreshUser: () => Promise<void>;
}

interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [authState, setAuthState] = useState<AuthState>({
    isAuthenticated: false,
    isLoading: true,
    token: null,
    refreshToken: null,
    expiresAt: null,
  });
  const [user, setUser] = useState<User | null>(null);
  const router = useRouter();

  // Initialize auth state on mount
  useEffect(() => {
    initializeAuth();
    
    // Listen for auth events
    const handleAuthLogout = () => {
      logout();
    };

    window.addEventListener('auth:logout', handleAuthLogout);
    
    return () => {
      window.removeEventListener('auth:logout', handleAuthLogout);
    };
  }, []);

  const initializeAuth = useCallback(async () => {
    try {
      // Check if user is already authenticated
      if (api.auth.isAuthenticated()) {
        const userData = await api.auth.getUser();
        setUser(userData);
        setAuthState(prev => ({
          ...prev,
          isAuthenticated: true,
          isLoading: false,
          token: api.auth.getUser() // This would need to be updated to get token
        }));
      } else {
        setAuthState(prev => ({
          ...prev,
          isLoading: false,
        }));
      }
    } catch (error) {
      console.error('Auth initialization failed:', error);
      // Clear auth data if initialization fails
      api.auth.clearAuth();
      setAuthState(prev => ({
        ...prev,
        isAuthenticated: false,
        isLoading: false,
        token: null,
        refreshToken: null,
        expiresAt: null,
      }));
    }
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    try {
      setAuthState(prev => ({ ...prev, isLoading: true }));
      
      const { user: userData, token, refreshToken } = await api.auth.login(email, password);
      
      setUser(userData);
      setAuthState({
        isAuthenticated: true,
        isLoading: false,
        token,
        refreshToken,
        expiresAt: null, // Would be calculated from token
      });

      // Redirect to dashboard after successful login
      router.push('/');
    } catch (error) {
      setAuthState(prev => ({ ...prev, isLoading: false }));
      throw error;
    }
  }, [router]);

  const register = useCallback(async (userData: RegisterData) => {
    try {
      setAuthState(prev => ({ ...prev, isLoading: true }));
      
      const { user: newUser, token, refreshToken } = await api.auth.register(userData);
      
      setUser(newUser);
      setAuthState({
        isAuthenticated: true,
        isLoading: false,
        token,
        refreshToken,
        expiresAt: null,
      });

      // Redirect to dashboard after successful registration
      router.push('/');
    } catch (error) {
      setAuthState(prev => ({ ...prev, isLoading: false }));
      throw error;
    }
  }, [router]);

  const logout = useCallback(async () => {
    try {
      await api.auth.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Clear state regardless of API call success
      setUser(null);
      setAuthState({
        isAuthenticated: false,
        isLoading: false,
        token: null,
        refreshToken: null,
        expiresAt: null,
      });
      
      // Redirect to login page
      router.push('/auth/login');
    }
  }, [router]);

  const updateUser = useCallback((userData: Partial<User>) => {
    setUser(prev => prev ? { ...prev, ...userData } : null);
  }, []);

  const refreshUser = useCallback(async () => {
    try {
      if (authState.isAuthenticated) {
        const userData = await api.auth.getUser();
        setUser(userData);
      }
    } catch (error) {
      console.error('Failed to refresh user data:', error);
      // If refresh fails, user might need to re-authenticate
      await logout();
    }
  }, [authState.isAuthenticated, logout]);

  const value: AuthContextType = {
    user,
    isAuthenticated: authState.isAuthenticated,
    isLoading: authState.isLoading,
    login,
    register,
    logout,
    updateUser,
    refreshUser,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}
