<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate(['rating' => ['required','integer','between:1,5'], 'comment' => ['nullable','string','max:3000'], 'order_id' => ['required','exists:orders,id']]);
        $order = Order::where('user_id', $request->user()->id)->where('status', 'delivered')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))->find($data['order_id']);
        if (!$order) throw ValidationException::withMessages(['order_id' => 'A delivered purchase of this product is required.']);
        $review = Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id, 'order_id' => $order->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null, 'is_approved' => false]
        );
        return response()->json(['data' => $review], 201);
    }
}
