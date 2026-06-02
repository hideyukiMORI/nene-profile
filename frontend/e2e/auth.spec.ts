import { expect, test } from '@playwright/test'
import { installApiMocks, login, mockProblem, navigate } from './support/api'

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test.describe('login', () => {
  test('logs in with valid credentials and lands on the dashboard', async ({ page }) => {
    await login(page)
    await expect(page.getByRole('heading', { name: 'ダッシュボード' })).toBeVisible()
  })

  test('blocks submit and shows field errors when empty', async ({ page }) => {
    await page.goto('/login')
    await page.getByTestId('login-submit').click()

    await expect(page.getByText('メールアドレスを入力してください。')).toBeVisible()
    await expect(page.getByText('パスワードを入力してください。')).toBeVisible()
    await expect(page).toHaveURL(/\/login$/)
  })

  test('shows an auth error on 401 and stays on login', async ({ page }) => {
    await mockProblem(page, 'POST', '**/admin/auth/login', 401)

    await page.goto('/login')
    await page.getByLabel('メールアドレス').fill('admin@example.com')
    await page.getByLabel('パスワード', { exact: true }).fill('wrong-password')
    await page.getByTestId('login-submit').click()

    await expect(page.getByText('メールアドレスまたはパスワードが正しくありません。')).toBeVisible()
    await expect(page).toHaveURL(/\/login$/)
  })

  test('shows a generic error on a 5xx response', async ({ page }) => {
    await mockProblem(page, 'POST', '**/admin/auth/login', 500)

    await page.goto('/login')
    await page.getByLabel('メールアドレス').fill('admin@example.com')
    await page.getByLabel('パスワード', { exact: true }).fill('secret-password')
    await page.getByTestId('login-submit').click()

    await expect(page.getByText('エラーが発生しました。')).toBeVisible()
  })
})

test.describe('protected access', () => {
  test('redirects an unauthenticated full-page visit to login', async ({ page }) => {
    await page.goto('/organizations')
    await expect(page).toHaveURL(/\/login$/)
    await expect(page.getByRole('heading', { name: 'ログイン' })).toBeVisible()
  })

  test('routes to the forbidden page when the API answers 403', async ({ page }) => {
    await login(page)
    await mockProblem(page, 'GET', '**/admin/organizations?*', 403)

    await navigate(page, '組織')

    await expect(page.getByText('この操作を行う権限がありません。')).toBeVisible()
    await expect(page).toHaveURL(/\/forbidden$/)
  })
})

test.describe('unknown routes', () => {
  test('shows the not-found page', async ({ page }) => {
    await login(page)
    await page.goto('/this-route-does-not-exist')
    await expect(page.getByText('対象が見つかりませんでした。')).toBeVisible()
  })
})

test.describe('sign out', () => {
  test('clears the session and returns to login', async ({ page }) => {
    await login(page)
    await page.getByRole('button', { name: 'ログアウト' }).click()
    await expect(page).toHaveURL(/\/login$/)
  })
})

// Ensures the catch-all/default mocks keep unmocked endpoints off the proxy.
test('dashboard renders the empty state by default', async ({ page }) => {
  await login(page)
  await expect(page.getByText('まだジョブの実行履歴がありません。')).toBeVisible()
})
