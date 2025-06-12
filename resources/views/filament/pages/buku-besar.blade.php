<x-filament::page>
    <div class="space-y-6">
        {{-- Render form filter akun --}}
        <div class="max-w-screen-xl mx-auto">
            {{ $this->form }}
        </div>

        @forelse ($bukuBesar as $item)
            {{-- Menggunakan Filament Section untuk setiap buku besar akun --}}
            <x-filament::section
                :heading="$item['akun']"
                class="shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm filament-tables-table">
                        <thead>
                            <tr class="filament-tables-header-row">
                                <th class="filament-tables-header-cell px-4 py-2 text-left rtl:text-right text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="filament-tables-header-cell px-4 py-2 text-left rtl:text-right text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">Keterangan</th>
                                <th class="filament-tables-header-cell px-4 py-2 text-right text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">Debit</th>
                                <th class="filament-tables-header-cell px-4 py-2 text-right text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">Kredit</th>
                                <th class="filament-tables-header-cell px-4 py-2 text-right text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($item['transaksi'] as $transaksi)
                                <tr class="filament-tables-row hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($transaksi['tanggal'])->format('d-m-Y') }}</td>
                                    <td class="px-4 py-2">{{ $transaksi['keterangan'] }}</td>
                                    <td class="px-4 py-2 text-right font-mono">{{ rupiah($transaksi['debit']) }}</td>
                                    <td class="px-4 py-2 text-right font-mono">{{ rupiah($transaksi['kredit']) }}</td>
                                    <td class="px-4 py-2 text-right font-mono font-bold">{{ rupiah($transaksi['saldo']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        {{-- Saldo akhir akun --}}
                        <tfoot class="filament-tables-footer">
                            <tr class="bg-gray-100 dark:bg-gray-800 font-bold text-gray-700 dark:text-gray-300">
                                {{-- Perbaikan di sini: Tambahkan padding dan perkuat warna/font --}}
                                <td colspan="4" class="px-4 py-3 text-right text-md">Saldo Akhir {{ $item['akun'] }}:</td>
                                <td class="px-4 py-3 text-right text-md font-extrabold text-primary-600 dark:text-primary-400">{{ rupiah($item['saldo_akhir']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-filament::section>
        @empty
            <div class="filament-empty-state flex flex-col items-center justify-center p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow">
                <p class="text-gray-500 dark:text-gray-400 text-lg">Tidak ada transaksi yang ditemukan untuk akun ini atau belum ada data penjualan.</p>
                @if (!$akunDipilih)
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">Pilih akun dari dropdown di atas untuk melihat buku besar.</p>
                @endif
            </div>
        @endforelse
    </div>
</x-filament::page>

{{-- Pastikan fungsi rupiah() tersedia secara global atau melalui helper --}}
@php
if (!function_exists('rupiah')) {
    function rupiah($angka){
        if (is_null($angka)) {
            return 'Rp 0';
        }
        $hasil_rupiah = "Rp " . number_format($angka,0,',','.');
        return $hasil_rupiah;
    }
}
@endphp