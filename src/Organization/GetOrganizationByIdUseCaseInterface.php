<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

interface GetOrganizationByIdUseCaseInterface
{
    public function execute(GetOrganizationByIdInput $input): GetOrganizationByIdOutput;
}
