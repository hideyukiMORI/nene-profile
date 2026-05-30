<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use NeneProfile\Organization\OrganizationNotFoundException;
use NeneProfile\Organization\OrganizationNotFoundExceptionHandler;
use NeneProfile\Organization\OrganizationSlugConflictException;
use NeneProfile\Organization\OrganizationSlugConflictExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OrganizationExceptionHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_not_found_handler_returns_404(): void
    {
        $handler = new OrganizationNotFoundExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new OrganizationNotFoundException(9)));
        $this->assertFalse($handler->supports(new RuntimeException()));

        $response = $handler->handle(
            new OrganizationNotFoundException(9),
            $this->request('GET', '/admin/organizations/9'),
        );

        $payload = $this->assertProblem($response, 404, 'organization-not-found', '/admin/organizations/9');
        $this->assertSame('Organization Not Found', $payload['title']);
        $this->assertStringContainsString('9', (string) $payload['detail']);
    }

    public function test_slug_conflict_handler_returns_422(): void
    {
        $handler = new OrganizationSlugConflictExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new OrganizationSlugConflictException('acme')));

        $response = $handler->handle(
            new OrganizationSlugConflictException('acme'),
            $this->request('POST', '/admin/organizations'),
        );

        $this->assertProblem($response, 422, 'validation-failed', '/admin/organizations');
    }
}
