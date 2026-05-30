<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

interface ListOrganizationsUseCaseInterface
{
    public function execute(ListOrganizationsInput $input): ListOrganizationsOutput;
}
