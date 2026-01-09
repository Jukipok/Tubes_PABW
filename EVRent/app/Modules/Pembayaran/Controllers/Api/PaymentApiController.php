<?php

namespace App\Modules\Pembayaran\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Pembayaran\Models\M_Pembayaran;
use App\Modules\Pembayaran\Models\M_XenditPayment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentApiController extends Controller
{
    private $apiInstance;

    public function __construct()
    {
        Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $this->apiInstance = new InvoiceApi();
    }

    public function createInvoice(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required|exists:pemesanans,id_pemesanan',
        ]);

        $id_pemesanan = $request->id_pemesanan;
        $pemesanan = M_Pemesanan::with(['pelanggan.user', 'kendaraan'])->find($id_pemesanan);

        if (!$pemesanan) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($request->user()->id !== $pemesanan->pelanggan->user->id) {
             return response()->json(['message' => 'Unauthorized access to this booking'], 403);
        }

        if ($pemesanan->status_sewa === 'dibayar') {
            return response()->json(['message' => 'Booking already paid'], 400);
        }

        try {
            $external_id = 'booking_' . $pemesanan->id_pemesanan . '_' . Str::random(5);
            
            if ($mobile_number) {
                 $mobile_number = preg_replace('/[^0-9]/', '', $mobile_number);
                 if (substr($mobile_number, 0, 1) === '0') $mobile_number = '+62' . substr($mobile_number, 1);
                 if (substr($mobile_number, 0, 1) !== '+') $mobile_number = '+' . $mobile_number;
                 if (strlen($mobile_number) < 10 || strlen($mobile_number) > 15) $mobile_number = null;
            }

            $customerData = [
                'given_names' => $pemesanan->pelanggan->user->name ?? 'Guest',
                'email' => $pemesanan->pelanggan->user->email ?? 'guest@example.com',
            ];
            if ($mobile_number) $customerData['mobile_number'] = $mobile_number;

            $create_invoice_request = new CreateInvoiceRequest([
                'external_id' => $external_id,
                'description' => "Sewa Kendaraan " . $pemesanan->kendaraan->merk_kendaraan . " (" . $pemesanan->durasi_sewa . " Jam)",
                'amount' => (float) $pemesanan->total_biaya,
                'invoice_duration' => 172800,
                'currency' => 'IDR',
                'customer' => $customerData,
                'success_redirect_url' => $request->input('success_redirect_url'),
                'failure_redirect_url' => $request->input('failure_redirect_url'),
            ]);

            $result = $this->apiInstance->createInvoice($create_invoice_request);

            M_Pembayaran::create([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'metode_pembayaran' => 'xendit_invoice',
                'jumlah_bayar' => $pemesanan->total_biaya,
                'status_bayar' => 'menunggu_pembayaran',
                'bukti_transfer' => $result['invoice_url'],
                'tanggal_bayar' => now(),
            ]);

            M_XenditPayment::create([
                'external_id' => $external_id,
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'payment_id' => $result['id'],
                'status' => $result['status'],
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'raw_response' => json_encode($result)
            ]);

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice_url' => $result['invoice_url'],
                'external_id' => $external_id,
                'status' => $result['status'],
                'expiry_date' => $result['expiry_date']
            ]);

        } catch (\Exception $e) {
            Log::error('Xendit API Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create invoice: ' . $e->getMessage()], 500);
        }
    }
}
