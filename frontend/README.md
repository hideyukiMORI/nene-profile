# NeNe Profile Frontend

React + TypeScript admin UI for NeNe Profile. Phase 2.

This scaffold currently contains the **i18n message catalog foundation** — the
single place where every user-facing string lives, enabling smooth ja/en
language switching (ADR 0011).

## i18n — message catalogs

All screen text is managed as catalog files. Nothing is hardcoded in components.

```
src/shared/i18n/
  messages/
    ja.ts            # authoritative catalog (every key defined here first)
    en.ts            # English Partial — missing keys fall back to ja at runtime
    catalog.test.ts  # parity guard: en keys ⊆ ja keys, no empties, matching placeholders
  translate.ts       # pure translate(): catalog resolve + ja fallback + {{param}} interpolation
  locales.ts         # SupportedLocale, DEFAULT_LOCALE, resolveLocale, LOCALES
  context.ts         # I18nContextValue type
  i18n-context.tsx   # I18nProvider — localStorage persistence + navigator detection
  use-translation.ts # useTranslation() hook
  LocaleSwitcher.tsx # ja/en switch button group
  index.ts           # public barrel
```

### Usage

```tsx
import { I18nProvider, useTranslation, LocaleSwitcher } from '@/shared/i18n'

// once near the root
<I18nProvider>
  <App />
</I18nProvider>

// in any component
const { t, locale, setLocale } = useTranslation()
<h1>{t('admin.organizations.title')}</h1>
<p>{t('admin.account.signedInAs', { email })}</p>

// language switcher anywhere
<LocaleSwitcher />
```

### Adding a string

1. Add the key to `messages/ja.ts` (authoritative).
2. Add the English translation to `messages/en.ts`.
3. Reference it via `t('your.key')` — never hardcode the literal.
4. `npm run test` enforces ja/en parity.

### Key naming

`{area}.{feature}.{element}` — e.g. `admin.importJobs.status.completed`,
`common.actions.save`.

## Commands

```bash
npm install
npm run type-check   # tsc --noEmit (strict)
npm run test         # vitest run
npm run check        # type-check + test
```
