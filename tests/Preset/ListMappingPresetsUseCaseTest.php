<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use NeneProfile\Preset\ListMappingPresetsInput;
use NeneProfile\Preset\ListMappingPresetsUseCase;
use NeneProfile\Preset\MappingPreset;
use PHPUnit\Framework\TestCase;

final class ListMappingPresetsUseCaseTest extends TestCase
{
    private InMemoryMappingPresetRepository $repo;
    private ListMappingPresetsUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryMappingPresetRepository();
        $this->useCase = new ListMappingPresetsUseCase($this->repo);
    }

    private function seed(string $name, int $organizationId, bool $isDeleted = false): int
    {
        return $this->repo->save(new MappingPreset(
            id: 0,
            organizationId: $organizationId,
            name: $name,
            bankLabel: $name,
            currentVersionId: 1,
            isDeleted: $isDeleted,
        ));
    }

    public function test_returns_only_presets_of_the_organization(): void
    {
        $this->seed('mizuho', 7);
        $this->seed('mufg', 7);
        $this->seed('other', 99);

        $output = $this->useCase->execute(new ListMappingPresetsInput(organizationId: 7));

        $this->assertSame(2, $output->total);
        $this->assertCount(2, $output->items);
        foreach ($output->items as $preset) {
            $this->assertSame(7, $preset->organizationId);
        }
    }

    public function test_excludes_soft_deleted_presets(): void
    {
        $this->seed('live', 7);
        $this->seed('gone', 7, isDeleted: true);

        $output = $this->useCase->execute(new ListMappingPresetsInput(organizationId: 7));

        $this->assertSame(1, $output->total);
        $this->assertSame('live', $output->items[0]->name);
    }

    public function test_applies_limit_and_offset(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->seed("p{$i}", 7);
        }

        $output = $this->useCase->execute(new ListMappingPresetsInput(organizationId: 7, limit: 1, offset: 2));

        $this->assertSame(4, $output->total);
        $this->assertCount(1, $output->items);
        $this->assertSame('p2', $output->items[0]->name);
    }
}
