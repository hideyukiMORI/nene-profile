import { type ReactNode } from 'react'

/**
 * Fail-closed auth shell. With an in-memory session there is no async bootstrap
 * to await, so this is a pass-through today; it is the seam where a future
 * session-restore (cookie/refresh) check would gate rendering.
 */
export function AuthGate({ children }: { children: ReactNode }) {
  return <>{children}</>
}
