<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

// tambahan
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Penjualan;
use App\Models\Coa;
use App\Models\Pembeli;

use Illuminate\Support\Number;
use Carbon\Carbon;

class DashboardStatCards extends BaseWidget
{
    protected function getStats(): array
    {
        $now = now();
        $last7Days = $now->copy()->subDays(7);
        $previous7Days = $now->copy()->subDays(14);

        // Revenue 7 hari terakhir
        $currentRevenue = Penjualan::where('status', 'Bayar')
            ->whereBetween('tgl_transaksi', [$last7Days, $now])
            ->sum('total_harga');

        // Revenue 7 hari sebelumnya
        $pastRevenue = Penjualan::where('status', 'Bayar')
            ->whereBetween('tgl_transaksi', [$previous7Days, $last7Days])
            ->sum('total_harga');

        // Hitung persentase kenaikan/penurunan
        $change = $pastRevenue > 0
            ? round((($currentRevenue - $pastRevenue) / $pastRevenue) * 100, 2)
            : 100;

        $changeText = $change >= 0 ? "{$change}% naik" : abs($change) . '% turun';
        $changeColor = $change >= 0 ? 'success' : 'danger';
        $changeIcon = $change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';


        $startDate = ! is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            null;

        $endDate = ! is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        $isBusinessCustomersOnly = $this->filters['businessCustomersOnly'] ?? null;
        $businessCustomerMultiplier = match (true) {
            boolval($isBusinessCustomersOnly) => 2 / 3,
            blank($isBusinessCustomersOnly) => 1,
            default => 1 / 3,
        };

        $diffInDays = $startDate ? $startDate->diffInDays($endDate) : 0;

        $revenue = Penjualan::where('status', 'Bayar')->sum('total_harga');
        $newCustomers = (int) (($startDate ? ($diffInDays * 7) : 1340) * $businessCustomerMultiplier);
        $newOrders = (int) (($startDate ? ($diffInDays * 13) : 3543) * $businessCustomerMultiplier);

        $formatNumber = function (int $number): string {
            if ($number < 1000) {
                return (string) Number::format($number, 0);
            }

            if ($number < 1000000) {
                return Number::format($number / 1000, 2) . 'k';
            }

            return Number::format($number / 1000000, 2) . 'm';
        };

        return [
            Stat::make('Total Transaksi', Penjualan::count())
                ->description('Jumlah transaksi')
            ,
            Stat::make('Total Penjualan', rupiah(
                        Penjualan::query()
                        ->where('status', 'Bayar') // Filter hanya yang statusnya 'Bayar'
                        ->sum('total_harga')
                    ))
                ->description('Jumlah transaksi terBayar')
            ,
            Stat::make('Total Keuntungan', rupiah(
                Penjualan::query()
                ->join('penjualan_detail', 'Penjualan.no_transaksi', '=', 'penjualan_detail.no_transaksi') 
                ->where('status', 'Bayar') // Filter hanya yang statusnya 'Bayar'
                ->selectRaw('SUM((penjualan_detail.total - penjualan_detail.harga_barang) * penjualan_detail.jml_barang) as total_penjualan') // Perhitungan total penjualan
                ->value('total_penjualan') // Ambil hasil perhitungan
            ))
                ->description('Jumlah keuntungan')
            ,
            Stat::make('Revenue', rupiah($currentRevenue))
                ->description($changeText)
                ->descriptionIcon($changeIcon)
                ->color($changeColor)
                ->chart([7, 2, 10, 3, 15, 4, 17])
            ,
        ];
    }

    // tambahan untuk kartu
    protected function getCards(): array
    {
        return [
            // Card::make('Total Transaksi', Penjualan::count())
            //     ->description('Jumlah transaksi yang tercatat')
            //     // ->color('primary')
            // ,
            // Card::make('Total Pendapatan', 'Rp ' . number_format(\App\Models\Transaksi::sum('total')))
            //     ->description('Total uang masuk')
            //     ->color('success'),

            // Card::make('Jumlah Akun COA', Coa::count())
            //     ->description('Data akun aktif')
            //     ->color('warning'),
        ];
    }
}