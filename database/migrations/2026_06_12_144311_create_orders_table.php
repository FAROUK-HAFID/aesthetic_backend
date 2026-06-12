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

            $table->string('customer_name');

            $table->string('customer_phone');

            $table->string('customer_email')
                ->nullable();

            $table->text('shipping_address');

            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('discount_amount', 10, 2)
                ->default(0);

            $table->decimal('total_amount', 12, 2);

            $table->enum('status', [
                'pending',
                'paid',
                'shipped',
                'delivered',
                'cancelled'
            ])->default('pending');

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
