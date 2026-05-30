<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

final readonly class ListAuditLogsInput
{
    public function __construct(
        public ?int $organizationId,
        public int $limit = 20,
        public int $offset = 0,
    ) {
    }
}
