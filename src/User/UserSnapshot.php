<?php

declare(strict_types=1);

namespace NeneProfile\User;

/**
 * Produces a sanitized representation of a User for API responses and audit
 * snapshots. The `password_hash` is NEVER included (ADR 0005).
 */
final class UserSnapshot
{
    /** @return array<string, mixed> */
    public static function toArray(User $user): array
    {
        return [
            'id'              => $user->id,
            'email'           => $user->email,
            'role'            => $user->role,
            'organization_id' => $user->organizationId,
            'status'          => $user->status,
            'created_at'      => $user->createdAt,
            'updated_at'      => $user->updatedAt,
        ];
    }
}
