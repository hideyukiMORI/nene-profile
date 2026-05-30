import { type ReactNode } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { authStore } from '@/entities/auth'

/** Fail-closed route guard: unauthenticated access redirects to /login. */
export function RequireAuth({ children }: { children: ReactNode }) {
  const location = useLocation()

  if (!authStore.isAuthenticated()) {
    return <Navigate to="/login" state={{ from: location }} replace />
  }

  return <>{children}</>
}
