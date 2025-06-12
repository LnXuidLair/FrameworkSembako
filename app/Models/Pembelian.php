<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// untuk tambahan db
use Illuminate\Support\Facades\DB;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function getKodeFaktur()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(no_faktur), 'FB-000') as no_faktur 
                FROM pembelian";
        $kodefaktur = DB::select($sql);

        // cacah hasilnya
        foreach ($kodefaktur as $kdpmbl) {
            $kd = $kdpmbl->no_faktur;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($kd,-3);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'F-'.str_pad($noakhir,7,"0",STR_PAD_LEFT); //menyambung dengan string P-00001
        return $noakhir;

    }

    // relasi ke tabel pembeli
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'id_vendor');
    }
    public function PembelianBarang()
    {
        return $this->hasMany(PembelianBarang::class, 'pembelian_id');
    }

}