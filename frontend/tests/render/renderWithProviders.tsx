import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, type RenderResult } from '@testing-library/react'
import { type ReactElement, type ReactNode } from 'react'
import { MemoryRouter } from 'react-router-dom'
import { I18nProvider } from '@/shared/i18n'

/** A QueryClient with retries off, for deterministic tests. */
export function createTestQueryClient(): QueryClient {
  return new QueryClient({
    defaultOptions: {
      queries: { retry: false },
      mutations: { retry: false },
    },
  })
}

export interface RenderOptions {
  route?: string
}

/**
 * Renders a UI subtree wrapped in the providers a feature depends on
 * (QueryClient + Router + i18n). Use in feature/hook tests.
 */
export function renderWithProviders(ui: ReactElement, options: RenderOptions = {}): RenderResult {
  const queryClient = createTestQueryClient()

  function Wrapper({ children }: { children: ReactNode }) {
    return (
      <QueryClientProvider client={queryClient}>
        <I18nProvider>
          <MemoryRouter initialEntries={[options.route ?? '/']}>{children}</MemoryRouter>
        </I18nProvider>
      </QueryClientProvider>
    )
  }

  return render(ui, { wrapper: Wrapper })
}
