<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NeneProfile\Auth\AuthContext;
use NeneProfile\Organization\Resolution\OrgScope;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

final readonly class CreateImportJobHandler
{
    public function __construct(
        private CreateImportJobUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = OrgScope::requireId($request);

        $files = $request->getUploadedFiles();
        $file = $files['file'] ?? null;

        if (!$file instanceof UploadedFileInterface) {
            throw new ValidationException([new ValidationError('file', 'A CSV file upload is required.', 'required')]);
        }

        // The per-organization size limit is enforced in the use case
        // (CreateImportJobUseCase) against organization_settings, not here.
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
