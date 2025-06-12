<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Penjualan;
use App\Models\Coa;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Form;
use Filament\Forms\Components\Section as FormSection;
use Carbon\Carbon;

class BukuBesar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.pages.buku-besar';
    protected static ?string $navigationGroup = 'Akuntansi';
    protected static ?string $navigationLabel = 'Buku Besar';

    public ?string $akunDipilih = null;

    public function form(Form $form): Form
    {
        // Ambil daftar akun dari model Coa
        $coaOptions = Coa::pluck('nama_akun', 'nama_akun')->toArray();

        // Tambahkan opsi 'Lihat Semua' di awal array options
        // Menggunakan kunci kosong '' atau null untuk merepresentasikan "tidak ada filter"
        $filterOptions = ['' => 'Lihat Semua'] + $coaOptions;

        return $form
            ->schema([
                FormSection::make('Filter Buku Besar')
                    ->description('Pilih akun untuk menampilkan transaksi buku besar atau lihat semua.')
                    ->schema([
                        FormsSelect::make('akunDipilih')
                            ->label('Pilih Akun')
                            ->placeholder('Pilih Akun') // Placeholder bisa tetap atau dihilangkan jika 'Lihat Semua' ada
                            // Gunakan $filterOptions yang sudah dimodifikasi
                            ->options($filterOptions)
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->default(''), // Set default ke string kosong agar 'Lihat Semua' terpilih saat pertama kali dibuka
                    ])
                    ->columns(1),
            ]);
    }

    public function updatedAkunDipilih(): void
    {
        // No change needed here
    }

    protected function getViewData(): array
    {
        $coa = Coa::all();
        $penjualan = Penjualan::with('penjualanDetail.barang')->get();

        $bukuBesar = [];

        foreach ($coa as $akun) {
            // Logika filter: Jika $akunDipilih TIDAK kosong DAN $akunDipilih TIDAK SAMA dengan nama akun saat ini,
            // maka lewati akun ini. Jika $akunDipilih kosong, berarti 'Lihat Semua' aktif, jadi semua akun diproses.
            if ($this->akunDipilih !== '' && $this->akunDipilih !== $akun->nama_akun) {
                continue;
            }

            $transaksiAkun = [];
            $saldoAkun = 0;

            foreach ($penjualan as $pj) {
                foreach ($pj->penjualanDetail as $item) {
                    if ($akun->nama_akun == 'Kas') {
                        $saldoAkun += $item->total;
                        $transaksiAkun[] = [
                            'tanggal' => $pj->tgl_transaksi,
                            'keterangan' => 'Penjualan Tunai No. ' . $pj->no_transaksi . ' (' . ($item->barang->nama_barang ?? 'N/A') . ')',
                            'debit' => $item->total,
                            'kredit' => 0,
                            'saldo' => $saldoAkun,
                        ];
                    } elseif ($akun->nama_akun == 'Pendapatan Barang') {
                        $saldoAkun += $item->total;
                        $transaksiAkun[] = [
                            'tanggal' => $pj->tgl_transaksi,
                            'keterangan' => 'Pendapatan dari penjualan ' . ($item->barang->nama_barang ?? 'Barang'),
                            'debit' => 0,
                            'kredit' => $item->total,
                            'saldo' => $saldoAkun,
                        ];
                    }
                }
            }

            if (!empty($transaksiAkun)) {
                $bukuBesar[] = [
                    'akun' => $akun->nama_akun,
                    'transaksi' => $transaksiAkun,
                    'saldo_akhir' => $saldoAkun,
                ];
            }
        }

        return [
            'bukuBesar' => $bukuBesar,
            'coaList' => $coa,
            'akunDipilih' => $this->akunDipilih,
        ];
    }
}