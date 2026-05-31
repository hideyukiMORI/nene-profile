<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateOrganizationHandler
{
    public function __construct(
        private UpdateOrganizationUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<string, string> $params */
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = (int) ($params['id'] ?? 0);

        $body   = JsonRequestBodyParser::parse($request);
        $errors = [];

        $name = array_key_exists('name', $body)
            ? (is_string($body['name']) ? trim($body['name']) : null)
            : null;

        $slug = array_key_exists('slug', $body)
            ? (is_string($body['slug']) ? trim($body['slug']) : null)
            : null;

        $isActive = array_key_exists('is_active', $body)
            ? (is_bool($body['is_active']) ? $body['is_active'] : null)
            : null;

        $customDomainProvided = array_key_exists('custom_domain', $body);
        $customDomain = null;
        if ($customDomainProvided) {
            $raw = $body['custom_domain'];
            $customDomain = (is_string($raw) && $raw !== '') ? $raw : null;
        }

        if ($name !== null && $name === '') {
            $errors[] = new ValidationError('name', 'Name must not be empty.', 'invalid');
        }

        if ($slug !== null) {
            if ($slug === '') {
                $errors[] = new ValidationError('slug', 'Slug must not be empty.', 'invalid');
            } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                $errors[] = new ValidationError('slug', 'Slug must contain only lowercase letters, digits, and hyphens.', 'invalid_format');
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $org = $this->useCase->execute(
            AuthContext::userId($request),
            new UpdateOrganizationInput(
                id: $id,
                name: $name,
                slug: $slug,
                isActive: $isActive,
                customDomain: $customDomain,
                customDomainProvided: $customDomainProvided,
            ),
        );

        return $this->response->create([
            'id'            => $org->id,
            'name'          => $org->name,
            'slug'          => $org->slug,
            'is_active'     => $org->isActive,
            'custom_domain' => $org->customDomain,
            'created_at'    => $org->createdAt,
            'updated_at'    => $org->updatedAt,
        ]);
    }
}
