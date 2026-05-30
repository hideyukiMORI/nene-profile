<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

interface DeleteOrganizationUseCaseInterface
{
    public function execute(?int $actorUserId, DeleteOrganizationInput $input): void;
}
