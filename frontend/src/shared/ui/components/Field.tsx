import { useId, type ReactNode } from 'react'

export interface FieldProps {
  label: string
  /** Optional muted hint shown under the control. */
  hint?: string
  error?: string
  /** Render-prop receiving the id to wire to the control (aria correctness). */
  children: (props: { id: string; invalid: boolean }) => ReactNode
}

/** Labelled form field (design-system `.field`) with accessible error wiring. */
export function Field({ label, hint, error, children }: FieldProps) {
  const id = useId()
  const errorId = `${id}-error`
  const invalid = error !== undefined && error !== ''

  return (
    <div className="field">
      <label className="field__label" htmlFor={id}>
        {label}
      </label>
      {children({ id, invalid })}
      {hint !== undefined && hint !== '' && !invalid ? (
        <span className="field__hint">{hint}</span>
      ) : null}
      {invalid ? (
        <span id={errorId} role="alert" className="field__error">
          {error}
        </span>
      ) : null}
    </div>
  )
}
