import { Link, Outlet, useNavigate } from 'react-router-dom'
import { authStore, hasCapability, type Capability } from '@/entities/auth'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { LocaleSwitcher } from '@/shared/i18n'
import { Button, Text } from '@/shared/ui'

interface NavItem {
  to: string
  key: MessageKey
  /** When set, the link is shown only if the session role has the capability. */
  capability?: Capability
}

const NAV_ITEMS: readonly NavItem[] = [
  { to: '/organizations', key: 'admin.nav.organizations', capability: 'manage_organizations' },
  { to: '/users', key: 'admin.nav.users', capability: 'manage_users' },
  { to: '/mapping-presets', key: 'admin.nav.mappingPresets' },
  { to: '/import-jobs', key: 'admin.nav.importJobs' },
  { to: '/audit-logs', key: 'admin.nav.auditLogs' },
]

/** Authenticated layout: top bar + nav + routed content. */
export function AppShell() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const session = authStore.getSession()
  const role = session?.role

  const navItems = NAV_ITEMS.filter(
    (item) =>
      item.capability === undefined || (role !== undefined && hasCapability(role, item.capability)),
  )

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
          {navItems.map((item) => (
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
