import { screen, waitFor, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { OrganizationsPage } from './OrganizationsPage'

function orgListResponse(items: unknown[], total = items.length) {
  return HttpResponse.json({ items, total, limit: 20, offset: 0 })
}

const acme = {
  id: 10,
  name: 'Acme Corp',
  slug: 'acme',
  is_active: true,
  custom_domain: null,
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-30T00:00:00Z',
}

describe('OrganizationsPage', () => {
  it('renders organizations returned by the API', async () => {
    server.use(http.get('/admin/organizations', () => orgListResponse([acme])))

    renderWithProviders(<OrganizationsPage />)

    expect(await screen.findByText('Acme Corp')).toBeInTheDocument()
    expect(screen.getByText('acme')).toBeInTheDocument()
    expect(screen.getByText('有効')).toBeInTheDocument()
  })

  it('shows the empty state when there are no organizations', async () => {
    server.use(http.get('/admin/organizations', () => orgListResponse([])))

    renderWithProviders(<OrganizationsPage />)

    expect(await screen.findByText('組織がまだありません。')).toBeInTheDocument()
  })

  it('deletes an organization through the confirm dialog', async () => {
    let deleted = false
    server.use(
      http.get('/admin/organizations', () =>
        deleted ? orgListResponse([]) : orgListResponse([acme]),
      ),
      http.delete('/admin/organizations/10', () => {
        deleted = true
        return new HttpResponse(null, { status: 204 })
      }),
    )

    renderWithProviders(<OrganizationsPage />)
    const user = userEvent.setup()

    const row = (await screen.findByText('Acme Corp')).closest('tr')
    expect(row).not.toBeNull()
    await user.click(within(row as HTMLElement).getByRole('button', { name: '削除' }))

    const dialog = await screen.findByRole('dialog')
    expect(within(dialog).getByText(/「Acme Corp」を削除します/)).toBeInTheDocument()
    await user.click(screen.getByTestId('confirm-dialog-confirm'))

    await waitFor(() => {
      expect(screen.queryByText('Acme Corp')).not.toBeInTheDocument()
    })
  })
})
