<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pembayaran;
use App\Models\Barang; // Needed for stock decrement
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Throwable; // <-- TAMBAHKAN INI
use Illuminate\Support\Facades\Log; // <-- TAMBAHKAN INI

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function beforeCreate(): void
    {
        $this->data['status'] = $this->data['status'] ?? 'Bayar';
    }

    // afterCreate() ini tidak diperlukan jika simpanPembayaran() yang menangani semua proses save.
    // protected function afterCreate(): void { /* ... */ }


    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('Bayar')
                ->label('Bayar dan Simpan')
                ->color('success')
                ->action(fn () => $this->simpanPembayaran()) // Memanggil method custom kita
                ->requiresConfirmation() // Menampilkan popup konfirmasi
                ->modalHeading('Konfirmasi Pembayaran')
                ->modalDescription('Apakah Anda yakin ingin menyimpan dan memproses pembayaran untuk transaksi ini?')
                ->modalButton('Proses Pembayaran'),

            Actions\Action::make('cancel')
                ->label(__('Cancel')) // Menggunakan helper __() untuk terjemahan
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function simpanPembayaran()
    {
        // 1. Validasi form terlebih dahulu. Ini SANGAT PENTING.
        // Jika validasi gagal (misalnya repeater kosong padahal required),
        // ini akan melemparkan ValidationException dan menghentikan eksekusi.
        try {
            $this->form->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Validasi Gagal')
                ->body('Pastikan semua field yang wajib diisi sudah terisi dengan benar, termasuk detail barang. Silakan periksa kembali form Anda.')
                ->danger()
                ->send();
            // Penting: Melemparkan kembali exception agar Filament bisa menampilkan error di field-field form.
            throw $e;
        }

        // Ambil seluruh data dari form setelah validasi berhasil
        $data = $this->form->getState();

        // Lapisan keamanan ekstra: Pastikan 'penjualanDetail' ada dan berupa array,
        // meskipun seharusnya sudah dijamin oleh validasi 'required()' dan 'minItems(1)'.
        $penjualanDetailsData = $data['penjualanDetail'] ?? [];

        if (empty($penjualanDetailsData)) {
            Notification::make()
                ->title('Detail Barang Kosong')
                ->body('Anda harus menambahkan setidaknya satu item barang untuk melanjutkan transaksi.')
                ->danger()
                ->send();
            return; // Hentikan eksekusi jika repeater kosong
        }

        DB::beginTransaction(); // Mulai transaksi database untuk atomicity

        try {
            // 2. Buat record Penjualan utama
            $penjualan = Penjualan::create([
                'no_transaksi'  => $data['no_transaksi'],
                'tgl_transaksi' => $data['tgl_transaksi'],
                'status'        => 'Bayar', // Set status langsung di sini
                'total_harga'   => 0, // Akan diupdate setelah detail disimpan
            ]);

            $totalHargaPenjualan = 0;

            // 3. Iterasi dan buat record PenjualanDetail, serta kurangi stok barang
            foreach ($penjualanDetailsData as $itemData) { // Gunakan variabel yang sudah diamankan
                $barang = Barang::find($itemData['id_barang']);

                if (!$barang) {
                    throw new \Exception("Barang dengan ID '{$itemData['id_barang']}' tidak ditemukan. Silakan refresh dan coba lagi.");
                }

                $hargaPerBarang = (int) ($itemData['harga_barang'] ?? 0);
                $jumlahBarang = (int) ($itemData['jml_barang'] ?? 0); // Pastikan ini 'jumlahBarang'

                if ($jumlahBarang <= 0) {
                    throw new \Exception("Jumlah barang untuk '{$barang->nama_barang}' harus lebih dari 0.");
                }

                // Cek ketersediaan stok
                if ($barang->stok < $jumlahBarang) { // Pastikan ini 'jumlahBarang'
                    throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi. Tersedia: {$barang->stok}, Diminta: {$jumlahBarang}.");
                }

                $totalPerItem = $hargaPerBarang * $jumlahBarang;

                PenjualanDetail::create([
                    'no_transaksi' => $penjualan->no_transaksi, // Tautkan ke penjualan yang baru dibuat
                    'id_barang'    => $itemData['id_barang'],
                    'harga_barang' => $hargaPerBarang,
                    'jml_barang'   => $jumlahBarang,
                    'total'        => $totalPerItem,
                ]);

                // Kurangi stok barang
                $barang->decrement('stok', $jumlahBarang);

                $totalHargaPenjualan += $totalPerItem;
            }

            // 4. Update total_harga di record Penjualan utama
            $penjualan->update(['total_harga' => $totalHargaPenjualan]);

            // 5. Buat record Pembayaran
            Pembayaran::create([
                'penjualan_id'     => $penjualan->id,
                'tgl_bayar'        => now(),
                'jenis_pembayaran' => 'tunai', // Atau ambil dari field form jika ada
                'transaction_time' => now(),
                'gross_amount'     => $totalHargaPenjualan, // Gunakan total yang sudah dihitung
                'order_id'         => $penjualan->no_transaksi,
            ]);

            DB::commit(); // Commit transaksi jika semua berhasil

            // Notifikasi sukses
            Notification::make()
                ->title('Pembayaran Berhasil!')
                ->body('Transaksi ' . $penjualan->no_transaksi . ' telah berhasil disimpan dan dibayar.')
                ->success()
                ->send();

            // Redirect ke halaman index setelah sukses
            return redirect()->to($this->getResource()::getUrl('index'));

        } catch (Throwable $e) { // Tangkap semua jenis Throwable
            DB::rollBack(); // Rollback transaksi jika ada kesalahan
            // Notifikasi kesalahan
            Notification::make()
                ->title('Terjadi Kesalahan!')
                ->body($e->getMessage()) // Tampilkan pesan kesalahan yang spesifik
                ->danger()
                ->send();
            // Log kesalahan untuk debugging
            Log::error('Penjualan payment failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Tidak perlu redirect, tetap di halaman form agar user bisa memperbaiki
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}