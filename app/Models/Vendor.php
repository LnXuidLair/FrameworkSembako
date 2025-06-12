<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendor';
    protected $primaryKey = 'id_vendor';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($vendor) {
            $vendor->id_vendor = self::generateIdVendor();
        });
    }

    public static function generateIdVendor()
    {
        $lastId = DB::table('vendor')
            ->select(DB::raw("IFNULL(MAX(id_vendor), 'VE000') as id_vendor"))
            ->value('id_vendor');

        $number = (int) substr($lastId, 2);
        $newId = 'VE' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);

        return $newId;
    }

    public function setHargaBarangAttribute($value)
    {
        // Hapus semua titik dan koma agar bisa disimpan sebagai integer
        $this->attributes['harga_barang'] = (int) str_replace(['.', ','], '', $value);
    }
        protected $casts = [
        'items' => 'array',
    ];
    // relasi ke tabel pembelian
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'id_vendor');
    }
     // relasi ke tabel vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'id_vendor');
    }

    // relasi ke tabel pembelian barang
    public function pembelianBarang()
    {
        return $this->hasMany(PembelianBarang::class, 'pembelian_id');
    }

}