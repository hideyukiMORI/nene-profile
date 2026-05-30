<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuditRouteRegistrar
{
    public function __construct(
        private ListAuditLogsHandler $listHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list = $this->listHandler;

        $router->get('/admin/audit-logs', static fn (ServerRequestInterface $r) => $list->handle($r));
    }
}
