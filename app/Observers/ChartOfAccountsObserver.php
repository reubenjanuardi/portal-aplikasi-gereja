<?php

namespace App\Observers;

use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Membersihkan cache Laporan Triwulan setiap kali CoA berubah.
 *
 * Cache key laporan triwulan memuat versi (timestamp) CoA terakhir, sehingga
 * perubahan nama/tambah/hapus CoA otomatis membuat key baru tanpa perlu
 * mem-flush seluruh cache aplikasi.
 */
class ChartOfAccountsObserver
{
    /** Key cache yang menyimpan versi CoA terakhir. */
    public const VERSION_KEY = 'triwulan:coa_version';

    public function saved(ChartOfAccount $coa): void
    {
        $this->bumpVersion();
    }

    public function deleted(ChartOfAccount $coa): void
    {
        $this->bumpVersion();
    }

    /**
     * Naikkan versi CoA sehingga seluruh key laporan triwulan lama jadi invalid.
     */
    protected function bumpVersion(): void
    {
        Cache::forever(self::VERSION_KEY, now()->getTimestampMs());
    }

    /**
     * Ambil versi CoA saat ini (dipakai service untuk menyusun cache key).
     */
    public static function version(): int
    {
        $version = Cache::get(self::VERSION_KEY);

        if ($version === null) {
            $version = self::currentVersionFromDatabase();
            Cache::forever(self::VERSION_KEY, $version);
        }

        return (int) $version;
    }

    /**
     * Versi fallback dari database: timestamp perubahan CoA terakhir.
     */
    protected static function currentVersionFromDatabase(): int
    {
        try {
            if (! Schema::hasTable('chart_of_accounts')) {
                return 0;
            }

            $maxUpdated = ChartOfAccount::max('updated_at');

            return $maxUpdated ? (int) $maxUpdated->getTimestampMs() : 0;
        } catch (\Throwable) {
            return 0;
        }
    }
}
