<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable()->change();
            });

            return;
        }

        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable(false)->change();
            });

            return;
        }

        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NOT NULL');
    }
};
