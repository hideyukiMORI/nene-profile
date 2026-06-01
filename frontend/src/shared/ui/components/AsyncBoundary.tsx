import type { ReactNode } from 'react'
import { Button } from '@/shared/ui/primitives/Button'
import { Spinner } from '@/shared/ui/primitives/Spinner'

export interface AsyncBoundaryProps {
  isLoading: boolean
  isError: boolean
  loadingLabel: string
  errorLabel: string
  retryLabel: string
  onRetry?: (() => void) | undefined
  children: ReactNode
}

/**
 * Renders the loading / error states of an async resource and otherwise its
 * children. Centralises the query-state UX so every resource screen behaves the
 * same way. Empty state is the caller's job — it depends on the resolved shape.
 */
export function AsyncBoundary({
  isLoading,
  isError,
  loadingLabel,
  errorLabel,
  retryLabel,
  onRetry,
  children,
}: AsyncBoundaryProps) {
  if (isLoading) {
    return (
      <div className="state-block">
        <Spinner label={loadingLabel} />
        <span>{loadingLabel}</span>
      </div>
    )
  }

  if (isError) {
    return (
      <div className="card">
        <div className="card__body stack-sm">
          <span style={{ color: 'var(--danger)', fontWeight: 600 }}>{errorLabel}</span>
          {onRetry !== undefined ? (
            <div>
              <Button variant="secondary" size="sm" onClick={onRetry}>
                {retryLabel}
              </Button>
            </div>
          ) : null}
        </div>
      </div>
    )
  }

  return <>{children}</>
}
