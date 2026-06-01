import type { ReactNode } from 'react'

export type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost' | 'link' | 'link-danger'
export type ButtonSize = 'sm' | 'md' | 'lg'

export interface ButtonProps {
  variant?: ButtonVariant
  size?: ButtonSize
  block?: boolean
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
  children: ReactNode
  onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void
  /** Stable hook for tests; prefer over matching on (i18n) button text. */
  'data-testid'?: string
}

const btnVariant: Record<Exclude<ButtonVariant, 'link' | 'link-danger'>, string> = {
  primary: 'btn--primary',
  secondary: 'btn--secondary',
  danger: 'btn--danger',
  ghost: 'btn--ghost',
}

const btnSize: Record<ButtonSize, string> = {
  sm: 'btn--sm',
  md: '',
  lg: 'btn--lg',
}

/**
 * Button primitive bound to the design-system classes (.btn / .linkbtn).
 * `link` / `link-danger` render an inline text-action button used in table rows.
 */
export function Button({
  variant = 'primary',
  size = 'md',
  block = false,
  disabled = false,
  type = 'button',
  children,
  onClick,
  'data-testid': dataTestId,
}: ButtonProps) {
  const isLink = variant === 'link' || variant === 'link-danger'
  const classes = isLink
    ? `linkbtn${variant === 'link-danger' ? ' linkbtn--danger' : ''}`
    : ['btn', btnVariant[variant], btnSize[size], block ? 'btn--block' : '']
        .filter(Boolean)
        .join(' ')

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
