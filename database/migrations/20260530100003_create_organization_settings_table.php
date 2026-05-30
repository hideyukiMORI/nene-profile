<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOrganizationSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('organization_settings')
            ->addColumn('organization_id', 'integer', ['null' => false])
            ->addColumn('default_encoding', 'string', ['limit' => 20, 'null' => false, 'default' => 'auto'])
            ->addColumn('max_file_size_bytes', 'integer', ['null' => false, 'default' => 10485760])
            ->addColumn('clear_bearer_token', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex(['organization_id'], ['unique' => true, 'name' => 'uniq_organization_settings_organization_id'])
            ->create();
    }
}
