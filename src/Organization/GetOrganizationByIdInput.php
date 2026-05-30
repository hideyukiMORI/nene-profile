<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class GetOrganizationByIdInput
{
    public function __construct(
        public int $id,
    ) {
    }
}
