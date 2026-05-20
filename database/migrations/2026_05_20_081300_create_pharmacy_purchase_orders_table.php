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
        Schema::create('pharmacy_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('medication_id')->nullable()->constrained('medications', 'medication_id')->onDelete('set null');
            $table->string('medication_name'); // Denormalized for safety if medication deleted
            $table->integer('quantity');
            $table->string('supplier_name')->default('Global Pharma Distributors');
            $table->decimal('estimated_cost', 10, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, ordered, received, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_purchase_orders');
    }
};
