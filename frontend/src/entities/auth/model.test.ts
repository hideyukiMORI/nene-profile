import { afterEach, describe, expect, it } from 'vitest'
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
})
