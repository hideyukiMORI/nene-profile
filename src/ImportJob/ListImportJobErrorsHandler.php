<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use Nene2\Routing\Router;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListImportJobErrorsHandler
{
    public function __construct(
        private ListImportJobErrorsUseCaseInterface $useCase,
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

        /** @var array<string, string> $params */
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = (int) ($params['id'] ?? 0);

        $pagination = PaginationQueryParser::parse($request);
        $output = $this->useCase->execute(new ListImportJobErrorsInput(
            jobId: $id,
            organizationId: $organizationId,
            limit: $pagination->limit,
            offset: $pagination->offset,
        ));

        return $this->response->create((new PaginationResponse(
            items: array_map(
                static fn (ImportJobError $e): array => [
                    'id'             => $e->id,
                    'import_job_id'  => $e->importJobId,
                    'raw_row_number' => $e->rawRowNumber,
                    'message'        => $e->message,
                    'raw_snippet'    => $e->rawSnippet,
                ],
                $output->items,
            ),
            limit: $output->limit,
            offset: $output->offset,
            total: $output->total,
        ))->toArray());
    }
}
