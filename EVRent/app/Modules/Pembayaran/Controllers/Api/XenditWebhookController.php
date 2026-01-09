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


        $data = $request->all();

        try {
            Log::info('Xendit Webhook Received:', $data);

            if (isset($data['status'])) {
                $external_id = $data['external_id'] ?? null;
                $status = $data['status'];

                if ($external_id && ($status === 'PAID' || $status === 'SETTLED')) {
                    
                    $payment = M_XenditPayment::where('external_id', $external_id)->first();
                    
                    if ($payment) {
                        $payment->update([
                            'status' => 'PAID',
                            'amount' => $data['amount'],
                            'payment_channel' => $data['bank_code'] ?? 'Virtual Account',
                            'paid_at' => \Carbon\Carbon::parse($data['paid_at']),
                            'raw_response' => json_encode($data)
                        ]);

                        if ($payment->pemesanan) {
                            $payment->pemesanan->update(['status_sewa' => 'dibayar']);
                            
                             \App\Modules\Pembayaran\Models\M_Pembayaran::where('id_pemesanan', $payment->id_pemesanan)
                                ->update(['status_bayar' => 'terverifikasi']);
                        }

                        return response()->json(['message' => 'Payment processed successfully'], 200);
                    }
                }

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
