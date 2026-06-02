<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Routing\Router;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class ExportImportJobCsvHandler
{
    public function __construct(
        private ExportImportJobUseCaseInterface $useCase,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
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

        $csv = CsvExporter::export($output->transactions, $job->id, $job->presetVersionId);

        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->withHeader('Content-Disposition', sprintf('attachment; filename="import-job-%d.csv"', $job->id))
            ->withBody($this->streamFactory->createStream($csv));
    }
}
