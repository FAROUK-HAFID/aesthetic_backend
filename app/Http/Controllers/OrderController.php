<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'customer_name' => 'required|max:255',
            'customer_phone' => 'required|max:50',
            'customer_email' => 'nullable|email',
            'shipping_address' => 'required',
            'coupon_code' => 'nullable|string'
        ]);

        $cart = Cart::where('session_id', $request->session_id)
            ->with('items.product')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $subtotal = 0;

            foreach ($cart->items as $item) {

                if ($item->product->stock_quantity < $item->quantity) {

                    return response()->json([
                        'message' => "Insufficient stock for {$item->product->name}"
                    ], 422);
                }

                $subtotal += $item->product->price * $item->quantity;
            }

            $discount = 0;
            $coupon = null;

            if ($request->coupon_code) {

                $coupon = Coupon::where('code', $request->coupon_code)
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

                if ($coupon->discount_type === 'percentage') {
                    $discount = $subtotal * ($coupon->discount_value / 100);
                } else {
                    $discount = $coupon->discount_value;
                }

                $discount = min($discount, $subtotal);
            }

            $total = $subtotal - $discount;

            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'shipping_address' => $request->shipping_address,
                'coupon_id' => $coupon?->id,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'status' => 'pending'
            ]);

            foreach ($cart->items as $item) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price
                ]);

                $item->product->decrement(
                    'stock_quantity',
                    $item->quantity
                );
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order_id' => $order->id,
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'total' => round($total, 2)
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Order $order)
    {
        return $order->load([
            'items.product',
            'coupon'
        ]);
    }
}
