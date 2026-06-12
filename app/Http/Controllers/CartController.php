<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1'
        ]);

        $cart = Cart::firstOrCreate([
            'session_id' => $request->session_id
        ]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($item) {
            $item->quantity += $request->quantity ?? 1;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity ?? 1
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart'
        ]);
    }
    public function show($sessionId)
    {
        $cart = Cart::where('session_id', $sessionId)
            ->with('items.product.images')
            ->first();

        if (!$cart) {
            return response()->json([
                'items' => [],
                'total' => 0
            ]);
        }

        $total = 0;

        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        return response()->json([
            'cart' => $cart,
            'total' => $total
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::findOrFail($request->item_id);
        $item->quantity = $request->quantity;
        $item->save();

        return response()->json([
            'message' => 'Cart updated'
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id'
        ]);

        CartItem::destroy($request->item_id);

        return response()->json([
            'message' => 'Item removed'
        ]);
    }
}
