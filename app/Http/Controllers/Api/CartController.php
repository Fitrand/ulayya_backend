<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('items.product');

        return response()->json([
            'data' => $cart,
        ]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);

        if ($product->stock < $validated['quantity']) {
            return response()->json([
                'message' => 'Stok produk tidak cukup.',
            ], 422);
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $newQuantity = $item->quantity + $validated['quantity'];
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'message' => 'Jumlah melebihi stok produk.',
                ], 422);
            }

            $item->update([
                'quantity' => $newQuantity,
                'price_snapshot' => $product->price,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'price_snapshot' => $product->price,
            ]);
        }

        $cart->load('items.product');

        return response()->json([
            'message' => 'Item berhasil ditambahkan ke cart.',
            'data' => $cart,
        ]);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $item = $cart->items()->with('product')->findOrFail($itemId);

        if ($item->product->stock < $validated['quantity']) {
            return response()->json([
                'message' => 'Jumlah melebihi stok produk.',
            ], 422);
        }

        $item->update([
            'quantity' => $validated['quantity'],
            'price_snapshot' => $item->product->price,
        ]);

        $cart->load('items.product');

        return response()->json([
            'message' => 'Cart item berhasil diupdate.',
            'data' => $cart,
        ]);
    }

    public function removeItem(Request $request, int $itemId)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $item = $cart->items()->findOrFail($itemId);
        $item->delete();

        $cart->load('items.product');

        return response()->json([
            'message' => 'Item dihapus dari cart.',
            'data' => $cart,
        ]);
    }
}
