import { expect, test } from '@playwright/test'
import { installApiMocks, login, mockJson, paginated } from './support/api'

function job(
  id: number,
  filename: string,
  rowCount: number,
  errorCount: number,
): Record<string, unknown> {
  return {
    id,
    organization_id: 7,
    preset_version_id: 9,
    original_filename: filename,
    original_file_hash: 'hash',
    status: errorCount > 0 ? 'completed_with_errors' : 'completed',
    row_count: rowCount,
    error_count: errorCount,
    started_at: null,
    completed_at: null,
    created_at: '2026-05-30T00:00:00Z',
  }
}

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('shows the aggregate error rate and recent jobs', async ({ page }) => {
  await mockJson(
    page,
    'GET',
    '**/admin/import-jobs?*',
    200,
    paginated([job(1, 'a.csv', 80, 2), job(2, 'b.csv', 20, 3)], 2, 5, 0),
  )
  await login(page, 'member')

  // 5 errors / 100 rows = 5.0%
  await expect(page.getByText('5.0%')).toBeVisible()
  await expect(page.getByRole('cell', { name: 'a.csv', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: 'b.csv', exact: true })).toBeVisible()
})

test('shows the empty state when no jobs have run', async ({ page }) => {
  // installApiMocks already returns an empty import-jobs list by default.
  await login(page, 'member')
  await expect(page.getByText('まだジョブの実行履歴がありません。')).toBeVisible()
})
