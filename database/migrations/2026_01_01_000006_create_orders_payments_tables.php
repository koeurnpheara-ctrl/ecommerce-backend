<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_number')->unique(); $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending'); $table->string('payment_method')->default('cod');
            $table->decimal('subtotal', 12, 2); $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0); $table->decimal('grand_total', 12, 2);
            $table->json('shipping_address'); $table->text('notes')->nullable(); $table->timestamps();
            $table->index(['user_id', 'status']); $table->index(['created_at', 'status']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name'); $table->string('sku'); $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity'); $table->decimal('line_total', 12, 2); $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('cod'); $table->string('transaction_reference')->nullable()->unique();
            $table->decimal('amount', 12, 2); $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending'); $table->json('payload')->nullable(); $table->timestamps();
            $table->index(['order_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); Schema::dropIfExists('order_items'); Schema::dropIfExists('orders'); }
};
