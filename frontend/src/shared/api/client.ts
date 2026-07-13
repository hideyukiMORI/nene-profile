import { authStore } from '@/entities/auth'
import { AppError, parseProblemDetails } from '@/shared/api/errors'
import { env } from '@/shared/config/env'

type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'DELETE'

interface RequestOptions {
  method?: HttpMethod
  body?: unknown
  signal?: AbortSignal
}

/**
 * Side-effecting auth handling shared by request() and upload(): a 401 clears
 * the (in-memory) session and bounces to login; a 403 routes to forbidden.
 * Fail-closed by default.
 */
function handleErrorResponse(response: Response, path: string): void {
  if (response.status === 401 && !path.includes('/auth/login')) {
    authStore.clearSession()
    window.location.href = '/login'
  }
  if (response.status === 403) {
    window.location.href = '/forbidden'
  }
}

function buildUrl(path: string): string {
  return `${env.apiBaseUrl.replace(/\/$/, '')}${path}`
}

function authHeaders(): Record<string, string> {
  const token = authStore.getToken()
  return token !== null ? { Authorization: `Bearer ${token}` } : {}
}

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const headers: Record<string, string> = { ...authHeaders() }
  if (options.body !== undefined) headers['Content-Type'] = 'application/json'

  const response = await fetch(buildUrl(path), {
    method: options.method ?? 'GET',
    headers,
    ...(options.body !== undefined ? { body: JSON.stringify(options.body) } : {}),
    ...(options.signal !== undefined ? { signal: options.signal } : {}),
  })

  if (!response.ok) {
    handleErrorResponse(response, path)
    throw await parseProblemDetails(response)
  }

  if (response.status === 204) return undefined as T

  return (await response.json()) as T
}

export const apiClient = {
  get<T>(path: string, signal?: AbortSignal): Promise<T> {
    return request<T>(path, signal !== undefined ? { signal } : {})
  },
  post<T>(path: string, body: unknown): Promise<T> {
    return request<T>(path, { method: 'POST', body })
  },
  patch<T>(path: string, body: unknown): Promise<T> {
    return request<T>(path, { method: 'PATCH', body })
  },
  delete(path: string): Promise<undefined> {
    return request<undefined>(path, { method: 'DELETE' })
  },
  async upload<T>(path: string, formData: FormData): Promise<T> {
    const response = await fetch(buildUrl(path), {
      method: 'POST',
      headers: authHeaders(),
      body: formData,
    })

    if (!response.ok) {
      handleErrorResponse(response, path)
      throw await parseProblemDetails(response)
    }

    return (await response.json()) as T
  },
  /**
   * Authenticated file download. Bearer auth means a plain <a href> cannot carry
   * the token, so we fetch the bytes and trigger a client-side download.
   */
  async download(path: string, filename: string): Promise<void> {
    const response = await fetch(buildUrl(path), { headers: authHeaders() })

    if (!response.ok) {
      handleErrorResponse(response, path)
      throw await parseProblemDetails(response)
    }

    const blob = await response.blob()
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
