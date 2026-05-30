<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class DeleteOrganizationInput
{
    public function __construct(
        public int $id,
    ) {
    }
}
