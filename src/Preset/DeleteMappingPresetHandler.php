<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Routing\Router;
use NeneProfile\Auth\AuthContext;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DeleteMappingPresetHandler
{
    public function __construct(
        private DeleteMappingPresetUseCaseInterface $useCase,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        /** @var array<string, string> $params */
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = (int) ($params['id'] ?? 0);

        $this->useCase->execute(
            AuthContext::userId($request),
            new DeleteMappingPresetInput($id, $organizationId),
        );

        return $this->responseFactory->createResponse(204);
    }
}
