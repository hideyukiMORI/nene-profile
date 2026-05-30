import { Link, Outlet, useNavigate } from 'react-router-dom'
import { authStore } from '@/entities/auth'
import { useTranslation } from '@/shared/i18n'
import { LocaleSwitcher } from '@/shared/i18n'
import { Button, Text } from '@/shared/ui'

const NAV_ITEMS = [
  { to: '/mapping-presets', key: 'admin.nav.mappingPresets' },
  { to: '/import-jobs', key: 'admin.nav.importJobs' },
  { to: '/audit-logs', key: 'admin.nav.auditLogs' },
] as const

/** Authenticated layout: top bar + nav + routed content. */
export function AppShell() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const session = authStore.getSession()

  const signOut = (): void => {
    authStore.clearSession()
    void navigate('/login', { replace: true })
  }

  return (
    <div className="min-h-full">
      <header className="flex items-center justify-between border-b border-border bg-surface px-inline-lg py-stack-sm">
        <Link to="/">
          <Text as="span" variant="heading">
            {t('common.appName')}
          </Text>
        </Link>
        <nav className="flex items-center gap-inline-md">
          {NAV_ITEMS.map((item) => (
            <Link key={item.to} to={item.to}>
              <Text as="span" variant="body" tone="muted">
                {t(item.key)}
              </Text>
            </Link>
          ))}
          <LocaleSwitcher />
          {session !== null ? (
            <Text as="span" variant="caption" tone="muted">
              {t('admin.account.signedInAs', { email: session.email })}
            </Text>
          ) : null}
          <Button variant="ghost" size="sm" onClick={signOut}>
            {t('common.actions.signOut')}
          </Button>
        </nav>
      </header>
      <main className="p-inline-lg">
        <Outlet />
      </main>
    </div>
  )
}
