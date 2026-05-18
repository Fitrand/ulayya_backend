<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$clientKey    = config('midtrans.client_key');
        MidtransConfig::$isProduction = config('midtrans.is_production', false);
        MidtransConfig::$isSanitized  = config('midtrans.is_sanitized', true);
        MidtransConfig::$is3ds        = config('midtrans.is_3ds', true);
    }

    /**
     * Terima notifikasi pembayaran dari Midtrans.
     * Endpoint ini di-hit oleh server Midtrans (tidak butuh auth).
     */
    public function notification(Request $request)
    {
        try {
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $paymentType       = $notification->payment_type;
            $orderId           = $notification->order_id; // = order_number kita
            $fraudStatus       = $notification->fraud_status;

            Log::info('Midtrans notification', [
                'order_id'   => $orderId,
                'status'     => $transactionStatus,
                'fraud'      => $fraudStatus,
                'payment'    => $paymentType,
            ]);

            // Temukan order berdasarkan order_number
            $order = Order::where('order_number', $orderId)->firstOrFail();
            $payment = $order->payment;

            DB::transaction(function () use (
                $order, $payment, $transactionStatus, $fraudStatus, $paymentType
            ) {
                [$orderStatus, $paymentStatus] = $this->resolveStatus(
                    $transactionStatus,
                    $fraudStatus
                );

                // Update payment
                if ($payment) {
                    $payment->update([
                        'status' => $paymentStatus,
                        'method' => $paymentType ?? $payment->method,
                    ]);
                }

                // Update order hanya jika status berubah
                if ($order->status !== $orderStatus) {
                    $order->update(['status' => $orderStatus]);

                    OrderStatusHistory::create([
                        'order_id'   => $order->id,
                        'status'     => $orderStatus,
                        'note'       => "Pembayaran {$transactionStatus} via Midtrans.",
                        'location'   => 'Midtrans Gateway',
                        'created_by' => $order->user_id,
                    ]);
                }
            });

            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    /**
     * Petakan transaction_status Midtrans ke status order & payment kita.
     *
     * Midtrans transaction_status:
     *   capture   → capture (kartu kredit, cek fraud)
     *   settlement → lunas
     *   pending   → menunggu bayar
     *   deny / expire / cancel → dibatalkan / expired
     */
    private function resolveStatus(string $txStatus, ?string $fraudStatus): array
    {
        return match (true) {
            $txStatus === 'capture' && $fraudStatus === 'accept' => ['paid', 'paid'],
            $txStatus === 'settlement'                           => ['paid', 'paid'],
            $txStatus === 'pending'                              => ['pending',    'pending'],
            in_array($txStatus, ['deny', 'expire', 'cancel'])   => ['cancelled',  'failed'],
            default                                              => ['pending',    'pending'],
        };
    }
}
