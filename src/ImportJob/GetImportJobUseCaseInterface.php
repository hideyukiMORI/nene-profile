<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

interface GetImportJobUseCaseInterface
{
    public function execute(GetImportJobInput $input): ImportJob;
}
