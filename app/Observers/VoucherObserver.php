<?php

namespace App\Observers;

use App\Models\Voucher;
use Illuminate\Support\Facades\Cache;

/***
 * Membersihkan cache Laporan Triwulan setiap voucher dibuat/diubah/dihapus.
 *
 * Cache key laporan triwulan menyematkan versi CoA (`triwulan:coa_version`),
 * sehingga perubahan akun otomatik membuat key baru. Observer ini menambah
 * versi saat voucher CRUD sehingga laporan sinkron dengan data transaksi
 * terbaru tanpa perlu mem-flush cache aplikasi sepenuhnya.
 */
class VoucherObserver
{
    /** Key cache versi CoA (dipampakkan ChartOfAccountsObserver). */
    public const VERSION_KEY = 'triwulan:coa_version';

    /** Create dan update memicu saved; jangan daftarkan created/updated juga. */
    public function saved(Voucher $voucher): void
    {
        $this->bumpVersion();
    }

    public function deleted(Voucher $voucher): void
    {
        $this->bumpVersion();
    }

    /** Naikkan versi cache sehingga semua cache key laporan kuncup.
     * Key lama akan expired sendiri menurut TTL-nya (1 hari). */
    protected function bumpVersion(): void
    {
        Cache::forever(self::VERSION_KEY, now()->getTimestampMs());
    }
}