<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OrganizationSettingsRouteRegistrar
{
    public function __construct(
        private GetOrganizationSettingsHandler $getHandler,
        private UpdateOrganizationSettingsHandler $updateHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $get    = $this->getHandler;
        $update = $this->updateHandler;

        $router->get('/admin/organization-settings', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->patch('/admin/organization-settings', static fn (ServerRequestInterface $r) => $update->handle($r));
    }
}
