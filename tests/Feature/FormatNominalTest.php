<?php

use App\Filament\Resources\VoucherResource;

test('format nominal tanpa desimal tetap dibulatkan', function () {
    expect(VoucherResource::formatNominal(531554.0))->toBe('531.554');
    expect(VoucherResource::formatNominal(2756703.0))->toBe('2.756.703');
    expect(VoucherResource::formatNominal(0.0))->toBe('0');
});

test('format nominal dengan pecahan menampilkan koma desimal', function () {
    expect(VoucherResource::formatNominal(531553.99))->toBe('531.553,99');
    expect(VoucherResource::formatNominal(1500000.50))->toBe('1.500.000,50');
    expect(VoucherResource::formatNominal(123.45))->toBe('123,45');
});

test('nilai yang mendekati bulat tetap dibulatkan', function () {
    // Selisih < 0.01 dianggap tidak ada pecahan yang relevan.
    expect(VoucherResource::formatNominal(1000.001))->toBe('1.000');
    expect(VoucherResource::formatNominal(1000.004))->toBe('1.000');
});

test('pecahan satu digit tetap ditampilkan dua digit', function () {
    expect(VoucherResource::formatNominal(500000.5))->toBe('500.000,50');
});

test('nilai negatif tidak merusak format', function () {
    expect(VoucherResource::formatNominal(-250000.75))->toBe('-250.000,75');
});
