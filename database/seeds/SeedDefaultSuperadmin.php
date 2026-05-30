<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seeds the default superadmin user and a default organization.
 *
 * Credentials are read from environment variables; defaults are for local development only.
 * Never deploy with default credentials in production.
 */
final class SeedDefaultSuperadmin extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Default organization (for single-org mode)
        $orgSlug = (string) (getenv('ORG_SLUG') ?: 'default');
        $existing = $this->fetchRow(
            'SELECT id FROM organizations WHERE slug = ?',
            [$orgSlug],
        );

        if ($existing === false || $existing === null) {
            $this->execute(
                'INSERT INTO organizations (name, slug, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?)',
                ['Default Organization', $orgSlug, 1, $now, $now],
            );
        }

        // Default superadmin
        $superadminEmail = (string) (getenv('SUPERADMIN_EMAIL') ?: 'admin@nene-profile.local');
        $superadminPassword = (string) (getenv('SUPERADMIN_PASSWORD') ?: 'changeme');

        $existingUser = $this->fetchRow(
            'SELECT id FROM users WHERE email = ?',
            [$superadminEmail],
        );

        if ($existingUser === false || $existingUser === null) {
            $this->execute(
                'INSERT INTO users (email, password_hash, role, organization_id, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $superadminEmail,
                    password_hash($superadminPassword, PASSWORD_BCRYPT),
                    'superadmin',
                    null,
                    'active',
                    $now,
                    $now,
                ],
            );
        }
    }
}
