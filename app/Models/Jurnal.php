<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'jurnal';

    protected $fillable = [
        'tanggal',
        'no_transaksi',
        'coa_id',
        'posisi',
        'nominal',
        'keterangan',
    ];

    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }
}