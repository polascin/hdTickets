'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from './AuthProvider';

interface AuthGuardProps {
  children: React.ReactNode;
  requireAuth?: boolean;
  redirectTo?: string;
  fallback?: React.ReactNode;
}

export function AuthGuard({ 
  children, 
  requireAuth = true, 
  redirectTo = '/auth/login',
  fallback
}: AuthGuardProps) {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading) {
      if (requireAuth && !user) {
        // User needs to be authenticated but isn't
        const currentPath = window.location.pathname;
        const redirectUrl = `${redirectTo}?redirect=${encodeURIComponent(currentPath)}`;
        router.push(redirectUrl);
      } else if (!requireAuth && user) {
        // User shouldn't be authenticated but is (e.g., on login page)
        router.push('/dashboard');
      }
    }
  }, [user, isLoading, requireAuth, redirectTo, router]);

  // Show loading state
  if (isLoading) {
    return (
      fallback || (
        <div className="min-h-screen bg-gray-50 flex items-center justify-center">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p className="text-gray-600">Loading...</p>
          </div>
        </div>
      )
    );
  }

  // Check authentication requirements
  if (requireAuth && !user) {
    // User needs to be authenticated but isn't - redirect will happen in useEffect
    return null;
  }

  if (!requireAuth && user) {
    // User shouldn't be authenticated but is - redirect will happen in useEffect
    return null;
  }

  // All checks passed, render children
  return <>{children}</>;
}
