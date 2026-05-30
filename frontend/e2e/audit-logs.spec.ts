import { expect, test, type Page } from '@playwright/test'
import { installApiMocks, login, mockJson, navigate, paginated } from './support/api'

function entry(overrides: Record<string, unknown> = {}): Record<string, unknown> {
  return {
    id: 1,
    actor_user_id: 5,
    organization_id: 7,
    action: 'organization.updated',
    entity_type: 'organization',
    entity_id: 9,
    before: { name: 'Old' },
    after: { name: 'New' },
    created_at: '2026-05-30T00:00:00Z',
    ...overrides,
  }
}

async function openAudit(page: Page, items: Record<string, unknown>[]): Promise<void> {
  await login(page, 'admin')
  await mockJson(page, 'GET', '**/admin/audit-logs?*', 200, paginated(items))
  await navigate(page, '監査ログ')
}

test.beforeEach(async ({ page }) => {
  await installApiMocks(page)
})

test('renders audit entries with actor and entity', async ({ page }) => {
  await openAudit(page, [entry()])

  await expect(page.getByRole('cell', { name: 'organization.updated', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: 'organization #9', exact: true })).toBeVisible()
  await expect(page.getByRole('cell', { name: '#5', exact: true })).toBeVisible()
})

test('toggles the before/after diff', async ({ page }) => {
  await openAudit(page, [entry()])

  await page.getByRole('button', { name: '差分を表示' }).click()

  await expect(page.getByText('変更前')).toBeVisible()
  await expect(page.getByText('変更後')).toBeVisible()
  await expect(page.getByText('"name": "Old"')).toBeVisible()
  await expect(page.getByText('"name": "New"')).toBeVisible()
})

test('labels a system actor when there is no user', async ({ page }) => {
  await openAudit(page, [entry({ id: 2, actor_user_id: null, action: 'system.purge' })])

  await expect(page.getByRole('cell', { name: 'システム', exact: true })).toBeVisible()
})

test('shows the empty state', async ({ page }) => {
  await openAudit(page, [])
  await expect(page.getByText('監査ログがまだありません。')).toBeVisible()
})
