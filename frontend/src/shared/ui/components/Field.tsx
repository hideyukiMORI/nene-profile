import { useId, type ReactNode } from 'react'
import { Stack } from '@/shared/ui/primitives/Stack'
import { Text } from '@/shared/ui/primitives/Text'

export interface FieldProps {
  label: string
  error?: string
  /** Render-prop receiving the id to wire to the control (aria correctness). */
  children: (props: { id: string; invalid: boolean }) => ReactNode
}

/** Labelled form field with accessible error wiring (aria-describedby). */
export function Field({ label, error, children }: FieldProps) {
  const id = useId()
  const errorId = `${id}-error`
  const invalid = error !== undefined && error !== ''

  return (
    <Stack gap="xs">
      <Text as="label" variant="caption" tone="muted" htmlFor={id}>
        {label}
      </Text>
      {children({ id, invalid })}
      {invalid ? (
        <span id={errorId} role="alert" className="text-caption text-danger">
          {error}
        </span>
      ) : null}
    </Stack>
  )
}
