<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pegawai extends Model
{
    protected $table = 'pegawai'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function getKodePegawai()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(kode_pegawai), 'AB000') as kode_pegawai 
                FROM pegawai ";
        $kodepegawai = DB::select($sql);

        // cacah hasilnya
        foreach ($kodepegawai as $kdpgw) {
            $kd = $kdpgw->kode_pegawai;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($kd,-3);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'AB'.str_pad($noakhir,3,"0",STR_PAD_LEFT); //menyambung dengan string PR-001
        return $noakhir;
    }
}
