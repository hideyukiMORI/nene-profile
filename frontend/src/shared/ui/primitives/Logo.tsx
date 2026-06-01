export type LogoVariant = 'navy' | 'light'

export interface LogoProps {
  /** `navy` = dark mark for light backgrounds; `light` = white mark for dark/navy backgrounds. */
  variant?: LogoVariant
  /** Pixel size of the square mark. */
  size?: number
}

const palette: Record<LogoVariant, { fill: string; stroke: string }> = {
  navy: { fill: '#142031', stroke: '#3a5a8c' },
  light: { fill: '#ffffff', stroke: '#88a6cf' },
}

/**
 * NeNe Profile brand mark — a filled play triangle followed by an open chevron,
 * evoking forward data flow / normalization. Single source of truth for the
 * logo (sidebar, auth, favicon all use this same geometry).
 */
export function Logo({ variant = 'navy', size = 34 }: LogoProps) {
  const { fill, stroke } = palette[variant]
  return (
    <svg
      viewBox="11 11 26 26"
      width={size}
      height={size}
      role="img"
      aria-label="NeNe Profile"
      style={{ display: 'block' }}
    >
      <path d="M13.5 13L24 24L13.5 35Z" fill={fill} />
      <path
        d="M26 13.5L35.5 24L26 34.5"
        fill="none"
        stroke={stroke}
        strokeWidth="5"
        strokeLinejoin="miter"
        strokeLinecap="butt"
      />
    </svg>
  )
}
