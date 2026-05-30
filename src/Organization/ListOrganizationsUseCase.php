<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class ListOrganizationsUseCase implements ListOrganizationsUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
    ) {
    }

    public function execute(ListOrganizationsInput $input): ListOrganizationsOutput
    {
        $orgs = $this->organizations->findAll($input->limit, $input->offset);
        $total = $this->organizations->count();

        $items = array_map(
            static fn (Organization $org) => new ListOrganizationItem(
                id: $org->id ?? 0,
                name: $org->name,
                slug: $org->slug,
                isActive: $org->isActive,
                customDomain: $org->customDomain,
                createdAt: (string) $org->createdAt,
                updatedAt: (string) $org->updatedAt,
            ),
            $orgs,
        );

        return new ListOrganizationsOutput(
            items: $items,
            total: $total,
            limit: $input->limit,
            offset: $input->offset,
        );
    }
}
