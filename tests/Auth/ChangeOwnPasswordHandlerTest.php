<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use NeneProfile\Auth\ChangeOwnPasswordHandler;
use NeneProfile\Auth\ChangeOwnPasswordUseCase;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use NeneProfile\Tests\User\InMemoryUserRepository;
use NeneProfile\User\User;
use PHPUnit\Framework\TestCase;

final class ChangeOwnPasswordHandlerTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryUserRepository $users;
    private ChangeOwnPasswordHandler $handler;
    private int $userId;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->userId = $this->users->save(new User(
            id: 0,
            email: 'user@example.com',
            passwordHash: password_hash('current-password', PASSWORD_BCRYPT),
            role: 'admin',
            organizationId: 1,
        ));

        $this->handler = new ChangeOwnPasswordHandler(
            new ChangeOwnPasswordUseCase($this->users),
            $this->psr17(),
            $this->problemFactory(),
        );
    }

    public function test_returns_204_on_success(): void
    {
        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/auth/me/password', [
                'current_password' => 'current-password',
                'new_password'     => 'new-password-123',
            ]),
            userId: $this->userId,
        );

        $response = $this->handler->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_returns_401_when_no_auth_claims(): void
    {
        $request = $this->jsonRequest('PATCH', '/admin/auth/me/password', [
            'current_password' => 'current-password',
            'new_password'     => 'new-password-123',
        ]);

        $response = $this->handler->handle($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_returns_422_when_current_password_missing(): void
    {
        $this->expectException(\Nene2\Validation\ValidationException::class);

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/auth/me/password', [
                'new_password' => 'new-password-123',
            ]),
            userId: $this->userId,
        );

        $this->handler->handle($request);
    }

    public function test_returns_422_when_new_password_missing(): void
    {
        $this->expectException(\Nene2\Validation\ValidationException::class);

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/auth/me/password', [
                'current_password' => 'current-password',
            ]),
            userId: $this->userId,
        );

        $this->handler->handle($request);
    }

    public function test_returns_422_when_new_password_too_short(): void
    {
        $this->expectException(\Nene2\Validation\ValidationException::class);

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/auth/me/password', [
                'current_password' => 'current-password',
                'new_password'     => 'short',
            ]),
            userId: $this->userId,
        );

        $this->handler->handle($request);
    }

    public function test_returns_422_when_new_password_exactly_7_chars(): void
    {
        $this->expectException(\Nene2\Validation\ValidationException::class);

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/auth/me/password', [
                'current_password' => 'current-password',
                'new_password'     => '1234567', // 7 chars — below minimum of 8
            ]),
            userId: $this->userId,
        );

        $this->handler->handle($request);
    }

    public function test_accepts_new_password_exactly_8_chars(): void
    {
        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/auth/me/password', [
                'current_password' => 'current-password',
                'new_password'     => '12345678', // exactly 8
            ]),
            userId: $this->userId,
        );

        $response = $this->handler->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_propagates_invalid_current_password_exception(): void
    {
        $this->expectException(\NeneProfile\Auth\InvalidCurrentPasswordException::class);

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', '/admin/auth/me/password', [
                'current_password' => 'wrong-password',
                'new_password'     => 'new-password-123',
            ]),
            userId: $this->userId,
        );

        $this->handler->handle($request);
    }
}
