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
    public $timestamps = false;
    protected $fillable = ['no_transaksi', 'tgl_transaksi', 'total_harga', 'status'];
    protected $casts = [
        'tgl_transaksi' => 'datetime',
    ];


    /**
     * Relasi ke tabel penjualan_detail.
     */
    public function penjualanDetail()
    {
        return $this->hasMany(PenjualanDetail::class, 'no_transaksi', 'no_transaksi');
    }

    /**
     * Relasi ke tabel barang.
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
    
    /**
     * Buat dan ambil nomor transaksi berikutnya.
     */
    public static function getNoTransaksi()
    {
        $latest = DB::table('penjualan')
            ->select(DB::raw("IFNULL(MAX(no_transaksi), 'FK-000') AS no_transaksi"))
            ->first();

        $currentNumber = (int) substr($latest->no_transaksi, 3);
        $nextNumber = $currentNumber + 1;

        return 'FK-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung total harga dari seluruh detail penjualan.
     */
    public function hitungTotalDariDetails(): int
    {
        return $this->PenjualanDetail->sum('total');
    }
    
    /**
     * Update otomatis total_harga dari penjualan_detail saat disimpan.
     */
    protected static function booted(): void
    {
        static::saving(function (Penjualan $penjualan) {
            $penjualan->total_harga = $penjualan->hitungTotalDariDetails();
        });
    }
}