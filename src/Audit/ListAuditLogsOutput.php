<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use Nene2\Audit\AuditEvent;

final readonly class ListAuditLogsOutput
{
    /**
     * @param list<AuditEvent> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $limit,
        public int $offset,
    ) {
    }
}
