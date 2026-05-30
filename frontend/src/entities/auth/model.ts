import type { UserRole } from './capabilities'

export interface AuthSession {
  token: string
  expiresAt: string
  email: string
  role: UserRole
  orgId: number | null
}

/**
 * In-memory session store (frontend-standards.md: in-memory by default; a
 * persistent store requires an ADR). The token lives only for the SPA session
 * — a reload is fail-closed and requires re-login.
 */
let session: AuthSession | null = null

export const authStore = {
  getSession(): AuthSession | null {
    return session
  },

  setSession(next: AuthSession): void {
    session = next
  },

  clearSession(): void {
    session = null
  },

  getToken(): string | null {
    return session?.token ?? null
  },

  isAuthenticated(): boolean {
    if (session === null) return false
    return new Date(session.expiresAt) > new Date()
  },
}
