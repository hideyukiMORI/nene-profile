<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

final readonly class ListAuditLogsOutput
{
    /**
     * @param list<AuditLog> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $limit,
        public int $offset,
    ) {
    }
}
