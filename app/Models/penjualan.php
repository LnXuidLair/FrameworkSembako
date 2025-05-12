<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = "penjualan";
    protected $guarded = [];

    /**
     * Relasi ke tabel barang.
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    /**
     * Ambil stok barang berdasarkan ID.
     */
    public static function getStock($id_barang)
    {
        return DB::table('barang')->where('id', $id_barang)->value('stok');
    }

    /**
     * Ambil semua data barang.
     */
    public static function getBarang()
    {
        return DB::table('barang')->get();
    }

    /**
     * Ambil data barang berdasarkan ID.
     */
    public static function getBarangId($id)
    {
        return DB::table('barang')->where('id', $id)->first();
    }

    /**
     * Buat dan ambil nomor transaksi berikutnya.
     */
   public static function getNoTransaksi()
    {
        $latest = DB::table('penjualan')
            ->select(DB::raw("IFNULL(MAX(no_transaksi), 'FK-00000') AS no_transaksi"))
            ->first();

        $currentNumber = (int) substr($latest->no_transaksi, 3); // FK- = 3 chars
        $nextNumber = $currentNumber + 1;

        return 'FK-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT); // FK-00001
    }
}