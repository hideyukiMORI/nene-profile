<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetUserByIdHandler
{
    public function __construct(
        private GetUserByIdUseCaseInterface $useCase,
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

        $user = $this->useCase->execute(new GetUserByIdInput($id, $organizationId));

        return $this->response->create(UserSnapshot::toArray($user));
    }
}
