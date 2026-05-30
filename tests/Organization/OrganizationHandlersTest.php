<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use Nene2\Routing\Router;
use NeneProfile\Organization\GetOrganizationByIdHandler;
use NeneProfile\Organization\GetOrganizationByIdUseCase;
use NeneProfile\Organization\ListOrganizationsHandler;
use NeneProfile\Organization\ListOrganizationsUseCase;
use NeneProfile\Organization\Organization;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;

final class OrganizationHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryOrganizationRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new InMemoryOrganizationRepository();
    }

    public function test_get_by_id_handler_returns_snake_case_json(): void
    {
        $id = $this->repo->save(new Organization(name: 'Acme', slug: 'acme', isActive: true));

        $handler = new GetOrganizationByIdHandler(
            new GetOrganizationByIdUseCase($this->repo),
            $this->jsonFactory(),
        );

        $request = $this->request('GET', "/admin/organizations/{$id}")
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);
        $payload = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($id, $payload['id']);
        $this->assertSame('Acme', $payload['name']);
        $this->assertSame('acme', $payload['slug']);
        $this->assertTrue($payload['is_active']);
        $this->assertArrayHasKey('custom_domain', $payload);
        $this->assertArrayHasKey('created_at', $payload);
    }

    public function test_list_handler_returns_paginated_envelope(): void
    {
        $this->repo->save(new Organization(name: 'Acme', slug: 'acme', isActive: true));
        $this->repo->save(new Organization(name: 'Beta', slug: 'beta', isActive: false));

        $handler = new ListOrganizationsHandler(
            new ListOrganizationsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $response = $handler->handle($this->request('GET', '/admin/organizations'));
        $payload = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $payload['total']);
        /** @var list<array<string, mixed>> $items */
        $items = $payload['items'];
        $this->assertCount(2, $items);
        $this->assertSame('acme', $items[0]['slug']);
        $this->assertArrayHasKey('is_active', $items[0]);
        $this->assertArrayHasKey('limit', $payload);
        $this->assertArrayHasKey('offset', $payload);
    }
}
