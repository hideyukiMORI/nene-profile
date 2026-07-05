<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Routing\Router;
use Nene2\Validation\ValidationException;
use NeneProfile\Organization\CreateOrganizationHandler;
use NeneProfile\Organization\CreateOrganizationUseCase;
use NeneProfile\Organization\DeleteOrganizationHandler;
use NeneProfile\Organization\DeleteOrganizationUseCase;
use NeneProfile\Organization\OrganizationRepositoryInterface;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class OrganizationCreateDeleteHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryOrganizationRepository $repo;
    private AuditRecorderFactoryInterface $audit;

    protected function setUp(): void
    {
        $this->repo  = new InMemoryOrganizationRepository();
        $this->audit = new InMemoryAuditRecorderFactory(new FixedClock());
    }

    /** @return Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface */
    private function organizationsFactory(): Closure
    {
        $repo = $this->repo;

        return static fn (DatabaseQueryExecutorInterface $exec): OrganizationRepositoryInterface => $repo;
    }

    private function createUseCase(): CreateOrganizationUseCase
    {
        return new CreateOrganizationUseCase(
            $this->repo,
            new ImmediateTransactionManager(),
            $this->organizationsFactory(),
            $this->audit,
        );
    }

    private function deleteUseCase(): DeleteOrganizationUseCase
    {
        return new DeleteOrganizationUseCase(
            $this->repo,
            new ImmediateTransactionManager(),
            $this->organizationsFactory(),
            $this->audit,
        );
    }

    // ── CreateOrganizationHandler ─────────────────────────────────────────

    public function test_create_returns_201_with_payload(): void
    {
        $handler = new CreateOrganizationHandler(
            $this->createUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/organizations', [
                'name' => 'Acme Corp',
                'slug' => 'acme',
            ]),
            role: 'superadmin',
        );

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Acme Corp', $payload['name']);
        $this->assertSame('acme', $payload['slug']);
        $this->assertTrue($payload['is_active']);
        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('custom_domain', $payload);
    }

    public function test_create_returns_422_when_name_missing(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateOrganizationHandler(
            $this->createUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/organizations', ['slug' => 'acme']),
            role: 'superadmin',
        );
        $handler->handle($request);
    }

    public function test_create_returns_422_when_slug_missing(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateOrganizationHandler(
            $this->createUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/organizations', ['name' => 'Acme']),
            role: 'superadmin',
        );
        $handler->handle($request);
    }

    public function test_create_returns_422_when_slug_has_uppercase(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateOrganizationHandler(
            $this->createUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/organizations', [
                'name' => 'Acme',
                'slug' => 'ACME', // uppercase invalid
            ]),
            role: 'superadmin',
        );
        $handler->handle($request);
    }

    public function test_create_returns_422_when_slug_has_spaces(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateOrganizationHandler(
            $this->createUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/organizations', [
                'name' => 'Acme',
                'slug' => 'acme corp', // spaces invalid
            ]),
            role: 'superadmin',
        );
        $handler->handle($request);
    }

    public function test_create_accepts_is_active_false(): void
    {
        $handler = new CreateOrganizationHandler(
            $this->createUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/organizations', [
                'name'      => 'Inactive Org',
                'slug'      => 'inactive',
                'is_active' => false,
            ]),
            role: 'superadmin',
        );

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertFalse($payload['is_active']);
    }

    public function test_create_accepts_custom_domain(): void
    {
        $handler = new CreateOrganizationHandler(
            $this->createUseCase(),
            $this->jsonFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/organizations', [
                'name'          => 'Custom',
                'slug'          => 'custom',
                'custom_domain' => 'custom.example.com',
            ]),
            role: 'superadmin',
        );

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame('custom.example.com', $payload['custom_domain']);
    }

    // ── DeleteOrganizationHandler ─────────────────────────────────────────

    public function test_delete_returns_204(): void
    {
        $id = $this->repo->save(new \NeneProfile\Organization\Organization(
            name: 'ToDelete',
            slug: 'to-delete',
            isActive: true,
        ));

        $handler = new DeleteOrganizationHandler(
            $this->deleteUseCase(),
            $this->psr17(),
        );

        $request = $this->withAuth($this->request('DELETE', "/admin/organizations/{$id}"), role: 'superadmin')
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_delete_propagates_not_found_exception(): void
    {
        $this->expectException(\NeneProfile\Organization\OrganizationNotFoundException::class);

        $handler = new DeleteOrganizationHandler(
            $this->deleteUseCase(),
            $this->psr17(),
        );

        $request = $this->withAuth($this->request('DELETE', '/admin/organizations/999'), role: 'superadmin')
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => '999']);

        $handler->handle($request);
    }
}
