<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateImportJobsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('import_jobs')
            ->addColumn('organization_id', 'integer', ['null' => false])
            ->addColumn('actor_user_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('preset_version_id', 'integer', ['null' => false])
            ->addColumn('original_filename', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('original_file_hash', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('status', 'string', ['limit' => 32, 'null' => false, 'default' => 'pending'])
            ->addColumn('row_count', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('error_count', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('started_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('completed_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex(['organization_id'], ['name' => 'idx_import_jobs_organization_id'])
            ->addIndex(['preset_version_id'], ['name' => 'idx_import_jobs_preset_version_id'])
            ->create();
    }
}
