<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return response()->json(['data' => [
            'customers' => User::whereDoesntHave('roles', fn ($q) => $q->where('slug', 'admin'))->count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'revenue' => Order::whereIn('status', ['confirmed','processing','shipped','delivered'])->sum('grand_total'),
            'recent_orders' => Order::with('user')->latest()->limit(10)->get(),
            'low_stock_products' => Product::where('stock', '<=', 5)->orderBy('stock')->limit(20)->get(),
            'best_sellers' => Product::orderByDesc('sold_count')->limit(10)->get(),
        ]]);
    }
}
