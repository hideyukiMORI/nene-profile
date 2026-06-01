import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { AuditLogsPage } from './AuditLogsPage'

const entry = {
  id: 1,
  actor_user_id: 5,
  organization_id: 1,
  action: 'organization.updated',
  entity_type: 'organization',
  entity_id: 9,
  before: { name: 'Old' },
  after: { name: 'New' },
  created_at: '2026-05-30T00:00:00Z',
}

describe('AuditLogsPage', () => {
  it('renders audit entries and opens the field-level diff drawer', async () => {
    server.use(
      http.get('/admin/audit-logs', () =>
        HttpResponse.json({ items: [entry], total: 1, limit: 20, offset: 0 }),
      ),
    )

    renderWithProviders(<AuditLogsPage />)
    const user = userEvent.setup()

    expect(await screen.findByText('organization.updated')).toBeInTheDocument()
    expect(screen.getByText('organization #9')).toBeInTheDocument()
    expect(screen.getByText('#5')).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: '差分を表示' }))

    // Drawer opens with a field-by-field before → after diff.
    const drawer = await screen.findByRole('dialog', { name: '変更内容' })
    expect(within(drawer).getByText('name')).toBeInTheDocument()
    expect(within(drawer).getByText('Old')).toBeInTheDocument()
    expect(within(drawer).getByText('New')).toBeInTheDocument()
  })

  it('shows the empty state when there are no logs', async () => {
    server.use(
      http.get('/admin/audit-logs', () =>
        HttpResponse.json({ items: [], total: 0, limit: 20, offset: 0 }),
      ),
    )

    renderWithProviders(<AuditLogsPage />)

    expect(await screen.findByText('監査ログがまだありません。')).toBeInTheDocument()
  })
})
