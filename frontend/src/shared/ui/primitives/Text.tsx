import type { ReactNode } from 'react'

export type TextTone = 'primary' | 'muted' | 'danger'
export type TextVariant = 'caption' | 'body' | 'heading' | 'display'

export interface TextProps {
  as?: 'p' | 'span' | 'h1' | 'h2' | 'label'
  variant?: TextVariant
  tone?: TextTone
  htmlFor?: string
  children: ReactNode
}

const variantClasses: Record<TextVariant, string> = {
  caption: 'text-caption',
  body: 'text-body',
  heading: 'text-heading font-semibold',
  display: 'text-display font-bold',
}

const toneClasses: Record<TextTone, string> = {
  primary: 'text-text-primary',
  muted: 'text-text-muted',
  danger: 'text-danger',
}

export function Text({
  as: Tag = 'p',
  variant = 'body',
  tone = 'primary',
  htmlFor,
  children,
}: TextProps) {
  const className = `${variantClasses[variant]} ${toneClasses[tone]}`

  if (Tag === 'label') {
    return (
      <label htmlFor={htmlFor} className={className}>
        {children}
      </label>
    )
  }

  return <Tag className={className}>{children}</Tag>
}
