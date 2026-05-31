<?php

declare(strict_types=1);

namespace NeneProfile\Tests\OrgSettings;

use NeneProfile\Audit\AuditRecorder;
use NeneProfile\OrgSettings\GetOrganizationSettingsHandler;
use NeneProfile\OrgSettings\GetOrganizationSettingsUseCase;
use NeneProfile\OrgSettings\UpdateOrganizationSettingsHandler;
use NeneProfile\OrgSettings\UpdateOrganizationSettingsUseCase;
use NeneProfile\Tests\Audit\InMemoryAuditLogRepository;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;

final class OrgSettingsHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryOrganizationSettingsRepository $repo;
    private AuditRecorder $audit;

    protected function setUp(): void
    {
        $this->repo  = new InMemoryOrganizationSettingsRepository();
        $this->audit = new AuditRecorder(new InMemoryAuditLogRepository());
    }

    // ── GetOrganizationSettingsHandler ────────────────────────────────────

    public function test_get_returns_default_settings(): void
    {
        $handler = new GetOrganizationSettingsHandler(
            new GetOrganizationSettingsUseCase($this->repo),
            $this->jsonFactory(),
            $this->problemFactory(),
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
        $handler = new GetOrganizationSettingsHandler(
            new GetOrganizationSettingsUseCase($this->repo),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle($this->request('GET', '/admin/organization-settings'));

        $this->assertSame(400, $response->getStatusCode());
    }

    // ── UpdateOrganizationSettingsHandler ─────────────────────────────────

    public function test_update_returns_200_with_updated_encoding(): void
    {
        $handler = new UpdateOrganizationSettingsHandler(
            new UpdateOrganizationSettingsUseCase($this->repo, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
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
        $handler = new UpdateOrganizationSettingsHandler(
            new UpdateOrganizationSettingsUseCase($this->repo, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle($this->jsonRequest('PATCH', '/admin/organization-settings', []));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_update_returns_422_for_invalid_encoding(): void
    {
        $this->expectException(\NeneProfile\OrgSettings\EncodingNotSupportedException::class);

        $handler = new UpdateOrganizationSettingsHandler(
            new UpdateOrganizationSettingsUseCase($this->repo, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
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
            new UpdateOrganizationSettingsUseCase($this->repo, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
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
            new UpdateOrganizationSettingsUseCase($this->repo, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
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
