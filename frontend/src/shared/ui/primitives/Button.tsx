import type { ReactNode } from 'react'

export type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost'
export type ButtonSize = 'sm' | 'md'

export interface ButtonProps {
  variant?: ButtonVariant
  size?: ButtonSize
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
  children: ReactNode
  onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void
  /** Stable hook for tests; prefer over matching on (i18n) button text. */
  'data-testid'?: string
}

const variantClasses: Record<ButtonVariant, string> = {
  primary: 'bg-accent text-text-inverse hover:bg-accent-hover border-transparent',
  secondary: 'bg-surface-raised text-text-primary hover:bg-surface-overlay border-border',
  danger: 'bg-danger text-text-inverse hover:bg-danger-hover border-transparent',
  ghost: 'bg-transparent text-text-muted hover:text-text-primary border-transparent',
}

const sizeClasses: Record<ButtonSize, string> = {
  sm: 'px-inline-sm py-stack-xs text-caption',
  md: 'px-inline-md py-stack-sm text-body',
}

export function Button({
  variant = 'primary',
  size = 'md',
  disabled = false,
  type = 'button',
  children,
  onClick,
  'data-testid': dataTestId,
}: ButtonProps) {
  const classes = [
    'inline-flex items-center justify-center rounded-sm border font-semibold transition-colors',
    'focus-visible:outline-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-50',
    variantClasses[variant],
    sizeClasses[size],
  ].join(' ')

  return (
    <button
      type={type}
      disabled={disabled}
      onClick={onClick}
      className={classes}
      data-testid={dataTestId}
    >
      {children}
    </button>
  )
}
