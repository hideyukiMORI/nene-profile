<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Http\JsonResponseFactory;
use Psr\Container\ContainerInterface;

final readonly class AuditServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                AuditLogRepositoryInterface::class,
                static function (ContainerInterface $c): AuditLogRepositoryInterface {
                    $query = $c->get(DatabaseQueryExecutorInterface::class);

                    if (!$query instanceof DatabaseQueryExecutorInterface) {
                        throw new LogicException('Database query executor service is invalid.');
                    }

                    return new PdoAuditLogRepository($query);
                },
            )
            ->set(
                AuditRecorderInterface::class,
                static function (ContainerInterface $c): AuditRecorderInterface {
                    $repo = $c->get(AuditLogRepositoryInterface::class);

                    if (!$repo instanceof AuditLogRepositoryInterface) {
                        throw new LogicException('Audit log repository service is invalid.');
                    }

                    return new AuditRecorder($repo);
                },
            )
            ->set(
                ListAuditLogsUseCaseInterface::class,
                static function (ContainerInterface $c): ListAuditLogsUseCaseInterface {
                    $repo = $c->get(AuditLogRepositoryInterface::class);

                    if (!$repo instanceof AuditLogRepositoryInterface) {
                        throw new LogicException('Audit log repository service is invalid.');
                    }

                    return new ListAuditLogsUseCase($repo);
                },
            )
            ->set(
                ListAuditLogsHandler::class,
                static function (ContainerInterface $c): ListAuditLogsHandler {
                    $uc   = $c->get(ListAuditLogsUseCaseInterface::class);
                    $json = $c->get(JsonResponseFactory::class);

                    if (!$uc instanceof ListAuditLogsUseCaseInterface) {
                        throw new LogicException('ListAuditLogs use case service is invalid.');
                    }

                    if (!$json instanceof JsonResponseFactory) {
                        throw new LogicException('JSON response factory service is invalid.');
                    }

                    return new ListAuditLogsHandler($uc, $json);
                },
            )
            ->set(
                AuditRouteRegistrar::class,
                static function (ContainerInterface $c): AuditRouteRegistrar {
                    $list = $c->get(ListAuditLogsHandler::class);

                    if (!$list instanceof ListAuditLogsHandler) {
                        throw new LogicException('ListAuditLogs handler service is invalid.');
                    }

                    return new AuditRouteRegistrar($list);
                },
            );
    }
}
