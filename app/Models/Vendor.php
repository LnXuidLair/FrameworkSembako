<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB; // Tambahkan ini

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendor';
    protected $guarded = [];

    public static function getIdVendor()
    {
        // Ambil id_vendor terbesar (misalnya VE025)
        $result = DB::table('vendor')->select(DB::raw("IFNULL(MAX(id_vendor), 'VE000') as id_vendor"))->first();
        $lastId = $result->id_vendor;

        // Ambil 3 digit terakhir dan tambahkan 1
        $lastNumber = (int) substr($lastId, 2);
        $newNumber = $lastNumber + 1;

        // Format hasil: VE001, VE002, dst
        $newId = 'VE' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        return $newId;
    }

    public function setHargaBarangAttribute($value)
    {
        $this->attributes['harga_barang'] = str_replace(',', '', $value);
    }
}

