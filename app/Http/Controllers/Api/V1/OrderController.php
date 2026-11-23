<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->orders()->with('items')->latest()->paginate(15));
    }
    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->hasRole('admin'), 404);
        return response()->json(['data' => $order->load(['items','payments'])]);
    }
    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        return DB::transaction(function () use ($order, $request) {
            $order = Order::whereKey($order->id)->lockForUpdate()->with('items')->firstOrFail();
            if (!in_array($order->status, ['pending','confirmed'], true)) {
                throw ValidationException::withMessages(['order' => 'This order can no longer be cancelled.']);
            }
            foreach ($order->items as $item) {
                if (!$item->product_id) continue;
                $product = \App\Models\Product::whereKey($item->product_id)->lockForUpdate()->first();
                if (!$product) continue;
                if ($item->product_variant_id) {
                    $variant = \App\Models\ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->first();
                    if ($variant) $variant->increment('stock', $item->quantity);
                } else {
                    $product->increment('stock', $item->quantity);
                }
                InventoryMovement::create([
                    'product_id' => $product->id, 'product_variant_id' => $item->product_variant_id,
                    'user_id' => $request->user()->id, 'type' => 'return', 'quantity' => $item->quantity,
                    'reference' => $order->order_number, 'notes' => 'Stock restored after order cancellation',
                ]);
            }
            $order->update(['status' => 'cancelled']);
            return response()->json(['data' => $order->refresh()]);
        }, 3);
    }
}
