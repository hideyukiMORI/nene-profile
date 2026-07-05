<?php

declare(strict_types=1);

namespace NeneProfile\Tests\OrgSettings;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NeneProfile\OrgSettings\EncodingNotSupportedException;
use NeneProfile\OrgSettings\OrganizationSettings;
use NeneProfile\OrgSettings\OrganizationSettingsRepositoryInterface;
use NeneProfile\OrgSettings\OrganizationSettingsSnapshot;
use NeneProfile\OrgSettings\UpdateOrganizationSettingsInput;
use NeneProfile\OrgSettings\UpdateOrganizationSettingsUseCase;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class UpdateOrganizationSettingsUseCaseTest extends TestCase
{
    private InMemoryOrganizationSettingsRepository $repo;
    private InMemoryAuditRecorderFactory $auditRepo;
    private UpdateOrganizationSettingsUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo      = new InMemoryOrganizationSettingsRepository();
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        $this->useCase   = new UpdateOrganizationSettingsUseCase(
            $this->repo,
            new ImmediateTransactionManager(),
            $this->settingsFactory(),
            $this->auditRepo,
        );
    }

    /** @return Closure(DatabaseQueryExecutorInterface): OrganizationSettingsRepositoryInterface */
    private function settingsFactory(): Closure
    {
        $repo = $this->repo;

        return static fn (DatabaseQueryExecutorInterface $exec): OrganizationSettingsRepositoryInterface => $repo;
    }

    public function test_creates_settings_on_first_update(): void
    {
        $settings = $this->useCase->execute(1, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            defaultEncoding: 'shift_jis',
            maxFileSizeBytes: 2048,
        ));

        $this->assertSame('shift_jis', $settings->defaultEncoding);
        $this->assertSame(2048, $settings->maxFileSizeBytes);
        $this->assertNotNull($this->repo->findByOrganizationId(7));
    }

    public function test_partial_update_keeps_unchanged_fields(): void
    {
        $this->repo->upsert(new OrganizationSettings(
            organizationId: 7,
            defaultEncoding: 'utf-8',
            maxFileSizeBytes: 5000,
        ));

        $settings = $this->useCase->execute(1, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            maxFileSizeBytes: 9999,
        ));

        $this->assertSame('utf-8', $settings->defaultEncoding);
        $this->assertSame(9999, $settings->maxFileSizeBytes);
    }

    public function test_rejects_unsupported_encoding(): void
    {
        $this->expectException(EncodingNotSupportedException::class);

        $this->useCase->execute(1, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            defaultEncoding: 'euc-jp',
        ));
    }

    public function test_can_set_and_clear_bearer_token(): void
    {
        $this->useCase->execute(1, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            clearBearerTokenProvided: true,
            clearBearerToken: 'super-secret',
        ));
        $afterSet = $this->repo->findByOrganizationId(7);
        $this->assertNotNull($afterSet);
        $this->assertSame('super-secret', $afterSet->clearBearerToken);

        $this->useCase->execute(1, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            clearBearerTokenProvided: true,
            clearBearerToken: null,
        ));
        $afterClear = $this->repo->findByOrganizationId(7);
        $this->assertNotNull($afterClear);
        $this->assertNull($afterClear->clearBearerToken);
    }

    public function test_token_omitted_when_not_provided(): void
    {
        $this->useCase->execute(1, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            clearBearerTokenProvided: true,
            clearBearerToken: 'keep-me',
        ));

        // Update something else without touching the token
        $this->useCase->execute(1, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            maxFileSizeBytes: 1234,
        ));

        $kept = $this->repo->findByOrganizationId(7);
        $this->assertNotNull($kept);
        $this->assertSame('keep-me', $kept->clearBearerToken);
    }

    public function test_audit_never_contains_token_value(): void
    {
        $this->useCase->execute(99, new UpdateOrganizationSettingsInput(
            organizationId: 7,
            clearBearerTokenProvided: true,
            clearBearerToken: 'top-secret-value',
        ));

        $log = $this->auditRepo->appended[0];
        $this->assertSame('organization_settings.updated', $log->action);
        $encoded = json_encode([$log->before, $log->after]);
        $this->assertIsString($encoded);
        $this->assertStringNotContainsString('top-secret-value', $encoded);
        $this->assertTrue($log->after['clear_bearer_token_set'] ?? false);
    }

    public function test_snapshot_exposes_only_token_boolean(): void
    {
        $arr = OrganizationSettingsSnapshot::toArray(new OrganizationSettings(
            organizationId: 7,
            clearBearerToken: 'secret',
        ));

        $this->assertArrayNotHasKey('clear_bearer_token', $arr);
        $this->assertTrue($arr['clear_bearer_token_set']);
    }
}
