<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class ListOrganizationsInput
{
    public function __construct(
        public int $limit = 50,
        public int $offset = 0,
    ) {
    }
}
