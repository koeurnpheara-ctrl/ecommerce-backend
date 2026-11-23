<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getForUser(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId])->load('items');
    }

    public function add(int $userId, int $productId, int $quantity, ?int $variantId = null): Cart
    {
        return DB::transaction(function () use ($userId, $productId, $quantity, $variantId) {
            $product = Product::active()->findOrFail($productId);
            $variant = $variantId ? ProductVariant::where('product_id', $product->id)->where('is_active', true)->findOrFail($variantId) : null;
            $available = $variant?->stock ?? $product->stock;
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            $item = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)->lockForUpdate()->first();

            $newQuantity = ($item?->quantity ?? 0) + $quantity;
            if ($newQuantity > $available) {
                throw ValidationException::withMessages(['quantity' => 'Requested quantity exceeds available stock.']);
            }

            if ($item) { $item->update(['quantity' => $newQuantity]); }
            else { CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'product_variant_id' => $variant?->id, 'quantity' => $quantity]); }

            return $cart->load('items');
        });
    }
}
