<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListMappingPresetsHandler
{
    public function __construct(
        private ListMappingPresetsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
        private ProblemDetailsResponseFactory $problemDetails,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = AuthContext::resolvedOrganizationId($request);

        if ($organizationId === null) {
            return $this->problemDetails->create($request, 'org-not-resolved', 'Organization Required', 400, 'This action requires an organization context.');
        }

        $pagination = PaginationQueryParser::parse($request);

        $output = $this->useCase->execute(new ListMappingPresetsInput(
            organizationId: $organizationId,
            limit: $pagination->limit,
            offset: $pagination->offset,
        ));

        return $this->response->create([
            'items'  => array_map(static fn (MappingPreset $p): array => MappingPresetSnapshot::toArray($p), $output->items),
            'total'  => $output->total,
            'limit'  => $output->limit,
            'offset' => $output->offset,
        ]);
    }
}
