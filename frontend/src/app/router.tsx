import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import { ForbiddenPage } from '@/pages/forbidden/ForbiddenPage'
import { HomePage } from '@/pages/home/HomePage'
import { AppShell } from '@/pages/layout/AppShell'
import { LoginPage } from '@/pages/login/LoginPage'
import { NotFoundPage } from '@/pages/not-found/NotFoundPage'
import { ImportJobsPage } from '@/pages/import-jobs/ImportJobsPage'
import { MappingPresetsPage } from '@/pages/mapping-presets/MappingPresetsPage'
import { OrganizationsPage } from '@/pages/organizations/OrganizationsPage'
import { UsersPage } from '@/pages/users/UsersPage'
import { RequireAuth } from '@/shared/auth/RequireAuth'

function AdminShell() {
  return (
    <RequireAuth>
      <AppShell />
    </RequireAuth>
  )
}

const router = createBrowserRouter([
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
    ],
  },
  { path: '*', element: <NotFoundPage /> },
])

export function AppRouter() {
  return <RouterProvider router={router} />
}
