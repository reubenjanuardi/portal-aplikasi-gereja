@extends('errors.layout')

@section('code', '419')
@section('title', 'Sesi Kedaluwarsa')
@section('description', 'Sesi Anda telah berakhir. Silakan kembali dan coba lagi.')

@section('icon')
    <svg class="h-7 w-7 text-blue-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endsection

@section('actions')
    <a
        href="/login"
        class="inline-flex items-center justify-center rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
    >
        Kembali ke Login
    </a>
    <a
        href="/"
        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
    >
        Kembali ke Beranda
    </a>
@endsection
