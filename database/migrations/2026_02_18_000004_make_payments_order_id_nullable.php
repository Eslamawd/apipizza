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
        // Make order_id nullable using raw SQL (safe approach)
        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to NOT NULL
        DB::statement('ALTER TABLE payments MODIFY order_id BIGINT UNSIGNED NOT NULL');
    }
};
