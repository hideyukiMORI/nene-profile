import { useEffect, useId } from 'react'
import { Button } from '@/shared/ui/primitives/Button'
import { Stack } from '@/shared/ui/primitives/Stack'
import { Text } from '@/shared/ui/primitives/Text'

export interface ConfirmDialogProps {
  open: boolean
  title: string
  message: string
  confirmLabel: string
  cancelLabel: string
  isConfirming?: boolean
  /** Optional error to surface in-dialog (e.g. a failed delete). */
  error?: string | undefined
  /** Renders the confirm action with the danger token (delete flows). */
  destructive?: boolean
  onConfirm: () => void
  onCancel: () => void
}

/**
 * Modal confirmation used by destructive actions. Fail-safe: the confirm button
 * is never the default focus target and Escape cancels. Presentational only —
 * the mutation lives in the calling feature.
 */
export function ConfirmDialog({
  open,
  title,
  message,
  confirmLabel,
  cancelLabel,
  isConfirming = false,
  error,
  destructive = false,
  onConfirm,
  onCancel,
}: ConfirmDialogProps) {
  const titleId = useId()
  const messageId = useId()

  useEffect(() => {
    if (!open) return
    const onKeyDown = (event: KeyboardEvent): void => {
      if (event.key === 'Escape') onCancel()
    }
    window.addEventListener('keydown', onKeyDown)
    return () => {
      window.removeEventListener('keydown', onKeyDown)
    }
  }, [open, onCancel])

  if (!open) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-inline-lg">
      <button
        type="button"
        aria-label={cancelLabel}
        onClick={onCancel}
        className="absolute inset-0 bg-text-primary/40"
      />
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby={titleId}
        aria-describedby={messageId}
        className="relative w-full max-w-sm rounded-md border border-border bg-surface p-inline-lg"
      >
        <Stack gap="md">
          <Text as="h2" variant="heading">
            <span id={titleId}>{title}</span>
          </Text>
          <Text variant="body" tone="muted">
            <span id={messageId}>{message}</span>
          </Text>
          {error !== undefined && error !== '' ? (
            <Text variant="caption" tone="danger">
              {error}
            </Text>
          ) : null}
          <div className="flex justify-end gap-inline-sm">
            <Button variant="ghost" size="sm" disabled={isConfirming} onClick={onCancel}>
              {cancelLabel}
            </Button>
            <Button
              variant={destructive ? 'danger' : 'primary'}
              size="sm"
              disabled={isConfirming}
              onClick={onConfirm}
              data-testid="confirm-dialog-confirm"
            >
              {confirmLabel}
            </Button>
          </div>
        </Stack>
      </div>
    </div>
  )
}
