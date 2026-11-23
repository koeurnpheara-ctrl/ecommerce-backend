<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function checkout(int $userId, int $addressId, string $paymentMethod = 'cod'): Order
    {
        return DB::transaction(function () use ($userId, $addressId, $paymentMethod) {
            $address = \App\Models\Address::where('user_id', $userId)->findOrFail($addressId);
            $cart = Cart::where('user_id', $userId)->with('items')->lockForUpdate()->firstOrFail();
            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $subtotalCents = 0;
            $lines = [];
            foreach ($cart->items as $cartItem) {
                $product = Product::whereKey($cartItem->product_id)->lockForUpdate()->firstOrFail();
                if (!$product->is_active) {
                    throw ValidationException::withMessages(['product' => "{$product->name} is unavailable."]);
                }

                $variant = null;
                if ($cartItem->product_variant_id) {
                    $variant = ProductVariant::whereKey($cartItem->product_variant_id)->lockForUpdate()->firstOrFail();
                    if (!$variant->is_active || $variant->product_id !== $product->id) {
                        throw ValidationException::withMessages(['variant' => 'A selected variant is unavailable.']);
                    }
                }

                $stock = $variant?->stock ?? $product->stock;
                if ($stock < $cartItem->quantity) {
                    throw ValidationException::withMessages(['stock' => "Insufficient stock for {$product->name}."]);
                }

                $price = $variant?->price ?? $product->discount_price ?? $product->price;
                $unitCents = (int) round(((float) $price) * 100);
                $lineCents = $unitCents * $cartItem->quantity;
                $subtotalCents += $lineCents;
                $lines[] = compact('product', 'variant', 'cartItem', 'price', 'lineCents');
            }

            $shippingCents = 0; // Replace with server-side shipping rules.
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                'discount_total' => '0.00',
                'shipping_total' => number_format($shippingCents / 100, 2, '.', ''),
                'grand_total' => number_format(($subtotalCents + $shippingCents) / 100, 2, '.', ''),
                'shipping_address' => $address->only(['recipient_name','phone','line1','line2','city','province','postal_code','country']),
            ]);

            foreach ($lines as $line) {
                $product = $line['product']; $variant = $line['variant']; $item = $line['cartItem'];
                OrderItem::create([
                    'order_id' => $order->id, 'product_id' => $product->id,
                    'product_variant_id' => $variant?->id, 'product_name' => $product->name,
                    'sku' => $variant?->sku ?? $product->sku, 'unit_price' => $line['price'],
                    'quantity' => $item->quantity, 'line_total' => number_format($line['lineCents'] / 100, 2, '.', ''),
                ]);

                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                } else {
                    $product->decrement('stock', $item->quantity);
                }
                $product->increment('sold_count', $item->quantity);

                InventoryMovement::create([
                    'product_id' => $product->id, 'product_variant_id' => $variant?->id,
                    'user_id' => $userId, 'type' => 'sale', 'quantity' => -$item->quantity,
                    'reference' => $order->order_number, 'notes' => 'Stock deducted at checkout',
                ]);
            }

            Payment::create([
                'order_id' => $order->id, 'provider' => $paymentMethod,
                'amount' => $order->grand_total, 'currency' => 'USD',
                'status' => $paymentMethod === 'cod' ? 'pending' : 'requires_action',
            ]);

            $cart->items()->delete();

            return $order->load('items', 'payments');
        }, 3);
    }
}
