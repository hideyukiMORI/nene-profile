<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use Nene2\Routing\Router;
use Nene2\Validation\ValidationException;
use NeneProfile\Audit\AuditRecorder;
use NeneProfile\Tests\Audit\InMemoryAuditLogRepository;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use NeneProfile\User\CreateUserHandler;
use NeneProfile\User\CreateUserUseCase;
use NeneProfile\User\DeleteUserHandler;
use NeneProfile\User\DeleteUserUseCase;
use NeneProfile\User\GetUserByIdHandler;
use NeneProfile\User\GetUserByIdUseCase;
use NeneProfile\User\ListUsersHandler;
use NeneProfile\User\ListUsersUseCase;
use NeneProfile\User\UpdateUserHandler;
use NeneProfile\User\UpdateUserUseCase;
use NeneProfile\User\User;
use PHPUnit\Framework\TestCase;

final class UserHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryUserRepository $users;
    private AuditRecorder $audit;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->audit = new AuditRecorder(new InMemoryAuditLogRepository());
    }

    private function savedUser(int $orgId = 1, string $role = 'member'): int
    {
        return $this->users->save(new User(
            id: 0,
            email: "user-{$orgId}@example.com",
            passwordHash: password_hash('password', PASSWORD_BCRYPT),
            role: $role,
            organizationId: $orgId,
        ));
    }

    // ── CreateUserHandler ─────────────────────────────────────────────────

    public function test_create_returns_201_with_snake_case_payload(): void
    {
        $handler = new CreateUserHandler(
            new CreateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/users', [
                'email'    => 'new@example.com',
                'password' => 'password123',
                'role'     => 'member',
            ]),
        );

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('new@example.com', $payload['email']);
        $this->assertSame('member', $payload['role']);
        $this->assertArrayNotHasKey('password_hash', $payload);
        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('status', $payload);
    }

    public function test_create_returns_400_when_no_org_context(): void
    {
        $handler = new CreateUserHandler(
            new CreateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->jsonRequest('POST', '/admin/users', [
            'email'    => 'new@example.com',
            'password' => 'password123',
            'role'     => 'member',
        ]);

        $response = $handler->handle($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_create_throws_validation_when_email_missing(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateUserHandler(
            new CreateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->jsonRequest('POST', '/admin/users', [
            'password' => 'password123',
            'role'     => 'member',
        ]));
        $handler->handle($request);
    }

    public function test_create_throws_validation_when_password_too_short(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateUserHandler(
            new CreateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->jsonRequest('POST', '/admin/users', [
            'email'    => 'new@example.com',
            'password' => 'short', // < 8 chars
            'role'     => 'member',
        ]));
        $handler->handle($request);
    }

    public function test_create_throws_validation_when_password_exactly_7_chars(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateUserHandler(
            new CreateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->jsonRequest('POST', '/admin/users', [
            'email'    => 'new@example.com',
            'password' => '1234567',
            'role'     => 'member',
        ]));
        $handler->handle($request);
    }

    public function test_create_accepts_password_exactly_8_chars(): void
    {
        $handler = new CreateUserHandler(
            new CreateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->jsonRequest('POST', '/admin/users', [
            'email'    => 'new@example.com',
            'password' => '12345678',
            'role'     => 'member',
        ]));

        $response = $handler->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_create_throws_validation_when_role_missing(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateUserHandler(
            new CreateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->jsonRequest('POST', '/admin/users', [
            'email'    => 'new@example.com',
            'password' => 'password123',
        ]));
        $handler->handle($request);
    }

    // ── GetUserByIdHandler ────────────────────────────────────────────────

    public function test_get_returns_user_json(): void
    {
        $id = $this->savedUser();

        $handler = new GetUserByIdHandler(
            new GetUserByIdUseCase($this->users),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('GET', "/admin/users/{$id}"))
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($id, $payload['id']);
        $this->assertSame('user-1@example.com', $payload['email']);
        $this->assertArrayNotHasKey('password_hash', $payload);
    }

    public function test_get_returns_400_without_org(): void
    {
        $handler = new GetUserByIdHandler(
            new GetUserByIdUseCase($this->users),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle(
            $this->request('GET', '/admin/users/1')
                ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => '1']),
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    // ── ListUsersHandler ──────────────────────────────────────────────────

    public function test_list_returns_paginated_envelope(): void
    {
        $this->savedUser(1);
        $this->savedUser(1);

        $handler = new ListUsersHandler(
            new ListUsersUseCase($this->users),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle($this->withAuth($this->request('GET', '/admin/users')));
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $payload['total']);
        $this->assertArrayHasKey('items', $payload);
        $this->assertArrayHasKey('limit', $payload);
        $this->assertArrayHasKey('offset', $payload);
    }

    public function test_list_respects_pagination_params(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->users->save(new User(
                id: 0,
                email: "user{$i}@example.com",
                passwordHash: 'hash',
                role: 'member',
                organizationId: 1,
            ));
        }

        $handler = new ListUsersHandler(
            new ListUsersUseCase($this->users),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('GET', '/admin/users'))
            ->withQueryParams(['limit' => '2', 'offset' => '3']);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(5, $payload['total']);
        $this->assertCount(2, $payload['items']);
    }

    public function test_list_returns_400_without_org(): void
    {
        $handler = new ListUsersHandler(
            new ListUsersUseCase($this->users),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle($this->request('GET', '/admin/users'));

        $this->assertSame(400, $response->getStatusCode());
    }

    // ── UpdateUserHandler ─────────────────────────────────────────────────

    public function test_update_returns_200_with_updated_role(): void
    {
        $id = $this->savedUser();

        $handler = new UpdateUserHandler(
            new UpdateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', "/admin/users/{$id}", ['role' => 'admin']),
        )->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('admin', $payload['role']);
    }

    public function test_update_returns_400_without_org(): void
    {
        $handler = new UpdateUserHandler(
            new UpdateUserUseCase($this->users, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle(
            $this->jsonRequest('PATCH', '/admin/users/1', ['role' => 'admin'])
                ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => '1']),
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    // ── DeleteUserHandler ─────────────────────────────────────────────────

    public function test_delete_returns_204(): void
    {
        $actorId = $this->users->save(new User(
            id: 0,
            email: 'actor@example.com',
            passwordHash: 'hash',
            role: 'admin',
            organizationId: 1,
        ));
        $targetId = $this->savedUser();

        $handler = new DeleteUserHandler(
            new DeleteUserUseCase($this->users, $this->audit),
            $this->psr17(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('DELETE', "/admin/users/{$targetId}"), userId: $actorId)
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $targetId]);

        $response = $handler->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_delete_returns_400_without_org(): void
    {
        $handler = new DeleteUserHandler(
            new DeleteUserUseCase($this->users, $this->audit),
            $this->psr17(),
            $this->problemFactory(),
        );

        $response = $handler->handle(
            $this->request('DELETE', '/admin/users/1')
                ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => '1']),
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_delete_propagates_cannot_delete_self(): void
    {
        $this->expectException(\NeneProfile\User\CannotDeleteSelfException::class);

        $id = $this->savedUser();

        $handler = new DeleteUserHandler(
            new DeleteUserUseCase($this->users, $this->audit),
            $this->psr17(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('DELETE', "/admin/users/{$id}"), userId: $id)
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $handler->handle($request);
    }
}
