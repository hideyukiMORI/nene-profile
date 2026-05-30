<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use Nene2\Http\JsonResponseFactory;
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
    private const MAX_LIMIT = 100;
    private const DEFAULT_LIMIT = 20;

    public function __construct(
        private ListAuditLogsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query  = $request->getQueryParams();
        $limit  = min(self::MAX_LIMIT, max(1, (int) ($query['limit'] ?? self::DEFAULT_LIMIT)));
        $offset = max(0, (int) ($query['offset'] ?? 0));

        $role           = AuthContext::role($request);
        $organizationId = ($role === Role::Superadmin) ? null : AuthContext::organizationId($request);

        $output = $this->useCase->execute(new ListAuditLogsInput(
            organizationId: $organizationId,
            limit: $limit,
            offset: $offset,
        ));

        return $this->response->create([
            'items'  => array_map(static fn (AuditLog $log): array => [
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
            'total'  => $output->total,
            'limit'  => $output->limit,
            'offset' => $output->offset,
        ]);
    }
}
