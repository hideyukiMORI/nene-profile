import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it, vi } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { CreatePresetForm } from './CreatePresetForm'

const presetResponse = {
  id: 3,
  name: 'みずほ',
  bank_label: 'みずほ銀行',
  current_version_id: 9,
  version_number: 1,
  is_deleted: false,
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-30T00:00:00Z',
}

describe('CreatePresetForm', () => {
  it('builds a definition from the mapped (non-blank) columns only', async () => {
    let received: { definition?: { columns?: Record<string, unknown> } } | null = null
    server.use(
      http.post('/admin/mapping-presets', async ({ request }) => {
        received = (await request.json()) as typeof received
        return HttpResponse.json(presetResponse, { status: 201 })
      }),
    )
    const onCreated = vi.fn()

    renderWithProviders(<CreatePresetForm onCreated={onCreated} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.type(screen.getByLabelText('名称'), 'みずほ')
    await user.type(screen.getByLabelText('銀行ラベル'), 'みずほ銀行')

    await user.type(screen.getByLabelText('取引日 元の列見出し'), '取引日')
    await user.selectOptions(screen.getByLabelText('取引日 変換'), 'date_ymd_slash')
    await user.type(screen.getByLabelText('金額 元の列見出し'), '金額')
    await user.selectOptions(screen.getByLabelText('金額 変換'), 'amount_yen_to_cents')

    await user.click(screen.getByTestId('preset-create-submit'))

    await waitFor(() => {
      expect(onCreated).toHaveBeenCalledOnce()
    })
    expect(received).toEqual({
      name: 'みずほ',
      bank_label: 'みずほ銀行',
      definition: {
        encoding: 'auto',
        delimiter: 'auto',
        header_row_index: 0,
        year_pivot: 50,
        columns: {
          transaction_date: { source: '取引日', transform: 'date_ymd_slash' },
          amount: { source: '金額', transform: 'amount_yen_to_cents' },
        },
      },
    })
  })

  it('requires a name and bank label', async () => {
    const onCreated = vi.fn()
    renderWithProviders(<CreatePresetForm onCreated={onCreated} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.click(screen.getByTestId('preset-create-submit'))

    expect(await screen.findByText('名称を入力してください。')).toBeInTheDocument()
    expect(screen.getByText('銀行ラベルを入力してください。')).toBeInTheDocument()
    expect(onCreated).not.toHaveBeenCalled()
  })
})
