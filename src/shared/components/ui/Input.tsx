import React from 'react'
import clsx from 'clsx'

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  className?: string
  size?: 'sm' | 'md' | 'lg'
  error?: boolean
  label?: string
  description?: string
  leftIcon?: React.ReactNode
  rightIcon?: React.ReactNode
}

export const Input: React.FC<InputProps> = ({
  className,
  size = 'md',
  error = false,
  label,
  description,
  leftIcon,
  rightIcon,
  id,
  ...props
}) => {
  const inputId = id || `input-${Math.random().toString(36).substr(2, 9)}`

  const inputComponent = (
    <div className="relative">
      {/* Left Icon */}
      {leftIcon && (
        <div className="absolute inset-y-0 left-0 flex items-center pointer-events-none">
          <div
            className={clsx(
              'text-gray-400',
              {
                'ml-2': size === 'sm',
                'ml-3': size === 'md',
                'ml-4': size === 'lg',
              }
            )}
          >
            {leftIcon}
          </div>
        </div>
      )}
      
      <input
        id={inputId}
        className={clsx(
          // Base styles
          'block w-full rounded-lg border bg-white',
          'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-nfl-500 focus:border-nfl-500',
          'disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50',
          'transition-colors duration-200',
          
          // Size variants
          {
            'px-3 py-1.5 text-sm': size === 'sm',
            'px-3 py-2 text-sm': size === 'md', 
            'px-4 py-3 text-base': size === 'lg',
          },
          
          // Icon padding adjustments
          {
            'pl-8': leftIcon && size === 'sm',
            'pl-10': leftIcon && size === 'md',
            'pl-12': leftIcon && size === 'lg',
            'pr-8': rightIcon && size === 'sm',
            'pr-10': rightIcon && size === 'md',
            'pr-12': rightIcon && size === 'lg',
          },
          
          // Color variants
          error
            ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 text-gray-900 placeholder-gray-400',
          
          className
        )}
        {...props}
      />
      
      {/* Right Icon */}
      {rightIcon && (
        <div className="absolute inset-y-0 right-0 flex items-center pointer-events-none">
          <div
            className={clsx(
              'text-gray-400',
              {
                'mr-2': size === 'sm',
                'mr-3': size === 'md',
                'mr-4': size === 'lg',
              }
            )}
          >
            {rightIcon}
          </div>
        </div>
      )}
    </div>
  )

  // If label or description provided, wrap in a container
  if (label || description) {
    return (
      <div className="w-full">
        {label && (
          <label
            htmlFor={inputId}
            className={clsx(
              'block font-medium text-gray-900 mb-1',
              size === 'sm' && 'text-sm',
              size === 'md' && 'text-sm',
              size === 'lg' && 'text-base'
            )}
          >
            {label}
          </label>
        )}
        
        {inputComponent}
        
        {description && (
          <p
            className={clsx(
              'mt-1 text-gray-600',
              error && 'text-red-600',
              size === 'sm' && 'text-xs',
              size === 'md' && 'text-sm',
              size === 'lg' && 'text-sm'
            )}
          >
            {description}
          </p>
        )}
      </div>
    )
  }

  return inputComponent
}
