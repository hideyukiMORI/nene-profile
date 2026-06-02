<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NeneProfile\Auth\AuthContext;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateOrganizationSettingsHandler
{
    public function __construct(
        private UpdateOrganizationSettingsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        $body   = JsonRequestBodyParser::parse($request);
        $errors = [];

        $defaultEncoding = array_key_exists('default_encoding', $body) && is_string($body['default_encoding'])
            ? $body['default_encoding']
            : null;

        $maxFileSizeBytes = null;
        if (array_key_exists('max_file_size_bytes', $body)) {
            if (!is_int($body['max_file_size_bytes']) || $body['max_file_size_bytes'] < 1) {
                $errors[] = new ValidationError('max_file_size_bytes', 'Must be a positive integer.', 'invalid');
            } else {
                $maxFileSizeBytes = $body['max_file_size_bytes'];
            }
        }

        $clearBearerTokenProvided = array_key_exists('clear_bearer_token', $body);
        $clearBearerToken = null;
        if ($clearBearerTokenProvided) {
            $raw = $body['clear_bearer_token'];
            if ($raw !== null && !is_string($raw)) {
                $errors[] = new ValidationError('clear_bearer_token', 'Must be a string or null.', 'invalid');
            } else {
                $clearBearerToken = is_string($raw) && $raw !== '' ? $raw : null;
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $settings = $this->useCase->execute(
            AuthContext::userId($request),
            new UpdateOrganizationSettingsInput(
                organizationId: $organizationId,
                defaultEncoding: $defaultEncoding,
                maxFileSizeBytes: $maxFileSizeBytes,
                clearBearerTokenProvided: $clearBearerTokenProvided,
                clearBearerToken: $clearBearerToken,
            ),
        );

        return $this->response->create(OrganizationSettingsSnapshot::toArray($settings));
    }
}
