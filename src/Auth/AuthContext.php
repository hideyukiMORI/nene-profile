<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Reads the authenticated principal from verified token claims stored by
 * AdminApiAuthMiddleware on the request (`nene2.auth.claims`).
 *
 * The token is issued by LoginUseCase with claims:
 *  - `sub`    : user ID (integer)
 *  - `role`   : role string
 *  - `org_id` : organization ID (integer; null for superadmin)
 */
final class AuthContext
{
    private const CLAIMS_ATTRIBUTE = 'nene2.auth.claims';

    public static function userId(ServerRequestInterface $request): ?int
    {
        $value = self::claim($request, 'sub');

        return is_int($value) ? $value : null;
    }

    public static function role(ServerRequestInterface $request): ?Role
    {
        $value = self::claim($request, 'role');

        return is_string($value) ? Role::tryFrom($value) : null;
    }

    public static function organizationId(ServerRequestInterface $request): ?int
    {
        $value = self::claim($request, 'org_id');

        return is_int($value) ? $value : null;
    }

    private static function claim(ServerRequestInterface $request, string $key): mixed
    {
        $claims = $request->getAttribute(self::CLAIMS_ATTRIBUTE);

        return is_array($claims) ? ($claims[$key] ?? null) : null;
    }
}
