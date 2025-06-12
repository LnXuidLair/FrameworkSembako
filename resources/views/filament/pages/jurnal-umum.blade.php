<x-filament::page>
    <form method="GET" class="mb-6 flex gap-4 flex-wrap items-end p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-sm">
        {{-- Bulan Filter --}}
        <div>
            <label for="bulan" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Bulan:</label>
            <select name="bulan" id="bulan" class="filament-forms-select-input w-full rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-primary-500 focus:border-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">-- Semua Bulan --</option>
                @foreach ([
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ] as $num => $name)
                    <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tahun Filter --}}
        <div>
            <label for="tahun" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tahun:</label>
            <select name="tahun" id="tahun" class="filament-forms-select-input w-full rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-primary-500 focus:border-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                @for ($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        {{-- Submit Button --}}
        <div>
            <button type="submit" class="mt-6 inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-900">
                Tampilkan
            </button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200">
            <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 text-center text-gray-700 dark:text-white font-semibold">
                <tr>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600">Tanggal</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600">No Transaksi</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600">Keterangan</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600">Akun</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600">Debit</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jurnal as $row)
                    <tr class="border-t border-gray-300 dark:border-gray-700">
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $row['tanggal'] }}</td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $row['no_transaksi'] }}</td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $row['keterangan'] }}</td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 {{ $row['kredit'] > 0 ? 'text-right' : '' }}">
                            {{ $row['akun'] }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right">
                            {{ $row['debit'] != 0 ? rupiah($row['debit']) : '' }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right">
                            {{ $row['kredit'] != 0 ? rupiah($row['kredit']) : '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold bg-gray-100 dark:bg-gray-800 border-t border-gray-300 dark:border-gray-700">
                    <td colspan="4" class="text-center px-4 py-2 border border-gray-300 dark:border-gray-700">Total</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right">
                        {{ $totalDebit != 0 ? rupiah($totalDebit) : '' }}
                    </td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right">
                        {{ $totalKredit != 0 ? rupiah($totalKredit) : '' }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-filament::page>