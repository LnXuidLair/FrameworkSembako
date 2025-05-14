<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeBarangToPembelianBarangTable extends Migration
{
    public function up()
    {
        Schema::table('pembelian_barang', function (Blueprint $table) {
            $table->string('kode_barang')->nullable(); // bisa diubah sesuai kebutuhan (nullable atau tidak)
        });
    }

    public function down()
    {
        Schema::table('pembelian_barang', function (Blueprint $table) {
            $table->dropColumn('kode_barang');
        });
    }
}
