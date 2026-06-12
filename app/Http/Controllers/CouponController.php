<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\Cart;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'code' => 'required'
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'message' => 'Invalid coupon'
            ], 422);
        }

        if (
            $coupon->expiration_date &&
            Carbon::parse($coupon->expiration_date)->isPast()
        ) {
            return response()->json([
                'message' => 'Coupon expired'
            ], 422);
        }

        if (
            $coupon->usage_limit &&
            $coupon->used_count >= $coupon->usage_limit
        ) {
            return response()->json([
                'message' => 'Coupon usage limit reached'
            ], 422);
        }

        $cart = Cart::where('session_id', $request->session_id)
            ->with('items.product')
            ->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        }

        $total = 0;

        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        if ($coupon->discount_type === 'percentage') {
            $discount = $total * ($coupon->discount_value / 100);
        } else {
            $discount = $coupon->discount_value;
        }

        $discount = min($discount, $total);

        return response()->json([
            'coupon' => $coupon->code,
            'subtotal' => $total,
            'discount' => round($discount, 2),
            'total' => round($total - $discount, 2)
        ]);
    }
}
