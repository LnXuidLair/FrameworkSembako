@php
    $pembayarans = $pembayarans ?? [];
@endphp
<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">No Transaksi</th>
            <th class="border border-gray-300 px-4 py-2">Tanggal Bayar</th>
            <th class="border border-gray-300 px-4 py-2">Nominal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pembayarans as $pembayaran)
            <tr>
                <td class="border border-gray-300 px-4 py-2">{{ $pembayaran->no_transaksi }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $pembayaran->tgl_transaksi }}</td>
                <td class="border border-gray-300 px-4 py-2">Rp{{ number_format($pembayaran->total_harga, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
