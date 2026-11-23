<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->with(['images', 'category'])->withAvg('reviews', 'rating')->withCount('reviews');
        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
        }
        if ($request->filled('category_id')) $query->where('category_id', $request->integer('category_id'));
        if ($request->boolean('featured')) $query->where('is_featured', true);
        if ($request->filled('min_price')) $query->whereRaw('COALESCE(discount_price, price) >= ?', [$request->input('min_price')]);
        if ($request->filled('max_price')) $query->whereRaw('COALESCE(discount_price, price) <= ?', [$request->input('max_price')]);

        match ($request->input('sort', 'newest')) {
            'price_asc' => $query->orderByRaw('COALESCE(discount_price, price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(discount_price, price) desc'),
            'best_selling' => $query->orderByDesc('sold_count'),
            default => $query->latest(),
        };

        return response()->json($query->paginate(min(max($request->integer('per_page', 12), 1), 100)));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        return response()->json(['data' => $product->load(['images', 'variants', 'category'])->loadAvg('reviews', 'rating')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required','exists:categories,id'], 'name' => ['required','string','max:255'],
            'sku' => ['required','string','max:100','unique:products,sku'], 'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0'], 'discount_price' => ['nullable','numeric','min:0','lte:price'],
            'stock' => ['required','integer','min:0'], 'is_active' => ['sometimes','boolean'], 'is_featured' => ['sometimes','boolean'],
        ]);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        return response()->json(['data' => Product::create($data)], 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['sometimes','exists:categories,id'], 'name' => ['sometimes','string','max:255'],
            'sku' => ['sometimes','string','max:100','unique:products,sku,'.$product->id],
            'description' => ['nullable','string'], 'price' => ['sometimes','numeric','min:0'],
            'discount_price' => ['nullable','numeric','min:0'], 'stock' => ['sometimes','integer','min:0'],
            'is_active' => ['sometimes','boolean'], 'is_featured' => ['sometimes','boolean'],
        ]);
        $product->update($data);
        return response()->json(['data' => $product->refresh()]);
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);
        return response()->json(['message' => 'Product archived.']);
    }
}
