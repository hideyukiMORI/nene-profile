<?php

declare(strict_types=1);

namespace NeneProfile\Support;

/**
 * Reads an environment variable from any of the sources a deployment might use.
 *
 * NENE2 loads `.env` via `Dotenv::createImmutable()->safeLoad()`, which populates
 * `$_ENV` / `$_SERVER` but NOT `getenv()`. Docker Compose, by contrast, provides
 * real process environment visible to `getenv()`. Reading all three makes
 * Profile-specific runtime config (tenant resolution, storage path) work
 * identically on Tier A shared hosting (.env) and Tier B Docker.
 */
final class Env
{
    public static function get(string $key, string $default = ''): string
    {
        if (isset($_ENV[$key]) && is_scalar($_ENV[$key])) {
            return (string) $_ENV[$key];
        }

        if (isset($_SERVER[$key]) && is_scalar($_SERVER[$key])) {
            return (string) $_SERVER[$key];
        }

        $value = getenv($key);

        return $value !== false ? $value : $default;
    }
}
