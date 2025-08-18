import React from 'react'
import clsx from 'clsx'

interface SwitchProps {
  checked: boolean
  onChange: (checked: boolean) => void
  className?: string
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
  label?: string
  description?: string
}

export const Switch: React.FC<SwitchProps> = ({
  checked,
  onChange,
  className,
  size = 'md',
  disabled = false,
  label,
  description,
  ...props
}) => {
  const handleClick = () => {
    if (!disabled) {
      onChange(!checked)
    }
  }

  const switchComponent = (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      onClick={handleClick}
      disabled={disabled}
      className={clsx(
        // Base styles
        'relative inline-flex shrink-0 rounded-full transition-colors duration-200 ease-in-out',
        'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-nfl-500',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        
        // Size variants
        {
          'w-8 h-5': size === 'sm',
          'w-11 h-6': size === 'md',
          'w-14 h-8': size === 'lg',
        },
        
        // Color variants
        checked ? 'bg-nfl-600' : 'bg-gray-200',
        
        className
      )}
      {...props}
    >
      <span
        className={clsx(
          // Base styles
          'pointer-events-none inline-block rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
          
          // Size variants
          {
            'w-4 h-4': size === 'sm',
            'w-5 h-5': size === 'md',
            'w-7 h-7': size === 'lg',
          },
          
          // Position based on checked state
          {
            'translate-x-0': !checked && size === 'sm',
            'translate-x-3': checked && size === 'sm',
            'translate-x-0': !checked && size === 'md',
            'translate-x-5': checked && size === 'md',
            'translate-x-0': !checked && size === 'lg',
            'translate-x-6': checked && size === 'lg',
          }
        )}
      />
    </button>
  )

  // If label or description provided, wrap in a container
  if (label || description) {
    return (
      <div className="flex items-start gap-3">
        {switchComponent}
        <div className="flex-1">
          {label && (
            <label
              className={clsx(
                'block font-medium text-gray-900 cursor-pointer',
                disabled && 'cursor-not-allowed opacity-50',
                size === 'sm' && 'text-sm',
                size === 'md' && 'text-sm',
                size === 'lg' && 'text-base'
              )}
              onClick={handleClick}
            >
              {label}
            </label>
          )}
          {description && (
            <p
              className={clsx(
                'text-gray-600',
                disabled && 'opacity-50',
                size === 'sm' && 'text-xs',
                size === 'md' && 'text-sm',
                size === 'lg' && 'text-sm'
              )}
            >
              {description}
            </p>
          )}
        </div>
      </div>
    )
  }

  return switchComponent
}
