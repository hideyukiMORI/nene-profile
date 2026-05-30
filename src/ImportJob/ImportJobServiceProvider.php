<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use NeneProfile\Audit\AuditRecorderInterface;
use NeneProfile\Preset\MappingPresetRepositoryInterface;
use NeneProfile\Preset\MappingPresetVersionRepositoryInterface;
use NeneProfile\Transformer\TransformerRegistry;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class ImportJobServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                ImportJobRepositoryInterface::class,
                static function (ContainerInterface $c): ImportJobRepositoryInterface {
                    $query = $c->get(DatabaseQueryExecutorInterface::class);

                    if (!$query instanceof DatabaseQueryExecutorInterface) {
                        throw new LogicException('Database query executor service is invalid.');
                    }

                    return new PdoImportJobRepository($query);
                },
            )
            ->set(
                FileStorageInterface::class,
                static function (ContainerInterface $c): FileStorageInterface {
                    $base = (string) (getenv('NENE_PROFILE_STORAGE_PATH') ?: '');
                    if ($base === '') {
                        $base = dirname(__DIR__, 2) . '/storage/uploads';
                    }

                    return new LocalFileStorage($base);
                },
            )
            ->set(CsvParser::class, static fn (ContainerInterface $c): CsvParser => new CsvParser())
            ->set(
                NormalizationRunner::class,
                static fn (ContainerInterface $c): NormalizationRunner => new NormalizationRunner(new TransformerRegistry()),
            )
            ->set(
                CreateImportJobUseCaseInterface::class,
                static fn (ContainerInterface $c): CreateImportJobUseCaseInterface => new CreateImportJobUseCase(
                    self::get($c, ImportJobRepositoryInterface::class),
                    self::get($c, MappingPresetRepositoryInterface::class),
                    self::get($c, MappingPresetVersionRepositoryInterface::class),
                    self::get($c, FileStorageInterface::class),
                    self::get($c, CsvParser::class),
                    self::get($c, NormalizationRunner::class),
                    self::get($c, AuditRecorderInterface::class),
                ),
            )
            ->set(
                ListImportJobsUseCase::class,
                static fn (ContainerInterface $c): ListImportJobsUseCase => new ListImportJobsUseCase(
                    self::get($c, ImportJobRepositoryInterface::class),
                ),
            )
            ->set(
                GetImportJobUseCase::class,
                static fn (ContainerInterface $c): GetImportJobUseCase => new GetImportJobUseCase(
                    self::get($c, ImportJobRepositoryInterface::class),
                ),
            )
            ->set(
                ListImportJobErrorsUseCase::class,
                static fn (ContainerInterface $c): ListImportJobErrorsUseCase => new ListImportJobErrorsUseCase(
                    self::get($c, ImportJobRepositoryInterface::class),
                ),
            )
            ->set(
                ExportImportJobUseCase::class,
                static fn (ContainerInterface $c): ExportImportJobUseCase => new ExportImportJobUseCase(
                    self::get($c, ImportJobRepositoryInterface::class),
                ),
            )
            ->set(
                CreateImportJobHandler::class,
                static fn (ContainerInterface $c): CreateImportJobHandler => new CreateImportJobHandler(
                    self::get($c, CreateImportJobUseCaseInterface::class),
                    self::json($c),
                    self::problemDetails($c),
                ),
            )
            ->set(
                ListImportJobsHandler::class,
                static fn (ContainerInterface $c): ListImportJobsHandler => new ListImportJobsHandler(
                    self::get($c, ListImportJobsUseCase::class),
                    self::json($c),
                    self::problemDetails($c),
                ),
            )
            ->set(
                GetImportJobHandler::class,
                static fn (ContainerInterface $c): GetImportJobHandler => new GetImportJobHandler(
                    self::get($c, GetImportJobUseCase::class),
                    self::json($c),
                    self::problemDetails($c),
                ),
            )
            ->set(
                ListImportJobErrorsHandler::class,
                static fn (ContainerInterface $c): ListImportJobErrorsHandler => new ListImportJobErrorsHandler(
                    self::get($c, ListImportJobErrorsUseCase::class),
                    self::json($c),
                    self::problemDetails($c),
                ),
            )
            ->set(
                ExportImportJobJsonHandler::class,
                static fn (ContainerInterface $c): ExportImportJobJsonHandler => new ExportImportJobJsonHandler(
                    self::get($c, ExportImportJobUseCase::class),
                    self::json($c),
                    self::problemDetails($c),
                ),
            )
            ->set(
                ExportImportJobCsvHandler::class,
                static fn (ContainerInterface $c): ExportImportJobCsvHandler => new ExportImportJobCsvHandler(
                    self::get($c, ExportImportJobUseCase::class),
                    self::get($c, ResponseFactoryInterface::class),
                    self::get($c, StreamFactoryInterface::class),
                    self::problemDetails($c),
                ),
            )
            ->set(
                ImportJobNotFoundExceptionHandler::class,
                static fn (ContainerInterface $c): ImportJobNotFoundExceptionHandler => new ImportJobNotFoundExceptionHandler(
                    self::problemDetails($c),
                ),
            )
            ->set(
                ImportJobRouteRegistrar::class,
                static fn (ContainerInterface $c): ImportJobRouteRegistrar => new ImportJobRouteRegistrar(
                    self::get($c, ListImportJobsHandler::class),
                    self::get($c, GetImportJobHandler::class),
                    self::get($c, CreateImportJobHandler::class),
                    self::get($c, ListImportJobErrorsHandler::class),
                    self::get($c, ExportImportJobJsonHandler::class),
                    self::get($c, ExportImportJobCsvHandler::class),
                ),
            );
    }

    private static function json(ContainerInterface $c): JsonResponseFactory
    {
        return self::get($c, JsonResponseFactory::class);
    }

    private static function problemDetails(ContainerInterface $c): ProblemDetailsResponseFactory
    {
        return self::get($c, ProblemDetailsResponseFactory::class);
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
