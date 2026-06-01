import { forwardRef, type InputHTMLAttributes } from 'react'

export type InputProps = InputHTMLAttributes<HTMLInputElement> & {
  invalid?: boolean
}

/**
 * Text input primitive bound to the design-system `.input` class. `invalid`
 * sets `aria-invalid`, which the stylesheet uses to switch the border to danger.
 * Extra `className` (e.g. `mono`) is appended.
 */
export const Input = forwardRef<HTMLInputElement, InputProps>(function Input(
  { invalid = false, className, ...rest },
  ref,
) {
  const classes = ['input', className].filter(Boolean).join(' ')
  return <input ref={ref} className={classes} aria-invalid={invalid || undefined} {...rest} />
})
