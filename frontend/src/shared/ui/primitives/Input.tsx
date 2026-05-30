import { forwardRef, type InputHTMLAttributes } from 'react'

export type InputProps = InputHTMLAttributes<HTMLInputElement> & {
  invalid?: boolean
}

/**
 * Text input primitive. Visual state is theme-driven; `invalid` switches the
 * border to the danger token for form validation feedback.
 */
export const Input = forwardRef<HTMLInputElement, InputProps>(function Input(
  { invalid = false, className, ...rest },
  ref,
) {
  const classes = [
    'w-full rounded-sm border bg-surface px-inline-md py-stack-sm text-body text-text-primary',
    'focus-visible:outline-2 focus-visible:outline-accent',
    invalid ? 'border-danger' : 'border-border',
    className ?? '',
  ].join(' ')

  return <input ref={ref} className={classes} {...rest} />
})
