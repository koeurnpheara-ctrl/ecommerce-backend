<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    protected $fillable = ['user_id', 'order_number', 'status', 'payment_status', 'payment_method',
        'subtotal', 'discount_total', 'shipping_total', 'grand_total', 'shipping_address', 'notes'];
    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2', 'grand_total' => 'decimal:2', 'shipping_address' => 'array'];
    }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
