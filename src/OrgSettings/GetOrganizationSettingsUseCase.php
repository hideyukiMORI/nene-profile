<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

final readonly class GetOrganizationSettingsUseCase implements GetOrganizationSettingsUseCaseInterface
{
    public function __construct(
        private OrganizationSettingsRepositoryInterface $repository,
    ) {
    }

    public function execute(int $organizationId): OrganizationSettings
    {
        return $this->repository->findByOrganizationId($organizationId)
            ?? OrganizationSettings::defaultsFor($organizationId);
    }
}
