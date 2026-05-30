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

    /**
     * @return array{preset: MappingPreset, version: ?MappingPresetVersion}
     */
    public function execute(GetMappingPresetByIdInput $input): array
    {
        $preset = $this->presets->findByIdInOrganization($input->id, $input->organizationId);

        if ($preset === null) {
            throw new MappingPresetNotFoundException($input->id);
        }

        $version = $preset->currentVersionId !== null
            ? $this->versions->findById($preset->currentVersionId)
            : null;

        return ['preset' => $preset, 'version' => $version];
    }
}
