import { expect, test } from '@playwright/test'
import { installApiMocks, login, mockJson, mockProblem, navigate } from './support/api'

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('navigates to account page from nav link', async ({ page }) => {
  await login(page)
  await navigate(page, 'アカウント')

  await expect(page.getByRole('heading', { name: 'アカウント' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'パスワード変更' })).toBeVisible()
})

test('change password: success resets form and shows confirmation', async ({ page }) => {
  await login(page)
  await navigate(page, 'アカウント')

  await mockJson(page, 'PATCH', '**/admin/auth/me/password', 204, null)

  await page.getByTestId('current-password').fill('current-password')
  await page.getByTestId('new-password').fill('new-password-123')
  await page.getByTestId('confirm-password').fill('new-password-123')
  await page.getByTestId('change-password-submit').click()

  await expect(page.getByRole('status')).toContainText('パスワードを変更しました。')
  await expect(page.getByTestId<HTMLInputElement>('current-password')).toHaveValue('')
})

test('change password: shows validation error for mismatched passwords', async ({ page }) => {
  await login(page)
  await navigate(page, 'アカウント')

  await page.getByTestId('current-password').fill('current-password')
  await page.getByTestId('new-password').fill('new-password-123')
  await page.getByTestId('confirm-password').fill('different-password')
  await page.getByTestId('change-password-submit').click()

  await expect(page.getByText('パスワードが一致しません。')).toBeVisible()
})

test('change password: shows validation error when new password too short', async ({ page }) => {
  await login(page)
  await navigate(page, 'アカウント')

  await page.getByTestId('current-password').fill('current-password')
  await page.getByTestId('new-password').fill('short')
  await page.getByTestId('confirm-password').fill('short')
  await page.getByTestId('change-password-submit').click()

  await expect(page.getByText('新しいパスワードは8文字以上で入力してください。')).toBeVisible()
})

test('change password: shows error when current password is wrong (422)', async ({ page }) => {
  await login(page)
  await navigate(page, 'アカウント')

  await mockProblem(page, 'PATCH', '**/admin/auth/me/password', 422, {
    type: 'invalid-current-password',
  })

  await page.getByTestId('current-password').fill('wrong-password')
  await page.getByTestId('new-password').fill('new-password-123')
  await page.getByTestId('confirm-password').fill('new-password-123')
  await page.getByTestId('change-password-submit').click()

  await expect(page.getByText('現在のパスワードが正しくありません。')).toBeVisible()
})
