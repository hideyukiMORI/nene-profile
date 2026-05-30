import { act } from 'react'
import { createRoot, type Root } from 'react-dom/client'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { I18nProvider } from './i18n-context'
import { LocaleSwitcher } from './LocaleSwitcher'
import { useTranslation } from './use-translation'

let container: HTMLDivElement
let root: Root

beforeEach(() => {
  localStorage.clear()
  // Make detection deterministic: no stored locale + ja navigator → ja default.
  vi.spyOn(navigator, 'language', 'get').mockReturnValue('ja-JP')
  container = document.createElement('div')
  document.body.appendChild(container)
  root = createRoot(container)
})

afterEach(() => {
  act(() => root.unmount())
  container.remove()
  vi.restoreAllMocks()
})

function TitleProbe() {
  const { t } = useTranslation()
  return <h1>{t('admin.organizations.title')}</h1>
}

describe('I18nProvider + LocaleSwitcher', () => {
  it('renders ja by default', () => {
    act(() => {
      root.render(
        <I18nProvider>
          <TitleProbe />
        </I18nProvider>,
      )
    })
    expect(container.querySelector('h1')?.textContent).toBe('組織一覧')
  })

  it('switches to en when the en button is clicked, and persists it', () => {
    act(() => {
      root.render(
        <I18nProvider>
          <TitleProbe />
          <LocaleSwitcher />
        </I18nProvider>,
      )
    })

    const enButton = [...container.querySelectorAll('button')].find(
      (b) => b.textContent === 'English',
    )
    expect(enButton).toBeDefined()

    act(() => {
      enButton?.dispatchEvent(new MouseEvent('click', { bubbles: true }))
    })

    expect(container.querySelector('h1')?.textContent).toBe('Organizations')
    expect(localStorage.getItem('nene-profile-locale')).toBe('en')
  })

  it('restores the persisted locale on mount', () => {
    localStorage.setItem('nene-profile-locale', 'en')
    act(() => {
      root.render(
        <I18nProvider>
          <TitleProbe />
        </I18nProvider>,
      )
    })
    expect(container.querySelector('h1')?.textContent).toBe('Organizations')
  })
})
