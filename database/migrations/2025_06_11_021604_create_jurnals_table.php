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
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_transaksi');
            $table->unsignedBigInteger('coa_id'); // FK ke tabel coa
            $table->enum('posisi', ['debit', 'kredit']);
            $table->bigInteger('nominal');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('coa_id')->references('id')->on('coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};
