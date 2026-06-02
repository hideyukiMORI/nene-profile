import { useEffect, useState } from 'react'
import { Link, NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
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

/** Quick destinations for the mobile bottom tab bar (≤600px). `match` decides
 * which tab is highlighted for the current route; `メニュー` opens the sheet. */
interface QuickTab {
  to: string
  key: MessageKey
  icon: IconName
  capability?: Capability
  match: (pathname: string) => boolean
}
const QUICK_TABS: readonly QuickTab[] = [
  { to: '/', key: 'admin.tab.home', icon: 'grid', match: (p) => p === '/' },
  {
    to: '/import-jobs',
    key: 'admin.tab.imports',
    icon: 'jobs',
    match: (p) => p.startsWith('/import-jobs') || p.startsWith('/mapping-presets'),
  },
  {
    to: '/organizations',
    key: 'admin.nav.organizations',
    icon: 'building',
    capability: 'manage_organizations',
    match: (p) => p.startsWith('/organizations'),
  },
  {
    to: '/users',
    key: 'admin.nav.users',
    icon: 'users',
    capability: 'manage_users',
    match: (p) => p.startsWith('/users'),
  },
]

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
  const [sheetOpen, setSheetOpen] = useState(false)

  // Off-canvas sidebar (tablet): the design-system stylesheet keys off `body.nav-open`.
  useEffect(() => {
    document.body.classList.toggle('nav-open', navOpen)
    return () => {
      document.body.classList.remove('nav-open')
    }
  }, [navOpen])

  // Mobile menu bottom-sheet (phone): keys off `body.navsheet-open`.
  useEffect(() => {
    document.body.classList.toggle('navsheet-open', sheetOpen)
    return () => {
      document.body.classList.remove('navsheet-open')
    }
  }, [sheetOpen])

  // Close the sheet on Escape.
  useEffect(() => {
    if (!sheetOpen) return
    const onKey = (event: KeyboardEvent): void => {
      if (event.key === 'Escape') setSheetOpen(false)
    }
    document.addEventListener('keydown', onKey)
    return () => {
      document.removeEventListener('keydown', onKey)
    }
  }, [sheetOpen])

  // Auto-close both overlays when crossing back to the desktop breakpoint.
  useEffect(() => {
    const onResize = (): void => {
      if (window.innerWidth > 900) setNavOpen(false)
      if (window.innerWidth > 600) setSheetOpen(false)
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

  const quickTabs = QUICK_TABS.filter(
    (tab) =>
      tab.capability === undefined || (role !== undefined && hasCapability(role, tab.capability)),
  )
  const quickActive = quickTabs.some((tab) => tab.match(location.pathname))
  const menuActive = sheetOpen || !quickActive

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

      {/* Mobile bottom tab bar — hidden by CSS until ≤600px. */}
      <nav className="tabbar" aria-label={t('admin.shell.menu')}>
        {quickTabs.map((tab) => {
          const active = tab.match(location.pathname)
          return (
            <Link
              key={tab.to}
              to={tab.to}
              className={`tabbar__item${active ? ' is-active' : ''}`}
              aria-current={active ? 'page' : undefined}
              onClick={() => {
                setSheetOpen(false)
              }}
            >
              <span className="tabbar__ic">
                <Icon name={tab.icon} />
              </span>
              <span className="tabbar__lb">{t(tab.key)}</span>
            </Link>
          )
        })}
        <button
          type="button"
          className={`tabbar__item${menuActive ? ' is-active' : ''}`}
          aria-label={t('admin.shell.menu')}
          aria-expanded={sheetOpen}
          onClick={() => {
            setSheetOpen((open) => !open)
          }}
        >
          <span className="tabbar__ic">
            <Icon name="menu" />
          </span>
          <span className="tabbar__lb">{t('admin.shell.menu')}</span>
        </button>
      </nav>

      {/* Mobile menu bottom-sheet (full grouped nav) — hidden by CSS until ≤600px. */}
      <div
        className={`navsheet-backdrop${sheetOpen ? ' open' : ''}`}
        aria-hidden="true"
        onClick={() => {
          setSheetOpen(false)
        }}
      />
      <div
        className={`navsheet${sheetOpen ? ' open' : ''}`}
        role="dialog"
        aria-modal="true"
        aria-label={t('admin.shell.menu')}
      >
        <div className="navsheet__handle" />
        <div className="navsheet__head">
          <div className="navsheet__brand">
            <div className="navsheet__mark">
              <Logo variant="navy" size={22} />
            </div>
            <div>
              <div className="navsheet__name">{t('admin.shell.brandName')}</div>
              <div className="navsheet__sub">{t('admin.shell.brandTagline')}</div>
            </div>
          </div>
          <button
            type="button"
            className="navsheet__close"
            aria-label={t('common.actions.close')}
            onClick={() => {
              setSheetOpen(false)
            }}
          >
            <Icon name="close" />
          </button>
        </div>
        <div className="navsheet__body">
          {visibleGroups.map((group) => (
            <div className="navsheet__group" key={group.key}>
              <div className="navsheet__glabel">{t(group.key)}</div>
              {group.items.map((item) => (
                <NavLink
                  key={item.to}
                  to={item.to}
                  end={item.to === '/'}
                  className={({ isActive }) => `navsheet__item${isActive ? ' is-active' : ''}`}
                  onClick={() => {
                    setSheetOpen(false)
                  }}
                >
                  <span className="navsheet__ic">
                    <Icon name={item.icon} />
                  </span>
                  <span className="navsheet__lb">{t(item.key)}</span>
                  <span className="navsheet__chev" aria-hidden="true">
                    <Icon name="chevron" />
                  </span>
                </NavLink>
              ))}
            </div>
          ))}
        </div>
        {session !== null ? (
          <div className="navsheet__foot">
            <div className="navsheet__avatar" aria-hidden="true">
              {initialsOf(session.email)}
            </div>
            <div className="navsheet__who">
              <b>{t(roleLabelKey[role ?? ''] ?? 'admin.nav.account')}</b>
              <span>{session.email}</span>
            </div>
          </div>
        ) : null}
      </div>
    </div>
  )
}
