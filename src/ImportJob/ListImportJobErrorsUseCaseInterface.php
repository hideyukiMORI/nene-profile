<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

interface ListImportJobErrorsUseCaseInterface
{
    public function execute(ListImportJobErrorsInput $input): ListImportJobErrorsOutput;
}
