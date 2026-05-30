<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateMappingPresetHandler
{
    public function __construct(
        private CreateMappingPresetUseCaseInterface $useCase,
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

        $body   = JsonRequestBodyParser::parse($request);
        $errors = [];

        $name = isset($body['name']) && is_string($body['name']) ? trim($body['name']) : '';
        $bankLabel = isset($body['bank_label']) && is_string($body['bank_label']) ? trim($body['bank_label']) : '';

        if ($name === '') {
            $errors[] = new ValidationError('name', 'Name is required.', 'required');
        }

        if ($bankLabel === '') {
            $errors[] = new ValidationError('bank_label', 'Bank label is required.', 'required');
        }

        if (!isset($body['definition']) || !is_array($body['definition'])) {
            $errors[] = new ValidationError('definition', 'Definition is required.', 'required');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        /** @var array<string, mixed> $definitionArray */
        $definitionArray = $body['definition'];
        $definition = MappingDefinitionFactory::fromArray($definitionArray);

        $result = $this->useCase->execute(
            AuthContext::userId($request),
            new CreateMappingPresetInput(
                organizationId: $organizationId,
                name: $name,
                bankLabel: $bankLabel,
                definition: $definition,
            ),
        );

        return $this->response->create(
            MappingPresetSnapshot::toArray($result['preset'], $result['version']),
            201,
        );
    }
}
