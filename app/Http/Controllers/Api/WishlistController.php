<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()
            ->wishlistProducts()
            ->with(['category', 'images'])
            ->latest('wishlists.created_at')
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }

    public function toggle(Request $request, Product $product)
    {
        $exists = $request->user()
            ->wishlistProducts()
            ->where('products.id', $product->id)
            ->exists();

        if ($exists) {
            $request->user()->wishlistProducts()->detach($product->id);

            return response()->json([
                'message' => 'Produk dihapus dari wishlist.',
                'is_favorited' => false,
            ]);
        }

        $request->user()->wishlistProducts()->attach($product->id);

        return response()->json([
            'message' => 'Produk ditambahkan ke wishlist.',
            'is_favorited' => true,
        ]);
    }
}
