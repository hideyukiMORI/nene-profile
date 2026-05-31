<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use NeneProfile\Auth\InvalidCurrentPasswordException;
use NeneProfile\Auth\InvalidCurrentPasswordExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InvalidCurrentPasswordExceptionHandlerTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_supports_only_its_exception(): void
    {
        $handler = new InvalidCurrentPasswordExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new InvalidCurrentPasswordException()));
        $this->assertFalse($handler->supports(new RuntimeException('other')));
    }

    public function test_returns_422_with_problem_type(): void
    {
        $handler = new InvalidCurrentPasswordExceptionHandler($this->problemFactory());

        $response = $handler->handle(
            new InvalidCurrentPasswordException(),
            $this->request('PATCH', '/admin/auth/me/password'),
        );

        $this->assertProblem($response, 422, 'invalid-current-password', '/admin/auth/me/password');
    }
}
