<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetMappingPresetByIdHandler
{
    public function __construct(
        private GetMappingPresetByIdUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        /** @var array<string, string> $params */
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = (int) ($params['id'] ?? 0);

        $result = $this->useCase->execute(new GetMappingPresetByIdInput($id, $organizationId));

        return $this->response->create(MappingPresetSnapshot::toArray($result->preset, $result->version));
    }
}
