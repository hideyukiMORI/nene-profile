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
use NeneProfile\Auth\InvalidCurrentPasswordExceptionHandler;
use NeneProfile\ImportJob\ImportFileTooLargeExceptionHandler;
use NeneProfile\ImportJob\ImportJobNotFoundExceptionHandler;
use NeneProfile\ImportJob\ImportJobRouteRegistrar;
use NeneProfile\ImportJob\ImportJobServiceProvider;
use NeneProfile\Organization\OrganizationNotFoundExceptionHandler;
use NeneProfile\Organization\OrganizationNotResolvedExceptionHandler;
use NeneProfile\Organization\OrganizationRouteRegistrar;
use NeneProfile\Organization\OrganizationServiceProvider;
use NeneProfile\Organization\OrganizationSlugConflictExceptionHandler;
use NeneProfile\OrgSettings\EncodingNotSupportedExceptionHandler;
use NeneProfile\OrgSettings\OrganizationSettingsRouteRegistrar;
use NeneProfile\OrgSettings\OrganizationSettingsServiceProvider;
use NeneProfile\Preset\InvalidMappingDefinitionExceptionHandler;
use NeneProfile\Preset\MappingPresetNotFoundExceptionHandler;
use NeneProfile\Preset\MappingPresetRouteRegistrar;
use NeneProfile\Preset\MappingPresetServiceProvider;
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
            ->addProvider(new OrganizationSettingsServiceProvider())
            ->addProvider(new MappingPresetServiceProvider())
            ->addProvider(new ImportJobServiceProvider())
            ->addProvider(new UserServiceProvider())
            ->addProvider(new AuthServiceProvider());

        $builder
            ->set(
                self::ROUTE_REGISTRARS,
                static function (ContainerInterface $c): array {
                    $auth         = $c->get(AuthRouteRegistrar::class);
                    $organization = $c->get(OrganizationRouteRegistrar::class);
                    $orgSettings  = $c->get(OrganizationSettingsRouteRegistrar::class);
                    $preset       = $c->get(MappingPresetRouteRegistrar::class);
                    $importJob    = $c->get(ImportJobRouteRegistrar::class);
                    $user         = $c->get(UserRouteRegistrar::class);
                    $audit        = $c->get(AuditRouteRegistrar::class);

                    if (!$auth instanceof AuthRouteRegistrar) {
                        throw new LogicException('AuthRouteRegistrar service is invalid.');
                    }

                    if (!$organization instanceof OrganizationRouteRegistrar) {
                        throw new LogicException('OrganizationRouteRegistrar service is invalid.');
                    }

                    if (!$orgSettings instanceof OrganizationSettingsRouteRegistrar) {
                        throw new LogicException('OrganizationSettingsRouteRegistrar service is invalid.');
                    }

                    if (!$preset instanceof MappingPresetRouteRegistrar) {
                        throw new LogicException('MappingPresetRouteRegistrar service is invalid.');
                    }

                    if (!$importJob instanceof ImportJobRouteRegistrar) {
                        throw new LogicException('ImportJobRouteRegistrar service is invalid.');
                    }

                    if (!$user instanceof UserRouteRegistrar) {
                        throw new LogicException('UserRouteRegistrar service is invalid.');
                    }

                    if (!$audit instanceof AuditRouteRegistrar) {
                        throw new LogicException('AuditRouteRegistrar service is invalid.');
                    }

                    return [$auth, $organization, $orgSettings, $preset, $importJob, $user, $audit];
                },
            )
            ->set(
                self::EXCEPTION_HANDLERS,
                static function (ContainerInterface $c): array {
                    $invalidCredentials        = $c->get(InvalidCredentialsExceptionHandler::class);
                    $invalidCurrentPassword    = $c->get(InvalidCurrentPasswordExceptionHandler::class);
                    $organizationNotFound      = $c->get(OrganizationNotFoundExceptionHandler::class);
                    $organizationNotResolved   = $c->get(OrganizationNotResolvedExceptionHandler::class);
                    $organizationSlugConflict  = $c->get(OrganizationSlugConflictExceptionHandler::class);
                    $userNotFound              = $c->get(UserNotFoundExceptionHandler::class);
                    $userEmailConflict         = $c->get(UserEmailConflictExceptionHandler::class);
                    $roleNotAssignable         = $c->get(RoleNotAssignableExceptionHandler::class);
                    $cannotDeleteSelf          = $c->get(CannotDeleteSelfExceptionHandler::class);
                    $encodingNotSupported      = $c->get(EncodingNotSupportedExceptionHandler::class);
                    $presetNotFound            = $c->get(MappingPresetNotFoundExceptionHandler::class);
                    $invalidDefinition         = $c->get(InvalidMappingDefinitionExceptionHandler::class);
                    $importJobNotFound         = $c->get(ImportJobNotFoundExceptionHandler::class);
                    $importFileTooLarge        = $c->get(ImportFileTooLargeExceptionHandler::class);

                    $handlers = [
                        $invalidCredentials,
                        $invalidCurrentPassword,
                        $organizationNotFound,
                        $organizationNotResolved,
                        $organizationSlugConflict,
                        $userNotFound,
                        $userEmailConflict,
                        $roleNotAssignable,
                        $cannotDeleteSelf,
                        $encodingNotSupported,
                        $presetNotFound,
                        $invalidDefinition,
                        $importJobNotFound,
                        $importFileTooLarge,
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
