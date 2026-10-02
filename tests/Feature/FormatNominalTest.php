<?php

use App\Filament\Resources\VoucherResource;
use App\Support\Format;

test('format nominal tanpa desimal tetap dibulatkan', function () {
    expect(Format::nominal(531554.0))->toBe('531.554');
    expect(Format::nominal(2756703.0))->toBe('2.756.703');
    expect(Format::nominal(0))->toBe('0');
    expect(Format::nominal(null))->toBe('0');
});

test('format nominal dengan pecahan menampilkan koma desimal', function () {
    expect(Format::nominal(531553.99))->toBe('531.553,99');
    expect(Format::nominal(1500000.50))->toBe('1.500.000,50');
    expect(Format::nominal(123.45))->toBe('123,45');
    expect(Format::nominal(500000.5))->toBe('500.000,50');
});

test('nilai yang mendekati bulat tidak memunculkan desimal sia-sia', function () {
    // Selisih di bawah 0,005 dianggap bulat agar floating point tidak
    // menampilkan ",00" yang menyesatkan.
    expect(Format::nominal(1000.001))->toBe('1.000');
    expect(Format::nominal(1000.004))->toBe('1.000');
    expect(Format::nominal(1000.005))->toBe('1.000,01');
});

test('nilai negatif tidak merusak format', function () {
    expect(Format::nominal(-250000.75))->toBe('-250.000,75');
});

test('format rupiah memakai awalan Rp', function () {
    expect(Format::rupiah(1500000.50))->toBe('Rp 1.500.000,50');
    expect(Format::rupiah(1500000.0))->toBe('Rp 1.500.000');
});

test('voucher resource memakai helper yang sama', function () {
    expect(VoucherResource::formatNominal(531553.99))->toBe(Format::nominal(531553.99));
    expect(VoucherResource::formatNominal(2756703.0))->toBe('2.756.703');
});
