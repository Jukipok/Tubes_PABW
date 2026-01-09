<?php

namespace App\Modules\Pembayaran\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Modules\Transaksi\Models\M_Pemesanan;
use App\Modules\Pembayaran\Models\M_Pembayaran;
use App\Modules\Pembayaran\Models\M_XenditPayment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class C_Xendit extends Controller
{
    private $apiInstance;

    public function __construct()
    {
        Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $this->apiInstance = new InvoiceApi();
    }

    public function createInvoice($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::with(['pelanggan.user', 'kendaraan'])->findOrFail($id_pemesanan);

        if ($pemesanan->status_sewa === 'dibayar') {
            return redirect()->route('my_bookings')->with('success', 'Pesanan ini sudah dibayar.');
        }

        $external_id = 'booking_' . $pemesanan->id_pemesanan . '_' . Str::random(5);
        
        $mobile_number = $pemesanan->pelanggan->user->no_hp;
        if ($mobile_number) {
            $mobile_number = preg_replace('/[^0-9]/', '', $mobile_number);
            if (substr($mobile_number, 0, 1) === '0') {
                $mobile_number = '+62' . substr($mobile_number, 1);
            }
            if (substr($mobile_number, 0, 1) !== '+') {
                 $mobile_number = '+' . $mobile_number;
            }
            if (strlen($mobile_number) < 10 || strlen($mobile_number) > 15) {
                $mobile_number = null; 
            }
        }
        
        $customerData = [
            'given_names' => $pemesanan->pelanggan->user->name ?? 'Guest',
            'email' => $pemesanan->pelanggan->user->email ?? 'guest@example.com',
        ];

        if ($mobile_number) {
            $customerData['mobile_number'] = $mobile_number;
        }

        $create_invoice_request = new CreateInvoiceRequest([
            'external_id' => $external_id,
            'description' => "Sewa Kendaraan " . $pemesanan->kendaraan->merk_kendaraan . " (" . $pemesanan->durasi_sewa . " Jam)",
            'amount' => (float) $pemesanan->total_biaya,
            'invoice_duration' => 172800,
            'currency' => 'IDR',
            'customer' => $customerData,
            'success_redirect_url' => route('payment.success', ['id' => $pemesanan->id_pemesanan]),
            'failure_redirect_url' => route('my_bookings'),
        ]);

        try {
            $result = $this->apiInstance->createInvoice($create_invoice_request);
            
             M_Pembayaran::create([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'metode_pembayaran' => 'xendit_invoice',
                'jumlah_bayar' => $pemesanan->total_biaya,
                'status_bayar' => 'menunggu_pembayaran',
                'bukti_transfer' => $result['invoice_url'],
                'tanggal_bayar' => now(),
            ]);

            \App\Modules\Pembayaran\Models\M_XenditPayment::create([
                'external_id' => $external_id,
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'payment_id' => $result['id'],
                'status' => $result['status'],
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'raw_response' => json_encode($result)
            ]);

            return redirect($result['invoice_url']);

        } catch (\Xendit\XenditSdkException $e) {
            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $id_pemesanan = $request->input('id');
        
        if (!$id_pemesanan) {
            return redirect()->route('katalog');
        }

        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        
        if ($pemesanan->status_sewa !== 'dibayar') {
             $pemesanan->update(['status_sewa' => 'dibayar']);
             
             M_Pembayaran::where('id_pemesanan', $id_pemesanan)
                 ->latest()
                 ->update(['status_bayar' => 'terverifikasi']);

             \App\Modules\Pembayaran\Models\M_XenditPayment::where('id_pemesanan', $id_pemesanan)
                 ->latest()
                 ->update([
                     'status' => 'PAID',
                     'paid_at' => now()
                 ]);
        }

        return redirect()->route('my_bookings')->with('success', 'Pembayaran berhasil! Terima kasih.');
    }

    public function callback(Request $request) 
    {
        return response()->json(['status' => 'ok']);
    }
}
