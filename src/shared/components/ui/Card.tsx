import React from 'react'
import clsx from 'clsx'

interface CardProps {
  children: React.ReactNode
  className?: string
  variant?: 'default' | 'elevated' | 'outlined'
  hover?: boolean
}

interface CardSectionProps {
  children: React.ReactNode
  className?: string
}

export const Card: React.FC<CardProps> = ({ 
  children, 
  className,
  variant = 'default',
  hover = false,
  ...props 
}) => {
  return (
    <div
      className={clsx(
        'bg-white rounded-xl border border-gray-200',
        variant === 'elevated' && 'shadow-enterprise-md',
        variant === 'outlined' && 'border-2',
        hover && 'transition-all duration-200 hover:shadow-enterprise-lg hover:-translate-y-0.5',
        className
      )}
      {...props}
    >
      {children}
    </div>
  )
}

export const CardHeader: React.FC<CardSectionProps> = ({ 
  children, 
  className,
  ...props 
}) => {
  return (
    <div
      className={clsx(
        'px-6 py-4 border-b border-gray-100',
        className
      )}
      {...props}
    >
      {children}
    </div>
  )
}

export const CardContent: React.FC<CardSectionProps> = ({ 
  children, 
  className,
  ...props 
}) => {
  return (
    <div
      className={clsx(
        'px-6 py-4',
        className
      )}
      {...props}
    >
      {children}
    </div>
  )
}

export const CardFooter: React.FC<CardSectionProps> = ({ 
  children, 
  className,
  ...props 
}) => {
  return (
    <div
      className={clsx(
        'px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-xl',
        className
      )}
      {...props}
    >
      {children}
    </div>
  )
}
