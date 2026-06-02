import { useState } from 'react'
import { ConfirmDialog } from '@/shared/ui/components/ConfirmDialog'
import { Button } from '@/shared/ui/primitives/Button'

export interface DeleteActionProps {
  /** Text of the inline trigger button (usually `削除`). */
  triggerLabel: string
  title: string
  message: string
  confirmLabel: string
  cancelLabel: string
  isPending: boolean
  /** Localized error to surface in-dialog after a failed delete. */
  error?: string | undefined
  /**
   * Performs the delete. A resolved promise closes the dialog; a rejected one
   * keeps it open so the (caller-supplied) `error` stays visible.
   */
  onConfirm: () => Promise<unknown>
  /** Clears prior mutation error when the dialog is dismissed. */
  onReset?: (() => void) | undefined
}

/**
 * Inline delete trigger + confirmation, shared by every list row's delete
 * action. Owns the dialog open state and the confirm/close flow; the calling
 * feature supplies the entity mutation (via `onConfirm`) and its localized copy,
 * so no two domains re-implement the same dialog plumbing.
 */
export function DeleteAction({
  triggerLabel,
  title,
  message,
  confirmLabel,
  cancelLabel,
  isPending,
  error,
  onConfirm,
  onReset,
}: DeleteActionProps) {
  const [open, setOpen] = useState(false)

  const close = (): void => {
    setOpen(false)
    onReset?.()
  }

  const confirm = (): void => {
    void onConfirm().then(
      () => {
        setOpen(false)
      },
      () => {
        // Error is surfaced via the `error` prop; keep the dialog open.
      },
    )
  }

  return (
    <>
      <Button
        variant="link-danger"
        onClick={() => {
          setOpen(true)
        }}
      >
        {triggerLabel}
      </Button>
      <ConfirmDialog
        open={open}
        destructive
        title={title}
        message={message}
        confirmLabel={confirmLabel}
        cancelLabel={cancelLabel}
        isConfirming={isPending}
        error={error}
        onConfirm={confirm}
        onCancel={close}
      />
    </>
  )
}
