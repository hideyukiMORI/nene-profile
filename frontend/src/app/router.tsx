import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import { AccountPage } from '@/pages/account/AccountPage'
import { ForbiddenPage } from '@/pages/forbidden/ForbiddenPage'
import { HomePage } from '@/pages/home/HomePage'
import { AppShell } from '@/pages/layout/AppShell'
import { LoginPage } from '@/pages/login/LoginPage'
import { NotFoundPage } from '@/pages/not-found/NotFoundPage'
import { AuditLogsPage } from '@/pages/audit-logs/AuditLogsPage'
import { ImportJobsPage } from '@/pages/import-jobs/ImportJobsPage'
import { MappingPresetsPage } from '@/pages/mapping-presets/MappingPresetsPage'
import { OrganizationsPage } from '@/pages/organizations/OrganizationsPage'
import { SettingsPage } from '@/pages/settings/SettingsPage'
import { UsersPage } from '@/pages/users/UsersPage'
import { RequireAuth } from '@/shared/auth/RequireAuth'

function AdminShell() {
  return (
    <RequireAuth>
      <AppShell />
    </RequireAuth>
  )
}

const router = createBrowserRouter(
  [
    { path: '/login', element: <LoginPage /> },
    { path: '/forbidden', element: <ForbiddenPage /> },
    {
      path: '/',
      element: <AdminShell />,
      children: [
        { index: true, element: <HomePage /> },
        { path: 'organizations', element: <OrganizationsPage /> },
        { path: 'users', element: <UsersPage /> },
        { path: 'mapping-presets', element: <MappingPresetsPage /> },
        { path: 'import-jobs', element: <ImportJobsPage /> },
        { path: 'settings', element: <SettingsPage /> },
        { path: 'audit-logs', element: <AuditLogsPage /> },
        { path: 'account', element: <AccountPage /> },
      ],
    },
    { path: '*', element: <NotFoundPage /> },
  ],
  {
    // Tier A same-origin: the production bundle is served from /admin/, so the
    // router must resolve routes against that base. In dev/test BASE_URL is '/'.
    basename: import.meta.env.BASE_URL.replace(/\/$/, '') || '/',
  },
)

export function AppRouter() {
  return <RouterProvider router={router} />
}
