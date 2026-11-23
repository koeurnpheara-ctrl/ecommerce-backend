<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $admin = User::firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@example.com')],
            ['name' => 'Store Administrator', 'password' => env('SEED_ADMIN_PASSWORD', 'ChangeMe123!')]
        );
        $admin->roles()->syncWithoutDetaching([Role::where('slug', 'admin')->value('id')]);

        $electronics = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'is_active' => true]);
        $fashion = Category::firstOrCreate(['slug' => 'fashion'], ['name' => 'Fashion', 'is_active' => true]);
        $home = Category::firstOrCreate(['slug' => 'home-living'], ['name' => 'Home & Living', 'is_active' => true]);

        $samples = [
            [$electronics->id, 'Wireless Bluetooth Headphones', 'DEMO-HEADPHONE-001', 39.99, 34.99, 35],
            [$electronics->id, 'Smart Watch Series', 'DEMO-WATCH-001', 69.00, null, 20],
            [$fashion->id, 'Classic Cotton T-Shirt', 'DEMO-SHIRT-001', 12.50, 10.00, 100],
            [$fashion->id, 'Everyday Backpack', 'DEMO-BAG-001', 29.90, null, 40],
            [$home->id, 'Ceramic Coffee Mug', 'DEMO-MUG-001', 8.50, null, 60],
        ];
        foreach ($samples as [$categoryId, $name, $sku, $price, $discount, $stock]) {
            Product::firstOrCreate(['sku' => $sku], [
                'category_id' => $categoryId, 'name' => $name,
                'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
                'description' => 'Demo product. Replace with real catalog content.',
                'price' => $price, 'discount_price' => $discount, 'stock' => $stock,
                'is_active' => true, 'is_featured' => true,
            ]);
        }
    }
}
