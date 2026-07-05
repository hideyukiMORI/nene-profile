<?php

declare(strict_types=1);

namespace NeneProfile\Tests\OrgSettings;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NeneProfile\Organization\OrganizationNotResolvedException;
use NeneProfile\OrgSettings\GetOrganizationSettingsHandler;
use NeneProfile\OrgSettings\GetOrganizationSettingsUseCase;
use NeneProfile\OrgSettings\OrganizationSettingsRepositoryInterface;
use NeneProfile\OrgSettings\UpdateOrganizationSettingsHandler;
use NeneProfile\OrgSettings\UpdateOrganizationSettingsUseCase;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class OrgSettingsHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryOrganizationSettingsRepository $repo;
    private AuditRecorderFactoryInterface $audit;

    protected function setUp(): void
    {
        $this->repo  = new InMemoryOrganizationSettingsRepository();
        $this->audit = new InMemoryAuditRecorderFactory(new FixedClock());
    }

    /** @return Closure(DatabaseQueryExecutorInterface): OrganizationSettingsRepositoryInterface */
    private function settingsFactory(): Closure
    {
        $repo = $this->repo;

        return static fn (DatabaseQueryExecutorInterface $exec): OrganizationSettingsRepositoryInterface => $repo;
    }

    private function updateUseCase(): UpdateOrganizationSettingsUseCase
    {
        return new UpdateOrganizationSettingsUseCase($this->repo, new ImmediateTransactionManager(), $this->settingsFactory(), $this->audit);
    }

    // ── GetOrganizationSettingsHandler ────────────────────────────────────

    public function test_get_returns_default_settings(): void
    {
        $handler = new GetOrganizationSettingsHandler(
            new GetOrganizationSettingsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $response = $handler->handle($this->withAuth($this->request('GET', '/admin/organization-settings')));
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('organization_id', $payload);
        $this->assertArrayHasKey('default_encoding', $payload);
        $this->assertArrayHasKey('max_file_size_bytes', $payload);
    }

    public function test_get_returns_400_without_org(): void
    {
        $this->expectException(OrganizationNotResolvedException::class);

        $handler = new GetOrganizationSettingsHandler(
            new GetOrganizationSettingsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $response = $handler->handle($this->request('GET', '/admin/organization-settings'));

    }

    // ── UpdateOrganizationSettingsHandler ─────────────────────────────────

    public function test_update_returns_200_with_updated_encoding(): void
    {
        $handler = new UpdateOrganizationSettingsHandler(
            $this->updateUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/organization-settings', [
                'default_encoding' => 'shift_jis',
            ]),
        );

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('shift_jis', $payload['default_encoding']);
    }

    public function test_update_returns_400_without_org(): void
    {
        $this->expectException(OrganizationNotResolvedException::class);

        $handler = new UpdateOrganizationSettingsHandler(
            $this->updateUseCase(),
            $this->jsonFactory(),
        );

        $response = $handler->handle($this->jsonRequest('PATCH', '/admin/organization-settings', []));

    }

    public function test_update_returns_422_for_invalid_encoding(): void
    {
        $this->expectException(\NeneProfile\OrgSettings\EncodingNotSupportedException::class);

        $handler = new UpdateOrganizationSettingsHandler(
            $this->updateUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/organization-settings', [
                'default_encoding' => 'euc-jp', // unsupported
            ]),
        );

        $handler->handle($request);
    }

    public function test_update_returns_422_for_invalid_max_file_size(): void
    {
        $this->expectException(\Nene2\Validation\ValidationException::class);

        $handler = new UpdateOrganizationSettingsHandler(
            $this->updateUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/organization-settings', [
                'max_file_size_bytes' => 0, // must be positive
            ]),
        );

        $handler->handle($request);
    }

    public function test_update_with_empty_body_succeeds(): void
    {
        $handler = new UpdateOrganizationSettingsHandler(
            $this->updateUseCase(),
            $this->jsonFactory(),
        );

        // Empty JSON object {} — json_encode([]) produces array "[]", must use object
        $body    = $this->psr17()->createStream('{}');
        $request = $this->withAuth($this->psr17()
            ->createServerRequest('PATCH', 'https://example.test/admin/organization-settings')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body));
        $response = $handler->handle($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
