<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use NeneProfile\Audit\AuditRecorderInterface;
use Psr\Container\ContainerInterface;

final readonly class OrganizationSettingsServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                OrganizationSettingsRepositoryInterface::class,
                static function (ContainerInterface $c): OrganizationSettingsRepositoryInterface {
                    $query = $c->get(DatabaseQueryExecutorInterface::class);

                    if (!$query instanceof DatabaseQueryExecutorInterface) {
                        throw new LogicException('Database query executor service is invalid.');
                    }

                    return new PdoOrganizationSettingsRepository($query);
                },
            )
            ->set(
                GetOrganizationSettingsUseCaseInterface::class,
                static fn (ContainerInterface $c): GetOrganizationSettingsUseCaseInterface => new GetOrganizationSettingsUseCase(
                    self::repo($c),
                ),
            )
            ->set(
                UpdateOrganizationSettingsUseCaseInterface::class,
                static fn (ContainerInterface $c): UpdateOrganizationSettingsUseCaseInterface => new UpdateOrganizationSettingsUseCase(
                    self::repo($c),
                    self::get($c, AuditRecorderInterface::class),
                ),
            )
            ->set(
                GetOrganizationSettingsHandler::class,
                static fn (ContainerInterface $c): GetOrganizationSettingsHandler => new GetOrganizationSettingsHandler(
                    self::get($c, GetOrganizationSettingsUseCaseInterface::class),
                    self::get($c, JsonResponseFactory::class),
                    self::get($c, ProblemDetailsResponseFactory::class),
                ),
            )
            ->set(
                UpdateOrganizationSettingsHandler::class,
                static fn (ContainerInterface $c): UpdateOrganizationSettingsHandler => new UpdateOrganizationSettingsHandler(
                    self::get($c, UpdateOrganizationSettingsUseCaseInterface::class),
                    self::get($c, JsonResponseFactory::class),
                    self::get($c, ProblemDetailsResponseFactory::class),
                ),
            )
            ->set(
                EncodingNotSupportedExceptionHandler::class,
                static fn (ContainerInterface $c): EncodingNotSupportedExceptionHandler => new EncodingNotSupportedExceptionHandler(
                    self::get($c, ProblemDetailsResponseFactory::class),
                ),
            )
            ->set(
                OrganizationSettingsRouteRegistrar::class,
                static fn (ContainerInterface $c): OrganizationSettingsRouteRegistrar => new OrganizationSettingsRouteRegistrar(
                    self::get($c, GetOrganizationSettingsHandler::class),
                    self::get($c, UpdateOrganizationSettingsHandler::class),
                ),
            );
    }

    private static function repo(ContainerInterface $c): OrganizationSettingsRepositoryInterface
    {
        return self::get($c, OrganizationSettingsRepositoryInterface::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    private static function get(ContainerInterface $c, string $id): object
    {
        $service = $c->get($id);

        if (!$service instanceof $id) {
            throw new LogicException(sprintf('Service %s is invalid.', $id));
        }

        return $service;
    }
}
