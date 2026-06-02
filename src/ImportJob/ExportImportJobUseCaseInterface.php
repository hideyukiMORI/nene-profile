<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

interface ExportImportJobUseCaseInterface
{
    public function execute(ExportImportJobInput $input): ExportImportJobOutput;
}
