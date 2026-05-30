<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class CreateOrganizationUseCase implements CreateOrganizationUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
    ) {
    }

    public function execute(CreateOrganizationInput $input): CreateOrganizationOutput
    {
        if ($this->organizations->findBySlug($input->slug) !== null) {
            throw new OrganizationSlugConflictException($input->slug);
        }

        $id = $this->organizations->save(new Organization(
            name: $input->name,
            slug: $input->slug,
            isActive: $input->isActive,
            customDomain: $input->customDomain,
        ));

        $org = $this->organizations->findById($id);
        assert($org !== null);

        return new CreateOrganizationOutput(
            id: $id,
            name: $org->name,
            slug: $org->slug,
            isActive: $org->isActive,
            customDomain: $org->customDomain,
            createdAt: (string) $org->createdAt,
            updatedAt: (string) $org->updatedAt,
        );
    }
}
