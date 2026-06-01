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
 * Native select primitive bound to the design-system `.select` class (custom
 * chevron). `invalid` sets `aria-invalid` for danger styling. Options are passed
 * as data so callers keep all copy in the i18n catalog.
 */
export const Select = forwardRef<HTMLSelectElement, SelectProps>(function Select(
  { invalid = false, options, className, ...rest },
  ref,
) {
  const classes = ['select', className].filter(Boolean).join(' ')
  return (
    <select ref={ref} className={classes} aria-invalid={invalid || undefined} {...rest}>
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  )
})
