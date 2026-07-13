import { tokenStore } from '@/shared/api/client'
import type { UserRole } from './capabilities'

export interface AuthSession {
  token: string
  expiresAt: string
  email: string
  role: UserRole
  orgId: number | null
}

/** Everything in `AuthSession` except the bearer token. */
type SessionExtras = Omit<AuthSession, 'token'>

/**
 * In-memory session extras (frontend-standards.md: in-memory by default; a
 * persistent store requires an ADR). `email`/`role`/`orgId`/`expiresAt` do not
 * survive a reload — a reload still requires re-login through `RequireAuth`.
 *
 * The bearer token itself (`tokenStore`, `@hideyukimori/nene2-client`'s
 * `createSessionTokenStore`) is backed by `sessionStorage` (fleet decision
 * 2026-07-14, issue #113). **Behavior change**: the raw token now survives a
 * same-tab reload, whereas before this migration it was purely in-memory and
 * vanished on any reload along with the rest of the session. In practice this
 * is not user-visible — `getSession()`/`isAuthenticated()` below still return
 * null/false after a reload (because `extras` is gone), so `RequireAuth`
 * still redirects to `/login` and the app never reads the leftover token
 * through the gated UI. The token is overwritten on the next login, cleared
 * on explicit logout, and auto-cleared by the transport on a 401.
 */
let extras: SessionExtras | null = null

export const authStore = {
  getSession(): AuthSession | null {
    const token = tokenStore.getToken()
    if (token === null || extras === null) return null
    return { token, ...extras }
  },

  setSession(next: AuthSession): void {
    const { token, ...rest } = next
    tokenStore.setToken(token)
    extras = rest
  },

  clearSession(): void {
    tokenStore.clearToken()
    extras = null
  },

  getToken(): string | null {
    return tokenStore.getToken()
  },

  isAuthenticated(): boolean {
    const session = authStore.getSession()
    if (session === null) return false
    return new Date(session.expiresAt) > new Date()
  },
}
