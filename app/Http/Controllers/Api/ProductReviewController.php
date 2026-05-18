<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Order $order, OrderItem $item)
    {
        if ($order->user_id !== $request->user()->id || $item->order_id !== $order->id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = ProductReview::updateOrCreate(
            [
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Review berhasil disimpan.',
            'data' => $review,
        ]);
    }
}
