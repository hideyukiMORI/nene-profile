<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserRouteRegistrar
{
    public function __construct(
        private ListUsersHandler $listHandler,
        private GetUserByIdHandler $getHandler,
        private CreateUserHandler $createHandler,
        private UpdateUserHandler $updateHandler,
        private DeleteUserHandler $deleteHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list   = $this->listHandler;
        $get    = $this->getHandler;
        $create = $this->createHandler;
        $update = $this->updateHandler;
        $delete = $this->deleteHandler;

        $router->get('/admin/users', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->get('/admin/users/{id}', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->post('/admin/users', static fn (ServerRequestInterface $r) => $create->handle($r));
        $router->patch('/admin/users/{id}', static fn (ServerRequestInterface $r) => $update->handle($r));
        $router->delete('/admin/users/{id}', static fn (ServerRequestInterface $r) => $delete->handle($r));
    }
}
