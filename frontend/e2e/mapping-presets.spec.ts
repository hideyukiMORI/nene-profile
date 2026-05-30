import { expect, test, type Page } from '@playwright/test'
import { installApiMocks, login, navigate, routeResource, type ResourceState } from './support/api'

function preset(
  id: number,
  name: string,
  versionNumber = 1,
  withDefinition = false,
): Record<string, unknown> {
  const base: Record<string, unknown> = {
    id,
    name,
    bank_label: `${name}銀行`,
    current_version_id: id * 10,
    version_number: versionNumber,
    is_deleted: false,
    created_at: '2026-05-30T00:00:00Z',
    updated_at: '2026-05-30T00:00:00Z',
  }
  if (withDefinition) {
    base['definition'] = {
      encoding: 'auto',
      delimiter: 'comma',
      header_row_index: 0,
      year_pivot: 50,
      columns: {
        transaction_date: { source: '日付', transform: 'date_ymd_slash' },
        amount: { source: '金額', transform: 'amount_yen_to_cents' },
      },
    }
  }
  return base
}

async function open(page: Page, state: ResourceState): Promise<void> {
  await login(page, 'member')
  await routeResource(page, 'mapping-presets', state, {
    make: (body, id) => ({
      ...preset(id, String(body['name'])),
      bank_label: String(body['bank_label']),
    }),
    patch: (existing) => ({ ...existing, version_number: Number(existing['version_number']) + 1 }),
  })
  await navigate(page, 'マッピングプリセット')
}

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('renders presets with the current version', async ({ page }) => {
  await open(page, { items: [preset(1, 'みずほ', 2)] })

  await expect(page.getByRole('cell', { name: 'みずほ', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: 'みずほ銀行', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: 'v2', exact: true })).toBeVisible()
})

test('shows the empty state', async ({ page }) => {
  await open(page, { items: [] })
  await expect(page.getByText('プリセットがまだありません。')).toBeVisible()
})

test('create: requires a name and bank label', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: 'プリセットを作成' }).click()
  await page.getByTestId('preset-create-submit').click()

  await expect(page.getByText('名称を入力してください。')).toBeVisible()
  await expect(page.getByText('銀行ラベルを入力してください。')).toBeVisible()
})

test('create: succeeds with a column mapping', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: 'プリセットを作成' }).click()
  await page.getByLabel('名称').fill('三井住友')
  await page.getByLabel('銀行ラベル').fill('三井住友銀行')
  await page.getByLabel('取引日 元の列見出し').fill('取引日')
  await page.getByLabel('取引日 変換').selectOption('date_ymd_slash')
  await page.getByTestId('preset-create-submit').click()

  await expect(page.getByRole('cell', { name: '三井住友', exact: true })).toBeVisible()
})

test('edit: pre-fills the definition and creates a new version', async ({ page }) => {
  await open(page, { items: [preset(1, 'みずほ', 1, true)] })

  await page.getByRole('button', { name: '編集', exact: true }).click()
  await expect(page.getByText('プリセットの編集（新バージョン）')).toBeVisible()
  // The loaded definition pre-fills the source inputs.
  await expect(page.getByLabel('取引日 元の列見出し')).toHaveValue('日付')
  await expect(page.getByLabel('金額 元の列見出し')).toHaveValue('金額')

  await page.getByTestId('preset-edit-submit').click()

  await expect(page.getByRole('cell', { name: 'v2', exact: true })).toBeVisible()
})

test('delete: confirm removes the row', async ({ page }) => {
  await open(page, { items: [preset(1, 'みずほ')] })

  await page.getByRole('button', { name: '削除', exact: true }).click()
  await expect(page.getByRole('dialog')).toContainText('「みずほ」を削除します')
  await page.getByTestId('confirm-dialog-confirm').click()

  await expect(page.getByRole('cell', { name: 'みずほ', exact: true })).toHaveCount(0)
})
