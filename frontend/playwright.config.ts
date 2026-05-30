import { defineConfig, devices } from '@playwright/test'

const PORT = 5173
const baseURL = `http://localhost:${String(PORT)}`

/**
 * Browser E2E for the admin SPA. The backend is mocked at the network layer
 * (page.route) so flows are deterministic and need no PHP server. Each test logs
 * in through the UI (in-memory auth) and then navigates via in-app links.
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env['CI'],
  retries: process.env['CI'] ? 1 : 0,
  reporter: process.env['CI'] ? [['github'], ['list']] : [['list']],
  use: {
    baseURL,
    // ja is the authoritative catalog; pin the browser locale so the i18n layer
    // resolves to Japanese deterministically (matches the unit-test setup).
    locale: 'ja-JP',
    trace: 'on-first-retry',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
  webServer: {
    command: `npm run dev -- --port ${String(PORT)} --strictPort`,
    url: baseURL,
    reuseExistingServer: !process.env['CI'],
    timeout: 120_000,
  },
})
