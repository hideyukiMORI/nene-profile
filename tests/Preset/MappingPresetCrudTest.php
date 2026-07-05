<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NeneProfile\Preset\CreateMappingPresetInput;
use NeneProfile\Preset\CreateMappingPresetUseCase;
use NeneProfile\Preset\DeleteMappingPresetInput;
use NeneProfile\Preset\DeleteMappingPresetUseCase;
use NeneProfile\Preset\GetMappingPresetByIdInput;
use NeneProfile\Preset\GetMappingPresetByIdUseCase;
use NeneProfile\Preset\MappingDefinition;
use NeneProfile\Preset\MappingDefinitionFactory;
use NeneProfile\Preset\MappingPresetNotFoundException;
use NeneProfile\Preset\MappingPresetRepositoryInterface;
use NeneProfile\Preset\MappingPresetVersionRepositoryInterface;
use NeneProfile\Preset\UpdateMappingPresetInput;
use NeneProfile\Preset\UpdateMappingPresetUseCase;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class MappingPresetCrudTest extends TestCase
{
    private InMemoryMappingPresetRepository $presets;
    private InMemoryMappingPresetVersionRepository $versions;
    private InMemoryAuditRecorderFactory $auditRepo;
    private AuditRecorderFactoryInterface $audit;

    protected function setUp(): void
    {
        $this->presets   = new InMemoryMappingPresetRepository();
        $this->versions  = new InMemoryMappingPresetVersionRepository();
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        $this->audit     = $this->auditRepo;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): MappingPresetRepositoryInterface */
    private function presetsFactory(): Closure
    {
        $repo = $this->presets;

        return static fn (DatabaseQueryExecutorInterface $exec): MappingPresetRepositoryInterface => $repo;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): MappingPresetVersionRepositoryInterface */
    private function versionsFactory(): Closure
    {
        $repo = $this->versions;

        return static fn (DatabaseQueryExecutorInterface $exec): MappingPresetVersionRepositoryInterface => $repo;
    }

    private function createUseCase(): CreateMappingPresetUseCase
    {
        return new CreateMappingPresetUseCase(new ImmediateTransactionManager(), $this->presetsFactory(), $this->versionsFactory(), $this->audit);
    }

    private function updateUseCase(): UpdateMappingPresetUseCase
    {
        return new UpdateMappingPresetUseCase($this->presets, $this->versions, new ImmediateTransactionManager(), $this->presetsFactory(), $this->versionsFactory(), $this->audit);
    }

    private function definition(string $dateTransform = 'date_ymd_slash'): MappingDefinition
    {
        return MappingDefinitionFactory::fromArray([
            'encoding'  => 'auto',
            'delimiter' => 'comma',
            'columns'   => [
                'transaction_date' => ['source' => '日付', 'transform' => $dateTransform],
                'amount_cents'     => ['source' => ['入金金額', '出金金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ]);
    }

    public function test_create_makes_preset_and_version_one(): void
    {
        $useCase = $this->createUseCase();

        $result = $useCase->execute(1, new CreateMappingPresetInput(
            organizationId: 7,
            name: 'MUFG',
            bankLabel: 'MUFG',
            definition: $this->definition(),
        ));

        $this->assertSame('MUFG', $result->preset->name);
        $this->assertSame(1, $result->version->versionNumber);
        $this->assertSame($result->version->id, $result->preset->currentVersionId);
        $this->assertSame(1, $this->versions->count());

        $log = $this->auditRepo->appended[0];
        $this->assertSame('mapping_preset.created', $log->action);
    }

    public function test_update_with_definition_creates_new_version_and_keeps_old(): void
    {
        $create = $this->createUseCase();
        $created = $create->execute(1, new CreateMappingPresetInput(
            organizationId: 7,
            name: 'MUFG',
            bankLabel: 'MUFG',
            definition: $this->definition('date_ymd_slash'),
        ));
        $v1Id = $created->version->id;

        $update = $this->updateUseCase();
        $updated = $update->execute(1, new UpdateMappingPresetInput(
            id: $created->preset->id,
            organizationId: 7,
            definition: $this->definition('date_ymd_dash'),
        ));

        // A new version (v2) was created; v1 still exists unchanged.
        $this->assertSame(2, $updated->version?->versionNumber);
        $this->assertSame(2, $this->versions->count());

        $v1 = $this->versions->findById($v1Id);
        $this->assertNotNull($v1);
        $this->assertSame(1, $v1->versionNumber);
        $this->assertSame('date_ymd_slash', $v1->definition->columns['transaction_date']->transform);

        // current points to v2
        $this->assertNotSame($v1Id, $updated->preset->currentVersionId);
    }

    public function test_metadata_only_update_does_not_create_version(): void
    {
        $create = $this->createUseCase();
        $created = $create->execute(1, new CreateMappingPresetInput(
            organizationId: 7,
            name: 'MUFG',
            bankLabel: 'MUFG',
            definition: $this->definition(),
        ));

        $update = $this->updateUseCase();
        $updated = $update->execute(1, new UpdateMappingPresetInput(
            id: $created->preset->id,
            organizationId: 7,
            name: 'MUFG Ordinary',
        ));

        $this->assertSame('MUFG Ordinary', $updated->preset->name);
        $this->assertSame(1, $this->versions->count());
    }

    public function test_get_returns_preset_with_current_version(): void
    {
        $create = $this->createUseCase();
        $created = $create->execute(1, new CreateMappingPresetInput(
            organizationId: 7,
            name: 'SMBC',
            bankLabel: 'SMBC',
            definition: $this->definition(),
        ));

        $get = new GetMappingPresetByIdUseCase($this->presets, $this->versions);
        $result = $get->execute(new GetMappingPresetByIdInput($created->preset->id, 7));

        $this->assertSame('SMBC', $result->preset->name);
        $this->assertNotNull($result->version);
        $this->assertSame(1, $result->version->versionNumber);
    }

    public function test_cross_tenant_get_is_not_found(): void
    {
        $create = $this->createUseCase();
        $created = $create->execute(1, new CreateMappingPresetInput(
            organizationId: 7,
            name: 'SMBC',
            bankLabel: 'SMBC',
            definition: $this->definition(),
        ));

        $get = new GetMappingPresetByIdUseCase($this->presets, $this->versions);

        $this->expectException(MappingPresetNotFoundException::class);
        $get->execute(new GetMappingPresetByIdInput($created->preset->id, 999));
    }

    public function test_delete_soft_deletes_and_records_audit(): void
    {
        $create = $this->createUseCase();
        $created = $create->execute(1, new CreateMappingPresetInput(
            organizationId: 7,
            name: 'Rakuten',
            bankLabel: 'Rakuten',
            definition: $this->definition(),
        ));

        $auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        $delete = new DeleteMappingPresetUseCase($this->presets, new ImmediateTransactionManager(), $this->presetsFactory(), $auditRepo);
        $delete->execute(5, new DeleteMappingPresetInput($created->preset->id, 7));

        $this->assertNull($this->presets->findByIdInOrganization($created->preset->id, 7));

        $log = $auditRepo->appended[0];
        $this->assertSame('mapping_preset.deleted', $log->action);
        $this->assertNotNull($log->before);
        $this->assertNull($log->after);
    }
}
