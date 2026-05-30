<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use NeneProfile\User\CannotDeleteSelfException;
use NeneProfile\User\CannotDeleteSelfExceptionHandler;
use NeneProfile\User\RoleNotAssignableException;
use NeneProfile\User\RoleNotAssignableExceptionHandler;
use NeneProfile\User\UserEmailConflictException;
use NeneProfile\User\UserEmailConflictExceptionHandler;
use NeneProfile\User\UserNotFoundException;
use NeneProfile\User\UserNotFoundExceptionHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class UserExceptionHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_user_not_found_returns_404(): void
    {
        $handler = new UserNotFoundExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new UserNotFoundException(5)));
        $this->assertFalse($handler->supports(new RuntimeException()));

        $response = $handler->handle(new UserNotFoundException(5), $this->request('GET', '/admin/users/5'));

        $this->assertProblem($response, 404, 'user-not-found', '/admin/users/5');
    }

    public function test_email_conflict_returns_422(): void
    {
        $handler = new UserEmailConflictExceptionHandler($this->problemFactory());

        $response = $handler->handle(
            new UserEmailConflictException('dup@example.com'),
            $this->request('POST', '/admin/users'),
        );

        $this->assertProblem($response, 422, 'validation-failed', '/admin/users');
    }

    public function test_cannot_delete_self_returns_422(): void
    {
        $handler = new CannotDeleteSelfExceptionHandler($this->problemFactory());

        $response = $handler->handle(new CannotDeleteSelfException(), $this->request('DELETE', '/admin/users/3'));

        $this->assertProblem($response, 422, 'validation-failed', '/admin/users/3');
    }

    public function test_role_not_assignable_returns_422(): void
    {
        $handler = new RoleNotAssignableExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new RoleNotAssignableException('superadmin')));

        $response = $handler->handle(
            new RoleNotAssignableException('superadmin'),
            $this->request('POST', '/admin/users'),
        );

        $this->assertProblem($response, 422, 'validation-failed', '/admin/users');
    }
}
