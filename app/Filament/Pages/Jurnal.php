<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Penjualan;

class Jurnal extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.jurnal-umum';
    protected static ?string $navigationGroup = 'Akuntansi';
    protected static ?string $navigationLabel = 'Jurnal';

    public $tahun;
    public $bulan;

    public function mount(): void
    {
        // Set the default year to the current year if no year is provided in the request
        $this->tahun = request('tahun') ?? now()->year;
        // The month can be null, meaning "all months"
        $this->bulan = request('bulan');
    }

    public function getViewData(): array
    {
        $query = Penjualan::with('PenjualanDetail.barang')
            ->whereYear('tgl_transaksi', $this->tahun);

        if (!empty($this->bulan)) {
            $query->whereMonth('tgl_transaksi', $this->bulan);
        }

        $penjualan = $query->get();

        $jurnal = [];
        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($penjualan as $transaksi) {
            foreach ($transaksi->PenjualanDetail as $detail) {
                $tgl = $transaksi->tgl_transaksi->format('d-m-Y');
                $no = $transaksi->no_transaksi;
                $keterangan = 'Penjualan barang ' . ($detail->barang->nama_barang ?? '');
                $debit = $detail->total;
                $kredit = $detail->total;

                $jurnal[] = [
                    'tanggal' => $tgl,
                    'no_transaksi' => $no,
                    'keterangan' => $keterangan,
                    'akun' => 'Kas',
                    'debit' => $debit,
                    'kredit' => 0,
                ];

                $jurnal[] = [
                    'tanggal' => '',
                    'no_transaksi' => '',
                    'keterangan' => '',
                    'akun' => 'Pendapatan Barang',
                    'debit' => 0,
                    'kredit' => $kredit,
                ];

                $totalDebit += $debit;
                $totalKredit += $kredit;
            }
        }

        return [
            'jurnal' => $jurnal,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'tahun' => $this->tahun,
            'bulan' => $this->bulan,
        ];
    }
}