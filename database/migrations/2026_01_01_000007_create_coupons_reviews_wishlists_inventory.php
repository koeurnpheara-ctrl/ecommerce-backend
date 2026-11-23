<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('type'); $table->decimal('value', 12, 2);
            $table->decimal('min_order_amount', 12, 2)->default(0); $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0); $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating'); $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false); $table->timestamps();
            $table->unique(['user_id', 'product_id', 'order_id']);
            $table->index(['product_id', 'is_approved']);
        });
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); $table->integer('quantity'); $table->string('reference')->nullable();
            $table->text('notes')->nullable(); $table->timestamps(); $table->index(['product_id', 'created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_movements'); Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews'); Schema::dropIfExists('coupons');
    }
};
