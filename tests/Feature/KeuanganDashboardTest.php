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
        ->assertSee('grid-cols-1', false)
        ->assertSee('Arus penerimaan')
        ->assertSee('Voucher terbaru');
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
