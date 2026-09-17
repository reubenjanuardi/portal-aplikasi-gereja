<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoucherPdfController;
use App\Http\Controllers\LaporanPdfController;
use App\Http\Controllers\LaporanExcelController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard App Launcher
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Redirect /keuangan/dashboard ke /keuangan (home Filament panel)
Route::get('/keuangan/dashboard', function () {
    return redirect('/keuangan');
})->middleware(['auth', 'verified']);

// Redirect /admin & /admin/{any} ke /keuangan/{any} untuk kompatibilitas
Route::get('/admin', function () {
    return redirect('/keuangan');
});
Route::get('/admin/{any}', function ($any) {
    return redirect("/keuangan/{$any}");
})->where('any', '.*');

// Redirect login panel ke Breeze login page (/login)
Route::get('/admin/login', function () {
    return redirect()->route('login');
})->middleware('guest');

Route::get('/keuangan/login', function () {
    return redirect()->route('login');
})->middleware('guest');

Route::get('/settings/login', function () {
    return redirect()->route('login');
})->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/vouchers/{voucher}/pdf', [VoucherPdfController::class, 'stream'])->name('vouchers.pdf');
    Route::get('/laporan/buku-besar/pdf', [LaporanPdfController::class, 'bukuBesar'])->name('laporan.buku-besar.pdf');
    Route::get('/laporan/buku-besar/excel', [LaporanExcelController::class, 'bukuBesar'])->name('laporan.buku-besar.excel');
    Route::get('/laporan/jurnal/pdf', [LaporanPdfController::class, 'jurnal'])->name('laporan.jurnal.pdf');
    Route::get('/laporan/jurnal/excel', [LaporanExcelController::class, 'jurnal'])->name('laporan.jurnal.excel');
    Route::get('/laporan/jurnal-umum/pdf', [LaporanPdfController::class, 'jurnalUmum'])->name('laporan.jurnal-umum.pdf');
    Route::get('/laporan/jurnal-umum/excel', [LaporanExcelController::class, 'jurnalUmum'])->name('laporan.jurnal-umum.excel');
    Route::get('/laporan/realisasi-mingguan/pdf', [LaporanPdfController::class, 'realisasiMingguan'])->name('laporan.realisasi-mingguan.pdf');
    Route::get('/laporan/realisasi-mingguan/excel', [LaporanExcelController::class, 'realisasiMingguan'])->name('laporan.realisasi-mingguan.excel');
});


require __DIR__ . '/auth.php';
