<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ImportJobRouteRegistrar
{
    public function __construct(
        private ListImportJobsHandler $listHandler,
        private GetImportJobHandler $getHandler,
        private CreateImportJobHandler $createHandler,
        private ListImportJobErrorsHandler $errorsHandler,
        private ExportImportJobJsonHandler $exportJsonHandler,
        private ExportImportJobCsvHandler $exportCsvHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list       = $this->listHandler;
        $get        = $this->getHandler;
        $create     = $this->createHandler;
        $errors     = $this->errorsHandler;
        $exportJson = $this->exportJsonHandler;
        $exportCsv  = $this->exportCsvHandler;

        $router->get('/admin/import-jobs', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->post('/admin/import-jobs', static fn (ServerRequestInterface $r) => $create->handle($r));
        $router->get('/admin/import-jobs/{id}', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->get('/admin/import-jobs/{id}/errors', static fn (ServerRequestInterface $r) => $errors->handle($r));
        $router->get('/admin/import-jobs/{id}/export.json', static fn (ServerRequestInterface $r) => $exportJson->handle($r));
        $router->get('/admin/import-jobs/{id}/export.csv', static fn (ServerRequestInterface $r) => $exportCsv->handle($r));
    }
}
