import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { ImportJobsPage } from './ImportJobsPage'

const job = {
  id: 7,
  organization_id: 1,
  preset_version_id: 9,
  original_filename: 'bank.csv',
  original_file_hash: 'abc',
  status: 'completed_with_errors',
  row_count: 10,
  error_count: 2,
  started_at: null,
  completed_at: null,
  created_at: '2026-05-30T00:00:00Z',
}

describe('ImportJobsPage', () => {
  it('renders jobs with localized status and counts', async () => {
    server.use(
      http.get('/admin/import-jobs', () =>
        HttpResponse.json({ items: [job], total: 1, limit: 20, offset: 0 }),
      ),
    )

    renderWithProviders(<ImportJobsPage />)

    expect(await screen.findByText('bank.csv')).toBeInTheDocument()
    // completed_with_errors renders an "エラー {count}" badge in the design.
    expect(screen.getByText('エラー 2')).toBeInTheDocument()
  })

  it('expands the error rows for a job', async () => {
    server.use(
      http.get('/admin/import-jobs', () =>
        HttpResponse.json({ items: [job], total: 1, limit: 20, offset: 0 }),
      ),
      http.get('/admin/import-jobs/7/errors', () =>
        HttpResponse.json({
          items: [
            {
              id: 1,
              import_job_id: 7,
              raw_row_number: 4,
              message: '日付が不正です',
              raw_snippet: 'xx',
            },
          ],
          total: 1,
          limit: 20,
          offset: 0,
        }),
      ),
    )

    renderWithProviders(<ImportJobsPage />)
    const user = userEvent.setup()

    const row = (await screen.findByText('bank.csv')).closest('tr')
    await user.click(within(row as HTMLElement).getByRole('button', { name: 'エラー行' }))

    expect(await screen.findByText('日付が不正です')).toBeInTheDocument()
  })
})
