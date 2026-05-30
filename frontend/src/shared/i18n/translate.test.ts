import { describe, expect, it } from 'vitest'
import { jaMessages } from './messages/ja'
import { translate, type MessageCatalog } from './translate'

describe('translate', () => {
  it('returns the value from the active catalog', () => {
    const catalog: MessageCatalog = { 'common.actions.save': 'SAVE' }
    expect(translate(catalog, 'common.actions.save')).toBe('SAVE')
  })

  it('falls back to the ja catalog for a key missing in the active catalog', () => {
    const catalog: MessageCatalog = {}
    expect(translate(catalog, 'common.actions.save')).toBe(jaMessages['common.actions.save'])
  })

  it('interpolates a single placeholder', () => {
    const catalog: MessageCatalog = { 'admin.account.signedInAs': 'Signed in as {{email}}' }
    expect(translate(catalog, 'admin.account.signedInAs', { email: 'a@b.com' })).toBe(
      'Signed in as a@b.com',
    )
  })

  it('interpolates multiple placeholders', () => {
    const catalog: MessageCatalog = {
      'common.pagination.summary': '{{from}}-{{to}} of {{total}}',
    }
    expect(translate(catalog, 'common.pagination.summary', { from: 1, to: 20, total: 100 })).toBe(
      '1-20 of 100',
    )
  })

  it('leaves an unmatched placeholder untouched', () => {
    const catalog: MessageCatalog = { 'admin.account.signedInAs': '{{email}} / {{missing}}' }
    expect(translate(catalog, 'admin.account.signedInAs', { email: 'x' })).toBe('x / {{missing}}')
  })

  it('coerces numeric params to strings', () => {
    const catalog: MessageCatalog = { 'admin.importJobs.detail.summary': '{{rowCount}} rows' }
    expect(translate(catalog, 'admin.importJobs.detail.summary', { rowCount: 42 })).toBe('42 rows')
  })
})
