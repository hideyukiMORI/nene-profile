<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class GetMappingPresetByIdUseCase implements GetMappingPresetByIdUseCaseInterface
{
    public function __construct(
        private MappingPresetRepositoryInterface $presets,
        private MappingPresetVersionRepositoryInterface $versions,
    ) {
    }

    public function execute(GetMappingPresetByIdInput $input): GetMappingPresetByIdOutput
    {
        $preset = $this->presets->findByIdInOrganization($input->id, $input->organizationId);

        if ($preset === null) {
            throw new MappingPresetNotFoundException($input->id);
        }

        $version = $preset->currentVersionId !== null
            ? $this->versions->findById($preset->currentVersionId)
            : null;

        return new GetMappingPresetByIdOutput(preset: $preset, version: $version);
    }
}
