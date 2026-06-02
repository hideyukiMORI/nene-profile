<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class GetImportJobInput
{
    public function __construct(
        public int $id,
        public int $organizationId,
    ) {
    }
}
