<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('payments', function ($table) {
                $table->unsignedBigInteger('order_id')->nullable()->change();
            });

            return;
        }

        $database = DB::getDatabaseName();

        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, 'payments', 'order_id']
        );

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE payments DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
        }

        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NULL');

        // Clean orphan references so adding FK does not fail.
        DB::statement('UPDATE payments p LEFT JOIN orders o ON p.order_id = o.id SET p.order_id = NULL WHERE p.order_id IS NOT NULL AND o.id IS NULL');

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
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('payments', function ($table) {
                $table->unsignedBigInteger('order_id')->nullable(false)->change();
            });

            return;
        }

        $database = DB::getDatabaseName();

        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, 'payments', 'order_id']
        );

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE payments DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
        }

        // Down migration restores NOT NULL + CASCADE, so remove incompatible rows first.
        DB::statement('DELETE p FROM payments p LEFT JOIN orders o ON p.order_id = o.id WHERE p.order_id IS NULL OR o.id IS NULL');

        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');
    }
};
