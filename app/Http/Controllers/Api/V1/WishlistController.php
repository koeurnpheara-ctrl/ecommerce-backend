<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request) { return response()->json(['data' => Wishlist::where('user_id', $request->user()->id)->with('product.images')->paginate(20)]); }
    public function store(Request $request, Product $product)
    {
        Wishlist::firstOrCreate(['user_id' => $request->user()->id, 'product_id' => $product->id]);
        return response()->json(['message' => 'Added to wishlist.'], 201);
    }
    public function destroy(Request $request, Product $product)
    {
        Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();
        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
