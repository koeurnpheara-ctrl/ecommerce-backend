<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __invoke(Request $request, CheckoutService $service)
    {
        $data = $request->validate([
            'address_id' => ['required','integer','exists:addresses,id'],
            'payment_method' => ['required','in:cod'],
        ]);
        $order = $service->checkout($request->user()->id, $data['address_id'], $data['payment_method']);
        return response()->json(['data' => $order], 201);
    }
}
