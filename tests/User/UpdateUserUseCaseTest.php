<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use NeneProfile\Audit\AuditRecorder;
use NeneProfile\Tests\Audit\InMemoryAuditLogRepository;
use NeneProfile\User\RoleNotAssignableException;
use NeneProfile\User\UpdateUserInput;
use NeneProfile\User\UpdateUserUseCase;
use NeneProfile\User\User;
use NeneProfile\User\UserNotFoundException;
use PHPUnit\Framework\TestCase;

final class UpdateUserUseCaseTest extends TestCase
{
    private InMemoryUserRepository $repo;
    private InMemoryAuditLogRepository $auditRepo;
    private UpdateUserUseCase $useCase;
    private int $userId;

    protected function setUp(): void
    {
        $this->repo      = new InMemoryUserRepository();
        $this->auditRepo = new InMemoryAuditLogRepository();
        $this->useCase   = new UpdateUserUseCase($this->repo, new AuditRecorder($this->auditRepo));

        $this->userId = $this->repo->seed(new User(
            id: 0,
            email: 'u@example.com',
            passwordHash: password_hash('original', PASSWORD_BCRYPT),
            role: 'member',
            organizationId: 7,
            status: 'active',
        ));
    }

    public function test_updates_role(): void
    {
        $updated = $this->useCase->execute(1, new UpdateUserInput(
            id: $this->userId,
            organizationId: 7,
            role: 'viewer',
        ));

        $this->assertSame('viewer', $updated->role);
    }

    public function test_updates_password(): void
    {
        $updated = $this->useCase->execute(1, new UpdateUserInput(
            id: $this->userId,
            organizationId: 7,
            password: 'newpassword',
        ));

        $this->assertTrue(password_verify('newpassword', $updated->passwordHash));
    }

    public function test_rejects_superadmin_role(): void
    {
        $this->expectException(RoleNotAssignableException::class);

        $this->useCase->execute(1, new UpdateUserInput(
            id: $this->userId,
            organizationId: 7,
            role: 'superadmin',
        ));
    }

    public function test_rejects_cross_tenant_update(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->useCase->execute(1, new UpdateUserInput(
            id: $this->userId,
            organizationId: 999,
            role: 'viewer',
        ));
    }

    public function test_records_audit_with_before_and_after(): void
    {
        $this->useCase->execute(1, new UpdateUserInput(
            id: $this->userId,
            organizationId: 7,
            role: 'viewer',
        ));

        $log = $this->auditRepo->all()[0];
        $this->assertSame('user.updated', $log->action);
        $this->assertNotNull($log->before);
        $this->assertNotNull($log->after);
        $this->assertSame('member', $log->before['role']);
        $this->assertSame('viewer', $log->after['role']);
        $this->assertArrayNotHasKey('password_hash', $log->before);
        $this->assertArrayNotHasKey('password_hash', $log->after);
    }
}
