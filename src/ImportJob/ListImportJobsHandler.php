<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListImportJobsHandler
{
    public function __construct(
        private ListImportJobsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        $pagination = PaginationQueryParser::parse($request);
        $output = $this->useCase->execute(new ListImportJobsInput(
            organizationId: $organizationId,
            limit: $pagination->limit,
            offset: $pagination->offset,
        ));

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (ImportJob $j): array => ImportJobSnapshot::toArray($j), $output->items),
            limit: $output->limit,
            offset: $output->offset,
            total: $output->total,
        ))->toArray());
    }
}
