<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetOrganizationSettingsHandler
{
    public function __construct(
        private GetOrganizationSettingsUseCaseInterface $useCase,
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

        $settings = $this->useCase->execute($organizationId);

        return $this->response->create(OrganizationSettingsSnapshot::toArray($settings));
    }
}
