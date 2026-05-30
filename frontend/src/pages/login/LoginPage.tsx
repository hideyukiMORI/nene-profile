import { LoginForm } from '@/features/login'

export function LoginPage() {
  return (
    <main className="flex min-h-full items-center justify-center p-inline-lg">
      <div className="w-full max-w-sm rounded-md border border-border bg-surface p-inline-lg">
        <LoginForm />
      </div>
    </main>
  )
}
