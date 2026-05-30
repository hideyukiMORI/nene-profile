import { describe, expect, it } from 'vitest'
import { DEFAULT_LOCALE, LOCALES, resolveLocale } from './locales'

describe('resolveLocale', () => {
  it('defaults to ja for null / undefined', () => {
    expect(resolveLocale(null)).toBe('ja')
    expect(resolveLocale(undefined)).toBe('ja')
  })

  it('maps en-prefixed inputs to en', () => {
    expect(resolveLocale('en')).toBe('en')
    expect(resolveLocale('en-US')).toBe('en')
    expect(resolveLocale('EN')).toBe('en')
  })

  it('maps everything else to ja', () => {
    expect(resolveLocale('ja')).toBe('ja')
    expect(resolveLocale('ja-JP')).toBe('ja')
    expect(resolveLocale('fr')).toBe('ja')
    expect(resolveLocale('zh-CN')).toBe('ja')
  })
})

describe('LOCALES', () => {
  it('exposes exactly ja and en in order', () => {
    expect(LOCALES.map((l) => l.id)).toEqual(['ja', 'en'])
  })

  it('uses ja as the default locale (ADR 0011)', () => {
    expect(DEFAULT_LOCALE).toBe('ja')
  })
})
