<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetImportJobHandler
{
    public function __construct(
        private GetImportJobUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        /** @var array<string, string> $params */
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = (int) ($params['id'] ?? 0);

        $job = $this->useCase->execute(new GetImportJobInput(id: $id, organizationId: $organizationId));

        return $this->response->create(ImportJobSnapshot::toArray($job));
    }
}
