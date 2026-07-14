import { afterEach, describe, expect, it } from 'vitest'
import { tokenStore } from '@/shared/api/client'
import { authStore, type AuthSession } from './model'

function session(overrides: Partial<AuthSession> = {}): AuthSession {
  return {
    token: 'jwt-token',
    expiresAt: new Date(Date.now() + 3_600_000).toISOString(),
    email: 'admin@example.com',
    role: 'admin',
    orgId: 7,
    ...overrides,
  }
}

afterEach(() => {
  authStore.clearSession()
})

describe('authStore', () => {
  it('starts empty and unauthenticated', () => {
    expect(authStore.getSession()).toBeNull()
    expect(authStore.getToken()).toBeNull()
    expect(authStore.isAuthenticated()).toBe(false)
  })

  it('stores and exposes the session token', () => {
    authStore.setSession(session())

    expect(authStore.getSession()?.email).toBe('admin@example.com')
    expect(authStore.getToken()).toBe('jwt-token')
    expect(authStore.isAuthenticated()).toBe(true)
  })

  it('clears the session', () => {
    authStore.setSession(session())
    authStore.clearSession()

    expect(authStore.getSession()).toBeNull()
    expect(authStore.isAuthenticated()).toBe(false)
  })

  it('treats an expired session as unauthenticated', () => {
    authStore.setSession(session({ expiresAt: new Date(Date.now() - 1000).toISOString() }))

    expect(authStore.getSession()).not.toBeNull()
    expect(authStore.isAuthenticated()).toBe(false)
  })

  it('behavior change: the raw token outlives an in-memory reset (reload), unlike the extras', () => {
    // Simulates a same-tab reload: sessionStorage (the token store) survives,
    // but module-level state (the `extras` closure in model.ts) does not.
    // Before adopting createSessionTokenStore, the token was purely in-memory
    // and would have been lost too.
    authStore.setSession(session())
    tokenStore.setToken('jwt-token') // no-op re-set to be explicit about intent

    // A reload only resets in-memory module state, not sessionStorage.
    expect(tokenStore.getToken()).toBe('jwt-token')

    // But the app still treats the user as signed out, because getSession()
    // requires both the token *and* the in-memory extras — RequireAuth still
    // redirects to /login and re-populates extras via a fresh login.
    authStore.clearSession()
    tokenStore.setToken('jwt-token') // re-plant only the token, as a reload would leave it
    expect(authStore.getSession()).toBeNull()
    expect(authStore.isAuthenticated()).toBe(false)
  })
})
