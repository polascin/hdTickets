import { useState } from 'react';
import { cn } from '@/lib/utils/cn';

interface SwitchProps {
  checked?: boolean;
  onChange?: (checked: boolean) => void;
  disabled?: boolean;
  label?: string;
  className?: string;
}

export function Switch({ 
  checked: controlledChecked, 
  onChange, 
  disabled = false, 
  label,
  className 
}: SwitchProps) {
  const [internalChecked, setInternalChecked] = useState(false);
  
  const isControlled = controlledChecked !== undefined;
  const checked = isControlled ? controlledChecked : internalChecked;

  const handleToggle = () => {
    if (disabled) return;
    
    const newChecked = !checked;
    
    if (isControlled) {
      onChange?.(newChecked);
    } else {
      setInternalChecked(newChecked);
      onChange?.(newChecked);
    }
  };

  return (
    <div className={cn('flex items-center', className)}>
      <button
        type="button"
        className={cn(
          'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
          checked ? 'bg-blue-600' : 'bg-gray-200',
          disabled && 'opacity-50 cursor-not-allowed'
        )}
        onClick={handleToggle}
        disabled={disabled}
        role="switch"
        aria-checked={checked}
        aria-label={label}
      >
        <span
          className={cn(
            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
            checked ? 'translate-x-5' : 'translate-x-0'
          )}
        />
      </button>
      {label && (
        <span className="ml-3 text-sm font-medium text-gray-700">
          {label}
        </span>
      )}
    </div>
  );
}
