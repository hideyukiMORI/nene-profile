<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

interface CreateImportJobUseCaseInterface
{
    public function execute(CreateImportJobInput $input): ImportJob;
}
