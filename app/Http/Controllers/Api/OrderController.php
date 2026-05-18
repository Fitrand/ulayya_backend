<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\SellerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function __construct()
    {
        // Inisialisasi Midtrans config dari config/midtrans.php
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$clientKey    = config('midtrans.client_key');
        MidtransConfig::$isProduction = config('midtrans.is_production', false);
        MidtransConfig::$isSanitized  = config('midtrans.is_sanitized', true);
        MidtransConfig::$is3ds        = config('midtrans.is_3ds', true);
    }

    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['items.product', 'items.review', 'payment', 'statusHistories', 'address'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:1000'],
            'address_id'       => ['nullable', 'integer', 'exists:addresses,id'],
            'payment_method'   => ['required', 'string', 'max:50'],
            'latitude'         => ['nullable', 'numeric'],
            'longitude'        => ['nullable', 'numeric'],
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart masih kosong.'], 422);
        }

        $user         = $request->user();
        $subtotal     = 0;
        $addressId    = null;
        $midtransItems = [];

        // ── Validasi produk & hitung subtotal (sebelum transaksi DB) ─────────
        if (! empty($validated['address_id'])) {
            $address = Address::query()
                ->where('id', $validated['address_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
            $addressId = $address->id;
        }

        foreach ($cart->items as $item) {
            if (! $item->product || ! $item->product->is_active) {
                return response()->json(['message' => 'Produk tidak tersedia.'], 422);
            }
            if ($item->product->stock < $item->quantity) {
                return response()->json(['message' => "Stok {$item->product->name} tidak cukup."], 422);
            }
            $subtotal += $item->quantity * $item->product->price;

            $midtransItems[] = [
                'id'       => (string) $item->product_id,
                'price'    => (int) $item->product->price,
                'quantity' => $item->quantity,
                'name'     => substr($item->product->name, 0, 50),
            ];
        }

        $shippingCost = 0;
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $storeSettings = SellerSetting::query()->first();
            $storeLat = $storeSettings?->shop_latitude ? (float) $storeSettings->shop_latitude : 5.1801;
            $storeLng = $storeSettings?->shop_longitude ? (float) $storeSettings->shop_longitude : 97.1419;
            $userLat = (float) $validated['latitude'];
            $userLng = (float) $validated['longitude'];

            $earthRadius = 6371; // in km
            $dLat = deg2rad($userLat - $storeLat);
            $dLon = deg2rad($userLng - $storeLng);
            $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($storeLat)) * cos(deg2rad($userLat)) * sin($dLon/2) * sin($dLon/2);
            $c = 2 * asin(sqrt($a));
            $distance = $earthRadius * $c;

            if ($distance <= 5) {
                $shippingCost = 0;
            } elseif ($distance <= 10) {
                $shippingCost = 3000;
            } elseif ($distance <= 25) {
                $shippingCost = 8000;
            } else {
                $shippingCost = 12000;
            }
        }
        $totalAmount  = $subtotal + $shippingCost;

        // ── Generate Midtrans Snap Token SEBELUM DB transaction ──────────────
        // Dilakukan di luar transaction agar koneksi DB tidak tertahan
        // selama request HTTP ke Midtrans berlangsung.
        $snapToken = null;
        $selectedMethod  = strtolower($validated['payment_method']);
        $enabledPayments = [];

        if (str_contains($selectedMethod, 'bsi')) {
            $enabledPayments = ['bsi_va', 'other_va', 'bank_transfer'];
        } elseif (str_contains($selectedMethod, 'dana')) {
            $enabledPayments = ['gopay', 'other_qris', 'shopeepay'];
        } elseif (str_contains($selectedMethod, 'qris')) {
            $enabledPayments = ['gopay', 'other_qris', 'shopeepay'];
        }

        $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . $user->id;

        try {
            $snapParams = [
                'transaction_details' => [
                    'order_id'     => $orderNumber,
                    'gross_amount' => (int) $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email'      => $user->email,
                    'phone'      => $user->phone ?? '',
                ],
                'item_details' => $midtransItems,
                'callbacks'    => [
                    'finish' => config('app.url') . '/payment/finish',
                ],
            ];

            if (! empty($enabledPayments)) {
                $snapParams['enabled_payments'] = $enabledPayments;
            }

            $snapToken = Snap::getSnapToken($snapParams);
        } catch (\Exception $e) {
            // Midtrans gagal → lanjutkan tanpa token, jangan batalkan order
            Log::error('Midtrans snap token error: ' . $e->getMessage());
            $snapToken = null;
        }

        // ── DB transaction: simpan order & bersihkan keranjang ───────────────
        // Keranjang hanya dihapus setelah Midtrans selesai (baik sukses maupun gagal).
        $order = DB::transaction(function () use (
            $cart, $user, $validated, $addressId,
            $subtotal, $shippingCost, $totalAmount,
            $orderNumber, $snapToken
        ) {
            // ── Buat Order ──────────────────────────────────────────────────
            $order = Order::create([
                'user_id'          => $user->id,
                'order_number'     => $orderNumber,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_cost'    => $shippingCost,
                'total_amount'     => $totalAmount,
                'shipping_address' => $validated['shipping_address'],
                'address_id'       => $addressId,
                'payment_method'   => $validated['payment_method'],
                'latitude'         => $validated['latitude'] ?? null,
                'longitude'        => $validated['longitude'] ?? null,
                'snap_token'       => $snapToken,
            ]);

            // ── Item & kurangi stok ─────────────────────────────────────────
            foreach ($cart->items as $item) {
                $price        = (int) $item->product->price;
                $itemSubtotal = $item->quantity * $price;

                $order->items()->create([
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'price'        => $price,
                    'quantity'     => $item->quantity,
                    'subtotal'     => $itemSubtotal,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            // ── Hapus keranjang SETELAH semua data tersimpan ─────────────────
            // Ini aman karena Midtrans sudah dipanggil di luar transaction.
            $cart->items()->delete();

            // ── Payment record ───────────────────────────────────────────────
            Payment::create([
                'order_id' => $order->id,
                'method'   => $validated['payment_method'],
                'status'   => 'pending',
                'amount'   => $totalAmount,
            ]);

            // ── Status history ───────────────────────────────────────────────
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'pending',
                'note'       => 'Pesanan berhasil dibuat.',
                'location'   => 'Ulayya Kue Bhoi',
                'created_by' => $user->id,
            ]);

            return $order->load(['items', 'payment', 'statusHistories', 'address']);
        });

        // ── Build Snap URL ──────────────────────────────────────────────────
        $isProduction = config('midtrans.is_production', false);
        $snapBaseUrl  = $isProduction
            ? 'https://app.midtrans.com/snap/v2/vtweb/'
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        $paymentUrl = $snapToken ? ($snapBaseUrl . $snapToken) : null;

        return response()->json([
            'message'     => 'Checkout berhasil.',
            'data'        => $order,
            'snap_token'  => $snapToken,
            'payment_url' => $paymentUrl,
        ], 201);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        return response()->json([
            'data' => $order->load(['items.product', 'items.review', 'payment', 'statusHistories', 'address']),
        ]);
    }

    /**
     * Customer konfirmasi pesanan sudah diterima (dalam_pengiriman → terkirim)
     */
    public function confirmDelivered(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status !== 'shipping') {
            return response()->json([
                'message' => 'Status pesanan tidak dapat diubah. Status saat ini: ' . $order->status,
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update(['status' => 'delivered']);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'delivered',
                'note'       => 'Pesanan dikonfirmasi telah diterima oleh pelanggan.',
                'location'   => 'Lokasi Pelanggan',
                'created_by' => $request->user()->id,
            ]);
        });

        return response()->json([
            'message' => 'Pesanan dikonfirmasi telah diterima.',
            'data'    => $order->fresh(['items', 'payment', 'statusHistories']),
        ]);
    }

    /**
     * Customer konfirmasi pesanan selesai (terkirim → selesai)
     */
    public function completeOrder(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status !== 'delivered') {
            return response()->json([
                'message' => 'Status pesanan tidak dapat diselesaikan. Status saat ini: ' . $order->status,
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update(['status' => 'completed']);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'completed',
                'note'       => 'Pesanan selesai dikonfirmasi oleh pelanggan.',
                'location'   => 'Lokasi Pelanggan',
                'created_by' => $request->user()->id,
            ]);
        });

        return response()->json([
            'message' => 'Pesanan berhasil diselesaikan.',
            'data'    => $order->fresh(['items', 'payment', 'statusHistories']),
        ]);
    }

    /**
     * Cek status pembayaran ke Midtrans secara manual (untuk tombol Cek Status)
     */
    public function checkPaymentStatus(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        // Jika sudah tidak pending, tidak perlu cek ke Midtrans
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Pesanan sudah tidak pending',
                'data' => $order->load(['items.product', 'items.review', 'payment', 'statusHistories', 'address']),
            ]);
        }

        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$clientKey    = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized  = config('midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds        = config('midtrans.is_3ds', true);

        try {
            $statusResponse = \Midtrans\Transaction::status($order->order_number);
            
            $transactionStatus = $statusResponse->transaction_status ?? null;
            $fraudStatus       = $statusResponse->fraud_status ?? null;
            $paymentType       = $statusResponse->payment_type ?? null;

            if ($transactionStatus) {
                DB::transaction(function () use ($order, $transactionStatus, $fraudStatus, $paymentType) {
                    [$orderStatus, $paymentStatus] = match (true) {
                        $transactionStatus === 'capture' && $fraudStatus === 'accept' => ['paid', 'paid'],
                        $transactionStatus === 'settlement'                           => ['paid', 'paid'],
                        $transactionStatus === 'pending'                              => ['pending',    'pending'],
                        in_array($transactionStatus, ['deny', 'expire', 'cancel'])    => ['cancelled',  'failed'],
                        default                                                       => ['pending',    'pending'],
                    };

                    if ($orderStatus !== 'pending') {
                        $order->update(['status' => $orderStatus]);

                        if ($order->payment) {
                            $order->payment->update([
                                'status' => $paymentStatus,
                                'method' => $paymentType ?? $order->payment->method,
                            ]);
                        }

                        \App\Models\OrderStatusHistory::create([
                            'order_id'   => $order->id,
                            'status'     => $orderStatus,
                            'note'       => "Pembayaran {$transactionStatus} via Midtrans (Pengecekan Manual).",
                            'location'   => 'Midtrans Gateway',
                            'created_by' => $order->user_id,
                        ]);
                    }
                });
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Check Status Midtrans Error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Status berhasil dicek',
            'data' => $order->fresh(['items.product', 'items.review', 'payment', 'statusHistories', 'address']),
        ]);
    }

    public function calculateShipping(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $storeSettings = SellerSetting::query()->first();
        $storeLat = $storeSettings?->shop_latitude ? (float) $storeSettings->shop_latitude : 5.1801;
        $storeLng = $storeSettings?->shop_longitude ? (float) $storeSettings->shop_longitude : 97.1419;

        $userLat = (float) $validated['latitude'];
        $userLng = (float) $validated['longitude'];

        // Haversine formula
        $earthRadius = 6371; // in km
        $dLat = deg2rad($userLat - $storeLat);
        $dLon = deg2rad($userLng - $storeLng);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($storeLat)) * cos(deg2rad($userLat)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        $distance = $earthRadius * $c;

        $cost = 0;
        if ($distance <= 5) {
            $cost = 0;
        } elseif ($distance <= 10) {
            $cost = 3000;
        } elseif ($distance <= 25) {
            $cost = 8000;
        } else {
            $cost = 12000;
        }

        return response()->json([
            'distance_km' => round($distance, 2),
            'shipping_cost' => $cost,
        ]);
    }
}
