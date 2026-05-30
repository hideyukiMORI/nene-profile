<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class GetOrganizationByIdUseCase implements GetOrganizationByIdUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
    ) {
    }

    public function execute(GetOrganizationByIdInput $input): GetOrganizationByIdOutput
    {
        $org = $this->organizations->findById($input->id);

        if ($org === null) {
            throw new OrganizationNotFoundException($input->id);
        }

        return new GetOrganizationByIdOutput(
            id: $org->id ?? 0,
            name: $org->name,
            slug: $org->slug,
            isActive: $org->isActive,
            customDomain: $org->customDomain,
            createdAt: (string) $org->createdAt,
            updatedAt: (string) $org->updatedAt,
        );
    }
}
