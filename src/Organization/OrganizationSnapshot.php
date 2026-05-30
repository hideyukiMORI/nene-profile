<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

/**
 * Produces a sanitized audit snapshot of an Organization.
 * All fields are safe to record — no secrets exist on this entity.
 */
final class OrganizationSnapshot
{
    /** @return array<string, mixed> */
    public static function toArray(Organization $org): array
    {
        return [
            'id'            => $org->id,
            'name'          => $org->name,
            'slug'          => $org->slug,
            'is_active'     => $org->isActive,
            'custom_domain' => $org->customDomain,
            'created_at'    => $org->createdAt,
            'updated_at'    => $org->updatedAt,
        ];
    }
}
