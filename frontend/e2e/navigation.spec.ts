import { expect, test } from '@playwright/test'
import { installApiMocks, login } from './support/api'

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('superadmin sees the capability-gated nav links', async ({ page }) => {
  await login(page, 'superadmin')

  await expect(page.getByRole('link', { name: '組織', exact: true })).toBeVisible()
  await expect(page.getByRole('link', { name: 'ユーザー', exact: true })).toBeVisible()
  await expect(page.getByRole('link', { name: '組織設定', exact: true })).toBeVisible()
  await expect(page.getByRole('link', { name: 'マッピングプリセット', exact: true })).toBeVisible()
})

test('viewer only sees the ungated nav links', async ({ page }) => {
  await login(page, 'viewer')

  // Hidden: require manage_organizations / manage_users / manage_organization_settings.
  await expect(page.getByRole('link', { name: '組織', exact: true })).toHaveCount(0)
  await expect(page.getByRole('link', { name: 'ユーザー', exact: true })).toHaveCount(0)
  await expect(page.getByRole('link', { name: '組織設定', exact: true })).toHaveCount(0)

  // Visible: no capability gate.
  await expect(page.getByRole('link', { name: 'マッピングプリセット', exact: true })).toBeVisible()
  await expect(page.getByRole('link', { name: 'インポートジョブ', exact: true })).toBeVisible()
  await expect(page.getByRole('link', { name: '監査ログ', exact: true })).toBeVisible()
})

test('switches the UI language between Japanese and English', async ({ page }) => {
  await login(page, 'member')
  await expect(page.getByRole('heading', { name: 'ダッシュボード' })).toBeVisible()

  await page.getByRole('button', { name: 'English' }).click()
  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Mapping presets', exact: true })).toBeVisible()

  await page.getByRole('button', { name: '日本語' }).click()
  await expect(page.getByRole('heading', { name: 'ダッシュボード' })).toBeVisible()
})
