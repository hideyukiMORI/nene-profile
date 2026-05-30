export interface SpinnerProps {
  label?: string
}

/** Accessible loading indicator. */
export function Spinner({ label = 'Loading' }: SpinnerProps) {
  return (
    <span
      role="status"
      aria-label={label}
      className="inline-block h-4 w-4 animate-spin rounded-full border-2 border-border border-t-accent"
    />
  )
}
