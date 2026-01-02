<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class C_Xendit extends Controller
{
    public function __construct()
    {
        // Set API Key from Environment
        if (env('XENDIT_API_KEY')) {
            Configuration::setXenditKey(env('XENDIT_API_KEY'));
        }
    }

    public function createInvoice($id_pemesanan)
    {
        $pemesanan = \App\Models\M_Pemesanan::withoutGlobalScope('active')->findOrFail($id_pemesanan);
        $user = auth()->user();

        // Create Invoice Params
        $params = new CreateInvoiceRequest([
            'external_id' => 'booking_' . $pemesanan->id_pemesanan . '_' . time(),
            'payer_email' => $user->email ?? 'customer@example.com',
            'description' => 'Sewa Kendaraan: ' . $pemesanan->kendaraan->merk_kendaraan,
            'amount' => (float) $pemesanan->total_biaya,
            'invoice_duration' => 172800, // 48 Hours
            'currency' => 'IDR',
            'success_redirect_url' => route('payment.success', ['id' => $pemesanan->id_pemesanan]),
            'failure_redirect_url' => route('katalog'),
        ]);

        try {
            $apiInstance = new InvoiceApi();
            $createInvoice = $apiInstance->createInvoice($params);
            
            // Redirect user to Xendit Checkout Page
            return redirect($createInvoice['invoice_url']);

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        // Simple Success Handling (Demo)
        // In real app, use Webhook for security. Here we just update if ID is present.
        if ($request->has('id')) {
            $pemesanan = \App\Models\M_Pemesanan::find($request->id);
            if ($pemesanan) {
                $pemesanan->update(['status_sewa' => 'dibayar']);
            }
        }

        return redirect()->route('katalog')->with('success', 'Pembayaran Berhasil! Pesanan Anda telah dikonfirmasi.');
    }
}
