import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it, vi } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { CreateUserForm } from './CreateUserForm'

describe('CreateUserForm', () => {
  it('creates a user with the selected role', async () => {
    let received: unknown = null
    server.use(
      http.post('/admin/users', async ({ request }) => {
        received = await request.json()
        return HttpResponse.json(
          {
            id: 5,
            email: 'op@example.com',
            role: 'member',
            organization_id: 1,
            status: 'invited',
            created_at: '2026-05-30T00:00:00Z',
            updated_at: '2026-05-30T00:00:00Z',
          },
          { status: 201 },
        )
      }),
    )
    const onCreated = vi.fn()

    renderWithProviders(<CreateUserForm onCreated={onCreated} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('メールアドレス'), 'op@example.com')
    await user.type(screen.getByLabelText('パスワード（8文字以上）'), 'supersecret')
    await user.click(screen.getByTestId('user-create-submit'))

    await waitFor(() => {
      expect(onCreated).toHaveBeenCalledOnce()
    })
    expect(received).toEqual({ email: 'op@example.com', password: 'supersecret', role: 'member' })
  })

  it('blocks a short password', async () => {
    const onCreated = vi.fn()
    renderWithProviders(<CreateUserForm onCreated={onCreated} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('メールアドレス'), 'op@example.com')
    await user.type(screen.getByLabelText('パスワード（8文字以上）'), 'short')
    await user.click(screen.getByTestId('user-create-submit'))

    expect(await screen.findByText('パスワードは8文字以上で入力してください。')).toBeInTheDocument()
    expect(onCreated).not.toHaveBeenCalled()
  })
})
