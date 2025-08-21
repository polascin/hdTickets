import { ReactNode } from 'react';
import { cn } from '@/lib/utils/cn';

interface CardProps {
  children: ReactNode;
  className?: string;
  onClick?: () => void;
}

export function Card({ children, className, onClick }: CardProps) {
  const Component = onClick ? 'button' : 'div';
  
  return (
    <Component
      className={cn(
        'card',
        onClick && 'cursor-pointer hover:shadow-md transition-shadow',
        className
      )}
      onClick={onClick}
    >
      {children}
    </Component>
  );
}
