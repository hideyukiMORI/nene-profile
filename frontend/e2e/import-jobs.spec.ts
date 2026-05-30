import { expect, test, type Page } from '@playwright/test'
import { installApiMocks, login, mockJson, navigate, paginated } from './support/api'

function job(
  id: number,
  filename: string,
  status: string,
  rowCount = 10,
  errorCount = 0,
): Record<string, unknown> {
  return {
    id,
    organization_id: 7,
    preset_version_id: 9,
    original_filename: filename,
    original_file_hash: 'hash',
    status,
    row_count: rowCount,
    error_count: errorCount,
    started_at: null,
    completed_at: null,
    created_at: '2026-05-30T00:00:00Z',
  }
}

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

async function openJobs(page: Page, jobs: Record<string, unknown>[]): Promise<void> {
  await login(page, 'member')
  await mockJson(page, 'GET', '**/admin/import-jobs?*', 200, paginated(jobs))
  await navigate(page, 'インポートジョブ')
}

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('renders jobs with localized status and counts', async ({ page }) => {
  await openJobs(page, [job(7, 'bank.csv', 'completed_with_errors', 10, 2)])

  await expect(page.getByRole('cell', { name: 'bank.csv', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: 'エラーあり完了', exact: true })).toBeVisible()
})

test('shows the empty state', async ({ page }) => {
  await openJobs(page, [])
  await expect(page.getByText('ジョブがまだありません。')).toBeVisible()
})

test('upload: requires a CSV file', async ({ page }) => {
  await openJobs(page, [])
  await mockJson(page, 'GET', '**/admin/mapping-presets?*', 200, paginated([preset], 1, 100, 0))

  await page.getByRole('button', { name: 'CSV をインポート' }).click()
  await page.getByTestId('job-upload-submit').click()

  await expect(page.getByText('CSV ファイルを選択してください。')).toBeVisible()
})

test('upload: posts the file and closes the form', async ({ page }) => {
  await openJobs(page, [])
  await mockJson(page, 'GET', '**/admin/mapping-presets?*', 200, paginated([preset], 1, 100, 0))
  await mockJson(page, 'POST', '**/admin/import-jobs', 201, job(7, 'bank.csv', 'completed'))

  await page.getByRole('button', { name: 'CSV をインポート' }).click()
  // wait for the preset selector to auto-populate
  await expect(page.getByLabel('マッピングプリセット')).toHaveValue('3')
  await page.getByLabel('CSV ファイル').setInputFiles({
    name: 'bank.csv',
    mimeType: 'text/csv',
    buffer: Buffer.from('date,amount\n2026/05/30,1000\n'),
  })
  await page.getByTestId('job-upload-submit').click()

  // form closed → the import button is back, no error shown
  await expect(page.getByRole('button', { name: 'CSV をインポート' })).toBeVisible()
  await expect(page.getByText('インポートを開始できませんでした。')).toHaveCount(0)
})

test('errors: expands the rejected rows for a job', async ({ page }) => {
  await openJobs(page, [job(7, 'bank.csv', 'completed_with_errors', 10, 2)])
  await mockJson(
    page,
    'GET',
    '**/admin/import-jobs/7/errors*',
    200,
    paginated([
      { id: 1, import_job_id: 7, raw_row_number: 4, message: '日付が不正です', raw_snippet: 'xx' },
    ]),
  )

  await page.getByRole('button', { name: 'エラー行' }).click()

  await expect(page.getByText('日付が不正です')).toBeVisible()
  await expect(page.getByRole('cell', { name: '4', exact: true })).toBeVisible()
})

test('export: buttons appear for completed jobs', async ({ page }) => {
  await openJobs(page, [job(7, 'bank.csv', 'completed')])

  await expect(page.getByRole('button', { name: 'JSON でエクスポート' })).toBeVisible()
  await expect(page.getByRole('button', { name: 'CSV でエクスポート' })).toBeVisible()
})

test('export: buttons are hidden for pending jobs', async ({ page }) => {
  await openJobs(page, [job(8, 'queued.csv', 'pending')])

  await expect(page.getByRole('cell', { name: '待機中', exact: true })).toBeVisible()
  await expect(page.getByRole('button', { name: 'JSON でエクスポート' })).toHaveCount(0)
})
