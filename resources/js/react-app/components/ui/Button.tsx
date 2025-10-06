/**
 * HD Tickets - Button Component
 * Reusable button component with variants, sizes, and states
 */

import React from 'react';
import { motion, type HTMLMotionProps } from 'framer-motion';
import { cn, buttonVariants } from '../../utils/design';
import LoadingSpinner from './LoadingSpinner';

interface ButtonProps extends Omit<HTMLMotionProps<'button'>, 'size'> {
  variant?: keyof typeof buttonVariants.variant;
  size?: keyof typeof buttonVariants.size;
  isLoading?: boolean;
  loadingText?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  fullWidth?: boolean;
  children: React.ReactNode;
}

const Button: React.FC<ButtonProps> = ({
  variant = 'primary',
  size = 'base',
  isLoading = false,
  loadingText,
  leftIcon,
  rightIcon,
  fullWidth = false,
  className,
  disabled,
  children,
  ...props
}) => {
  const isDisabled = disabled || isLoading;

  return (
    <motion.button
      whileHover={!isDisabled ? { scale: 1.02 } : {}}
      whileTap={!isDisabled ? { scale: 0.98 } : {}}
      className={cn(
        // Base styles
        'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed',
        
        // Variant styles
        buttonVariants.variant[variant],
        
        // Size styles
        buttonVariants.size[size],
        
        // Full width
        fullWidth && 'w-full',
        
        // Custom className
        className
      )}
      disabled={isDisabled}
      {...props}
    >
      {/* Loading state */}
      {isLoading && (
        <LoadingSpinner
          size={size === 'xs' || size === 'sm' ? 'xs' : 'sm'}
          color="current"
          className="mr-2"
        />
      )}
      
      {/* Left icon */}
      {!isLoading && leftIcon && (
        <span className="mr-2">{leftIcon}</span>
      )}
      
      {/* Button content */}
      <span>
        {isLoading && loadingText ? loadingText : children}
      </span>
      
      {/* Right icon */}
      {!isLoading && rightIcon && (
        <span className="ml-2">{rightIcon}</span>
      )}
    </motion.button>
  );
};

// Icon-only button variant
export const IconButton: React.FC<{
  icon: React.ReactNode;
  'aria-label': string;
  variant?: ButtonProps['variant'];
  size?: ButtonProps['size'];
  className?: string;
  onClick?: () => void;
  disabled?: boolean;
}> = ({
  icon,
  'aria-label': ariaLabel,
  variant = 'ghost',
  size = 'base',
  className,
  ...props
}) => {
  const sizeClasses = {
    xs: 'p-1',
    sm: 'p-1.5',
    base: 'p-2',
    lg: 'p-3',
    xl: 'p-4',
  };

  return (
    <motion.button
      whileHover={{ scale: 1.05 }}
      whileTap={{ scale: 0.95 }}
      aria-label={ariaLabel}
      className={cn(
        'inline-flex items-center justify-center rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
        buttonVariants.variant[variant],
        sizeClasses[size],
        className
      )}
      {...props}
    >
      {icon}
    </motion.button>
  );
};

// Button group for related actions
export const ButtonGroup: React.FC<{
  children: React.ReactNode;
  className?: string;
}> = ({ children, className }) => {
  return (
    <div className={cn('inline-flex rounded-lg shadow-sm', className)}>
      {React.Children.map(children, (child, index) => {
        if (React.isValidElement(child)) {
          return React.cloneElement(child, {
            className: cn(
              child.props.className,
              index === 0 ? 'rounded-r-none' : index === React.Children.count(children) - 1 ? 'rounded-l-none' : 'rounded-none',
              index > 0 && '-ml-px'
            ),
          });
        }
        return child;
      })}
    </div>
  );
};

// Floating Action Button
export const FloatingActionButton: React.FC<{
  icon: React.ReactNode;
  onClick: () => void;
  'aria-label': string;
  position?: 'bottom-right' | 'bottom-left' | 'top-right' | 'top-left';
  className?: string;
}> = ({
  icon,
  onClick,
  'aria-label': ariaLabel,
  position = 'bottom-right',
  className,
}) => {
  const positionClasses = {
    'bottom-right': 'bottom-6 right-6',
    'bottom-left': 'bottom-6 left-6',
    'top-right': 'top-6 right-6',
    'top-left': 'top-6 left-6',
  };

  return (
    <motion.button
      whileHover={{ scale: 1.1 }}
      whileTap={{ scale: 0.9 }}
      initial={{ scale: 0 }}
      animate={{ scale: 1 }}
      exit={{ scale: 0 }}
      onClick={onClick}
      aria-label={ariaLabel}
      className={cn(
        'fixed z-50 h-14 w-14 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
        positionClasses[position],
        className
      )}
    >
      {icon}
    </motion.button>
  );
};

export default Button;