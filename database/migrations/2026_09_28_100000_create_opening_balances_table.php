<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel saldo awal (opening balance) per akun CoA pada tanggal tertentu.
 *
 * Tabel ini berdiri sendiri dan SENGAJA tidak menyentuh `transactions`/
 * `vouchers`, sehingga aman ditambahkan ke database yang sudah berisi data
 * keuangan historis. Nilainya terikat pada kode akun + tanggal, jadi bisa diisi ulang
 * (mis. saat tutup buku) tanpa merusak riwayat transaksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_balances', function (Blueprint $table) {
            // Satu akun satu tanggal = satu baris (unique di bawah).
            $table->id();

            $table->string('kode_akun');
            $table->date('periode');
            $table->decimal('saldo_awal', 15, 2)->default(0);

            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('kode_akun')
                ->references('kode_akun')
                ->on('chart_of_accounts')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        // Cegah duplikasi: satu akun hanya boleh punya satu saldo awal per tanggal.
        Schema::table('opening_balances', function (Blueprint $table) {
            $table->unique(['kode_akun', 'periode'], 'opening_balances_kode_akun_periode_unique');
            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_balances');
    }
};
