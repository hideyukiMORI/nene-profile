import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import type { OrganizationSettings } from '@/entities/organization-settings'
import { SettingsForm } from './SettingsForm'

const settings: OrganizationSettings = {
  organizationId: 1,
  defaultEncoding: 'auto',
  maxFileSizeBytes: 1048576,
  clearBearerTokenSet: false,
}

describe('SettingsForm', () => {
  it('omits the token when blank and confirms on save', async () => {
    let received: unknown = null
    server.use(
      http.patch('/admin/organization-settings', async ({ request }) => {
        received = await request.json()
        return HttpResponse.json({
          organization_id: 1,
          default_encoding: 'utf-8',
          max_file_size_bytes: 2000,
          clear_bearer_token_set: false,
        })
      }),
    )

    renderWithProviders(<SettingsForm settings={settings} />)
    const user = userEvent.setup()

    await user.selectOptions(screen.getByLabelText('デフォルト文字コード'), 'utf-8')
    const size = screen.getByLabelText('最大ファイルサイズ（バイト）')
    await user.clear(size)
    await user.type(size, '2000')
    await user.click(screen.getByTestId('settings-save'))

    expect(await screen.findByText('設定を保存しました。')).toBeInTheDocument()
    expect(received).toEqual({ default_encoding: 'utf-8', max_file_size_bytes: 2000 })
  })

  it('sends the token when provided', async () => {
    let received: { clear_bearer_token?: string } | null = null
    server.use(
      http.patch('/admin/organization-settings', async ({ request }) => {
        received = (await request.json()) as typeof received
        return HttpResponse.json({
          organization_id: 1,
          default_encoding: 'auto',
          max_file_size_bytes: 1048576,
          clear_bearer_token_set: true,
        })
      }),
    )

    renderWithProviders(<SettingsForm settings={settings} />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('NeNe Clear 連携トークン（任意）'), 'tok_123')
    await user.click(screen.getByTestId('settings-save'))

    await waitFor(() => {
      expect(received).not.toBeNull()
    })
    expect(received?.clear_bearer_token).toBe('tok_123')
  })
})
