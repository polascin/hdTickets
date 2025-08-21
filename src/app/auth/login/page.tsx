'use client';

import { useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAuth } from '@/components/auth/AuthProvider';
import { AuthWrapper } from '@/components/auth/AuthWrapper';

export default function LoginPage() {
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const searchParams = useSearchParams();
  
  const redirectTo = searchParams.get('redirect') || '/dashboard';

  useEffect(() => {
    // If user is already authenticated, redirect them
    if (user && !isLoading) {
      router.push(redirectTo);
    }
  }, [user, isLoading, router, redirectTo]);

  // Show loading state while checking authentication
  if (isLoading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-purple-900 flex items-center justify-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
      </div>
    );
  }

  // If user is authenticated, don't show the login form
  if (user) {
    return null;
  }

  return <AuthWrapper defaultMode="login" redirectTo={redirectTo} />;
}
