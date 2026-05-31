<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use NeneProfile\Auth\ChangeOwnPasswordInput;
use NeneProfile\Auth\ChangeOwnPasswordUseCase;
use NeneProfile\Auth\InvalidCurrentPasswordException;
use NeneProfile\Tests\User\InMemoryUserRepository;
use NeneProfile\User\User;
use PHPUnit\Framework\TestCase;

final class ChangeOwnPasswordUseCaseTest extends TestCase
{
    private InMemoryUserRepository $users;
    private ChangeOwnPasswordUseCase $useCase;

    protected function setUp(): void
    {
        $this->users   = new InMemoryUserRepository();
        $this->useCase = new ChangeOwnPasswordUseCase($this->users);
    }

    public function test_changes_password_when_current_is_correct(): void
    {
        $this->users->save(new User(
            id: 0,
            email: 'user@example.com',
            passwordHash: password_hash('old-password', PASSWORD_BCRYPT),
            role: 'admin',
            organizationId: 1,
        ));

        $user = $this->users->findByEmail('user@example.com');
        assert($user !== null);

        $this->useCase->execute(new ChangeOwnPasswordInput(
            actorUserId: $user->id,
            currentPassword: 'old-password',
            newPassword: 'new-password-123',
        ));

        $updated = $this->users->findById($user->id);
        assert($updated !== null);

        $this->assertTrue(password_verify('new-password-123', $updated->passwordHash));
        $this->assertFalse(password_verify('old-password', $updated->passwordHash));
    }

    public function test_throws_when_current_password_is_wrong(): void
    {
        $this->users->save(new User(
            id: 0,
            email: 'user@example.com',
            passwordHash: password_hash('correct-password', PASSWORD_BCRYPT),
            role: 'admin',
            organizationId: 1,
        ));

        $user = $this->users->findByEmail('user@example.com');
        assert($user !== null);

        $this->expectException(InvalidCurrentPasswordException::class);

        $this->useCase->execute(new ChangeOwnPasswordInput(
            actorUserId: $user->id,
            currentPassword: 'wrong-password',
            newPassword: 'new-password-123',
        ));
    }

    public function test_throws_when_user_not_found(): void
    {
        $this->expectException(InvalidCurrentPasswordException::class);

        $this->useCase->execute(new ChangeOwnPasswordInput(
            actorUserId: 999,
            currentPassword: 'any-password',
            newPassword: 'new-password-123',
        ));
    }
}
