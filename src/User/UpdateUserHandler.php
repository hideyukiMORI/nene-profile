<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateUserHandler
{
    public function __construct(
        private UpdateUserUseCaseInterface $useCase,
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

        $role     = isset($body['role']) && is_string($body['role']) ? $body['role'] : null;
        $status   = isset($body['status']) && is_string($body['status']) ? $body['status'] : null;
        $password = isset($body['password']) && is_string($body['password']) ? $body['password'] : null;

        $user = $this->useCase->execute(
            AuthContext::userId($request),
            new UpdateUserInput(
                id: $id,
                organizationId: $organizationId,
                role: $role,
                status: $status,
                password: $password,
            ),
        );

        return $this->response->create(UserSnapshot::toArray($user));
    }
}
