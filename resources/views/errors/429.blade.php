@extends('errors.layout')

@section('code', '429')
@section('title', 'Terlalu Banyak Permintaan')
@section('description', 'Permintaan Anda terlalu sering. Silakan coba beberapa saat lagi.')

@section('icon')
    <svg class="h-7 w-7 text-blue-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endsection
