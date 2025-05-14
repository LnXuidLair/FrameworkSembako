<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PembelianBarang extends Model
{
    use HasFactory;

    protected $table = 'pembelian_barang';
    protected $fillable = ['pembelian_id', 'id_vendor', 'harga_beli', 'jml', 'tgl'];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'id_vendor');
    }
}
