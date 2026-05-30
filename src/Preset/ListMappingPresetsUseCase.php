<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class ListMappingPresetsUseCase implements ListMappingPresetsUseCaseInterface
{
    public function __construct(
        private MappingPresetRepositoryInterface $presets,
    ) {
    }

    public function execute(ListMappingPresetsInput $input): ListMappingPresetsOutput
    {
        return new ListMappingPresetsOutput(
            items: $this->presets->findByOrganizationId($input->organizationId, $input->limit, $input->offset),
            total: $this->presets->countByOrganizationId($input->organizationId),
            limit: $input->limit,
            offset: $input->offset,
        );
    }
}
