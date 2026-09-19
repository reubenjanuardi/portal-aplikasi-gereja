@extends('errors.layout')

@section('code', '404')
@section('title', 'Halaman Tidak Ditemukan')
@section('description', 'Halaman yang Anda cari tidak tersedia atau alamatnya sudah berubah.')

@section('icon')
    <svg class="h-7 w-7 text-blue-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
@endsection
