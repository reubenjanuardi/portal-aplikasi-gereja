<?php

namespace App\Support;

/**
 * Format angka untuk seluruh laporan keuangan.
 *
 * Prinsip: nominal rupiah tidak boleh dibulatkan diam-diam. Nilai pecahan
 * (mis. 531.553,99 dari mutasi rekening bank) harus tetap terlihat supaya
 * selisih antara sistem dan mutasi rekening dapat ditelusuri.
 */
class Format
{
    /** Jumlah digit desimal maksimum yang ditampilkan. */
    public const DECIMALS = 2;

    /**
     * Format nominal rupiah tanpa simbol mata uang.
     *
     * Desimal hanya ditampilkan bila nilai benar-benar pecahan. Nilai yang
     * selisihnya di bawah 0,005 dianggap bulat agar pembulatan floating point
     * tidak memunculkan ",00" yang menyesatkan.
     */
    public static function nominal(float|int|string|null $amount): string
    {
        $value = round((float) ($amount ?? 0), self::DECIMALS);

        if (abs($value - round($value)) < 0.005) {
            return number_format($value, 0, ',', '.');
        }

        return number_format($value, self::DECIMALS, ',', '.');
    }

    /**
     * Format nominal rupiah dengan awalan "Rp".
     */
    public static function rupiah(float|int|string|null $amount): string
    {
        return 'Rp ' . self::nominal($amount);
    }
}
