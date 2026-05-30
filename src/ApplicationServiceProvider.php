<?php

declare(strict_types=1);

namespace NeneProfile;

use LogicException;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\DomainExceptionHandlerInterface;
use Nene2\Http\RequestScopedHolder;
use NeneProfile\Auth\AuthRouteRegistrar;
use NeneProfile\Auth\AuthServiceProvider;
use NeneProfile\Auth\InvalidCredentialsExceptionHandler;
use NeneProfile\Organization\OrganizationNotFoundExceptionHandler;
use NeneProfile\Organization\OrganizationRouteRegistrar;
use NeneProfile\Organization\OrganizationServiceProvider;
use NeneProfile\Organization\OrganizationSlugConflictExceptionHandler;
use Psr\Container\ContainerInterface;

final readonly class ApplicationServiceProvider implements ServiceProviderInterface
{
    public const ROUTE_REGISTRARS = 'nene-profile.route_registrars';
    public const EXCEPTION_HANDLERS = 'nene-profile.exception_handlers';

    /** Container key for the shared RequestScopedHolder<int> that carries org_id. */
    public const ORG_ID_HOLDER = 'nene-profile.org_id_holder';

    public function register(ContainerBuilder $builder): void
    {
        $builder->set(
            self::ORG_ID_HOLDER,
            static function (): RequestScopedHolder {
                /** @var RequestScopedHolder<int> */
                return new RequestScopedHolder();
            },
        );

        $builder
            ->addProvider(new OrganizationServiceProvider())
            ->addProvider(new AuthServiceProvider());

        $builder
            ->set(
                self::ROUTE_REGISTRARS,
                static function (ContainerInterface $c): array {
                    $auth         = $c->get(AuthRouteRegistrar::class);
                    $organization = $c->get(OrganizationRouteRegistrar::class);

                    if (!$auth instanceof AuthRouteRegistrar) {
                        throw new LogicException('AuthRouteRegistrar service is invalid.');
                    }

                    if (!$organization instanceof OrganizationRouteRegistrar) {
                        throw new LogicException('OrganizationRouteRegistrar service is invalid.');
                    }

                    return [$auth, $organization];
                },
            )
            ->set(
                self::EXCEPTION_HANDLERS,
                static function (ContainerInterface $c): array {
                    $invalidCredentials        = $c->get(InvalidCredentialsExceptionHandler::class);
                    $organizationNotFound      = $c->get(OrganizationNotFoundExceptionHandler::class);
                    $organizationSlugConflict  = $c->get(OrganizationSlugConflictExceptionHandler::class);

                    foreach ([
                        $invalidCredentials,
                        $organizationNotFound,
                        $organizationSlugConflict,
                    ] as $handler) {
                        if (!$handler instanceof DomainExceptionHandlerInterface) {
                            throw new LogicException('Exception handler service is invalid.');
                        }
                    }

                    return [
                        $invalidCredentials,
                        $organizationNotFound,
                        $organizationSlugConflict,
                    ];
                },
            );
    }
}
