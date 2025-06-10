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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'on_hold', 'completed', 'canceled', 'refunded', 'failed'])->default('pending');
            $table->string('name');
            $table->string('rut');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->foreignId('municipality_id')->constrained()->onDelete('cascade');
            $table->string('billing_address');
            $table->string('billing_address_details')->nullable();
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
