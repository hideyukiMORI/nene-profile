<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

interface ListAuditLogsUseCaseInterface
{
    public function execute(ListAuditLogsInput $input): ListAuditLogsOutput;
}
