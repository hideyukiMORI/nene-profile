<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMappingPresetsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('mapping_presets')
            ->addColumn('organization_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('bank_label', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('current_version_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('is_deleted', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex(['organization_id'], ['name' => 'idx_mapping_presets_organization_id'])
            ->create();
    }
}
