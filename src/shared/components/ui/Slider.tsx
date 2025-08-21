import React, { useState, useCallback } from 'react'
import clsx from 'clsx'

interface SliderProps {
  min: number
  max: number
  step?: number
  value: number | [number, number]
  onChange: (value: number | [number, number]) => void
  label?: string
  formatLabel?: (value: number) => string
  className?: string
  disabled?: boolean
  showLabels?: boolean
  marks?: Array<{ value: number; label?: string }>
}

export const Slider: React.FC<SliderProps> = ({
  min,
  max,
  step = 1,
  value,
  onChange,
  label,
  formatLabel,
  className,
  disabled = false,
  showLabels = true,
  marks = [],
}) => {
  const [isDragging, setIsDragging] = useState(false)
  
  const isRange = Array.isArray(value)
  const [minVal, maxVal] = isRange ? value : [min, value]
  
  const formatValue = useCallback((val: number) => {
    return formatLabel ? formatLabel(val) : val.toString()
  }, [formatLabel])
  
  const getPercentage = useCallback((val: number) => {
    return ((val - min) / (max - min)) * 100
  }, [min, max])
  
  const handleMouseDown = useCallback((e: React.MouseEvent<HTMLDivElement>) => {
    if (disabled) return
    
    const rect = e.currentTarget.getBoundingClientRect()
    const percentage = (e.clientX - rect.left) / rect.width
    const newValue = min + percentage * (max - min)
    const steppedValue = Math.round(newValue / step) * step
    const clampedValue = Math.max(min, Math.min(max, steppedValue))
    
    if (isRange) {
      const [currentMin, currentMax] = value as [number, number]
      const distanceToMin = Math.abs(clampedValue - currentMin)
      const distanceToMax = Math.abs(clampedValue - currentMax)
      
      if (distanceToMin <= distanceToMax) {
        onChange([clampedValue, currentMax])
      } else {
        onChange([currentMin, clampedValue])
      }
    } else {
      onChange(clampedValue)
    }
    
    setIsDragging(true)
  }, [disabled, min, max, step, value, onChange, isRange])
  
  React.useEffect(() => {
    const handleMouseUp = () => setIsDragging(false)
    
    if (isDragging) {
      document.addEventListener('mouseup', handleMouseUp)
      return () => document.removeEventListener('mouseup', handleMouseUp)
    }
  }, [isDragging])
  
  return (
    <div className={clsx('w-full', className)}>
      {label && (
        <label className="block text-sm font-medium text-gray-700 mb-2">
          {label}
        </label>
      )}
      
      <div className="relative">
        {/* Track */}
        <div 
          className={clsx(
            'relative h-2 bg-gray-200 rounded-full cursor-pointer',
            disabled && 'cursor-not-allowed opacity-50'
          )}
          onMouseDown={handleMouseDown}
        >
          {/* Active range */}
          <div
            className="absolute h-2 bg-blue-600 rounded-full"
            style={{
              left: `${getPercentage(minVal)}%`,
              width: `${getPercentage(isRange ? maxVal : maxVal) - getPercentage(minVal)}%`,
            }}
          />
          
          {/* Marks */}
          {marks.map((mark) => (
            <div
              key={mark.value}
              className="absolute w-1 h-1 bg-gray-400 rounded-full transform -translate-x-0.5"
              style={{ left: `${getPercentage(mark.value)}%`, top: '2px' }}
            />
          ))}
          
          {/* Min thumb (for range) or single thumb */}
          <div
            className={clsx(
              'absolute w-5 h-5 bg-white border-2 border-blue-600 rounded-full transform -translate-x-1/2 -translate-y-0.5',
              'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
              'hover:shadow-md transition-shadow',
              disabled && 'cursor-not-allowed',
              !disabled && 'cursor-grab active:cursor-grabbing'
            )}
            style={{ left: `${getPercentage(minVal)}%` }}
          />
          
          {/* Max thumb (only for range) */}
          {isRange && (
            <div
              className={clsx(
                'absolute w-5 h-5 bg-white border-2 border-blue-600 rounded-full transform -translate-x-1/2 -translate-y-0.5',
                'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                'hover:shadow-md transition-shadow',
                disabled && 'cursor-not-allowed',
                !disabled && 'cursor-grab active:cursor-grabbing'
              )}
              style={{ left: `${getPercentage(maxVal)}%` }}
            />
          )}
        </div>
        
        {/* Value labels */}
        {showLabels && (
          <div className="flex justify-between text-xs text-gray-600 mt-2">
            <span>{formatValue(min)}</span>
            {isRange ? (
              <span>
                {formatValue(minVal)} - {formatValue(maxVal)}
              </span>
            ) : (
              <span>{formatValue(maxVal)}</span>
            )}
            <span>{formatValue(max)}</span>
          </div>
        )}
        
        {/* Mark labels */}
        {marks.length > 0 && (
          <div className="relative mt-1">
            {marks.map((mark) => (
              mark.label && (
                <div
                  key={mark.value}
                  className="absolute text-xs text-gray-500 transform -translate-x-1/2"
                  style={{ left: `${getPercentage(mark.value)}%` }}
                >
                  {mark.label}
                </div>
              )
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

export default Slider
