import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { authStore } from '@/entities/auth'
import { LoginForm } from './LoginForm'

describe('LoginForm', () => {
  it('logs in with valid credentials and stores the session', async () => {
    server.use(
      http.post('/admin/auth/login', () =>
        HttpResponse.json({
          token: 'jwt-token',
          expires_at: new Date(Date.now() + 3_600_000).toISOString(),
          email: 'admin@example.com',
          role: 'admin',
          org_id: 7,
        }),
      ),
    )

    renderWithProviders(<LoginForm />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('メールアドレス'), 'admin@example.com')
    await user.type(screen.getByLabelText('パスワード'), 'secret')
    await user.click(screen.getByTestId('login-submit'))

    await waitFor(() => {
      expect(authStore.isAuthenticated()).toBe(true)
    })
    expect(authStore.getSession()?.email).toBe('admin@example.com')
  })

  it('shows an auth error on 401', async () => {
    authStore.clearSession()
    server.use(
      http.post('/admin/auth/login', () =>
        HttpResponse.json(
          { type: 'x/unauthenticated', title: 'Unauthenticated', status: 401 },
          { status: 401 },
        ),
      ),
    )

    renderWithProviders(<LoginForm />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('メールアドレス'), 'admin@example.com')
    await user.type(screen.getByLabelText('パスワード'), 'wrong')
    await user.click(screen.getByTestId('login-submit'))

    expect(
      await screen.findByText('メールアドレスまたはパスワードが正しくありません。'),
    ).toBeInTheDocument()
    expect(authStore.isAuthenticated()).toBe(false)
  })

  it('blocks submit and shows field errors when empty', async () => {
    renderWithProviders(<LoginForm />)
    const user = userEvent.setup()

    await user.click(screen.getByTestId('login-submit'))

    expect(await screen.findByText('メールアドレスを入力してください。')).toBeInTheDocument()
    expect(screen.getByText('パスワードを入力してください。')).toBeInTheDocument()
  })
})
