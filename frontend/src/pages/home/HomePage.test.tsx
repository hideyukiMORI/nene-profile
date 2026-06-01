import { screen } from '@testing-library/react'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { HomePage } from './HomePage'

function job(id: number, rowCount: number, errorCount: number, filename: string) {
  return {
    id,
    organization_id: 1,
    preset_version_id: 9,
    original_filename: filename,
    original_file_hash: 'h',
    status: errorCount > 0 ? 'completed_with_errors' : 'completed',
    row_count: rowCount,
    error_count: errorCount,
    started_at: null,
    completed_at: null,
    created_at: '2026-05-30T00:00:00Z',
  }
}

describe('HomePage dashboard', () => {
  it('shows the aggregate error rate and recent jobs', async () => {
    server.use(
      http.get('/admin/import-jobs', () =>
        HttpResponse.json({
          items: [job(1, 80, 2, 'a.csv'), job(2, 20, 3, 'b.csv')],
          total: 2,
          limit: 5,
          offset: 0,
        }),
      ),
    )

    renderWithProviders(<HomePage />)

    // 5 errors / 100 rows = 5.0% (rendered as "5.0" + a <small>%</small> in the stat card)
    expect(
      await screen.findByText(
        (_content, element) =>
          element?.classList.contains('stat__val') === true && element.textContent === '5.0%',
      ),
    ).toBeInTheDocument()
    expect(screen.getByText('a.csv')).toBeInTheDocument()
    expect(screen.getByText('b.csv')).toBeInTheDocument()
  })

  it('shows the empty state when there are no jobs', async () => {
    server.use(
      http.get('/admin/import-jobs', () =>
        HttpResponse.json({ items: [], total: 0, limit: 5, offset: 0 }),
      ),
    )

    renderWithProviders(<HomePage />)

    expect(await screen.findByText('まだジョブの実行履歴がありません。')).toBeInTheDocument()
  })
})
