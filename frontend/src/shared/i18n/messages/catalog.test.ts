import { describe, expect, it } from 'vitest'
import { enMessages } from './en'
import { jaMessages } from './ja'

/**
 * Catalog integrity guards. These tests are the safety net that makes language
 * switching reliable: they fail the build when ja and en drift apart.
 */
describe('message catalog integrity', () => {
  const jaKeys = Object.keys(jaMessages)
  const enKeys = Object.keys(enMessages)

  it('ja is the authoritative catalog and has no empty values', () => {
    for (const [key, value] of Object.entries(jaMessages)) {
      expect(value, `ja key "${key}" must not be empty`).not.toBe('')
    }
  })

  it('every en key exists in ja (no stale / typo keys)', () => {
    const jaKeySet = new Set(jaKeys)
    const orphans = enKeys.filter((key) => !jaKeySet.has(key))
    expect(orphans, `en has keys missing from ja: ${orphans.join(', ')}`).toEqual([])
  })

  it('no en value is empty', () => {
    for (const [key, value] of Object.entries(enMessages)) {
      expect(value, `en key "${key}" must not be empty`).not.toBe('')
    }
  })

  it('en provides a full translation (every ja key is translated)', () => {
    const enKeySet = new Set(enKeys)
    const untranslated = jaKeys.filter((key) => !enKeySet.has(key))
    // This is informational rather than fatal — missing keys fall back to ja.
    // Keep it green by translating every key; if intentionally deferring a key,
    // this assertion documents the gap.
    expect(untranslated, `en is missing translations for: ${untranslated.join(', ')}`).toEqual([])
  })

  it('interpolation placeholders match between ja and en for shared keys', () => {
    const placeholderPattern = /\{\{(\w+)\}\}/g
    const extract = (s: string): string[] =>
      [...s.matchAll(placeholderPattern)].map((m) => m[1] ?? '').sort()

    for (const key of enKeys) {
      const jaValue = jaMessages[key as keyof typeof jaMessages]
      const enValue = enMessages[key as keyof typeof enMessages]
      if (enValue === undefined) continue
      expect(extract(enValue), `placeholders differ for "${key}"`).toEqual(extract(jaValue))
    }
  })
})
