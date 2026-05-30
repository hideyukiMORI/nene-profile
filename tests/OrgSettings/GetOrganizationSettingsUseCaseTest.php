<?php

declare(strict_types=1);

namespace NeneProfile\Tests\OrgSettings;

use NeneProfile\OrgSettings\GetOrganizationSettingsUseCase;
use NeneProfile\OrgSettings\OrganizationSettings;
use PHPUnit\Framework\TestCase;

final class GetOrganizationSettingsUseCaseTest extends TestCase
{
    public function test_returns_defaults_when_no_row_exists(): void
    {
        $repo = new InMemoryOrganizationSettingsRepository();
        $useCase = new GetOrganizationSettingsUseCase($repo);

        $settings = $useCase->execute(7);

        $this->assertSame(7, $settings->organizationId);
        $this->assertSame('auto', $settings->defaultEncoding);
        $this->assertSame(OrganizationSettings::DEFAULT_MAX_FILE_SIZE_BYTES, $settings->maxFileSizeBytes);
        $this->assertNull($settings->clearBearerToken);
    }

    public function test_returns_persisted_settings(): void
    {
        $repo = new InMemoryOrganizationSettingsRepository();
        $repo->upsert(new OrganizationSettings(
            organizationId: 7,
            defaultEncoding: 'shift_jis',
            maxFileSizeBytes: 1024,
            clearBearerToken: 'secret-token',
        ));
        $useCase = new GetOrganizationSettingsUseCase($repo);

        $settings = $useCase->execute(7);

        $this->assertSame('shift_jis', $settings->defaultEncoding);
        $this->assertSame(1024, $settings->maxFileSizeBytes);
    }
}
