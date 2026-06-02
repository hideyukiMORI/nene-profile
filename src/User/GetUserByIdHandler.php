<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetUserByIdHandler
{
    public function __construct(
        private GetUserByIdUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        /** @var array<string, string> $params */
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = (int) ($params['id'] ?? 0);

        $user = $this->useCase->execute(new GetUserByIdInput($id, $organizationId));

        return $this->response->create(UserSnapshot::toArray($user));
    }
}
