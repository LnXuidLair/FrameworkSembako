<?php 
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Penjualan;
// use App\Models\PenjualanBarang;

class TotalPenjualanChart extends ChartWidget
{
    protected static ?string $heading = 'Total Penjualan'; // Judul widget chart

    // Mendapatkan data untuk chart
    protected function getData(): array
    {
        // Ambil data total penjualan berdasarkan rumus (total - harga_beli) * jumlah
        $data = Penjualan::query()
            ->join('penjualan_detail', 'penjualan.no_transaksi', '=', 'penjualan_detail.no_transaksi')
            ->join('barang', 'penjualan_detail.id_barang', '=', 'barang.id')
            ->where('penjualan.status', 'Bayar') // Hanya status 'Bayar'
            ->selectRaw('barang.nama_barang, SUM(penjualan_detail.total * penjualan_detail.jml_barang) as total_penjualan')
            ->groupBy('barang.nama_barang')
            ->get()
            ->map(function ($penjualan) {
                return [
                    'nama_barang' => $penjualan->nama_barang,
                    'total_penjualan' => $penjualan->total_penjualan,
                ];
            });
            // dd($data); // untuk melihat data sebelum dikirim ke chart

        // Pastikan data ada sebelum dikirim ke chart
        if ($data->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Mengembalikan data dalam format yang dibutuhkan untuk chart
        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data->pluck('total_penjualan')->toArray(), // Data untuk chart
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $data->pluck('nama_barang')->toArray(), // Label untuk sumbu X
        ];
    }

    // Jenis chart yang digunakan, misalnya bar chart
    protected function getType(): string
    {
        return 'bar'; // Tipe chart bisa diganti sesuai kebutuhan, seperti 'line', 'pie', dll.
    }
}
