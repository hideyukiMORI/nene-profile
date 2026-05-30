import { Component, type ErrorInfo, type ReactNode } from 'react'

interface Props {
  children: ReactNode
}

interface State {
  hasError: boolean
}

/**
 * Last-resort render error boundary. Shows a safe, static fallback (no i18n
 * dependency, since the i18n provider may be what failed) and logs in dev only.
 */
export class RootErrorBoundary extends Component<Props, State> {
  override state: State = { hasError: false }

  static getDerivedStateFromError(): State {
    return { hasError: true }
  }

  override componentDidCatch(error: Error, info: ErrorInfo): void {
    if (import.meta.env.DEV) {
      console.error('[RootErrorBoundary]', error, info)
    }
  }

  override render(): ReactNode {
    if (this.state.hasError) {
      return (
        <div className="flex min-h-full items-center justify-center p-inline-lg">
          <p className="text-body text-text-muted">Something went wrong.</p>
        </div>
      )
    }

    return this.props.children
  }
}
