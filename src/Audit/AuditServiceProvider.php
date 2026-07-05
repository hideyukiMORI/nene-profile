<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use LogicException;
use Nene2\Audit\AuditEventRepositoryInterface;
use Nene2\Audit\AuditPayloadMode;
use Nene2\Audit\AuditRecorderFactory;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Audit\AuditTableConfig;
use Nene2\Audit\PdoAuditEventRepository;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Http\ClockInterface;
use Nene2\Http\JsonResponseFactory;
use Psr\Container\ContainerInterface;

/**
 * Wires the framework audit module (`Nene2\Audit`, NENE2 #1495) onto profile's
 * existing `audit_logs` table — no re-migration.
 *
 * {@see AuditTableConfig} is the whole product/framework seam: it points the
 * framework repository and recorder at profile's physical columns (int
 * autoincrement id, `actor_user_id`, `created_at`, `before_json`/`after_json`;
 * the table has no metadata column). Writes go through the framework's
 * transaction-atomic {@see AuditRecorderFactoryInterface::forExecutor()}; reads
 * go through {@see ListAuditLogsUseCase}, which maps profile's org-scoping
 * (superadmin cross-org vs. tenant-scoped) onto `Nene2\Audit\AuditQuery`.
 */
final readonly class AuditServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                AuditTableConfig::class,
                static fn (): AuditTableConfig => self::tableConfig(),
            )
            // Non-transactional repository, used by the read side. Mutating use
            // cases build their own repository bound to the transaction executor
            // via AuditRecorderFactoryInterface::forExecutor().
            ->set(
                AuditEventRepositoryInterface::class,
                static function (ContainerInterface $c): AuditEventRepositoryInterface {
                    return new PdoAuditEventRepository(self::query($c), self::tableConfig());
                },
            )
            ->set(
                AuditRecorderFactoryInterface::class,
                static function (ContainerInterface $c): AuditRecorderFactoryInterface {
                    // No organization holder is passed: every profile use case
                    // sets AuditEvent::$organizationId explicitly (including the
                    // null value superadmin cross-org actions carry), so the
                    // recorder never needs the fallback.
                    return new AuditRecorderFactory(self::clock($c), self::tableConfig());
                },
            )
            ->set(
                ListAuditLogsUseCaseInterface::class,
                static function (ContainerInterface $c): ListAuditLogsUseCaseInterface {
                    $repo = $c->get(AuditEventRepositoryInterface::class);

                    if (!$repo instanceof AuditEventRepositoryInterface) {
                        throw new LogicException('Audit event repository service is invalid.');
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

    /**
     * Points the framework audit module at profile's existing `audit_logs`
     * table. This is the single knob a product turns to adopt `Nene2\Audit`
     * without re-migrating: physical column names, int autoincrement id
     * (`idIsAutoIncrement: true`), canonical before/after payload mode, and no
     * metadata column (the table has none).
     */
    private static function tableConfig(): AuditTableConfig
    {
        return new AuditTableConfig(
            table: 'audit_logs',
            mode: AuditPayloadMode::BeforeAfter,
            idColumn: 'id',
            actionColumn: 'action',
            entityTypeColumn: 'entity_type',
            entityIdColumn: 'entity_id',
            actorColumn: 'actor_user_id',
            organizationColumn: 'organization_id',
            occurredAtColumn: 'created_at',
            metadataColumn: null,
            beforeColumn: 'before_json',
            afterColumn: 'after_json',
            payloadColumn: null,
            idIsAutoIncrement: true,
        );
    }

    private static function query(ContainerInterface $c): DatabaseQueryExecutorInterface
    {
        $query = $c->get(DatabaseQueryExecutorInterface::class);

        if (!$query instanceof DatabaseQueryExecutorInterface) {
            throw new LogicException('Database query executor service is invalid.');
        }

        return $query;
    }

    private static function clock(ContainerInterface $c): ClockInterface
    {
        $clock = $c->get(ClockInterface::class);

        if (!$clock instanceof ClockInterface) {
            throw new LogicException('Clock service is invalid.');
        }

        return $clock;
    }
}
