import { useState, useEffect, useCallback, useRef } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import type { ApiResponse, ApiError, SearchFilters, Ticket, PriceAlert, User } from '@/types';

// Base API hook with error handling and loading states
export function useApi<T>(
  endpoint: string,
  options?: {
    enabled?: boolean;
    refetchInterval?: number;
    retry?: number;
    staleTime?: number;
  }
) {
  const {
    data,
    error,
    isLoading,
    isError,
    refetch,
    isRefetching
  } = useQuery<ApiResponse<T>, ApiError>({
    queryKey: [endpoint],
    queryFn: () => fetch(`/api/${endpoint}`).then(res => {
      if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      return res.json();
    }),
    enabled: options?.enabled ?? true,
    refetchInterval: options?.refetchInterval,
    retry: options?.retry ?? 3,
    staleTime: options?.staleTime ?? 1000 * 60 * 5, // 5 minutes
  });

  return {
    data: data?.data,
    meta: data?.meta,
    links: data?.links,
    error,
    isLoading,
    isError,
    refetch,
    isRefetching
  };
}

// Paginated API hook
export function usePaginatedApi<T>(
  endpoint: string,
  page: number = 1,
  perPage: number = 20,
  filters?: Record<string, any>
) {
  const queryParams = new URLSearchParams({
    page: page.toString(),
    per_page: perPage.toString(),
    ...Object.fromEntries(
      Object.entries(filters || {}).filter(([_, value]) => value != null)
    )
  });

  const queryKey = [endpoint, page, perPage, filters];
  
  const {
    data,
    error,
    isLoading,
    isError,
    refetch
  } = useQuery<ApiResponse<T[]>, ApiError>({
    queryKey,
    queryFn: () => 
      fetch(`/api/${endpoint}?${queryParams}`)
        .then(res => {
          if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
          return res.json();
        }),
    keepPreviousData: true,
    staleTime: 1000 * 60 * 2, // 2 minutes
  });

  return {
    data: data?.data || [],
    meta: data?.meta,
    links: data?.links,
    error,
    isLoading,
    isError,
    refetch
  };
}

// Mutation hook with optimistic updates
export function useApiMutation<TData, TVariables>(
  endpoint: string,
  method: 'POST' | 'PUT' | 'PATCH' | 'DELETE' = 'POST',
  options?: {
    onSuccess?: (data: TData) => void;
    onError?: (error: ApiError) => void;
    invalidateQueries?: string[];
    optimisticUpdate?: (variables: TVariables) => void;
  }
) {
  const queryClient = useQueryClient();

  return useMutation<TData, ApiError, TVariables>({
    mutationFn: async (variables: TVariables) => {
      const response = await fetch(`/api/${endpoint}`, {
        method,
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(variables),
      });

      if (!response.ok) {
        const error = await response.json();
        throw error;
      }

      return response.json();
    },
    onMutate: options?.optimisticUpdate,
    onSuccess: (data) => {
      // Invalidate and refetch queries
      options?.invalidateQueries?.forEach(queryKey => {
        queryClient.invalidateQueries({ queryKey: [queryKey] });
      });
      options?.onSuccess?.(data);
    },
    onError: options?.onError,
  });
}

// Infinite scroll hook
export function useInfiniteApi<T>(
  endpoint: string,
  filters?: Record<string, any>,
  perPage: number = 20
) {
  const [items, setItems] = useState<T[]>([]);
  const [hasMore, setHasMore] = useState(true);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<ApiError | null>(null);
  const pageRef = useRef(1);

  const loadMore = useCallback(async () => {
    if (isLoading || !hasMore) return;

    setIsLoading(true);
    setError(null);

    try {
      const queryParams = new URLSearchParams({
        page: pageRef.current.toString(),
        per_page: perPage.toString(),
        ...Object.fromEntries(
          Object.entries(filters || {}).filter(([_, value]) => value != null)
        )
      });

      const response = await fetch(`/api/${endpoint}?${queryParams}`);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const result: ApiResponse<T[]> = await response.json();
      
      if (pageRef.current === 1) {
        setItems(result.data);
      } else {
        setItems(prev => [...prev, ...result.data]);
      }

      setHasMore(result.meta.hasNextPage);
      pageRef.current += 1;
    } catch (err) {
      setError(err as ApiError);
    } finally {
      setIsLoading(false);
    }
  }, [endpoint, filters, perPage, isLoading, hasMore]);

  const reset = useCallback(() => {
    setItems([]);
    setHasMore(true);
    setError(null);
    pageRef.current = 1;
  }, []);

  const refresh = useCallback(() => {
    reset();
    loadMore();
  }, [reset, loadMore]);

  useEffect(() => {
    reset();
    loadMore();
  }, [endpoint, filters]);

  return {
    items,
    hasMore,
    isLoading,
    error,
    loadMore,
    refresh,
    reset
  };
}

// Specific hooks for different entities
export function useTickets(filters: SearchFilters, page: number = 1) {
  return usePaginatedApi<Ticket>('tickets/search', page, 20, filters);
}

export function useTicket(id: string) {
  return useApi<Ticket>(`tickets/${id}`, { enabled: !!id });
}

export function usePriceAlerts(userId?: string) {
  return useApi<PriceAlert[]>(`alerts${userId ? `?user=${userId}` : ''}`);
}

export function useCreatePriceAlert() {
  return useApiMutation<PriceAlert, Partial<PriceAlert>>(
    'alerts',
    'POST',
    {
      invalidateQueries: ['alerts'],
      onSuccess: (alert) => {
        console.log('Price alert created:', alert);
      }
    }
  );
}

export function useUpdatePriceAlert() {
  return useApiMutation<PriceAlert, { id: string; data: Partial<PriceAlert> }>(
    'alerts',
    'PUT',
    {
      invalidateQueries: ['alerts'],
    }
  );
}

export function useDeletePriceAlert() {
  return useApiMutation<void, string>(
    'alerts',
    'DELETE',
    {
      invalidateQueries: ['alerts'],
    }
  );
}

export function useUser() {
  return useApi<User>('auth/me');
}

export function useUpdateUserPreferences() {
  return useApiMutation<User, Partial<User>>(
    'auth/preferences',
    'PUT',
    {
      invalidateQueries: ['auth/me'],
    }
  );
}
