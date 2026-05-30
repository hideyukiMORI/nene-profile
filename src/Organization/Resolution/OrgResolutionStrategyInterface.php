<?php

declare(strict_types=1);

namespace NeneProfile\Organization\Resolution;

use Psr\Http\Message\ServerRequestInterface;

interface OrgResolutionStrategyInterface
{
    public function resolve(ServerRequestInterface $request): ?string;
}
