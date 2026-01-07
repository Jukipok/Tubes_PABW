<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Models\M_Pemesanan;
use App\Models\M_Pembayaran;
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

    /**
     * Create Invoice and Redirect to Xendit Payment Page
     */
    public function createInvoice($id_pemesanan)
    {
        $pemesanan = M_Pemesanan::with(['pelanggan.user', 'kendaraan'])->findOrFail($id_pemesanan);

        // Validasi: Jangan buat invoice jika sudah dibayar
        if ($pemesanan->status_sewa === 'dibayar') {
            return redirect()->route('my_bookings')->with('success', 'Pesanan ini sudah dibayar.');
        }

        $external_id = 'booking_' . $pemesanan->id_pemesanan . '_' . Str::random(5);
        
        // Sanitize Phone Number (Mobile Number must be in E.164 format, e.g., +628123456789)
        $mobile_number = $pemesanan->pelanggan->user->no_hp;
        if ($mobile_number) {
            // Remove non-numeric characters
            $mobile_number = preg_replace('/[^0-9]/', '', $mobile_number);
            // If starts with 0, replace with +62
            if (substr($mobile_number, 0, 1) === '0') {
                $mobile_number = '+62' . substr($mobile_number, 1);
            }
            // Ensure starts with +, if not add + (though usually handled above, if user enters 628... )
            if (substr($mobile_number, 0, 1) !== '+') {
                 $mobile_number = '+' . $mobile_number;
            }
            // Length check (simplest check)
            if (strlen($mobile_number) < 10 || strlen($mobile_number) > 15) {
                $mobile_number = null; // Invalid length, ignore
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
            'amount' => (float) $pemesanan->total_biaya, // Ensure float
            'invoice_duration' => 172800, // 48 jam
            'currency' => 'IDR',
            'customer' => $customerData,
            'success_redirect_url' => route('payment.success', ['id' => $pemesanan->id_pemesanan]),
            'failure_redirect_url' => route('my_bookings'),
        ]);

        try {
            $result = $this->apiInstance->createInvoice($create_invoice_request);
            
            // Simpan data pembayaran sementara (opsional, jika ingin track invoice ID)
             M_Pembayaran::create([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'metode_pembayaran' => 'xendit_invoice',
                'jumlah_bayar' => $pemesanan->total_biaya,
                'status_bayar' => 'menunggu_pembayaran',
                'bukti_transfer' => $result['invoice_url'], // Simpan URL invoice sebagai referensi
                'tanggal_bayar' => now(), // Tanggal generate invoice
            ]);

            // [NEW] Simpan detail teknis ke tabel khusus Xendit untuk laporan
            \App\Models\M_XenditPayment::create([
                'external_id' => $external_id,
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'payment_id' => $result['id'], // ID dari Xendit
                'status' => $result['status'], // PENDING
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

    /**
     * Handle Redirect Success from Xendit
     */
    public function success(Request $request)
    {
        // Di environment production, validasi pembayaran sebaiknya via Webhook/Callback.
        // Untuk demo/testing, kita cek status invoice manual atau langsung anggap sukses jika ada parameter tertentu.
        
        $id_pemesanan = $request->input('id');
        
        if (!$id_pemesanan) {
            return redirect()->route('katalog');
        }

        $pemesanan = M_Pemesanan::findOrFail($id_pemesanan);
        
        // Update status jika belum (jika webhook belum masuk)
        if ($pemesanan->status_sewa !== 'dibayar') {
             $pemesanan->update(['status_sewa' => 'dibayar']);
             
             // Update juga status pembayaran
             M_Pembayaran::where('id_pemesanan', $id_pemesanan)
                 ->latest()
                 ->update(['status_bayar' => 'terverifikasi']);

             // [NEW] Update status di tabel XenditPayment
             \App\Models\M_XenditPayment::where('id_pemesanan', $id_pemesanan)
                 ->latest()
                 ->update([
                     'status' => 'PAID',
                     'paid_at' => now()
                 ]);
        }

        return redirect()->route('my_bookings')->with('success', 'Pembayaran berhasil! Terima kasih.');
    }

    /**
     * Webhook Handler (Opsional untuk deployment)
     */
    public function callback(Request $request) 
    {
        // Code untuk verifikasi token callback Xendit dan update database 
        // akan diletakkan di sini.
        // Get headers x-callback-token dan bandingkan dengan XENDIT_CALLBACK_TOKEN di .env
        
        return response()->json(['status' => 'ok']);
    }
}
