<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name'); $table->string('slug')->unique(); $table->string('sku')->unique();
            $table->longText('description')->nullable(); $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable(); $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true); $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('sold_count')->default(0); $table->timestamps();
            $table->index(['category_id', 'is_active']); $table->index(['is_featured', 'is_active']);
        });
        Schema::create('product_images', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path'); $table->string('alt_text')->nullable(); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique(); $table->string('name'); $table->json('attributes');
            $table->decimal('price', 12, 2)->nullable(); $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['product_id', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('product_variants'); Schema::dropIfExists('product_images'); Schema::dropIfExists('products'); }
};
