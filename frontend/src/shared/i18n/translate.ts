import { jaMessages } from './messages/ja'

/** All valid message keys, derived from the authoritative ja catalog. */
export type MessageKey = keyof typeof jaMessages

/**
 * A catalog for a non-authoritative locale. Partial — missing keys fall back to
 * the ja catalog at runtime, so a locale never blocks on an untranslated key.
 */
export type MessageCatalog = Partial<Record<MessageKey, string>>

/** Interpolation parameters for `{{name}}` placeholders. */
export type TranslateParams = Record<string, string | number>

/**
 * Pure translation. Resolves the active catalog, falls back to the authoritative
 * ja catalog for missing keys, then interpolates `{{name}}` placeholders.
 *
 * This function has no side effects and no React dependency — it is the unit
 * that catalog tests exercise directly.
 */
export function translate(
  catalog: MessageCatalog,
  key: MessageKey,
  params?: TranslateParams,
): string {
  const template = catalog[key] ?? jaMessages[key]

  if (params === undefined) return template

  return template.replace(/\{\{(\w+)\}\}/g, (_match, name: string) => {
    const value = params[name]
    return value === undefined ? `{{${name}}}` : String(value)
  })
}
