import type { ReactNode, SyntheticEvent } from 'react'
import { Button } from '@/shared/ui/primitives/Button'

export interface FormCardProps {
  title: string
  /** Optional muted line under the title (e.g. the edited record's email). */
  description?: string | undefined
  onSubmit: (event: SyntheticEvent<HTMLFormElement>) => void
  isSubmitting: boolean
  submitLabel: string
  /** Label shown on the submit button while the mutation is pending. */
  submittingLabel: string
  /** Stable hook for tests; prefer over matching on (i18n) button text. */
  submitTestId?: string | undefined
  /** Secondary footer action (cancel / discard). Omit for submit-only forms. */
  cancel?: { label: string; onClick: () => void } | undefined
  /** Form-level error rendered after the fields (role=alert). */
  error?: string | undefined
  /** Success/status message rendered after the fields (role=status). */
  status?: string | undefined
  /** The form fields — rendered inside the `.form-grid`. */
  children: ReactNode
}

/**
 * Standard create/edit/settings form scaffold: a `<form>` wrapping the
 * design-system `.card` (head title[+desc] / body `.form-grid` / foot actions).
 * Every admin form shares this shell — callers supply only the fields and the
 * mutation wiring, keeping structure, footer buttons, and error/status surfaces
 * consistent. Presentational only; form state lives in the calling feature.
 */
export function FormCard({
  title,
  description,
  onSubmit,
  isSubmitting,
  submitLabel,
  submittingLabel,
  submitTestId,
  cancel,
  error,
  status,
  children,
}: FormCardProps) {
  return (
    <form onSubmit={onSubmit} noValidate>
      <div className="card">
        <div className="card__head">
          <div>
            <h2 className="card__title">{title}</h2>
            {description !== undefined && description !== '' ? (
              <div className="card__desc">{description}</div>
            ) : null}
          </div>
        </div>
        <div className="card__body">
          <div className="form-grid">
            {children}
            {error !== undefined && error !== '' ? (
              <span className="field__error" role="alert">
                {error}
              </span>
            ) : null}
            {status !== undefined && status !== '' ? (
              <span role="status" className="form-status">
                {status}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          {cancel !== undefined ? (
            <Button variant="ghost" disabled={isSubmitting} onClick={cancel.onClick}>
              {cancel.label}
            </Button>
          ) : null}
          <Button
            type="submit"
            disabled={isSubmitting}
            {...(submitTestId !== undefined ? { 'data-testid': submitTestId } : {})}
          >
            {isSubmitting ? submittingLabel : submitLabel}
          </Button>
        </div>
      </div>
    </form>
  )
}
