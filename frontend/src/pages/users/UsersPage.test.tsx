import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { UsersPage } from './UsersPage'

const member = {
  id: 5,
  email: 'op@example.com',
  role: 'member',
  organization_id: 1,
  status: 'active',
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-30T00:00:00Z',
}

describe('UsersPage', () => {
  it('renders users with localized role and status', async () => {
    server.use(
      http.get('/admin/users', () =>
        HttpResponse.json({ items: [member], total: 1, limit: 20, offset: 0 }),
      ),
    )

    renderWithProviders(<UsersPage />)

    expect(await screen.findByText('op@example.com')).toBeInTheDocument()
    expect(screen.getByText('メンバー')).toBeInTheDocument()
    expect(screen.getByText('有効')).toBeInTheDocument()
  })

  it('opens the edit form for a row', async () => {
    server.use(
      http.get('/admin/users', () =>
        HttpResponse.json({ items: [member], total: 1, limit: 20, offset: 0 }),
      ),
    )

    renderWithProviders(<UsersPage />)
    const actor = userEvent.setup()

    const row = (await screen.findByText('op@example.com')).closest('tr')
    await actor.click(within(row as HTMLElement).getByRole('button', { name: '編集' }))

    expect(await screen.findByText('ユーザーの編集')).toBeInTheDocument()
    expect(screen.getByTestId('user-edit-submit')).toBeInTheDocument()
  })
})
