<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_transaksi',7);
            $table->integer('id_barang');
            $table->dateTime('tgl_transaksi', $precision = 0);
            $table->integer('jml_barang');
            $table->integer('harga_satuan');
            $table->integer('total_harga');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
