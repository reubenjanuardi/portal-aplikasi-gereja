<?php

use App\Filament\Widgets\KeuanganDashboardWidget;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Livewire\Livewire;

test('finance dashboard is available to authenticated users', function () {
    $this->seed(RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $this->actingAs($user)
        ->get('/keuangan')
        ->assertOk()
        ->assertSee('Ringkasan Keuangan')
        ->assertSee('kd-page', false)
        ->assertSee('kd-metric--saldo', false)
        ->assertSee('Arus penerimaan')
        ->assertSee('Voucher terbaru');
});

test('dashboard stylesheet does not leak tailwind utilities into the panel', function () {
    $css = file_get_contents(resource_path('css/filament-dashboard.css'));

    // Berkas ini dimuat Filament sebagai aset panel GLOBAL. Bila memuat
    // `@tailwind`, seluruh utility proyek ikut ter-emit dan varian `dark:`
    // (yang dikompilasi ke `@media (prefers-color-scheme: dark)`) ikut
    // mengaktifkan gaya halaman lain berdasarkan tema OS.
    //
    // Cocokkan perintahnya, bukan teks bebas: komentar di dalam berkas CSS
    // ini menyebut nama perintah tersebut secara eksplisit.
    expect(collect(explode("\n", $css))
        ->map(fn (string $line) => ltrim($line))
        ->filter(fn (string $line) => str_starts_with($line, '@tailwind'))
        ->all())->toBe([]);

    // Dark mode mengikuti class `.dark` Filament, bukan media query OS.
    // Periksa aturan CSS asli, bukan teks komentar.
    $hasMediaDark = preg_match('/@media\s*\([^)]*prefers-color-scheme\s*:\s*dark[^)]*\)\s*\{/', $css);
    expect($hasMediaDark)->toBe(0);
    expect($css)->toContain(':where(.dark, .dark *) .kd-page');
});

test('finance dashboard offers weekly, monthly, and yearly ranges', function () {
    $this->seed(RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();
    $this->actingAs($user);

    Livewire::test(KeuanganDashboardWidget::class)
        ->assertSee('Minggu ini')
        ->assertSee('Bulan ini')
        ->assertSee('Tahun ini')
        ->set('rentang', 'bulan')
        ->assertSet('rentang', 'bulan')
        ->set('rentang', 'invalid')
        ->assertSet('rentang', 'minggu');
});
