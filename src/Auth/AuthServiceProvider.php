<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

use LogicException;
use Nene2\Auth\TokenIssuerInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Psr\Container\ContainerInterface;

final readonly class AuthServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                UserRepositoryInterface::class,
                static function (ContainerInterface $c): UserRepositoryInterface {
                    $query = $c->get(DatabaseQueryExecutorInterface::class);

                    if (!$query instanceof DatabaseQueryExecutorInterface) {
                        throw new LogicException('Database query executor service is invalid.');
                    }

                    return new PdoUserRepository($query);
                },
            )
            ->set(
                LoginUseCaseInterface::class,
                static function (ContainerInterface $c): LoginUseCaseInterface {
                    $users  = $c->get(UserRepositoryInterface::class);
                    $issuer = $c->get('nene-profile.token_issuer');

                    if (!$users instanceof UserRepositoryInterface) {
                        throw new LogicException('UserRepositoryInterface service is invalid.');
                    }

                    if (!$issuer instanceof TokenIssuerInterface) {
                        throw new LogicException('TokenIssuerInterface service is invalid.');
                    }

                    return new LoginUseCase($users, $issuer);
                },
            )
            ->set(
                LoginHandler::class,
                static function (ContainerInterface $c): LoginHandler {
                    $uc   = $c->get(LoginUseCaseInterface::class);
                    $json = $c->get(JsonResponseFactory::class);

                    if (!$uc instanceof LoginUseCaseInterface) {
                        throw new LogicException('LoginUseCase service is invalid.');
                    }

                    if (!$json instanceof JsonResponseFactory) {
                        throw new LogicException('JsonResponseFactory service is invalid.');
                    }

                    return new LoginHandler($uc, $json);
                },
            )
            ->set(
                InvalidCredentialsExceptionHandler::class,
                static function (ContainerInterface $c): InvalidCredentialsExceptionHandler {
                    $pd = $c->get(ProblemDetailsResponseFactory::class);

                    if (!$pd instanceof ProblemDetailsResponseFactory) {
                        throw new LogicException('ProblemDetailsResponseFactory service is invalid.');
                    }

                    return new InvalidCredentialsExceptionHandler($pd);
                },
            )
            ->set(
                AuthRouteRegistrar::class,
                static function (ContainerInterface $c): AuthRouteRegistrar {
                    $loginHandler = $c->get(LoginHandler::class);

                    if (!$loginHandler instanceof LoginHandler) {
                        throw new LogicException('LoginHandler service is invalid.');
                    }

                    return new AuthRouteRegistrar($loginHandler);
                },
            );
    }
}
