<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

interface LoginUseCaseInterface
{
    public function execute(LoginInput $input): LoginOutput;
}
