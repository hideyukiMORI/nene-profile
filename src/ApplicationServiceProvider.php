<?php

declare(strict_types=1);

namespace NeneProfile;

use LogicException;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\DomainExceptionHandlerInterface;
use Nene2\Http\RequestScopedHolder;
use NeneProfile\Audit\AuditRouteRegistrar;
use NeneProfile\Audit\AuditServiceProvider;
use NeneProfile\Auth\AuthRouteRegistrar;
use NeneProfile\Auth\AuthServiceProvider;
use NeneProfile\Auth\InvalidCredentialsExceptionHandler;
use NeneProfile\Organization\OrganizationNotFoundExceptionHandler;
use NeneProfile\Organization\OrganizationRouteRegistrar;
use NeneProfile\Organization\OrganizationServiceProvider;
use NeneProfile\Organization\OrganizationSlugConflictExceptionHandler;
use NeneProfile\User\CannotDeleteSelfExceptionHandler;
use NeneProfile\User\RoleNotAssignableExceptionHandler;
use NeneProfile\User\UserEmailConflictExceptionHandler;
use NeneProfile\User\UserNotFoundExceptionHandler;
use NeneProfile\User\UserRouteRegistrar;
use NeneProfile\User\UserServiceProvider;
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
            ->addProvider(new AuditServiceProvider())
            ->addProvider(new OrganizationServiceProvider())
            ->addProvider(new UserServiceProvider())
            ->addProvider(new AuthServiceProvider());

        $builder
            ->set(
                self::ROUTE_REGISTRARS,
                static function (ContainerInterface $c): array {
                    $auth         = $c->get(AuthRouteRegistrar::class);
                    $organization = $c->get(OrganizationRouteRegistrar::class);
                    $user         = $c->get(UserRouteRegistrar::class);
                    $audit        = $c->get(AuditRouteRegistrar::class);

                    if (!$auth instanceof AuthRouteRegistrar) {
                        throw new LogicException('AuthRouteRegistrar service is invalid.');
                    }

                    if (!$organization instanceof OrganizationRouteRegistrar) {
                        throw new LogicException('OrganizationRouteRegistrar service is invalid.');
                    }

                    if (!$user instanceof UserRouteRegistrar) {
                        throw new LogicException('UserRouteRegistrar service is invalid.');
                    }

                    if (!$audit instanceof AuditRouteRegistrar) {
                        throw new LogicException('AuditRouteRegistrar service is invalid.');
                    }

                    return [$auth, $organization, $user, $audit];
                },
            )
            ->set(
                self::EXCEPTION_HANDLERS,
                static function (ContainerInterface $c): array {
                    $invalidCredentials        = $c->get(InvalidCredentialsExceptionHandler::class);
                    $organizationNotFound      = $c->get(OrganizationNotFoundExceptionHandler::class);
                    $organizationSlugConflict  = $c->get(OrganizationSlugConflictExceptionHandler::class);
                    $userNotFound              = $c->get(UserNotFoundExceptionHandler::class);
                    $userEmailConflict         = $c->get(UserEmailConflictExceptionHandler::class);
                    $roleNotAssignable         = $c->get(RoleNotAssignableExceptionHandler::class);
                    $cannotDeleteSelf          = $c->get(CannotDeleteSelfExceptionHandler::class);

                    $handlers = [
                        $invalidCredentials,
                        $organizationNotFound,
                        $organizationSlugConflict,
                        $userNotFound,
                        $userEmailConflict,
                        $roleNotAssignable,
                        $cannotDeleteSelf,
                    ];

                    foreach ($handlers as $handler) {
                        if (!$handler instanceof DomainExceptionHandlerInterface) {
                            throw new LogicException('Exception handler service is invalid.');
                        }
                    }

                    return $handlers;
                },
            );
    }
}
