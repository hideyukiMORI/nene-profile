<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NeneProfile\Auth\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

final readonly class CreateImportJobHandler
{
    private const MAX_FILE_BYTES = 10_485_760; // 10 MiB

    public function __construct(
        private CreateImportJobUseCaseInterface $useCase,
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

        $files = $request->getUploadedFiles();
        $file = $files['file'] ?? null;

        if (!$file instanceof UploadedFileInterface) {
            throw new ValidationException([new ValidationError('file', 'A CSV file upload is required.', 'required')]);
        }

        $size = $file->getSize();
        if ($size !== null && $size > self::MAX_FILE_BYTES) {
            return $this->problemDetails->create($request, 'file-too-large', 'File Too Large', 413, 'The uploaded file exceeds the 10 MiB limit.');
        }

        $parsedBody = $request->getParsedBody();
        $presetId = is_array($parsedBody) && isset($parsedBody['preset_id']) && is_numeric($parsedBody['preset_id'])
            ? (int) $parsedBody['preset_id']
            : 0;

        if ($presetId < 1) {
            throw new ValidationException([new ValidationError('preset_id', 'A valid preset_id is required.', 'required')]);
        }

        $contents = (string) $file->getStream();

        $job = $this->useCase->execute(new CreateImportJobInput(
            organizationId: $organizationId,
            actorUserId: AuthContext::userId($request),
            presetId: $presetId,
            originalFilename: $file->getClientFilename() ?? 'upload.csv',
            fileContents: $contents,
        ));

        return $this->response->create(ImportJobSnapshot::toArray($job), 201);
    }
}
