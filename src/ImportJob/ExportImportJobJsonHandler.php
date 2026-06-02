<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ExportImportJobJsonHandler
{
    public function __construct(
        private ExportImportJobUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        /** @var array<string, string> $params */
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = (int) ($params['id'] ?? 0);

        $output = $this->useCase->execute(new ExportImportJobInput(jobId: $id, organizationId: $organizationId));
        $job = $output->job;

        $rows = array_map(
            static fn (NormalizedTransaction $t): array => StandardTransactionSerializer::toArray($t, $job->id, $job->presetVersionId),
            $output->transactions,
        );

        return $this->response->createList($rows);
    }
}
