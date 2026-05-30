<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final class ImportJobSnapshot
{
    /** @return array<string, mixed> */
    public static function toArray(ImportJob $job): array
    {
        return [
            'id'                 => $job->id,
            'organization_id'    => $job->organizationId,
            'preset_version_id'  => $job->presetVersionId,
            'original_filename'  => $job->originalFilename,
            'original_file_hash' => $job->originalFileHash,
            'status'             => $job->status,
            'row_count'          => $job->rowCount,
            'error_count'        => $job->errorCount,
            'started_at'         => $job->startedAt,
            'completed_at'       => $job->completedAt,
            'created_at'         => $job->createdAt,
        ];
    }
}
