<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

final readonly class OrganizationSettings
{
    public const DEFAULT_ENCODING = 'auto';
    public const DEFAULT_MAX_FILE_SIZE_BYTES = 10_485_760; // 10 MiB

    public function __construct(
        public int $organizationId,
        public string $defaultEncoding = self::DEFAULT_ENCODING,
        public int $maxFileSizeBytes = self::DEFAULT_MAX_FILE_SIZE_BYTES,
        public ?string $clearBearerToken = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function defaultsFor(int $organizationId): self
    {
        return new self(organizationId: $organizationId);
    }
}
