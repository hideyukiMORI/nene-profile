import { useContext } from 'react'
import { I18nContext, type I18nContextValue } from './context'

/**
 * Access the active locale, the locale setter, and the `t()` translator.
 * Must be called within an `<I18nProvider>`.
 */
export function useTranslation(): I18nContextValue {
  const ctx = useContext(I18nContext)
  if (ctx === null) {
    throw new Error('useTranslation must be used within I18nProvider')
  }
  return ctx
}
