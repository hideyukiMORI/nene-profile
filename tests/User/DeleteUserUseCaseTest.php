<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use NeneProfile\Audit\AuditRecorder;
use NeneProfile\Tests\Audit\InMemoryAuditLogRepository;
use NeneProfile\User\CannotDeleteSelfException;
use NeneProfile\User\DeleteUserInput;
use NeneProfile\User\DeleteUserUseCase;
use NeneProfile\User\User;
use NeneProfile\User\UserNotFoundException;
use PHPUnit\Framework\TestCase;

final class DeleteUserUseCaseTest extends TestCase
{
    private InMemoryUserRepository $repo;
    private InMemoryAuditLogRepository $auditRepo;
    private DeleteUserUseCase $useCase;
    private int $userId;

    protected function setUp(): void
    {
        $this->repo      = new InMemoryUserRepository();
        $this->auditRepo = new InMemoryAuditLogRepository();
        $this->useCase   = new DeleteUserUseCase($this->repo, new AuditRecorder($this->auditRepo));

        $this->userId = $this->repo->seed(new User(
            id: 0,
            email: 'victim@example.com',
            passwordHash: 'x',
            role: 'member',
            organizationId: 7,
        ));
    }

    public function test_deletes_user(): void
    {
        $this->useCase->execute(99, new DeleteUserInput($this->userId, 7));

        $this->assertNull($this->repo->findById($this->userId));
    }

    public function test_cannot_delete_self(): void
    {
        $this->expectException(CannotDeleteSelfException::class);

        $this->useCase->execute($this->userId, new DeleteUserInput($this->userId, 7));
    }

    public function test_rejects_cross_tenant_delete(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->useCase->execute(99, new DeleteUserInput($this->userId, 999));
    }

    public function test_records_audit_with_before_snapshot(): void
    {
        $this->useCase->execute(99, new DeleteUserInput($this->userId, 7));

        $log = $this->auditRepo->all()[0];
        $this->assertSame('user.deleted', $log->action);
        $this->assertSame($this->userId, $log->entityId);
        $this->assertNotNull($log->before);
        $this->assertSame('victim@example.com', $log->before['email']);
        $this->assertNull($log->after);
        $this->assertArrayNotHasKey('password_hash', $log->before);
    }

    public function test_no_audit_when_self_delete_blocked(): void
    {
        try {
            $this->useCase->execute($this->userId, new DeleteUserInput($this->userId, 7));
        } catch (CannotDeleteSelfException) {
        }

        $this->assertCount(0, $this->auditRepo->all());
    }
}
