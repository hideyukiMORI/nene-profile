import type { ReactNode } from 'react'
import { Button } from '@/shared/ui/primitives/Button'
import { Spinner } from '@/shared/ui/primitives/Spinner'
import { Stack } from '@/shared/ui/primitives/Stack'
import { Text } from '@/shared/ui/primitives/Text'

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
 * same way (terminology.md: consistent states). Empty state is the caller's job
 * — it depends on the resolved data shape.
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
      <Stack gap="sm">
        <Spinner label={loadingLabel} />
        <Text variant="caption" tone="muted">
          {loadingLabel}
        </Text>
      </Stack>
    )
  }

  if (isError) {
    return (
      <Stack gap="sm">
        <Text variant="body" tone="danger">
          {errorLabel}
        </Text>
        {onRetry !== undefined ? (
          <div>
            <Button variant="secondary" size="sm" onClick={onRetry}>
              {retryLabel}
            </Button>
          </div>
        ) : null}
      </Stack>
    )
  }

  return <>{children}</>
}
