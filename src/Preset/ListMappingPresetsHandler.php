<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListMappingPresetsHandler
{
    public function __construct(
        private ListMappingPresetsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        $pagination = PaginationQueryParser::parse($request);

        $output = $this->useCase->execute(new ListMappingPresetsInput(
            organizationId: $organizationId,
            limit: $pagination->limit,
            offset: $pagination->offset,
        ));

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (MappingPreset $p): array => MappingPresetSnapshot::toArray($p), $output->items),
            limit: $output->limit,
            offset: $output->offset,
            total: $output->total,
        ))->toArray());
    }
}
