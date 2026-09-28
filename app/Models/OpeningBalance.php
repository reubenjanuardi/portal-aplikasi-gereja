<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Saldo awal sebuah akun CoA pada tanggal tertentu (mis. 1 Juli 2026).
 *
 * Dipakai sebagai titik awal perhitungan Buku Besar agar saldo yang
 * ditampilkan sama dengan buku besar manual, meskipun data transaksi
 * di sistem baru mulai direkam setelah tanggal tersebut.
 */
class OpeningBalance extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'periode'    => 'date:Y-m-d',
        'saldo_awal' => 'float',
    ];

    /**
     * Kolom `date` di SQLite/PostgreSQL bisa tersimpan sebagai datetime
     * (2026-07-01 00:00:00) sehingga perbandingan dengan string Y-m-d gagal.
     * Accessor ini memastikan nilai yang dikembalikan selalu format Y-m-d.
     */
    protected function periode(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? Carbon::parse($value)->toDateString() : null,
            set: fn (?string $value): ?string => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }

    public function getActivityLogName(): string
    {
        return 'keuangan';
    }

    public function getActivityLogTitle(): string
    {
        $periode = $this->periode instanceof Carbon
            ? $this->periode->toDateString()
            : (string) $this->periode;

        return "{$this->kode_akun} per {$periode}";
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_akun', 'kode_akun');
    }

    /**
     * Ambil peta saldo awal [kode_akun => nominal] untuk satu periode.
     *
     * Dipakai BukuBesarService sebagai titik awal sebelum tanggal periode laporan.
     *
     * @return array<string, float>
     */
    public static function mapForPeriod(string $periode): array
    {
        return static::query()
            ->whereDate('periode', $periode)
            ->pluck('saldo_awal', 'kode_akun')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * Daftar tanggal yang sudah punya saldo awal, terbaru lebih dulu.
     *
     * @return array<int, string>
     */
    public static function existingPeriods(): array
    {
        return static::query()
            ->selectRaw('DISTINCT periode')
            ->orderByDesc('periode')
            ->pluck('periode')
            ->map(fn ($value) => Carbon::parse($value)->toDateString())
            ->all();
    }
}
