<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

interface ChangeOwnPasswordUseCaseInterface
{
    public function execute(ChangeOwnPasswordInput $input): void;
}
