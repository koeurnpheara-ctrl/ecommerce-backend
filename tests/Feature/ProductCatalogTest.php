<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_products_are_publicly_listed(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        Product::create([
            'category_id' => $category->id, 'name' => 'Demo Phone', 'slug' => 'demo-phone',
            'sku' => 'PHONE-1', 'price' => 100, 'stock' => 3, 'is_active' => true,
        ]);
        $this->getJson('/api/v1/products')->assertOk()->assertJsonFragment(['name' => 'Demo Phone']);
    }
}
