<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class MappingPresetRouteRegistrar
{
    public function __construct(
        private ListMappingPresetsHandler $listHandler,
        private GetMappingPresetByIdHandler $getHandler,
        private CreateMappingPresetHandler $createHandler,
        private UpdateMappingPresetHandler $updateHandler,
        private DeleteMappingPresetHandler $deleteHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list   = $this->listHandler;
        $get    = $this->getHandler;
        $create = $this->createHandler;
        $update = $this->updateHandler;
        $delete = $this->deleteHandler;

        $router->get('/admin/mapping-presets', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->get('/admin/mapping-presets/{id}', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->post('/admin/mapping-presets', static fn (ServerRequestInterface $r) => $create->handle($r));
        $router->patch('/admin/mapping-presets/{id}', static fn (ServerRequestInterface $r) => $update->handle($r));
        $router->delete('/admin/mapping-presets/{id}', static fn (ServerRequestInterface $r) => $delete->handle($r));
    }
}
