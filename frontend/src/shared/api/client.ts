import {
  createNene2Transport,
  createSessionTokenStore,
  isNene2ClientError,
  validationErrorsFromClientError,
} from '@hideyukimori/nene2-client'
import { AppError } from '@/shared/api/errors'
import { env } from '@/shared/config/env'

/**
 * Fleet-standard bearer token store (issue #113, adopting `@hideyukimori/nene2-client`
 * v1.1.0). Backed by `sessionStorage` in browsers (in-memory fallback in tests/SSR),
 * fail-closed on storage errors. `entities/auth/model.ts` wraps this as the token
 * half of `AuthSession` — see its doc comment for the behavior change this
 * introduces (the raw token now survives a same-tab reload; the rest of the
 * session does not).
 */
export const tokenStore = createSessionTokenStore({ key: 'nene_profile_token' })

const transport = createNene2Transport({
  baseUrl: env.apiBaseUrl,
  tokenStore,
  // `createNene2Transport` resolves and binds `globalThis.fetch` once, at
  // creation time. `transport` is a module-level singleton created at import
  // time — earlier than test setup (MSW) or any runtime fetch instrumentation
  // installs its patched `fetch`, so an eagerly-bound reference would miss it.
  // Passing a thin wrapper keeps the lookup lazy (resolved on every call, the
  // same as the pre-migration client's bare `fetch(...)`), so it always uses
  // whatever `fetch` is current — MSW in tests included.
  fetch: (input, init) => fetch(input, init),
  // A 401 only reaches here when a token was attached (e.g. not on a failed
  // login attempt), matching the previous handleErrorResponse's `/auth/login`
  // exclusion without needing a path check.
  onUnauthorized: () => {
    window.location.href = '/login'
  },
  onForbidden: () => {
    window.location.href = '/forbidden'
  },
})

/**
 * Maps a transport failure onto the product's existing `AppError` (RFC 9457
 * Problem Details) so every call site (which already catches/inspects
 * `AppError`) stays untouched. Non-transport errors (should not occur in
 * practice) are rethrown as-is.
 */
function toAppError(error: unknown): unknown {
  if (!isNene2ClientError(error)) return error
  const problem = error.problem
  const validationErrors = validationErrorsFromClientError(error)
  return new AppError({
    type: problem?.type ?? 'about:blank',
    title: problem?.title ?? 'Request failed',
    status: problem?.status ?? error.status,
    ...(problem?.detail !== undefined ? { detail: problem.detail } : {}),
    ...(problem?.instance !== undefined ? { instance: problem.instance } : {}),
    ...(validationErrors !== undefined ? { errors: validationErrors } : {}),
  })
}

async function guarded<T>(promise: Promise<T>): Promise<T> {
  try {
    return await promise
  } catch (error) {
    throw toAppError(error)
  }
}

export const apiClient = {
  get<T>(path: string, signal?: AbortSignal): Promise<T> {
    return guarded(transport.get<T>(path, signal !== undefined ? { signal } : {}))
  },
  post<T>(path: string, body: unknown): Promise<T> {
    return guarded(transport.post<T>(path, body))
  },
  patch<T>(path: string, body: unknown): Promise<T> {
    return guarded(transport.patch<T>(path, body))
  },
  delete(path: string): Promise<undefined> {
    return guarded(transport.delete<undefined>(path))
  },
  upload<T>(path: string, formData: FormData): Promise<T> {
    return guarded(transport.upload<T>(path, formData))
  },
  /**
   * Authenticated file download. Bearer auth means a plain <a href> cannot carry
   * the token, so we fetch the bytes and trigger a client-side download. Uses
   * the caller-supplied filename (as before); the server's `Content-Disposition`
   * suggestion (available via the transport's `getBlob`) is intentionally
   * ignored to keep this behavior-identical to the pre-migration client.
   */
  async download(path: string, filename: string): Promise<void> {
    const { blob } = await guarded(transport.getBlob(path))
    const url = URL.createObjectURL(blob)
    const anchor = document.createElement('a')
    anchor.href = url
    anchor.download = filename
    document.body.appendChild(anchor)
    anchor.click()
    anchor.remove()
    URL.revokeObjectURL(url)
  },
}

export { AppError }
