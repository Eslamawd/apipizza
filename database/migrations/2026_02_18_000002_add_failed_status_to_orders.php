<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'failed' to the status enum
        Schema::table('orders', function (Blueprint $table) {
            // MySQL: Modify the enum using raw SQL
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','in_progress','ready','delivered','cancelled','payid','failed') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove 'failed' from the status enum
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','in_progress','ready','delivered','cancelled','payid') DEFAULT 'pending'");
        });
    }
};
