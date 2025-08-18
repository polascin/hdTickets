import React from 'react'
import clsx from 'clsx'

interface SkeletonProps {
  className?: string
  variant?: 'text' | 'circular' | 'rectangular' | 'rounded'
  width?: string | number
  height?: string | number
  animation?: 'pulse' | 'wave' | 'none'
  children?: React.ReactNode
}

export const Skeleton: React.FC<SkeletonProps> = ({
  className,
  variant = 'rectangular',
  width,
  height,
  animation = 'pulse',
  children,
}) => {
  const baseClasses = 'bg-gray-200'
  
  const variantClasses = {
    text: 'h-4 rounded',
    circular: 'rounded-full',
    rectangular: 'rounded',
    rounded: 'rounded-lg',
  }
  
  const animationClasses = {
    pulse: 'animate-pulse',
    wave: 'animate-shimmer bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 bg-[length:200%_100%]',
    none: '',
  }
  
  const style: React.CSSProperties = {}
  
  if (width !== undefined) {
    style.width = typeof width === 'number' ? `${width}px` : width
  }
  
  if (height !== undefined) {
    style.height = typeof height === 'number' ? `${height}px` : height
  }
  
  return (
    <div
      className={clsx(
        baseClasses,
        variantClasses[variant],
        animationClasses[animation],
        className
      )}
      style={style}
      role="status"
      aria-label="Loading..."
    >
      {children}
    </div>
  )
}

// Skeleton component variations for common use cases
export const SkeletonText: React.FC<Omit<SkeletonProps, 'variant'>> = (props) => (
  <Skeleton {...props} variant="text" />
)

export const SkeletonAvatar: React.FC<Omit<SkeletonProps, 'variant'> & { size?: 'sm' | 'md' | 'lg' }> = ({ 
  size = 'md', 
  ...props 
}) => {
  const sizeClasses = {
    sm: 'w-8 h-8',
    md: 'w-12 h-12',
    lg: 'w-16 h-16',
  }
  
  return (
    <Skeleton 
      {...props} 
      variant="circular" 
      className={clsx(sizeClasses[size], props.className)}
    />
  )
}

export const SkeletonCard: React.FC<{
  lines?: number
  showAvatar?: boolean
  className?: string
}> = ({ lines = 3, showAvatar = false, className }) => {
  return (
    <div className={clsx('space-y-3', className)}>
      {showAvatar && (
        <div className="flex items-center space-x-3">
          <SkeletonAvatar size="md" />
          <div className="space-y-2 flex-1">
            <SkeletonText className="w-1/4" />
            <SkeletonText className="w-1/6" />
          </div>
        </div>
      )}
      
      <div className="space-y-2">
        {Array.from({ length: lines }).map((_, index) => (
          <SkeletonText
            key={index}
            className={clsx(
              index === lines - 1 ? 'w-2/3' : 'w-full'
            )}
          />
        ))}
      </div>
    </div>
  )
}

export const SkeletonTable: React.FC<{
  rows?: number
  columns?: number
  className?: string
}> = ({ rows = 5, columns = 4, className }) => {
  return (
    <div className={clsx('space-y-3', className)}>
      {/* Header */}
      <div className="grid gap-4" style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}>
        {Array.from({ length: columns }).map((_, index) => (
          <SkeletonText key={`header-${index}`} className="h-6" />
        ))}
      </div>
      
      {/* Rows */}
      {Array.from({ length: rows }).map((_, rowIndex) => (
        <div key={`row-${rowIndex}`} className="grid gap-4" style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}>
          {Array.from({ length: columns }).map((_, colIndex) => (
            <SkeletonText key={`cell-${rowIndex}-${colIndex}`} />
          ))}
        </div>
      ))}
    </div>
  )
}

export default Skeleton
