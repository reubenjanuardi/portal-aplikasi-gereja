<?php

use App\Filament\Resources\VoucherResource\Pages\CreateVoucher;
use App\Models\ChartOfAccount;
use App\Models\User;
use App\Models\Voucher;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $this->user = User::where('email', 'admin@gpibhosiana.org')->first();

    $this->kasBesar = ChartOfAccount::firstOrCreate(
        ['kode_akun' => '111.01'],
        [
            'nama_akun'   => 'Kas Besar',
            'kategori'    => 'Kas & Bank',
            'is_postable' => true,
        ]
    );

    $this->atk = ChartOfAccount::firstOrCreate(
        ['kode_akun' => '363.01.01'],
        [
            'nama_akun'   => 'ATK & Fotocopy',
            'kategori'    => 'Pengeluaran',
            'is_postable' => true,
        ]
    );
});

test('create voucher form initializes no_bukti and updates when tanggal or jenis changes', function () {
    $component = Livewire::actingAs($this->user)
        ->test(CreateVoucher::class)
        ->assertFormFieldExists('no_bukti');

    // Check that no_bukti is initially filled
    $initialNo = $component->get('data.no_bukti');
    expect($initialNo)->not->toBeEmpty()
        ->and($initialNo)->toStartWith('BKM');

    // Change tanggal to 2026-05-15 (May, week 3)
    $component->set('data.tanggal', '2026-05-15');
    expect($component->get('data.no_bukti'))->toBe('BKM001-03-V-2026');

    // Change jenis_voucher to BKK
    $component->set('data.jenis_voucher', 'BKK');
    expect($component->get('data.no_bukti'))->toBe('BKK001-03-V-2026');
});

test('can successfully create a voucher with auto-generated disabled no_bukti', function () {
    Livewire::actingAs($this->user)
        ->test(CreateVoucher::class)
        ->fillForm([
            'tanggal'            => '2026-07-10', // July week 2
            'pihak_terkait'      => 'Toko ATK Berkah',
            'jenis_voucher'      => 'BKK',
            'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
            'kode_akun'          => $this->atk->kode_akun,
            'transactions'       => [
                [
                    'uraian'  => 'Beli kertas HVS',
                    'nominal' => 75000,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('vouchers', [
        'no_bukti'           => 'BKK001-02-VII-2026',
        'pihak_terkait'      => 'Toko ATK Berkah',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 75000,
    ]);
});

