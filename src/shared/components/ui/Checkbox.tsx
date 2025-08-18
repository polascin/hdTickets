import React from 'react'
import clsx from 'clsx'

interface CheckboxProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'type'> {
  label?: string
  description?: string
  error?: string
  variant?: 'default' | 'card'
}

export const Checkbox: React.FC<CheckboxProps> = ({
  className,
  label,
  description,
  error,
  variant = 'default',
  ...props
}) => {
  const checkboxId = props.id || `checkbox-${Math.random().toString(36).substr(2, 9)}`

  return (
    <div className={clsx(
      'flex items-start gap-3',
      variant === 'card' && 'p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors',
      className
    )}>
      <div className="flex items-center h-5">
        <input
          type="checkbox"
          id={checkboxId}
          className={clsx(
            'w-4 h-4 text-blue-600 border-gray-300 rounded',
            'focus:ring-blue-500 focus:ring-2 focus:ring-offset-0',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            error && 'border-red-300 text-red-600'
          )}
          {...props}
        />
      </div>
      
      {(label || description) && (
        <div className="flex-1 min-w-0">
          {label && (
            <label 
              htmlFor={checkboxId} 
              className={clsx(
                'block text-sm font-medium cursor-pointer',
                error ? 'text-red-700' : 'text-gray-900',
                props.disabled && 'opacity-50 cursor-not-allowed'
              )}
            >
              {label}
            </label>
          )}
          {description && (
            <p className={clsx(
              'text-sm',
              error ? 'text-red-600' : 'text-gray-600',
              props.disabled && 'opacity-50'
            )}>
              {description}
            </p>
          )}
          {error && (
            <p className="text-sm text-red-600 mt-1">
              {error}
            </p>
          )}
        </div>
      )}
    </div>
  )
}

export default Checkbox
