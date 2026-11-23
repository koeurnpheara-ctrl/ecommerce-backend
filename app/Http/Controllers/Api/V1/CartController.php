<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id])->load('items');
        $subtotal = $cart->items->sum(fn ($item) => (float) ($item->variant?->price ?? $item->product->discount_price ?? $item->product->price) * $item->quantity);
        return response()->json(['data' => $cart, 'subtotal' => number_format($subtotal, 2, '.', '')]);
    }
    public function store(Request $request, CartService $service)
    {
        $data = $request->validate(['product_id' => ['required','exists:products,id'], 'quantity' => ['required','integer','min:1','max:50'], 'product_variant_id' => ['nullable','integer']]);
        $cart = $service->add($request->user()->id, $data['product_id'], $data['quantity'], $data['product_variant_id'] ?? null);
        return response()->json(['data' => $cart], 201);
    }
    public function update(Request $request, CartItem $cartItem)
    {
        abort_unless($cartItem->cart()->where('user_id', $request->user()->id)->exists(), 404);
        $data = $request->validate(['quantity' => ['required','integer','min:1','max:50']]);
        $cartItem->update($data);
        return response()->json(['data' => $cartItem->fresh(['product','variant'])]);
    }
    public function destroy(Request $request, CartItem $cartItem)
    {
        abort_unless($cartItem->cart()->where('user_id', $request->user()->id)->exists(), 404);
        $cartItem->delete();
        return response()->json(['message' => 'Cart item removed.']);
    }
}
