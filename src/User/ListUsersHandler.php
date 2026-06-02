<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListUsersHandler
{
    public function __construct(
        private ListUsersUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        $pagination = PaginationQueryParser::parse($request);

        $output = $this->useCase->execute(new ListUsersInput(
            organizationId: $organizationId,
            limit: $pagination->limit,
            offset: $pagination->offset,
        ));

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (User $u): array => UserSnapshot::toArray($u), $output->items),
            limit: $output->limit,
            offset: $output->offset,
            total: $output->total,
        ))->toArray());
    }
}
