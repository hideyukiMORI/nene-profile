import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it, vi } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import type { MappingPresetDetail } from '@/entities/mapping-preset'
import { EditPresetForm } from './EditPresetForm'

const preset: MappingPresetDetail = {
  id: 3,
  name: 'みずほ',
  bankLabel: 'みずほ銀行',
  currentVersionId: 9,
  versionNumber: 1,
  isDeleted: false,
  createdAt: '2026-05-30T00:00:00Z',
  updatedAt: '2026-05-30T00:00:00Z',
  encoding: 'shift_jis',
  delimiter: 'comma',
  headerRowIndex: 1,
  yearPivot: 50,
  columns: [
    { field: 'transaction_date', source: '取引日', transform: 'date_ymd_slash', optional: false },
    { field: 'value_date', source: '', transform: 'trim', optional: false },
    { field: 'amount', source: '金額', transform: 'amount_yen_to_cents', optional: false },
    { field: 'description', source: '', transform: 'trim', optional: false },
    { field: 'counterparty', source: '', transform: 'trim', optional: false },
    { field: 'balance', source: '', transform: 'trim', optional: false },
  ],
}

describe('EditPresetForm', () => {
  it('pre-fills the definition and PATCHes a new version', async () => {
    let received: unknown = null
    server.use(
      http.patch('/admin/mapping-presets/3', async ({ request }) => {
        received = await request.json()
        return HttpResponse.json({
          id: 3,
          name: 'みずほ',
          bank_label: 'みずほ銀行',
          current_version_id: 10,
          version_number: 2,
          is_deleted: false,
          created_at: '2026-05-30T00:00:00Z',
          updated_at: '2026-05-31T00:00:00Z',
        })
      }),
    )
    const onSaved = vi.fn()

    renderWithProviders(<EditPresetForm preset={preset} onSaved={onSaved} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    // The loaded definition pre-fills the source inputs.
    expect(screen.getByLabelText('取引日 元の列見出し')).toHaveValue('取引日')
    expect(screen.getByLabelText('金額 元の列見出し')).toHaveValue('金額')

    // Change the description mapping, then save.
    await user.type(screen.getByLabelText('摘要 元の列見出し'), '摘要')
    await user.click(screen.getByTestId('preset-edit-submit'))

    await waitFor(() => {
      expect(onSaved).toHaveBeenCalledOnce()
    })
    expect(received).toEqual({
      name: 'みずほ',
      bank_label: 'みずほ銀行',
      definition: {
        encoding: 'shift_jis',
        delimiter: 'comma',
        header_row_index: 1,
        year_pivot: 50,
        columns: {
          transaction_date: { source: '取引日', transform: 'date_ymd_slash' },
          amount: { source: '金額', transform: 'amount_yen_to_cents' },
          description: { source: '摘要', transform: 'trim' },
        },
      },
    })
  })
})
