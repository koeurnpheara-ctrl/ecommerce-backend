<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Category::where('is_active', true)->with('children')->whereNull('parent_id')->get()]);
    }
    public function show(Category $category)
    {
        abort_unless($category->is_active, 404);
        return response()->json(['data' => $category->load(['children', 'products' => fn ($q) => $q->active()->with('images')])]);
    }
    public function store(Request $request)
    {
        $data = $request->validate(['parent_id' => ['nullable','exists:categories,id'], 'name' => ['required','string','max:150'], 'description' => ['nullable','string'], 'is_active' => ['sometimes','boolean']]);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        return response()->json(['data' => Category::create($data)], 201);
    }
    public function update(Request $request, Category $category)
    {
        $data = $request->validate(['parent_id' => ['nullable','exists:categories,id'], 'name' => ['sometimes','string','max:150'], 'description' => ['nullable','string'], 'is_active' => ['sometimes','boolean']]);
        if (($data['parent_id'] ?? null) == $category->id) abort(422, 'A category cannot be its own parent.');
        if (isset($data['name'])) $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $category->update($data);
        return response()->json(['data' => $category->refresh()]);
    }
    public function destroy(Category $category)
    {
        abort_if($category->products()->exists() || $category->children()->exists(), 409, 'Move products and child categories before archiving.');
        $category->update(['is_active' => false]);
        return response()->json(['message' => 'Category archived.']);
    }
}
