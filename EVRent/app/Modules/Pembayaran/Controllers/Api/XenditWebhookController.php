<?php

namespace App\Modules\Pembayaran\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Modules\Pembayaran\Models\M_XenditPayment;
use App\Modules\Transaksi\Models\M_Pemesanan;
use Xendit\Xendit;

class XenditWebhookController extends Controller
{
    public function handleInvoice(Request $request)
    {
        // 1. Verifikasi Token (Opsional)
        // $xenditToken = $request->header('x-callback-token');
        // if ($xenditToken !== env('XENDIT_CALLBACK_TOKEN')) {
        //    return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // 2. Ambil data JSON dari Xendit
        $data = $request->all();

        try {
            // Catat ke log
            Log::info('Xendit Webhook Received:', $data);

            // 3. Cek status
            if (isset($data['status'])) {
                $external_id = $data['external_id'] ?? null;
                $status = $data['status']; // PAID, EXPIRED, etc.

                if ($external_id && ($status === 'PAID' || $status === 'SETTLED')) {
                    
                    // 4. Update Database
                    // Gunakan updateOrCreate pada M_XenditPayment
                    $payment = M_XenditPayment::where('external_id', $external_id)->first();
                    
                    if ($payment) {
                        $payment->update([
                            'status' => 'PAID', // Map to local status if needed
                            'amount' => $data['amount'],
                            'payment_channel' => $data['bank_code'] ?? 'Virtual Account', // Map 'channel'
                            'paid_at' => \Carbon\Carbon::parse($data['paid_at']),
                            'raw_response' => json_encode($data)
                        ]);

                        // Update Status Pemesanan
                        if ($payment->pemesanan) {
                            $payment->pemesanan->update(['status_sewa' => 'dibayar']);
                            
                            // Update generic M_Pembayaran record if exists
                             \App\Modules\Pembayaran\Models\M_Pembayaran::where('id_pemesanan', $payment->id_pemesanan)
                                ->update(['status_bayar' => 'terverifikasi']);
                        }

                        return response()->json(['message' => 'Payment processed successfully'], 200);
                    }
                }

                // Jika EXPIRED
                if ($status === 'EXPIRED') {
                    $payment = M_XenditPayment::where('external_id', $external_id)->first();
                     if ($payment) {
                        $payment->update(['status' => 'EXPIRED']);
                        if ($payment->pemesanan) {
                             $payment->pemesanan->update(['status_sewa' => 'dibatalkan']);
                        }
                     }
                     return response()->json(['message' => 'Payment expired'], 200);
                }
            }

            return response()->json(['message' => 'Webhook received but ignored'], 200);

        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
