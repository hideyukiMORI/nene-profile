<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use Nene2\Audit\AuditEvent;
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
 *
 * Persistence is the framework's `Nene2\Audit\AuditEventRepositoryInterface`;
 * the JSON shape below is unchanged from the pre-adoption `AuditLog` response.
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
            items: array_map(static fn (AuditEvent $event): array => [
                'id'              => $event->id,
                'actor_user_id'   => $event->actorId,
                'organization_id' => $event->organizationId,
                'action'          => $event->action,
                'entity_type'     => $event->entityType,
                'entity_id'       => $event->entityId,
                'before'          => $event->before,
                'after'           => $event->after,
                'created_at'      => $event->occurredAt,
            ], $output->items),
            limit: $output->limit,
            offset: $output->offset,
            total: $output->total,
        ))->toArray());
    }
}
