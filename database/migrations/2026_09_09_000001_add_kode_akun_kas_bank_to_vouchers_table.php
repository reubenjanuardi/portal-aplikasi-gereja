<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('kode_akun_kas_bank')->nullable()->after('jenis_voucher');
            $table->foreign('kode_akun_kas_bank')
                ->references('kode_akun')
                ->on('chart_of_accounts')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        // Backfill existing vouchers with sensible default Cash or Bank account
        $defaultKas = DB::table('chart_of_accounts')
            ->where('kategori', 'Kas & Bank')
            ->where('is_postable', true)
            ->where(function ($q) {
                $q->where('kode_akun', 'like', '111%')
                  ->orWhere('nama_akun', 'like', '%Kas%');
            })
            ->orderBy('kode_akun')
            ->value('kode_akun');

        $defaultBank = DB::table('chart_of_accounts')
            ->where('kategori', 'Kas & Bank')
            ->where('is_postable', true)
            ->where('kode_akun', 'not like', '111%')
            ->orderBy('kode_akun')
            ->value('kode_akun');

        // Fallback to any Kas & Bank account if specific types not found
        $anyKasBank = $defaultKas ?: ($defaultBank ?: DB::table('chart_of_accounts')->where('kategori', 'Kas & Bank')->value('kode_akun'));

        if ($anyKasBank) {
            // Update Kas vouchers
            DB::table('vouchers')
                ->whereNull('kode_akun_kas_bank')
                ->whereIn('jenis_voucher', ['BKM', 'BKK', 'Masuk', 'Keluar'])
                ->update(['kode_akun_kas_bank' => $defaultKas ?: $anyKasBank]);

            // Update Bank vouchers
            DB::table('vouchers')
                ->whereNull('kode_akun_kas_bank')
                ->whereIn('jenis_voucher', ['BBM', 'BBK'])
                ->update(['kode_akun_kas_bank' => $defaultBank ?: $anyKasBank]);

            // Any remaining without kas_bank
            DB::table('vouchers')
                ->whereNull('kode_akun_kas_bank')
                ->update(['kode_akun_kas_bank' => $anyKasBank]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['kode_akun_kas_bank']);
            $table->dropColumn('kode_akun_kas_bank');
        });
    }
};
