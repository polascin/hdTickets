import React from 'react'
import clsx from 'clsx'

interface ToggleProps {
  checked: boolean
  onChange: (checked: boolean) => void
  label?: string
  description?: string
  disabled?: boolean
  size?: 'sm' | 'md' | 'lg'
  variant?: 'default' | 'success' | 'warning' | 'danger'
  className?: string
  id?: string
}

export const Toggle: React.FC<ToggleProps> = ({
  checked,
  onChange,
  label,
  description,
  disabled = false,
  size = 'md',
  variant = 'default',
  className,
  id,
}) => {
  const toggleId = id || `toggle-${Math.random().toString(36).substr(2, 9)}`
  
  const sizeClasses = {
    sm: {
      switch: 'h-4 w-7',
      thumb: 'h-3 w-3',
      translate: 'translate-x-3',
    },
    md: {
      switch: 'h-5 w-9',
      thumb: 'h-4 w-4',
      translate: 'translate-x-4',
    },
    lg: {
      switch: 'h-6 w-11',
      thumb: 'h-5 w-5',
      translate: 'translate-x-5',
    },
  }
  
  const variantClasses = {
    default: 'bg-blue-600',
    success: 'bg-green-600',
    warning: 'bg-yellow-600',
    danger: 'bg-red-600',
  }
  
  const handleToggle = () => {
    if (!disabled) {
      onChange(!checked)
    }
  }
  
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault()
      handleToggle()
    }
  }
  
  return (
    <div className={clsx('flex items-start gap-3', className)}>
      <button
        type="button"
        id={toggleId}
        role="switch"
        aria-checked={checked}
        onClick={handleToggle}
        onKeyDown={handleKeyDown}
        disabled={disabled}
        className={clsx(
          'relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out',
          'focus:outline-none focus:ring-2 focus:ring-offset-2',
          sizeClasses[size].switch,
          checked 
            ? clsx(
                variantClasses[variant],
                'focus:ring-blue-500'
              )
            : 'bg-gray-200 focus:ring-gray-500',
          disabled && 'cursor-not-allowed opacity-50'
        )}
      >
        <span
          className={clsx(
            'pointer-events-none inline-block rounded-full bg-white shadow transform ring-0 transition duration-200 ease-in-out',
            sizeClasses[size].thumb,
            checked ? sizeClasses[size].translate : 'translate-x-0'
          )}
        />
      </button>
      
      {(label || description) && (
        <div className="flex-1 min-w-0">
          {label && (
            <label
              htmlFor={toggleId}
              className={clsx(
                'block text-sm font-medium cursor-pointer',
                disabled ? 'text-gray-400' : 'text-gray-900'
              )}
            >
              {label}
            </label>
          )}
          {description && (
            <p className={clsx(
              'text-sm',
              disabled ? 'text-gray-400' : 'text-gray-600'
            )}>
              {description}
            </p>
          )}
        </div>
      )}
    </div>
  )
}

export default Toggle
