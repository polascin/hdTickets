'use client';

import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { LoginForm } from './LoginForm';
import { RegisterForm } from './RegisterForm';

interface AuthWrapperProps {
  defaultMode?: 'login' | 'register';
  redirectTo?: string;
}

export function AuthWrapper({ defaultMode = 'login', redirectTo }: AuthWrapperProps) {
  const [mode, setMode] = useState<'login' | 'register'>(defaultMode);

  const switchToRegister = () => setMode('register');
  const switchToLogin = () => setMode('login');

  return (
    <div className="auth-wrapper">
      <AnimatePresence mode="wait">
        {mode === 'login' ? (
          <motion.div
            key="login"
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: 20 }}
            transition={{ duration: 0.3 }}
          >
            <LoginForm 
              onSwitchToRegister={switchToRegister}
              redirectTo={redirectTo}
            />
          </motion.div>
        ) : (
          <motion.div
            key="register"
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            transition={{ duration: 0.3 }}
          >
            <RegisterForm 
              onSwitchToLogin={switchToLogin}
              redirectTo={redirectTo}
            />
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
