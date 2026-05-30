import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import { Pagination } from './Pagination'

describe('Pagination', () => {
  it('renders the summary and fires prev/next handlers', async () => {
    const onPrev = vi.fn()
    const onNext = vi.fn()
    render(
      <Pagination
        summary="Showing 21–40 of 57"
        prevLabel="Prev"
        nextLabel="Next"
        canPrev
        canNext
        onPrev={onPrev}
        onNext={onNext}
      />,
    )
    const user = userEvent.setup()

    expect(screen.getByText('Showing 21–40 of 57')).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: 'Prev' }))
    await user.click(screen.getByRole('button', { name: 'Next' }))

    expect(onPrev).toHaveBeenCalledOnce()
    expect(onNext).toHaveBeenCalledOnce()
  })

  it('disables prev/next when not allowed', () => {
    render(
      <Pagination
        summary="Showing 1–20 of 20"
        prevLabel="Prev"
        nextLabel="Next"
        canPrev={false}
        canNext={false}
        onPrev={vi.fn()}
        onNext={vi.fn()}
      />,
    )

    expect(screen.getByRole('button', { name: 'Prev' })).toBeDisabled()
    expect(screen.getByRole('button', { name: 'Next' })).toBeDisabled()
  })
})
