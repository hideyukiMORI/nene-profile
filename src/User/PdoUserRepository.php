<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Database\DatabaseQueryExecutorInterface;

final readonly class PdoUserRepository implements UserRepositoryInterface
{
    private const SELECT_COLUMNS = '
        id, email, password_hash, role, organization_id, status,
        UNIX_TIMESTAMP(created_at) AS created_at,
        UNIX_TIMESTAMP(updated_at) AS updated_at
    ';

    public function __construct(
        private DatabaseQueryExecutorInterface $query,
    ) {
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::SELECT_COLUMNS . ' FROM users WHERE email = ?',
            [$email],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    public function findById(int $id): ?User
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::SELECT_COLUMNS . ' FROM users WHERE id = ?',
            [$id],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    public function findByIdInOrganization(int $id, int $organizationId): ?User
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::SELECT_COLUMNS . ' FROM users WHERE id = ? AND organization_id = ?',
            [$id, $organizationId],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    /** @return list<User> */
    public function findByOrganizationId(int $organizationId, int $limit, int $offset): array
    {
        $rows = $this->query->fetchAll(
            'SELECT ' . self::SELECT_COLUMNS . ' FROM users WHERE organization_id = ? ORDER BY id ASC LIMIT ? OFFSET ?',
            [$organizationId, $limit, $offset],
        );

        return array_map($this->mapRow(...), $rows);
    }

    public function countByOrganizationId(int $organizationId): int
    {
        $row = $this->query->fetchOne(
            'SELECT COUNT(*) AS cnt FROM users WHERE organization_id = ?',
            [$organizationId],
        );

        return (int) ($row['cnt'] ?? 0);
    }

    public function emailExists(string $email): bool
    {
        return $this->query->fetchOne('SELECT 1 FROM users WHERE email = ?', [$email]) !== null;
    }

    public function save(User $user): int
    {
        $now = date('Y-m-d H:i:s');
        $this->query->execute(
            'INSERT INTO users (email, password_hash, role, organization_id, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $user->email,
                $user->passwordHash,
                $user->role,
                $user->organizationId,
                $user->status,
                $now,
                $now,
            ],
        );

        return $this->query->lastInsertId();
    }

    public function updateRole(int $id, string $role): void
    {
        $this->query->execute(
            'UPDATE users SET role = ?, updated_at = ? WHERE id = ?',
            [$role, date('Y-m-d H:i:s'), $id],
        );
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->query->execute(
            'UPDATE users SET status = ?, updated_at = ? WHERE id = ?',
            [$status, date('Y-m-d H:i:s'), $id],
        );
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $this->query->execute(
            'UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?',
            [$passwordHash, date('Y-m-d H:i:s'), $id],
        );
    }

    public function delete(int $id): void
    {
        $this->query->execute('DELETE FROM users WHERE id = ?', [$id]);
    }

    public function count(): int
    {
        $row = $this->query->fetchOne('SELECT COUNT(*) AS cnt FROM users', []);

        return (int) ($row['cnt'] ?? 0);
    }

    /** @param array<string, mixed> $row */
    private function mapRow(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            email: (string) $row['email'],
            passwordHash: (string) $row['password_hash'],
            role: (string) $row['role'],
            organizationId: isset($row['organization_id']) ? (int) $row['organization_id'] : null,
            status: (string) ($row['status'] ?? 'active'),
            createdAt: isset($row['created_at']) ? (int) $row['created_at'] : null,
            updatedAt: isset($row['updated_at']) ? (int) $row['updated_at'] : null,
        );
    }
}
