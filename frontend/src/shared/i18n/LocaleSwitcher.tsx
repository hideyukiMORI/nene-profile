import { LOCALES } from './locales'
import { useTranslation } from './use-translation'

/**
 * Language switcher. Renders one button per supported locale (ja / en) and
 * switches the active catalog on click — instantly, with localStorage
 * persistence handled by the provider.
 *
 * All visible text comes from the catalog via `t()`; nothing is hardcoded.
 */
export function LocaleSwitcher() {
  const { locale, setLocale, t } = useTranslation()

  return (
    <div className="seg" role="group" aria-label={t('common.locale.label')}>
      {LOCALES.map((meta) => {
        const active = meta.id === locale
        return (
          <button
            key={meta.id}
            type="button"
            aria-pressed={active}
            onClick={() => {
              setLocale(meta.id)
            }}
          >
            {t(meta.labelKey)}
          </button>
        )
      })}
    </div>
  )
}
