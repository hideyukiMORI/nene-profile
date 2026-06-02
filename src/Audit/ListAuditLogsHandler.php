<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use NeneProfile\Auth\AuthContext;
use NeneProfile\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * `GET /admin/audit-logs`
 *
 * - superadmin: sees all logs across organizations (organizationId = null)
 * - admin / member: sees only their organization's logs
 * Capability: ManageUsers (gated in CapabilityResolver)
 */
final readonly class ListAuditLogsHandler
{
    public function __construct(
        private ListAuditLogsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);

        $role           = AuthContext::role($request);
        $organizationId = ($role === Role::Superadmin) ? null : AuthContext::organizationId($request);

        $output = $this->useCase->execute(new ListAuditLogsInput(
            organizationId: $organizationId,
            limit: $pagination->limit,
            offset: $pagination->offset,
        ));

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (AuditLog $log): array => [
                'id'              => $log->id,
                'actor_user_id'   => $log->actorUserId,
                'organization_id' => $log->organizationId,
                'action'          => $log->action,
                'entity_type'     => $log->entityType,
                'entity_id'       => $log->entityId,
                'before'          => $log->before,
                'after'           => $log->after,
                'created_at'      => $log->createdAt,
            ], $output->items),
            limit: $output->limit,
            offset: $output->offset,
            total: $output->total,
        ))->toArray());
    }
}
