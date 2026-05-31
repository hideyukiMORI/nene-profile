<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonRequestBodyParser;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ChangeOwnPasswordHandler
{
    public function __construct(
        private ChangeOwnPasswordUseCaseInterface $useCase,
        private ResponseFactoryInterface $responseFactory,
        private ProblemDetailsResponseFactory $problemDetails,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userId = AuthContext::userId($request);

        if ($userId === null) {
            return $this->problemDetails->create($request, 'unauthenticated', 'Unauthenticated', 401, 'Authentication required.');
        }

        $body   = JsonRequestBodyParser::parse($request);
        $errors = [];

        $currentPassword = is_string($body['current_password'] ?? null) ? (string) $body['current_password'] : null;
        $newPassword     = is_string($body['new_password'] ?? null) ? (string) $body['new_password'] : null;

        if ($currentPassword === null || $currentPassword === '') {
            $errors[] = new ValidationError('current_password', 'Current password is required.', 'required');
        }

        if ($newPassword === null || $newPassword === '') {
            $errors[] = new ValidationError('new_password', 'New password is required.', 'required');
        } elseif (strlen($newPassword) < 8) {
            $errors[] = new ValidationError('new_password', 'New password must be at least 8 characters.', 'min_length');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $this->useCase->execute(new ChangeOwnPasswordInput(
            actorUserId: $userId,
            currentPassword: (string) $currentPassword,
            newPassword: (string) $newPassword,
        ));

        return $this->responseFactory->createResponse(204);
    }
}
