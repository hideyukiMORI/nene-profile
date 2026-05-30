<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateUserHandler
{
    private const MIN_PASSWORD_LENGTH = 8;

    public function __construct(
        private CreateUserUseCaseInterface $useCase,
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

        $email    = isset($body['email']) && is_string($body['email']) ? trim($body['email']) : '';
        $password = isset($body['password']) && is_string($body['password']) ? $body['password'] : '';
        $role     = isset($body['role']) && is_string($body['role']) ? $body['role'] : '';

        if ($email === '') {
            $errors[] = new ValidationError('email', 'Email is required.', 'required');
        }

        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $errors[] = new ValidationError('password', 'Password must be at least 8 characters.', 'too_short');
        }

        if ($role === '') {
            $errors[] = new ValidationError('role', 'Role is required.', 'required');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $user = $this->useCase->execute(
            $organizationId,
            AuthContext::userId($request),
            new CreateUserInput(email: $email, password: $password, role: $role),
        );

        return $this->response->create(UserSnapshot::toArray($user), 201);
    }
}
