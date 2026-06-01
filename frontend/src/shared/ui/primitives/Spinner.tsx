export interface SpinnerProps {
  label?: string
}

/** Accessible loading indicator (design-system `.spinner`). */
export function Spinner({ label = 'Loading' }: SpinnerProps) {
  return <span role="status" aria-label={label} className="spinner" />
}
