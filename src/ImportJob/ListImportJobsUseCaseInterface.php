<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

interface ListImportJobsUseCaseInterface
{
    public function execute(ListImportJobsInput $input): ListImportJobsOutput;
}
