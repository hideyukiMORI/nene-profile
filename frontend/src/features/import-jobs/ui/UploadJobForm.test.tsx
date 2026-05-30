import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it, vi } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { UploadJobForm } from './UploadJobForm'

const preset = {
  id: 3,
  name: 'みずほ',
  bank_label: 'みずほ銀行',
  current_version_id: 9,
  version_number: 1,
  is_deleted: false,
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-30T00:00:00Z',
}

const jobDto = {
  id: 7,
  organization_id: 1,
  preset_version_id: 9,
  original_filename: 'bank.csv',
  original_file_hash: 'abc',
  status: 'completed',
  row_count: 3,
  error_count: 0,
  started_at: null,
  completed_at: null,
  created_at: '2026-05-30T00:00:00Z',
}

function presetsHandler() {
  return http.get('/admin/mapping-presets', () =>
    HttpResponse.json({ items: [preset], total: 1, limit: 100, offset: 0 }),
  )
}

describe('UploadJobForm', () => {
  it('posts a multipart upload to the jobs endpoint with the selected preset', async () => {
    // request.formData()/text() hang under undici/jsdom, so we assert the
    // request reached the endpoint as multipart rather than parsing the body.
    let hit = false
    let contentType = ''
    server.use(
      presetsHandler(),
      http.post('/admin/import-jobs', ({ request }) => {
        hit = true
        contentType = request.headers.get('content-type') ?? ''
        return HttpResponse.json(jobDto, { status: 201 })
      }),
    )
    const onUploaded = vi.fn()

    renderWithProviders(<UploadJobForm onUploaded={onUploaded} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await waitFor(() => {
      expect(screen.getByText('みずほ')).toBeInTheDocument()
    })
    // The preset selector is populated and auto-selects the first option.
    expect(screen.getByRole('combobox')).toHaveValue('3')

    const csv = new File(['date,amount\n'], 'bank.csv', { type: 'text/csv' })
    await user.upload(screen.getByLabelText('CSV ファイル'), csv)
    await user.click(screen.getByTestId('job-upload-submit'))

    await waitFor(() => {
      expect(onUploaded).toHaveBeenCalledOnce()
    })
    expect(hit).toBe(true)
    expect(contentType).toContain('multipart/form-data')
  })

  it('requires a file', async () => {
    server.use(presetsHandler())
    const onUploaded = vi.fn()

    renderWithProviders(<UploadJobForm onUploaded={onUploaded} onCancel={vi.fn()} />)
    const user = userEvent.setup()

    await user.click(screen.getByTestId('job-upload-submit'))

    expect(await screen.findByText('CSV ファイルを選択してください。')).toBeInTheDocument()
    expect(onUploaded).not.toHaveBeenCalled()
  })
})
