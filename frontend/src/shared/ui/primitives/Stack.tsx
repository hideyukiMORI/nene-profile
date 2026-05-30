import type { ReactNode } from 'react'

export type StackGap = 'xs' | 'sm' | 'md' | 'lg'

export interface StackProps {
  gap?: StackGap
  children: ReactNode
}

const gapClasses: Record<StackGap, string> = {
  xs: 'gap-stack-xs',
  sm: 'gap-stack-sm',
  md: 'gap-stack-md',
  lg: 'gap-stack-lg',
}

/** Vertical flex stack with theme-token spacing. */
export function Stack({ gap = 'md', children }: StackProps) {
  return <div className={`flex flex-col ${gapClasses[gap]}`}>{children}</div>
}
