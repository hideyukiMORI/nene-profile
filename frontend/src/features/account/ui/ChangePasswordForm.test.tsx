import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { ChangePasswordForm } from './ChangePasswordForm'

describe('ChangePasswordForm', () => {
  it('submits current and new password, shows success message, and resets form', async () => {
    let body: unknown = null
    server.use(
      http.patch('/admin/auth/me/password', async ({ request }) => {
        body = await request.json()
        return new HttpResponse(null, { status: 204 })
      }),
    )
    renderWithProviders(<ChangePasswordForm />)
    const user = userEvent.setup()

    await user.type(screen.getByTestId('current-password'), 'old-password')
    await user.type(screen.getByTestId('new-password'), 'new-password-123')
    await user.type(screen.getByTestId('confirm-password'), 'new-password-123')
    await user.click(screen.getByTestId('change-password-submit'))

    await waitFor(() => {
      expect(screen.getByRole('status')).toHaveTextContent('パスワードを変更しました。')
    })
    expect(body).toEqual({
      current_password: 'old-password',
      new_password: 'new-password-123',
    })
    expect(screen.getByTestId<HTMLInputElement>('current-password').value).toBe('')
  })

  it('shows field errors without submitting when form is empty', async () => {
    renderWithProviders(<ChangePasswordForm />)
    const user = userEvent.setup()

    await user.click(screen.getByTestId('change-password-submit'))

    expect(await screen.findByText('現在のパスワードを入力してください。')).toBeInTheDocument()
    expect(screen.getByText('新しいパスワードは8文字以上で入力してください。')).toBeInTheDocument()
  })

  it('shows mismatch error when passwords differ', async () => {
    renderWithProviders(<ChangePasswordForm />)
    const user = userEvent.setup()

    await user.type(screen.getByTestId('current-password'), 'old-password')
    await user.type(screen.getByTestId('new-password'), 'new-password-123')
    await user.type(screen.getByTestId('confirm-password'), 'different-password')
    await user.click(screen.getByTestId('change-password-submit'))

    expect(await screen.findByText('パスワードが一致しません。')).toBeInTheDocument()
  })

  it('shows current-password error when API returns invalid-current-password', async () => {
    server.use(
      http.patch('/admin/auth/me/password', () =>
        HttpResponse.json(
          { type: 'invalid-current-password', title: 'Invalid Current Password', status: 422 },
          { status: 422 },
        ),
      ),
    )
    renderWithProviders(<ChangePasswordForm />)
    const user = userEvent.setup()

    await user.type(screen.getByTestId('current-password'), 'wrong-password')
    await user.type(screen.getByTestId('new-password'), 'new-password-123')
    await user.type(screen.getByTestId('confirm-password'), 'new-password-123')
    await user.click(screen.getByTestId('change-password-submit'))

    expect(await screen.findByText('現在のパスワードが正しくありません。')).toBeInTheDocument()
  })
})
