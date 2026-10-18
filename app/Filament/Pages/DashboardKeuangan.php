<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

/**
 * Dashboard keuangan memakai satu kolom lebar penuh, bukan grid 2 kolom
 * bawaan Filament, karena widget ringkasan sudah merangkum seluruh
 * dashboard dan tetap terbaca lega pada layar lebar.
 */
class DashboardKeuangan extends Dashboard
{
    protected static ?string $title = 'Dashboard';

    public function getColumns(): int
    {
        return 1;
    }
}
