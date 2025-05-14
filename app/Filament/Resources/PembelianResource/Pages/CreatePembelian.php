<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

// tambahan untuk akses ke penjualabarang
use App\Models\Pembelian;
use App\Models\PembelianBarang;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

// untuk notifikasi
use Filament\Notifications\Notification;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;
    //penanganan kalau status masih kosong 
    protected function beforeCreate(): void
    {
        $this->data['status'] = $this->data['status'] ?? 'pesan';
    }

    // tambahan untuk simpan
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('bayar')
                ->label('Bayar')
                ->color('success')
                ->action(fn () => $this->simpanPembayaran())
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran')
                ->modalDescription('Apakah Anda yakin ingin menyimpan pembayaran ini?')
                ->modalButton('Ya, Bayar'),
        ];
    }

    // penanganan
    protected function simpanPembayaran()
    {
        // $pembelian = $this->record; // Ambil data pembelian yang sedang dibuat
        $pembelian = $this->record ?? Pembelian::latest()->first(); // Ambil pembelian terbaru jika null
        // Simpan ke tabel pembayaran2
        Pembayaran::create([
            'pembelian_id' => $pembelian->id,
            'tgl_beli'    => now(),
            'jenis_pembayaran' => 'tunai',
            'transaction_time' => now(),
            'gross_amount'       => $pembelian->tagihan, // Sesuaikan dengan field di tabel pembayaran
            'order_id' => $pembelian->no_faktur,
        ]);

        // Update status penjualan jadi "dibayar"
        $pembelian->update(['status' => 'bayar']);

        // Notifikasi sukses
        Notification::make()
            ->title('Pembayaran Berhasil!')
            ->success()
            ->send();
    }
}
