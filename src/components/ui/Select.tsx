import { SelectHTMLAttributes } from 'react';
import { cn } from '@/lib/utils/cn';

interface SelectOption {
  value: string;
  label: string;
}

interface SelectProps extends Omit<SelectHTMLAttributes<HTMLSelectElement>, 'onChange'> {
  options: SelectOption[];
  label?: string;
  error?: string;
  helperText?: string;
  onChange?: (value: string) => void;
}

export function Select({
  options,
  label,
  error,
  helperText,
  className,
  onChange,
  value,
  ...props
}: SelectProps) {
  return (
    <div className="w-full">
      {label && (
        <label className="block text-sm font-medium text-gray-700 mb-1">
          {label}
        </label>
      )}
      <select
        className={cn(
          'select-field',
          error && 'border-red-300 focus:border-red-500 focus:ring-red-500',
          className
        )}
        value={value}
        onChange={(e) => onChange?.(e.target.value)}
        {...props}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      {error && (
        <p className="mt-1 text-sm text-red-600">{error}</p>
      )}
      {helperText && !error && (
        <p className="mt-1 text-sm text-gray-500">{helperText}</p>
      )}
    </div>
  );
}
