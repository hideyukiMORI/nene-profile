import { expect, type Page, type Route } from '@playwright/test'

type Method = 'GET' | 'POST' | 'PATCH' | 'DELETE'
type Json = Record<string, unknown>

/** A captured request body, exposed to assertions via the returned ref. */
export interface BodyRef {
  value: unknown
}

function isMethod(route: Route, method: Method): boolean {
  return route.request().method() === method
}

/** Register a JSON responder for one endpoint + method (later registration wins). */
export async function mockJson(
  page: Page,
  method: Method,
  urlGlob: string,
  status: number,
  body: Json | null,
): Promise<BodyRef> {
  const ref: BodyRef = { value: undefined }
  await page.route(urlGlob, async (route) => {
    if (!isMethod(route, method)) {
      await route.fallback()
      return
    }
    try {
      // Multipart bodies are not JSON; postDataJSON() throws for them.
      ref.value = route.request().postDataJSON() as unknown
    } catch {
      ref.value = route.request().postData()
    }
    await route.fulfill({
      status,
      contentType: status === 204 ? 'text/plain' : 'application/json',
      body: status === 204 || body === null ? '' : JSON.stringify(body),
    })
  })
  return ref
}

/** Register a Problem Details (RFC 9457) responder for one endpoint + method. */
export async function mockProblem(
  page: Page,
  method: Method,
  urlGlob: string,
  status: number,
  extra: Json = {},
): Promise<void> {
  await page.route(urlGlob, async (route) => {
    if (!isMethod(route, method)) {
      await route.fallback()
      return
    }
    await route.fulfill({
      status,
      contentType: 'application/problem+json',
      body: JSON.stringify({ type: 'about:blank', title: 'Error', status, ...extra }),
    })
  })
}

export function paginated(items: Json[], total = items.length, limit = 20, offset = 0): Json {
  return { items, total, limit, offset }
}

/**
 * Install default backend mocks: empty list endpoints (so navigation renders
 * clean empty states) plus a catch-all that fails loudly for anything unmocked.
 * Specific per-test routes registered afterwards take precedence.
 */
export async function installApiMocks(page: Page): Promise<void> {
  // Catch-all (lowest precedence): surface gaps instead of hitting the proxy.
  await page.route('**/admin/**', async (route) => {
    await route.fulfill({
      status: 501,
      contentType: 'application/problem+json',
      body: JSON.stringify({
        type: 'about:blank',
        title: `Unmocked: ${route.request().method()} ${route.request().url()}`,
        status: 501,
      }),
    })
  })

  // Sensible empty defaults for the list endpoints.
  await mockJson(page, 'GET', '**/admin/organizations?*', 200, paginated([]))
  await mockJson(page, 'GET', '**/admin/users?*', 200, paginated([]))
  await mockJson(page, 'GET', '**/admin/mapping-presets?*', 200, paginated([], 0, 100, 0))
  await mockJson(page, 'GET', '**/admin/import-jobs?*', 200, paginated([]))
  await mockJson(page, 'GET', '**/admin/audit-logs?*', 200, paginated([]))
}

/** Log in through the UI with the given role, landing on the dashboard. */
export async function login(page: Page, role = 'superadmin'): Promise<void> {
  await mockJson(page, 'POST', '**/admin/auth/login', 200, {
    token: 'e2e-token',
    expires_at: new Date(Date.now() + 3_600_000).toISOString(),
    email: 'admin@example.com',
    role,
    org_id: 7,
  })

  await page.goto('/login')
  await page.getByLabel('メールアドレス').fill('admin@example.com')
  await page.getByLabel('パスワード').fill('secret-password')
  await page.getByTestId('login-submit').click()

  await expect(page.getByRole('heading', { name: 'ダッシュボード' })).toBeVisible()
}

export interface ResourceState {
  items: Json[]
}

interface ResourceOptions {
  /** Builds the created entity from the POST body (assigns the new id). */
  make?: (body: Json, id: number) => Json
  /** Builds the updated entity from a PATCH body merged onto the existing item. */
  patch?: (existing: Json, body: Json) => Json
}

/**
 * Stateful CRUD mock for a paginated resource. GET list is offset-aware, POST
 * appends, GET item returns by id, PATCH merges, DELETE removes. Lets a single
 * test exercise a create/delete flow and see the list refetch reflect it.
 */
export async function routeResource(
  page: Page,
  path: string,
  state: ResourceState,
  options: ResourceOptions = {},
): Promise<void> {
  let nextId = 1000
  const listRe = new RegExp(`/admin/${path}(\\?|$)`)
  const itemRe = new RegExp(`/admin/${path}/(\\d+)`)

  await page.route(listRe, async (route) => {
    const request = route.request()
    const url = new URL(request.url())
    if (request.method() === 'GET') {
      const limit = Number(url.searchParams.get('limit') ?? '20')
      const offset = Number(url.searchParams.get('offset') ?? '0')
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          items: state.items.slice(offset, offset + limit),
          total: state.items.length,
          limit,
          offset,
        }),
      })
      return
    }
    if (request.method() === 'POST' && options.make) {
      const created = options.make((request.postDataJSON?.() ?? {}) as Json, (nextId += 1))
      state.items.push(created)
      await route.fulfill({
        status: 201,
        contentType: 'application/json',
        body: JSON.stringify(created),
      })
      return
    }
    await route.fallback()
  })

  await page.route(itemRe, async (route) => {
    const request = route.request()
    const match = itemRe.exec(request.url())
    const id = match ? Number(match[1]) : 0
    const index = state.items.findIndex((item) => item['id'] === id)

    if (request.method() === 'GET') {
      const item = state.items[index]
      await route.fulfill({
        status: item ? 200 : 404,
        contentType: item ? 'application/json' : 'application/problem+json',
        body: item
          ? JSON.stringify(item)
          : JSON.stringify({ type: 'about:blank', title: 'Not Found', status: 404 }),
      })
      return
    }
    if (request.method() === 'PATCH' && index >= 0 && options.patch) {
      const updated = options.patch(
        state.items[index] as Json,
        (request.postDataJSON?.() ?? {}) as Json,
      )
      state.items[index] = updated
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(updated),
      })
      return
    }
    if (request.method() === 'DELETE') {
      if (index >= 0) state.items.splice(index, 1)
      await route.fulfill({ status: 204, contentType: 'text/plain', body: '' })
      return
    }
    await route.fallback()
  })
}

/** Click a top-nav link by its exact (Japanese) label and wait for navigation. */
export async function navigate(page: Page, label: string): Promise<void> {
  // Exact match: '組織' must not also select '組織設定'.
  await page.getByRole('link', { name: label, exact: true }).click()
}
