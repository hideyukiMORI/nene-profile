import { expect, test, type Page } from '@playwright/test'
import { installApiMocks, login, mockJson, navigate, type BodyRef } from './support/api'

function settings(tokenSet = false): Record<string, unknown> {
  return {
    organization_id: 7,
    default_encoding: 'auto',
    max_file_size_bytes: 1048576,
    clear_bearer_token_set: tokenSet,
  }
}

async function openSettings(page: Page, tokenSet = false): Promise<BodyRef> {
  await login(page, 'admin')
  await mockJson(page, 'GET', '**/admin/organization-settings', 200, settings(tokenSet))
  const patchBody = await mockJson(
    page,
    'PATCH',
    '**/admin/organization-settings',
    200,
    settings(tokenSet),
  )
  await navigate(page, '組織設定')
  await expect(page.getByLabel('デフォルト文字コード')).toBeVisible()
  return patchBody
}

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('saves encoding and size, omitting the token when blank', async ({ page }) => {
  const body = await openSettings(page)

  await page.getByLabel('デフォルト文字コード').selectOption('utf-8')
  const size = page.getByLabel('最大ファイルサイズ（バイト）')
  await size.fill('2000')
  await page.getByTestId('settings-save').click()

  await expect(page.getByText('設定を保存しました。')).toBeVisible()
  expect(body.value).toEqual({ default_encoding: 'utf-8', max_file_size_bytes: 2000 })
})

test('sends the token when provided', async ({ page }) => {
  const body = await openSettings(page)

  await page.getByLabel('NeNe Clear 連携トークン（任意）').fill('tok_123')
  await page.getByTestId('settings-save').click()

  await expect(page.getByText('設定を保存しました。')).toBeVisible()
  expect(body.value).toMatchObject({ clear_bearer_token: 'tok_123' })
})

test('shows the configured caption when a token is already set', async ({ page }) => {
  await openSettings(page, true)
  await expect(page.getByText('設定済み')).toBeVisible()
})

test('validates a max file size below 1', async ({ page }) => {
  await openSettings(page)

  const size = page.getByLabel('最大ファイルサイズ（バイト）')
  await size.fill('0')
  await page.getByTestId('settings-save').click()

  await expect(page.getByText('最大ファイルサイズは1以上で入力してください。')).toBeVisible()
  await expect(page.getByText('設定を保存しました。')).toHaveCount(0)
})
