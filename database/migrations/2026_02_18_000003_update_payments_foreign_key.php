<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $database = DB::getDatabaseName();

        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, 'payments', 'order_id']
        );

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE payments DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
        }

        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NULL');

        $existingTargetFk = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?',
            [$database, 'payments', 'order_id', 'orders']
        );

        if (empty($existingTargetFk)) {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $database = DB::getDatabaseName();

        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, 'payments', 'order_id']
        );

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE payments DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
        }

        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');
    }
};
