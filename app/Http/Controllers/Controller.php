namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembelianPersediaan;

class PembelianPersediaanController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_vendor'      => 'required|exists:vendors,id',
            'id_pembelian'   => 'required|string',
            'tgl_pembelian'  => 'required|date',
            'nama_barang'    => 'required|string',
            'harga_barang'   => 'required|numeric',
            'satuan_barang'  => 'required|string',
            'qty'            => 'required|integer',
        ]);

        $total_beli = (int) $request->harga_barang * (int) $request->qty;

        PembelianPersediaan::create([
            ...$validated,
            'total_beli' => $total_beli,
        ]);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }
}
