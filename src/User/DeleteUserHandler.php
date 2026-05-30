<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DeleteUserHandler
{
    public function __construct(
        private DeleteUserUseCaseInterface $useCase,
        private ResponseFactoryInterface $responseFactory,
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

        $this->useCase->execute(
            AuthContext::userId($request),
            new DeleteUserInput($id, $organizationId),
        );

        return $this->responseFactory->createResponse(204);
    }
}
