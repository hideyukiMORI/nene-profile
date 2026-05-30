<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListImportJobsHandler
{
    public function __construct(
        private ListImportJobsUseCase $useCase,
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
        $result = $this->useCase->execute($organizationId, $pagination->limit, $pagination->offset);

        return $this->response->create([
            'items'  => array_map(static fn (ImportJob $j): array => ImportJobSnapshot::toArray($j), $result['items']),
            'total'  => $result['total'],
            'limit'  => $pagination->limit,
            'offset' => $pagination->offset,
        ]);
    }
}
