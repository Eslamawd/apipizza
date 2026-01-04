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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_price');
            $table->enum('status', ['pending','in_progress', 'ready','delivered','cancelled', 'payid'])->default('pending');

                // بيانات التوصيل (لطلبات الدليفري)
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('table_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('payment_method', ['cash', 'credit_card', 'online'])->default('cash');
            


          $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');

           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
