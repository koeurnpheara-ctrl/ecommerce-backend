<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'description', 'price',
        'discount_price', 'stock', 'is_active', 'is_featured',
    ];
    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'discount_price' => 'decimal:2',
            'stock' => 'integer', 'is_active' => 'boolean', 'is_featured' => 'boolean'];
    }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function getEffectivePriceAttribute(): string
    {
        return (string) ($this->discount_price ?? $this->price);
    }
}
