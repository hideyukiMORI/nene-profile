<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use Nene2\Auth\TokenIssuerInterface;
use NeneProfile\Auth\InvalidCredentialsException;
use NeneProfile\Auth\LoginInput;
use NeneProfile\Auth\LoginUseCase;
use PHPUnit\Framework\TestCase;

final class LoginUseCaseTest extends TestCase
{
    private InMemoryUserRepository $repo;
    private LoginUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo = new InMemoryUserRepository();

        $tokenIssuer = new class () implements TokenIssuerInterface {
            /** @param array<string, mixed> $claims */
            public function issue(array $claims): string
            {
                return 'test-token.' . base64_encode(json_encode($claims) ?: '{}');
            }
        };

        $this->useCase = new LoginUseCase($this->repo, $tokenIssuer);
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $this->repo->create(
            email: 'admin@example.com',
            passwordHash: password_hash('secret', PASSWORD_BCRYPT),
            role: 'admin',
            organizationId: 1,
        );

        $output = $this->useCase->execute(new LoginInput(
            email: 'admin@example.com',
            password: 'secret',
        ));

        $this->assertSame('admin@example.com', $output->email);
        $this->assertSame('admin', $output->role);
        $this->assertSame(1, $output->orgId);
        $this->assertNotEmpty($output->token);
        $this->assertGreaterThan(time(), $output->expiresAt);
    }

    public function test_superadmin_login_has_null_org_id(): void
    {
        $this->repo->create(
            email: 'superadmin@example.com',
            passwordHash: password_hash('secret', PASSWORD_BCRYPT),
            role: 'superadmin',
            organizationId: null,
        );

        $output = $this->useCase->execute(new LoginInput(
            email: 'superadmin@example.com',
            password: 'secret',
        ));

        $this->assertNull($output->orgId);
        $this->assertSame('superadmin', $output->role);
    }

    public function test_throws_on_wrong_password(): void
    {
        $this->repo->create(
            email: 'admin@example.com',
            passwordHash: password_hash('secret', PASSWORD_BCRYPT),
            role: 'admin',
            organizationId: 1,
        );

        $this->expectException(InvalidCredentialsException::class);

        $this->useCase->execute(new LoginInput(
            email: 'admin@example.com',
            password: 'wrong-password',
        ));
    }

    public function test_throws_on_unknown_email(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $this->useCase->execute(new LoginInput(
            email: 'nobody@example.com',
            password: 'secret',
        ));
    }
}
