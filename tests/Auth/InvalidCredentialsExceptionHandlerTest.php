<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use NeneProfile\Auth\InvalidCredentialsException;
use NeneProfile\Auth\InvalidCredentialsExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InvalidCredentialsExceptionHandlerTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_supports_only_its_exception(): void
    {
        $handler = new InvalidCredentialsExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new InvalidCredentialsException()));
        $this->assertFalse($handler->supports(new RuntimeException('other')));
    }

    public function test_returns_401_unauthenticated_problem(): void
    {
        $handler = new InvalidCredentialsExceptionHandler($this->problemFactory());

        $response = $handler->handle(
            new InvalidCredentialsException(),
            $this->request('POST', '/admin/auth/login'),
        );

        $this->assertProblem($response, 401, 'unauthenticated', '/admin/auth/login');
    }
}
