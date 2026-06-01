import { expect, test, type Page } from '@playwright/test'
import {
  installApiMocks,
  login,
  mockProblem,
  navigate,
  routeResource,
  type ResourceState,
} from './support/api'

function org(id: number, name: string, slug: string, isActive = true): Record<string, unknown> {
  return {
    id,
    name,
    slug,
    is_active: isActive,
    custom_domain: null,
    created_at: '2026-05-30T00:00:00Z',
    updated_at: '2026-05-30T00:00:00Z',
  }
}

async function open(page: Page, state: ResourceState): Promise<void> {
  await login(page, 'superadmin')
  await routeResource(page, 'organizations', state, {
    make: (body, id) => org(id, String(body['name']), String(body['slug'])),
  })
  await navigate(page, '組織')
}

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('renders organizations with localized status', async ({ page }) => {
  await open(page, { items: [org(1, 'Acme', 'acme', true), org(2, 'Beta', 'beta', false)] })

  await expect(page.getByText('Acme', { exact: true })).toBeVisible()
  await expect(page.getByText('beta', { exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: '有効', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: '無効', exact: true })).toBeVisible()
})

test('shows the empty state', async ({ page }) => {
  await open(page, { items: [] })
  await expect(page.getByText('組織がまだありません。')).toBeVisible()
})

test('paginates through pages with prev/next boundaries', async ({ page }) => {
  const items = Array.from({ length: 25 }, (_, i) =>
    org(i + 1, `Org ${String(i + 1)}`, `org-${String(i + 1)}`),
  )
  await open(page, { items })

  await expect(page.getByText('25 件中 1–20 件を表示')).toBeVisible()
  await expect(page.getByRole('button', { name: '前へ' })).toBeDisabled()
  await expect(page.getByRole('button', { name: '次へ' })).toBeEnabled()

  await page.getByRole('button', { name: '次へ' }).click()

  await expect(page.getByText('25 件中 21–25 件を表示')).toBeVisible()
  await expect(page.getByText('Org 21')).toBeVisible()
  await expect(page.getByRole('button', { name: '前へ' })).toBeEnabled()
  await expect(page.getByRole('button', { name: '次へ' })).toBeDisabled()
})

test('create: validates required name and slug', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: '組織を作成' }).click()
  await page.getByTestId('org-create-submit').click()

  await expect(page.getByText('名称を入力してください。')).toBeVisible()
  await expect(page.getByText('スラグを入力してください。')).toBeVisible()
})

test('create: rejects an invalid slug pattern', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: '組織を作成' }).click()
  await page.getByLabel('名称').fill('Acme')
  await page.getByLabel('スラグ（英小文字・数字・ハイフン）').fill('Acme Corp!')
  await page.getByTestId('org-create-submit').click()

  await expect(page.getByText('スラグは英小文字・数字・ハイフンのみ使用できます。')).toBeVisible()
})

test('create: succeeds and the new row appears', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: '組織を作成' }).click()
  await page.getByLabel('名称').fill('Acme Corp')
  await page.getByLabel('スラグ（英小文字・数字・ハイフン）').fill('acme')
  await page.getByTestId('org-create-submit').click()

  await expect(page.getByText('Acme Corp')).toBeVisible()
  // form closed → the create button is back
  await expect(page.getByRole('button', { name: '組織を作成' })).toBeVisible()
})

test('create: surfaces a 422 error', async ({ page }) => {
  await open(page, { items: [] })
  await mockProblem(page, 'POST', '**/admin/organizations', 422)

  await page.getByRole('button', { name: '組織を作成' }).click()
  await page.getByLabel('名称').fill('Acme')
  await page.getByLabel('スラグ（英小文字・数字・ハイフン）').fill('acme')
  await page.getByTestId('org-create-submit').click()

  await expect(
    page.getByText('組織を作成できませんでした。入力内容を確認してください。'),
  ).toBeVisible()
})

test('delete: confirm removes the row', async ({ page }) => {
  await open(page, { items: [org(1, 'Acme', 'acme')] })

  await page.getByRole('button', { name: '削除', exact: true }).click()
  await expect(page.getByRole('dialog')).toContainText('「Acme」を削除します')
  await page.getByTestId('confirm-dialog-confirm').click()

  await expect(page.getByText('Acme', { exact: true })).toHaveCount(0)
})

test('delete: cancel keeps the row', async ({ page }) => {
  await open(page, { items: [org(1, 'Acme', 'acme')] })

  await page.getByRole('button', { name: '削除', exact: true }).click()
  // The full-screen backdrop also carries the cancel label, so scope to the dialog.
  await page.getByRole('dialog').getByRole('button', { name: 'キャンセル' }).click()

  await expect(page.getByRole('dialog')).toHaveCount(0)
  await expect(page.getByText('Acme', { exact: true })).toBeVisible()
})

test('delete: shows an in-dialog error on failure', async ({ page }) => {
  await open(page, { items: [org(1, 'Acme', 'acme')] })
  await mockProblem(page, 'DELETE', '**/admin/organizations/*', 500)

  await page.getByRole('button', { name: '削除', exact: true }).click()
  await page.getByTestId('confirm-dialog-confirm').click()

  await expect(page.getByText('削除できませんでした。')).toBeVisible()
  await expect(page.getByRole('dialog')).toBeVisible()
})

test('edit: opens form, updates name, and row reflects change', async ({ page }) => {
  await open(page, {
    items: [org(1, 'Acme', 'acme')],
  })
  await routeResource(
    page,
    'organizations',
    { items: [org(1, 'Acme', 'acme')] },
    {
      patch: (existing, body) => ({ ...existing, ...body }),
    },
  )

  await page.getByTestId('org-edit-1').click()
  await expect(page.getByTestId('org-name')).toBeVisible()
  await page.getByTestId('org-name').fill('Acme Updated')
  await page.getByTestId('org-edit-submit').click()

  await expect(page.getByText('Acme Updated', { exact: true })).toBeVisible()
  await expect(page.getByRole('button', { name: '組織を作成' })).toBeVisible()
})

test('edit: cancel closes form without saving', async ({ page }) => {
  await open(page, { items: [org(1, 'Acme', 'acme')] })

  await page.getByTestId('org-edit-1').click()
  await page.getByTestId('org-name').fill('Changed But Cancelled')
  await page.getByRole('button', { name: 'キャンセル', exact: true }).click()

  await expect(page.getByText('Acme', { exact: true })).toBeVisible()
  await expect(page.getByRole('button', { name: '組織を作成' })).toBeVisible()
})
