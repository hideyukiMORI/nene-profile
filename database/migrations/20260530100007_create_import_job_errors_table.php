<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Write-once table (compliance §4). Every unparseable or rule-violating row is
 * recorded here — rows are never silently dropped.
 */
final class CreateImportJobErrorsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('import_job_errors')
            ->addColumn('import_job_id', 'integer', ['null' => false])
            ->addColumn('raw_row_number', 'integer', ['null' => false])
            ->addColumn('message', 'string', ['limit' => 500, 'null' => false])
            ->addColumn('raw_snippet', 'text', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex(['import_job_id'], ['name' => 'idx_import_job_errors_import_job_id'])
            ->create();
    }
}
