<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Terjadi Kesalahan') — Portal GPIB Hosiana</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css'])

    <!-- Safe Fallback Inline CSS -->
    <style>
        body {
            font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex min-h-screen flex-col justify-between bg-slate-50 px-4 py-8 font-sans text-slate-900 antialiased sm:px-6 sm:py-12 lg:px-8">
    @php
        $isAuthenticated = false;
        try {
            $isAuthenticated = auth()->check();
        } catch (\Throwable $e) {
            $isAuthenticated = false;
        }
    @endphp

    <div class="flex flex-1 flex-col justify-center">
        <!-- Branding Header -->
        <div class="mb-6 text-center sm:mx-auto sm:w-full sm:max-w-md">
            <a
                href="/"
                class="inline-flex flex-col items-center gap-2.5 rounded-xl p-2 transition-opacity hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
            >
                <img
                    src="{{ asset('favicon.png') }}"
                    alt="Logo GPIB Hosiana"
                    class="h-12 w-12 object-contain"
                />
                <div>
                    <span class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Portal GPIB Hosiana
                    </span>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">
                        GPIB Jemaat Hosiana Jakarta Pusat
                    </p>
                </div>
            </a>
        </div>

        <!-- Error Card Container -->
        <div class="mx-auto w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-10">
            <!-- Icon & Code Badge -->
            <div class="mb-4 flex flex-col items-center justify-center gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-900 shadow-xs">
                    @yield('icon')
                </div>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-0.5 text-xs font-bold uppercase tracking-wider text-blue-900">
                    Error @yield('code')
                </span>
            </div>

            <!-- Title & Description -->
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                @yield('title')
            </h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                @yield('description')
            </p>

            <!-- Recovery Actions -->
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @hasSection('actions')
                    @yield('actions')
                @else
                    @if($isAuthenticated)
                        <a
                            href="/dashboard"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            Kembali ke Dashboard
                        </a>
                        <a
                            href="/"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            Kembali ke Beranda
                        </a>
                    @else
                        <a
                            href="/"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            Kembali ke Beranda
                        </a>
                        <a
                            href="/login"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            Kembali ke Login
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Footer Information -->
    <footer class="mt-8 space-y-1 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} GPIB Jemaat Hosiana Jakarta. Hak Cipta Dilindungi.</p>
        <p class="text-slate-400">Jl. Rajawali Selatan V No. 7, Jakarta Pusat 10772</p>
    </footer>
</body>
</html>
