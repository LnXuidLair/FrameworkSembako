<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_detail';
    protected $guarded = [];
    public $timestamps = false;
    protected $fillable = ['no_transaksi', 'id_barang', 'harga_barang', 'jml_barang', 'total'];

    /**
     * Relasi ke penjualan utama.
     */
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'no_transaksi', 'no_transaksi');
    }
    /**
     * Relasi ke barang.
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
}