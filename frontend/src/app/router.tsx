import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import { ForbiddenPage } from '@/pages/forbidden/ForbiddenPage'
import { HomePage } from '@/pages/home/HomePage'
import { AppShell } from '@/pages/layout/AppShell'
import { LoginPage } from '@/pages/login/LoginPage'
import { NotFoundPage } from '@/pages/not-found/NotFoundPage'
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
    children: [{ index: true, element: <HomePage /> }],
  },
  { path: '*', element: <NotFoundPage /> },
])

export function AppRouter() {
  return <RouterProvider router={router} />
}
