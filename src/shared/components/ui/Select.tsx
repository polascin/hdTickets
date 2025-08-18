import React from 'react'
import clsx from 'clsx'
import { ChevronDownIcon } from '@heroicons/react/24/outline'

interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  children: React.ReactNode
  className?: string
  size?: 'sm' | 'md' | 'lg'
  error?: boolean
  label?: string
  description?: string
}

export const Select: React.FC<SelectProps> = ({
  children,
  className,
  size = 'md',
  error = false,
  label,
  description,
  id,
  ...props
}) => {
  const selectId = id || `select-${Math.random().toString(36).substr(2, 9)}`

  const selectComponent = (
    <div className="relative">
      <select
        id={selectId}
        className={clsx(
          // Base styles
          'block w-full rounded-lg border appearance-none bg-white',
          'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-nfl-500 focus:border-nfl-500',
          'disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50',
          'transition-colors duration-200',
          
          // Size variants
          {
            'px-3 py-1.5 text-sm pr-8': size === 'sm',
            'px-3 py-2 text-sm pr-10': size === 'md',
            'px-4 py-3 text-base pr-12': size === 'lg',
          },
          
          // Color variants
          error
            ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 text-gray-900 placeholder-gray-400',
          
          className
        )}
        {...props}
      >
        {children}
      </select>
      
      {/* Chevron Icon */}
      <div className="absolute inset-y-0 right-0 flex items-center pointer-events-none">
        <ChevronDownIcon
          className={clsx(
            'text-gray-400',
            {
              'w-4 h-4 mr-2': size === 'sm',
              'w-5 h-5 mr-2.5': size === 'md',
              'w-6 h-6 mr-3': size === 'lg',
            }
          )}
        />
      </div>
    </div>
  )

  // If label or description provided, wrap in a container
  if (label || description) {
    return (
      <div className="w-full">
        {label && (
          <label
            htmlFor={selectId}
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
        
        {selectComponent}
        
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

  return selectComponent
}
