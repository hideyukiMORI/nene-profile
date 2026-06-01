import { useEffect, useState } from 'react'
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
import { authStore, hasCapability, type Capability } from '@/entities/auth'
import { LocaleSwitcher, useTranslation, type MessageKey } from '@/shared/i18n'
import { Icon, type IconName, Logo } from '@/shared/ui'

interface NavItem {
  to: string
  key: MessageKey
  icon: IconName
  /** When set, the link is shown only if the session role has the capability. */
  capability?: Capability
}
interface NavGroup {
  key: MessageKey
  items: readonly NavItem[]
}

const NAV_GROUPS: readonly NavGroup[] = [
  {
    key: 'admin.nav.group.overview',
    items: [{ to: '/', key: 'admin.nav.dashboard', icon: 'grid' }],
  },
  {
    key: 'admin.nav.group.masters',
    items: [
      {
        to: '/organizations',
        key: 'admin.nav.organizations',
        icon: 'building',
        capability: 'manage_organizations',
      },
      { to: '/users', key: 'admin.nav.users', icon: 'users', capability: 'manage_users' },
    ],
  },
  {
    key: 'admin.nav.group.import',
    items: [
      { to: '/mapping-presets', key: 'admin.nav.mappingPresets', icon: 'sliders' },
      { to: '/import-jobs', key: 'admin.nav.importJobs', icon: 'jobs' },
    ],
  },
  {
    key: 'admin.nav.group.system',
    items: [
      {
        to: '/settings',
        key: 'admin.nav.settings',
        icon: 'settings',
        capability: 'manage_organization_settings',
      },
      { to: '/audit-logs', key: 'admin.nav.auditLogs', icon: 'shield' },
      { to: '/account', key: 'admin.nav.account', icon: 'user' },
    ],
  },
]

const roleLabelKey: Record<string, MessageKey> = {
  superadmin: 'admin.users.role.superadmin',
  admin: 'admin.users.role.admin',
  member: 'admin.users.role.member',
  viewer: 'admin.users.role.viewer',
}

function initialsOf(email: string): string {
  const local = email.split('@')[0] ?? ''
  return (local.slice(0, 2) || 'NP').toUpperCase()
}

/** Authenticated layout: navy sidebar + topbar + routed content, responsive. */
export function AppShell() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const location = useLocation()
  const session = authStore.getSession()
  const role = session?.role
  const [navOpen, setNavOpen] = useState(false)

  // Off-canvas sidebar (mobile): the design-system stylesheet keys off `body.nav-open`.
  useEffect(() => {
    document.body.classList.toggle('nav-open', navOpen)
    return () => {
      document.body.classList.remove('nav-open')
    }
  }, [navOpen])

  // Auto-close the drawer when crossing back to the desktop breakpoint.
  useEffect(() => {
    const onResize = (): void => {
      if (window.innerWidth > 900) setNavOpen(false)
    }
    window.addEventListener('resize', onResize)
    return () => {
      window.removeEventListener('resize', onResize)
    }
  }, [])

  const visibleGroups = NAV_GROUPS.map((group) => ({
    key: group.key,
    items: group.items.filter(
      (item) =>
        item.capability === undefined ||
        (role !== undefined && hasCapability(role, item.capability)),
    ),
  })).filter((group) => group.items.length > 0)

  const activeGroup = NAV_GROUPS.find((group) =>
    group.items.some((item) => item.to === location.pathname),
  )
  const activeItem = activeGroup?.items.find((item) => item.to === location.pathname)

  const signOut = (): void => {
    authStore.clearSession()
    void navigate('/login', { replace: true })
  }

  return (
    <div className="app">
      <aside className="sidebar">
        <div className="brand">
          <div className="brand__mark">
            <Logo variant="light" size={34} />
          </div>
          <div>
            <div className="brand__name">{t('admin.shell.brandName')}</div>
            <div className="brand__sub">{t('admin.shell.brandTagline')}</div>
          </div>
        </div>
        <nav className="nav">
          {visibleGroups.map((group) => (
            <div className="nav__group" key={group.key}>
              <div className="nav__label">{t(group.key)}</div>
              {group.items.map((item) => (
                <NavLink
                  key={item.to}
                  to={item.to}
                  end={item.to === '/'}
                  className={({ isActive }) => `nav__item${isActive ? ' is-active' : ''}`}
                  onClick={() => {
                    setNavOpen(false)
                  }}
                >
                  <Icon name={item.icon} />
                  <span>{t(item.key)}</span>
                </NavLink>
              ))}
            </div>
          ))}
        </nav>
        {session !== null ? (
          <div className="sidebar__foot">
            <div className="avatar" aria-hidden="true">
              {initialsOf(session.email)}
            </div>
            <div className="who">
              <b>{t(roleLabelKey[role ?? ''] ?? 'admin.nav.account')}</b>
              <span>{session.email}</span>
            </div>
          </div>
        ) : null}
      </aside>

      <div className="main">
        <header className="topbar">
          <button
            type="button"
            className="navtoggle"
            aria-label={t('admin.shell.menu')}
            onClick={() => {
              setNavOpen((open) => !open)
            }}
          >
            <Icon name="menu" />
          </button>
          <div className="crumbs">
            {activeGroup !== undefined ? (
              <>
                <span>{t(activeGroup.key)}</span>
                <span className="sep" aria-hidden="true">
                  <Icon name="chevron" />
                </span>
              </>
            ) : null}
            <b>{activeItem !== undefined ? t(activeItem.key) : t('admin.dashboard.title')}</b>
          </div>
          <div className="spacer" />
          <LocaleSwitcher />
          <button
            type="button"
            className="iconbtn"
            aria-label={t('common.actions.signOut')}
            onClick={signOut}
          >
            <Icon name="logout" />
          </button>
        </header>
        <main className="content">
          <Outlet />
        </main>
      </div>

      <div
        className="nav-backdrop"
        aria-hidden="true"
        onClick={() => {
          setNavOpen(false)
        }}
      />
    </div>
  )
}
