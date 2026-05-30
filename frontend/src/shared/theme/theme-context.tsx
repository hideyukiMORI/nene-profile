import { type ReactNode } from 'react'
import '@/shared/ui/theme/index.css'

/**
 * Loads the theme stylesheet once. A future multi-theme switch would swap the
 * active token set here; for now it guarantees the theme layer is imported from
 * exactly one place (app providers), never from features/pages.
 */
export function ThemeProvider({ children }: { children: ReactNode }) {
  return <>{children}</>
}
