import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it, vi } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import type { User } from '@/entities/user'
import { EditUserForm } from './EditUserForm'

const user: User = {
  id: 5,
  email: 'op@example.com',
  role: 'member',
  organizationId: 1,
  status: 'invited',
  createdAt: '2026-05-30T00:00:00Z',
  updatedAt: '2026-05-30T00:00:00Z',
}

describe('EditUserForm', () => {
  it('omits the password when left blank and sends role + status', async () => {
    let received: unknown = null
    server.use(
      http.patch('/admin/users/5', async ({ request }) => {
        received = await request.json()
        return HttpResponse.json({
          ...{
            id: 5,
            email: 'op@example.com',
            organization_id: 1,
            created_at: '2026-05-30T00:00:00Z',
            updated_at: '2026-05-30T00:00:00Z',
          },
          role: 'admin',
          status: 'active',
        })
      }),
    )
    const onSaved = vi.fn()

    renderWithProviders(<EditUserForm user={user} onSaved={onSaved} onCancel={vi.fn()} />)
    const actor = userEvent.setup()

    await actor.selectOptions(screen.getByLabelText('役割'), 'admin')
    await actor.selectOptions(screen.getByLabelText('状態'), 'active')
    await actor.click(screen.getByTestId('user-edit-submit'))

    await waitFor(() => {
      expect(onSaved).toHaveBeenCalledOnce()
    })
    expect(received).toEqual({ role: 'admin', status: 'active' })
  })

  it('rejects a short new password', async () => {
    const onSaved = vi.fn()
    renderWithProviders(<EditUserForm user={user} onSaved={onSaved} onCancel={vi.fn()} />)
    const actor = userEvent.setup()

    await actor.type(screen.getByLabelText('パスワード（変更する場合のみ）'), 'short')
    await actor.click(screen.getByTestId('user-edit-submit'))

    expect(await screen.findByText('パスワードは8文字以上で入力してください。')).toBeInTheDocument()
    expect(onSaved).not.toHaveBeenCalled()
  })
})
