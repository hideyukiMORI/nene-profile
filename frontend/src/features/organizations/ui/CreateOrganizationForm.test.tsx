import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it, vi } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { CreateOrganizationForm } from './CreateOrganizationForm'

describe('CreateOrganizationForm', () => {
  it('creates an organization and notifies the caller', async () => {
    let received: unknown = null
    server.use(
      http.post('/admin/organizations', async ({ request }) => {
        received = await request.json()
        return HttpResponse.json(
          {
            id: 10,
            name: 'Acme Corp',
            slug: 'acme',
            is_active: true,
            custom_domain: null,
            created_at: '2026-05-30T00:00:00Z',
            updated_at: '2026-05-30T00:00:00Z',
          },
          { status: 201 },
        )
      }),
    )
    const onCreated = vi.fn()

    renderWithProviders(<CreateOrganizationForm onCreated={onCreated} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('名称'), 'Acme Corp')
    await user.type(screen.getByLabelText('スラグ（英小文字・数字・ハイフン）'), 'acme')
    await user.click(screen.getByTestId('org-create-submit'))

    await waitFor(() => {
      expect(onCreated).toHaveBeenCalledOnce()
    })
    expect(received).toEqual({ name: 'Acme Corp', slug: 'acme' })
  })

  it('shows required errors and does not submit when empty', async () => {
    const onCreated = vi.fn()
    renderWithProviders(<CreateOrganizationForm onCreated={onCreated} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.click(screen.getByTestId('org-create-submit'))

    expect(await screen.findByText('名称を入力してください。')).toBeInTheDocument()
    expect(screen.getByText('スラグを入力してください。')).toBeInTheDocument()
    expect(onCreated).not.toHaveBeenCalled()
  })

  it('rejects an invalid slug with the pattern message', async () => {
    renderWithProviders(<CreateOrganizationForm onCreated={vi.fn()} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('名称'), 'Acme')
    await user.type(screen.getByLabelText('スラグ（英小文字・数字・ハイフン）'), 'Acme Corp!')
    await user.click(screen.getByTestId('org-create-submit'))

    expect(
      await screen.findByText('スラグは英小文字・数字・ハイフンのみ使用できます。'),
    ).toBeInTheDocument()
  })
})
