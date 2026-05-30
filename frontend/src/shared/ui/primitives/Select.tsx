import { forwardRef, type SelectHTMLAttributes } from 'react'

export interface SelectOption {
  value: string
  label: string
}

export type SelectProps = SelectHTMLAttributes<HTMLSelectElement> & {
  invalid?: boolean
  options: readonly SelectOption[]
}

/**
 * Native select primitive. Visual state is theme-driven; `invalid` switches the
 * border to the danger token. Options are passed as data so callers keep all
 * copy in the i18n catalog.
 */
export const Select = forwardRef<HTMLSelectElement, SelectProps>(function Select(
  { invalid = false, options, className, ...rest },
  ref,
) {
  const classes = [
    'w-full rounded-sm border bg-surface px-inline-md py-stack-sm text-body text-text-primary',
    'focus-visible:outline-2 focus-visible:outline-accent',
    invalid ? 'border-danger' : 'border-border',
    className ?? '',
  ].join(' ')

  return (
    <select ref={ref} className={classes} {...rest}>
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  )
})
