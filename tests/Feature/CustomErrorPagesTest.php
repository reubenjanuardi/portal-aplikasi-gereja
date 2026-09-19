<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('404 error page renders properly for guest with correct title, description, and guest actions', function () {
    $response = $this->get('/non-existent-url-for-testing-404');

    $response->assertStatus(404);
    $response->assertSee('Halaman Tidak Ditemukan');
    $response->assertSee('Halaman yang Anda cari tidak tersedia atau alamatnya sudah berubah.');
    $response->assertSee('Error 404');
    $response->assertSee('Portal GPIB Hosiana');
    $response->assertSee('Kembali ke Beranda');
    $response->assertSee('Kembali ke Login');
    $response->assertDontSee('Kembali ke Dashboard');
});

test('404 error page renders properly for authenticated user with dashboard action', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/non-existent-url-for-testing-404');

    $response->assertStatus(404);
    $response->assertSee('Halaman Tidak Ditemukan');
    $response->assertSee('Kembali ke Dashboard');
    $response->assertSee('/dashboard');
});

test('403 error page renders correct title, description, and badge', function () {
    Route::get('/test-abort-403', function () {
        abort(403);
    });

    $response = $this->get('/test-abort-403');

    $response->assertStatus(403);
    $response->assertSee('Akses Tidak Diizinkan');
    $response->assertSee('Anda tidak memiliki izin untuk mengakses halaman ini.');
    $response->assertSee('Error 403');
});

test('419 error page renders correct title, description, badge, and recovery actions', function () {
    $view = $this->view('errors.419');

    $view->assertSee('Sesi Kedaluwarsa');
    $view->assertSee('Sesi Anda telah berakhir. Silakan kembali dan coba lagi.');
    $view->assertSee('Error 419');
    $view->assertSee('Kembali ke Login');
    $view->assertSee('Kembali ke Beranda');
});

test('429 error page renders correct title, description, and badge', function () {
    $view = $this->view('errors.429');

    $view->assertSee('Terlalu Banyak Permintaan');
    $view->assertSee('Permintaan Anda terlalu sering. Silakan coba beberapa saat lagi.');
    $view->assertSee('Error 429');
});

test('500 error page renders correct title, description, and badge without exposing sensitive information', function () {
    $view = $this->view('errors.500');

    $view->assertSee('Terjadi Kesalahan');
    $view->assertSee('Terjadi kesalahan pada server. Silakan coba kembali.');
    $view->assertSee('Error 500');
    $view->assertDontSee('Stack trace');
    $view->assertDontSee('Exception');
});

test('503 error page renders correct title, description, and badge', function () {
    $view = $this->view('errors.503');

    $view->assertSee('Layanan Tidak Tersedia');
    $view->assertSee('Layanan sedang dalam pemeliharaan atau tidak tersedia untuk sementara.');
    $view->assertSee('Error 503');
});
