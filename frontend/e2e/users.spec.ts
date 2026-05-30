import { expect, test, type Page } from '@playwright/test'
import { installApiMocks, login, navigate, routeResource, type ResourceState } from './support/api'

function user(
  id: number,
  email: string,
  role = 'member',
  status = 'active',
): Record<string, unknown> {
  return {
    id,
    email,
    role,
    organization_id: 7,
    status,
    created_at: '2026-05-30T00:00:00Z',
    updated_at: '2026-05-30T00:00:00Z',
  }
}

async function open(page: Page, state: ResourceState): Promise<void> {
  await login(page, 'admin')
  await routeResource(page, 'users', state, {
    make: (body, id) => user(id, String(body['email']), String(body['role']), 'invited'),
    patch: (existing, body) => ({
      ...existing,
      role: body['role'] ?? existing['role'],
      status: body['status'] ?? existing['status'],
    }),
  })
  await navigate(page, 'ユーザー')
}

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('renders users with localized role and status', async ({ page }) => {
  await open(page, { items: [user(1, 'op@example.com', 'member', 'active')] })

  await expect(page.getByRole('cell', { name: 'op@example.com', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: 'メンバー', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: '有効', exact: true })).toBeVisible()
})

test('shows the empty state', async ({ page }) => {
  await open(page, { items: [] })
  await expect(page.getByText('ユーザーがまだいません。')).toBeVisible()
})

test('create: requires an email', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: 'ユーザーを作成' }).click()
  await page.getByLabel('パスワード（8文字以上）').fill('supersecret')
  await page.getByTestId('user-create-submit').click()

  await expect(page.getByText('メールアドレスを入力してください。')).toBeVisible()
})

test('create: rejects a short password (8-char boundary)', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: 'ユーザーを作成' }).click()
  await page.getByLabel('メールアドレス').fill('op@example.com')
  await page.getByLabel('パスワード（8文字以上）').fill('short')
  await page.getByTestId('user-create-submit').click()

  await expect(page.getByText('パスワードは8文字以上で入力してください。')).toBeVisible()
})

test('create: succeeds with a selected role', async ({ page }) => {
  await open(page, { items: [] })
  await page.getByRole('button', { name: 'ユーザーを作成' }).click()
  await page.getByLabel('メールアドレス').fill('new@example.com')
  await page.getByLabel('パスワード（8文字以上）').fill('supersecret')
  await page.getByLabel('役割').selectOption('viewer')
  await page.getByTestId('user-create-submit').click()

  await expect(page.getByRole('cell', { name: 'new@example.com', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: '閲覧者', exact: true })).toBeVisible()
})

test('edit: updates role/status with a blank (unchanged) password', async ({ page }) => {
  await open(page, { items: [user(1, 'op@example.com', 'member', 'invited')] })

  await page.getByRole('button', { name: '編集', exact: true }).click()
  await expect(page.getByText('ユーザーの編集')).toBeVisible()
  await page.getByLabel('役割').selectOption('admin')
  await page.getByLabel('状態').selectOption('active')
  await page.getByTestId('user-edit-submit').click()

  await expect(page.getByRole('cell', { name: '管理者', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: '有効', exact: true })).toBeVisible()
})

test('edit: rejects a short new password', async ({ page }) => {
  await open(page, { items: [user(1, 'op@example.com')] })

  await page.getByRole('button', { name: '編集', exact: true }).click()
  await page.getByLabel('パスワード（変更する場合のみ）').fill('short')
  await page.getByTestId('user-edit-submit').click()

  await expect(page.getByText('パスワードは8文字以上で入力してください。')).toBeVisible()
})

test('delete: confirm removes the row', async ({ page }) => {
  await open(page, { items: [user(1, 'op@example.com')] })

  await page.getByRole('button', { name: '削除', exact: true }).click()
  await expect(page.getByRole('dialog')).toContainText('「op@example.com」を削除します')
  await page.getByTestId('confirm-dialog-confirm').click()

  await expect(page.getByRole('cell', { name: 'op@example.com', exact: true })).toHaveCount(0)
})
