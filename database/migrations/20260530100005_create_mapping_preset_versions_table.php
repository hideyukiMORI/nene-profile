<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Append-only table. Versions are NEVER updated or deleted (ADR 0004): editing
 * a preset creates a new version row. A version referenced by a completed
 * import job must remain readable indefinitely.
 */
final class CreateMappingPresetVersionsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('mapping_preset_versions')
            ->addColumn('preset_id', 'integer', ['null' => false])
            ->addColumn('version_number', 'integer', ['null' => false])
            ->addColumn('definition_json', 'text', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex(['preset_id'], ['name' => 'idx_mapping_preset_versions_preset_id'])
            ->addIndex(['preset_id', 'version_number'], ['unique' => true, 'name' => 'uniq_mapping_preset_versions_preset_version'])
            ->create();
    }
}
