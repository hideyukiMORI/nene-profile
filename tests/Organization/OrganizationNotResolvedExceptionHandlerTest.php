<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use NeneProfile\Organization\OrganizationNotResolvedException;
use NeneProfile\Organization\OrganizationNotResolvedExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OrganizationNotResolvedExceptionHandlerTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_supports_only_its_exception(): void
    {
        $handler = new OrganizationNotResolvedExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new OrganizationNotResolvedException()));
        $this->assertFalse($handler->supports(new RuntimeException()));
    }

    public function test_returns_400_problem(): void
    {
        $handler = new OrganizationNotResolvedExceptionHandler($this->problemFactory());

        $response = $handler->handle(
            new OrganizationNotResolvedException(),
            $this->request('GET', '/admin/users'),
        );

        $this->assertProblem($response, 400, 'org-not-resolved', '/admin/users');
    }
}
