import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import { AsyncBoundary } from './AsyncBoundary'

const labels = {
  loadingLabel: 'Loading…',
  errorLabel: 'Failed',
  retryLabel: 'Retry',
}

describe('AsyncBoundary', () => {
  it('shows the loading state and hides children', () => {
    render(
      <AsyncBoundary isLoading isError={false} {...labels}>
        <span>content</span>
      </AsyncBoundary>,
    )

    expect(screen.getByRole('status')).toBeInTheDocument()
    expect(screen.getByText('Loading…')).toBeInTheDocument()
    expect(screen.queryByText('content')).not.toBeInTheDocument()
  })

  it('shows the error state with a working retry button', async () => {
    const onRetry = vi.fn()
    render(
      <AsyncBoundary isLoading={false} isError onRetry={onRetry} {...labels}>
        <span>content</span>
      </AsyncBoundary>,
    )
    const user = userEvent.setup()

    expect(screen.getByText('Failed')).toBeInTheDocument()
    expect(screen.queryByText('content')).not.toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: 'Retry' }))
    expect(onRetry).toHaveBeenCalledOnce()
  })

  it('omits the retry button when no handler is given', () => {
    render(
      <AsyncBoundary isLoading={false} isError {...labels}>
        <span>content</span>
      </AsyncBoundary>,
    )

    expect(screen.queryByRole('button', { name: 'Retry' })).not.toBeInTheDocument()
  })

  it('renders children when settled', () => {
    render(
      <AsyncBoundary isLoading={false} isError={false} {...labels}>
        <span>content</span>
      </AsyncBoundary>,
    )

    expect(screen.getByText('content')).toBeInTheDocument()
  })
})
