<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; } /* 👈 Tambahkan ini */
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Daftar Penjualan</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center">No Transaksi</th>
                <th class="text-center">Total Transaksi</th>        
                <th class="text-center">Tanggal Transaksi</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan as $p)
            <tr>
                <td class="text-center">{{ $p->no_transaksi }}</td>
                <td class="text-right">{{ rupiah($p->total_harga) }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($p->tgl_transaksi)->translatedFormat('d F Y') }}</td>
                <td class="text-center">{{ $p->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
