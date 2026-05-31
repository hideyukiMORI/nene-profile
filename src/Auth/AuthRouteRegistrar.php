<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuthRouteRegistrar
{
    public function __construct(
        private LoginHandler $loginHandler,
        private ChangeOwnPasswordHandler $changePasswordHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $loginHandler          = $this->loginHandler;
        $changePasswordHandler = $this->changePasswordHandler;

        $router->post('/admin/auth/login', static fn (ServerRequestInterface $r) => $loginHandler->handle($r));
        $router->patch('/admin/auth/me/password', static fn (ServerRequestInterface $r) => $changePasswordHandler->handle($r));
    }
}
