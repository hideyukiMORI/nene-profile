<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use Nene2\Http\JsonResponseFactory;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetOrganizationSettingsHandler
{
    public function __construct(
        private GetOrganizationSettingsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        $settings = $this->useCase->execute($organizationId);

        return $this->response->create(OrganizationSettingsSnapshot::toArray($settings));
    }
}
