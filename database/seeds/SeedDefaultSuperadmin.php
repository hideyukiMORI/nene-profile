<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seeds the default superadmin user and a default organization.
 *
 * Credentials are read from environment variables; defaults are for local development only.
 * Never deploy with default credentials in production.
 *
 * Uses PDO directly because Phinx 0.16.x AbstractSeed::execute() does not support
 * bound parameters — passing an array is silently ignored and MySQL rejects the raw '?'.
 */
final class SeedDefaultSuperadmin extends AbstractSeed
{
    public function run(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        $now = date('Y-m-d H:i:s');

        // Default organization (for single-org mode)
        $orgSlug = (string) (getenv('ORG_SLUG') ?: 'default');

        $stmt = $pdo->prepare('SELECT id FROM organizations WHERE slug = ?');
        $stmt->execute([$orgSlug]);
        $existing = $stmt->fetch();

        if ($existing === false || $existing === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO organizations (name, slug, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?)',
            );
            $stmt->execute(['Default Organization', $orgSlug, 1, $now, $now]);
        }

        // Default superadmin
        $superadminEmail    = (string) (getenv('SUPERADMIN_EMAIL') ?: 'admin@nene-profile.local');
        $superadminPassword = (string) (getenv('SUPERADMIN_PASSWORD') ?: 'changeme');

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$superadminEmail]);
        $existingUser = $stmt->fetch();

        if ($existingUser === false || $existingUser === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO users (email, password_hash, role, organization_id, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            );
            $stmt->execute([
                $superadminEmail,
                password_hash($superadminPassword, PASSWORD_BCRYPT),
                'superadmin',
                null,
                'active',
                $now,
                $now,
            ]);
        }
    }
}
