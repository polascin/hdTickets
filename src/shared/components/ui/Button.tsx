import React from 'react'
import clsx from 'clsx'

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  isLoading?: boolean
  leftIcon?: React.ReactNode
  rightIcon?: React.ReactNode
  fullWidth?: boolean
}

export const Button: React.FC<ButtonProps> = ({
  children,
  className,
  variant = 'primary',
  size = 'md',
  isLoading = false,
  leftIcon,
  rightIcon,
  fullWidth = false,
  disabled,
  ...props
}) => {
  const isDisabled = disabled || isLoading

  return (
    <button
      className={clsx(
        // Base styles
        'inline-flex items-center justify-center font-medium rounded-lg',
        'transition-all duration-200 ease-in-out',
        'focus:outline-none focus:ring-2 focus:ring-offset-2',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        
        // Size variants
        {
          'px-3 py-1.5 text-sm gap-1.5': size === 'sm',
          'px-4 py-2 text-sm gap-2': size === 'md',
          'px-6 py-3 text-base gap-2': size === 'lg',
        },
        
        // Color variants
        {
          // Primary
          'bg-nfl-600 text-white hover:bg-nfl-700 focus:ring-nfl-500 shadow-sm hover:shadow-md':
            variant === 'primary',
          
          // Secondary
          'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500 shadow-sm hover:shadow-md':
            variant === 'secondary',
          
          // Outline
          'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-500 shadow-sm':
            variant === 'outline',
          
          // Ghost
          'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500':
            variant === 'ghost',
          
          // Danger
          'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm hover:shadow-md':
            variant === 'danger',
        },
        
        // Full width
        fullWidth && 'w-full',
        
        // Loading state
        isLoading && 'cursor-wait',
        
        className
      )}
      disabled={isDisabled}
      {...props}
    >
      {/* Left Icon or Loading Spinner */}
      {isLoading ? (
        <div className="animate-spin">
          <svg className="w-4 h-4" viewBox="0 0 24 24">
            <circle
              className="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              strokeWidth="4"
              fill="none"
            />
            <path
              className="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
        </div>
      ) : (
        leftIcon
      )}
      
      {/* Button Text */}
      <span>{children}</span>
      
      {/* Right Icon */}
      {!isLoading && rightIcon}
    </button>
  )
}
