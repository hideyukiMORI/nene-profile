<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateOrganizationHandler
{
    public function __construct(
        private CreateOrganizationUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = JsonRequestBodyParser::parse($request);
        $errors = [];

        $name = isset($body['name']) && is_string($body['name']) ? trim($body['name']) : '';
        $slug = isset($body['slug']) && is_string($body['slug']) ? trim($body['slug']) : '';
        $isActive = isset($body['is_active']) && is_bool($body['is_active']) ? $body['is_active'] : true;
        $customDomain = isset($body['custom_domain']) && is_string($body['custom_domain']) ? trim($body['custom_domain']) : null;

        if ($name === '') {
            $errors[] = new ValidationError('name', 'Name is required.', 'required');
        }

        if ($slug === '') {
            $errors[] = new ValidationError('slug', 'Slug is required.', 'required');
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = new ValidationError('slug', 'Slug must contain only lowercase letters, digits, and hyphens.', 'invalid_format');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $output = $this->useCase->execute(
            AuthContext::userId($request),
            new CreateOrganizationInput(
                name: $name,
                slug: $slug,
                isActive: $isActive,
                customDomain: $customDomain !== '' ? $customDomain : null,
            ),
        );

        return $this->response->create(
            [
                'id'            => $output->id,
                'name'          => $output->name,
                'slug'          => $output->slug,
                'is_active'     => $output->isActive,
                'custom_domain' => $output->customDomain,
                'created_at'    => $output->createdAt,
                'updated_at'    => $output->updatedAt,
            ],
            201,
        );
    }
}
