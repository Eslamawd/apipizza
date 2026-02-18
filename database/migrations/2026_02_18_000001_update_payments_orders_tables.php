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
        // Update payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'error_code')) {
                $table->string('error_code')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('payments', 'decline_code')) {
                $table->string('decline_code')->nullable()->after('error_code');
            }
        });

        // Update orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['not_required', 'pending', 'success', 'failed'])->default('not_required')->after('status');
            }
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->enum('order_type', ['pickup', 'delivery'])->default('pickup')->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'error_code')) {
                $table->dropColumn('error_code');
            }
            if (Schema::hasColumn('payments', 'decline_code')) {
                $table->dropColumn('decline_code');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('orders', 'order_type')) {
                $table->dropColumn('order_type');
            }
        });
    }
};
