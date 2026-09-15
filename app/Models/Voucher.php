<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use LogsActivity;

    protected $primaryKey = 'no_bukti';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function getActivityLogName(): string
    {
        return 'keuangan';
    }

    public function getActivityLogTitle(): string
    {
        return "{$this->no_bukti} ({$this->jenis_voucher}) - {$this->pihak_terkait}";
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'no_bukti', 'no_bukti');
    }

    /**
     * The primary chart of account (mata anggaran) for this voucher.
     * All transactions in this voucher share this same kode_akun.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_akun', 'kode_akun');
    }

    /**
     * The cash or bank account (sumber atau rekening tujuan) for this voucher.
     */
    public function akunKasBank()
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_akun_kas_bank', 'kode_akun');
    }

    /**
     * Check if voucher is an incoming cash/bank voucher.
     */
    public function isMasuk(): bool
    {
        return in_array($this->jenis_voucher, ['Masuk', 'BKM', 'BBM', 'Bukti Kas Masuk (BKM)', 'Bukti Bank Masuk (BBM)'], true);
    }

    /**
     * Check if voucher is an outgoing cash/bank voucher.
     */
    public function isKeluar(): bool
    {
        return in_array($this->jenis_voucher, ['Keluar', 'BKK', 'BBK', 'Bukti Kas Keluar (BKK)', 'Bukti Bank Keluar (BBK)'], true);
    }

    /**
     * Check if voucher involves cash (Kas).
     */
    public function isKas(): bool
    {
        return in_array($this->jenis_voucher, ['BKM', 'BKK', 'Masuk', 'Keluar', 'Bukti Kas Masuk (BKM)', 'Bukti Kas Keluar (BKK)'], true);
    }

    /**
     * Check if voucher involves bank account (Bank).
     */
    public function isBank(): bool
    {
        return in_array($this->jenis_voucher, ['BBM', 'BBK', 'Bukti Bank Masuk (BBM)', 'Bukti Bank Keluar (BBK)'], true);
    }
}
