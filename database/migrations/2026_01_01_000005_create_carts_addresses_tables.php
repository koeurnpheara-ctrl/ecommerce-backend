<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('carts', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->timestamps(); });
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('quantity'); $table->timestamps();
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_product_variant_unique');
        });
        Schema::create('addresses', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_name'); $table->string('phone', 30); $table->string('line1'); $table->string('line2')->nullable();
            $table->string('city'); $table->string('province')->nullable(); $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->default('KH'); $table->boolean('is_default')->default(false); $table->timestamps();
            $table->index(['user_id', 'is_default']);
        });
    }
    public function down(): void { Schema::dropIfExists('addresses'); Schema::dropIfExists('cart_items'); Schema::dropIfExists('carts'); }
};
