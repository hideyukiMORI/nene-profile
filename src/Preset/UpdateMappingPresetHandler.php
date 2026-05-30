<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateMappingPresetHandler
{
    public function __construct(
        private UpdateMappingPresetUseCaseInterface $useCase,
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

        $body = JsonRequestBodyParser::parse($request);

        $name = isset($body['name']) && is_string($body['name']) ? trim($body['name']) : null;
        $bankLabel = isset($body['bank_label']) && is_string($body['bank_label']) ? trim($body['bank_label']) : null;

        $definition = null;
        if (isset($body['definition']) && is_array($body['definition'])) {
            /** @var array<string, mixed> $definitionArray */
            $definitionArray = $body['definition'];
            $definition = MappingDefinitionFactory::fromArray($definitionArray);
        }

        $result = $this->useCase->execute(
            AuthContext::userId($request),
            new UpdateMappingPresetInput(
                id: $id,
                organizationId: $organizationId,
                name: $name,
                bankLabel: $bankLabel,
                definition: $definition,
            ),
        );

        return $this->response->create(MappingPresetSnapshot::toArray($result['preset'], $result['version']));
    }
}
