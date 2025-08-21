import React from 'react'
import clsx from 'clsx'

interface BadgeProps {
  children: React.ReactNode
  className?: string
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'info' | 'outline'
  size?: 'sm' | 'md' | 'lg'
  dot?: boolean
}

export const Badge: React.FC<BadgeProps> = ({
  children,
  className,
  variant = 'default',
  size = 'md',
  dot = false,
  ...props
}) => {
  return (
    <span
      className={clsx(
        // Base styles
        'inline-flex items-center font-medium rounded-full',
        'transition-colors duration-200',
        
        // Size variants
        {
          'px-2 py-0.5 text-xs gap-1': size === 'sm',
          'px-2.5 py-1 text-xs gap-1.5': size === 'md',
          'px-3 py-1.5 text-sm gap-2': size === 'lg',
        },
        
        // Color variants
        {
          // Default
          'bg-gray-100 text-gray-800': variant === 'default',
          
          // Success
          'bg-green-100 text-green-800': variant === 'success',
          
          // Warning
          'bg-yellow-100 text-yellow-800': variant === 'warning',
          
          // Danger
          'bg-red-100 text-red-800': variant === 'danger',
          
          // Info
          'bg-blue-100 text-blue-800': variant === 'info',
          
          // Outline
          'bg-transparent border border-gray-300 text-gray-700': variant === 'outline',
        },
        
        className
      )}
      {...props}
    >
      {/* Optional dot indicator */}
      {dot && (
        <div
          className={clsx(
            'rounded-full',
            {
              'w-1.5 h-1.5': size === 'sm',
              'w-2 h-2': size === 'md',
              'w-2.5 h-2.5': size === 'lg',
            },
            {
              'bg-gray-400': variant === 'default',
              'bg-green-500': variant === 'success',
              'bg-yellow-500': variant === 'warning',
              'bg-red-500': variant === 'danger',
              'bg-blue-500': variant === 'info',
              'bg-gray-400': variant === 'outline',
            }
          )}
        />
      )}
      
      <span>{children}</span>
    </span>
  )
}
