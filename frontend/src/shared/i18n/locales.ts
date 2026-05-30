import { jaMessages } from './messages/ja'
import type { MessageCatalog } from './translate'

/**
 * Supported locales. Bound to ja + en only by ADR 0011 — no other locales are
 * added without superseding that ADR.
 */
export type SupportedLocale = 'ja' | 'en'

/** ja is the primary/authoritative locale (ADR 0011). */
export const DEFAULT_LOCALE: SupportedLocale = 'ja'

interface LocaleMeta {
  readonly id: SupportedLocale
  readonly labelKey: 'common.locale.ja' | 'common.locale.en'
}

/** Locale options for the language switcher, in display order. */
export const LOCALES: readonly LocaleMeta[] = [
  { id: 'ja', labelKey: 'common.locale.ja' },
  { id: 'en', labelKey: 'common.locale.en' },
]

/**
 * Resolve any input (navigator.language, stored value) to a supported locale.
 * Anything starting with "en" maps to en; everything else falls back to ja.
 */
export function resolveLocale(input: string | null | undefined): SupportedLocale {
  if (input === null || input === undefined) return DEFAULT_LOCALE
  return input.toLowerCase().startsWith('en') ? 'en' : 'ja'
}

/** ja is the authoritative full catalog; en is a Partial loaded by the provider. */
export const jaCatalog: MessageCatalog = jaMessages
