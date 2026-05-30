<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Write-once staging of emitted StandardTransaction rows (output-schema v1.0).
 * All amounts are integer minimum currency units; no float/DECIMAL.
 */
final class CreateNormalizedTransactionsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('normalized_transactions')
            ->addColumn('import_job_id', 'integer', ['null' => false])
            ->addColumn('raw_row_number', 'integer', ['null' => false])
            ->addColumn('transaction_date', 'string', ['limit' => 10, 'null' => false])
            ->addColumn('value_date', 'string', ['limit' => 10, 'null' => false])
            ->addColumn('amount_cents', 'biginteger', ['null' => false])
            ->addColumn('description', 'string', ['limit' => 500, 'null' => false])
            ->addColumn('counterparty', 'string', ['limit' => 200, 'null' => true, 'default' => null])
            ->addColumn('balance_cents', 'biginteger', ['null' => true, 'default' => null])
            ->addColumn('currency', 'string', ['limit' => 3, 'null' => false, 'default' => 'JPY'])
            ->addColumn('line_hash', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex(['import_job_id'], ['name' => 'idx_normalized_transactions_import_job_id'])
            ->addIndex(['import_job_id', 'line_hash'], ['name' => 'idx_normalized_transactions_line_hash'])
            ->create();
    }
}
