import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import { ConfirmDialog } from './ConfirmDialog'

const baseProps = {
  title: 'Delete this?',
  message: 'This cannot be undone.',
  confirmLabel: 'Delete',
  cancelLabel: 'Cancel',
}

describe('ConfirmDialog', () => {
  it('renders nothing when closed', () => {
    render(<ConfirmDialog open={false} {...baseProps} onConfirm={vi.fn()} onCancel={vi.fn()} />)

    expect(screen.queryByRole('dialog')).not.toBeInTheDocument()
  })

  it('renders an accessible dialog and fires confirm/cancel', async () => {
    const onConfirm = vi.fn()
    const onCancel = vi.fn()
    render(<ConfirmDialog open {...baseProps} onConfirm={onConfirm} onCancel={onCancel} />)
    const user = userEvent.setup()

    const dialog = screen.getByRole('dialog')
    expect(dialog).toHaveAttribute('aria-modal', 'true')
    expect(screen.getByText('Delete this?')).toBeInTheDocument()

    await user.click(screen.getByTestId('confirm-dialog-confirm'))
    expect(onConfirm).toHaveBeenCalledOnce()

    // The full-screen backdrop also carries the cancel label, so scope to the dialog.
    await user.click(within(dialog).getByRole('button', { name: 'Cancel' }))
    expect(onCancel).toHaveBeenCalledOnce()
  })

  it('cancels on Escape', async () => {
    const onCancel = vi.fn()
    render(<ConfirmDialog open {...baseProps} onConfirm={vi.fn()} onCancel={onCancel} />)
    const user = userEvent.setup()

    await user.keyboard('{Escape}')
    expect(onCancel).toHaveBeenCalledOnce()
  })

  it('surfaces an in-dialog error when provided', () => {
    render(
      <ConfirmDialog
        open
        {...baseProps}
        error="Could not delete."
        onConfirm={vi.fn()}
        onCancel={vi.fn()}
      />,
    )

    expect(screen.getByText('Could not delete.')).toBeInTheDocument()
  })
})
