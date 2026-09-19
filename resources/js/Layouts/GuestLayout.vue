<template>
    <div
        class="relative flex min-h-screen flex-col justify-between bg-slate-50 px-4 py-8 font-sans text-slate-900 sm:px-6 sm:py-12 lg:px-8"
    >
        <!-- Tombol Kembali di Pojok Kiri Atas -->
        <div
            class="absolute left-4 top-4 sm:left-6 sm:top-6 lg:left-8 lg:top-8"
        >
            <a
                href="/"
                class="shadow-xs inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
            >
                <svg
                    class="h-4 w-4 text-slate-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <div class="flex flex-1 flex-col justify-center">
            <!-- Branding Header -->
            <div class="mb-6 text-center sm:mx-auto sm:w-full sm:max-w-md">
                <a
                    href="/"
                    class="inline-flex flex-col items-center gap-2.5 rounded-xl p-2 transition-opacity hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                >
                    <img
                        src="/favicon.png"
                        alt="Logo GPIB Hosiana"
                        class="shadow-xs h-12 w-12 object-contain"
                    />
                    <div>
                        <span
                            class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl"
                        >
                            Portal GPIB Hosiana
                        </span>
                        <p
                            class="text-xs font-medium uppercase tracking-wider text-slate-500"
                        >
                            GPIB Jemaat Hosiana Jakarta Pusat
                        </p>
                    </div>
                </a>
            </div>

            <!-- Main Form Card Container -->
            <div
                class="mx-auto w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
            >
                <slot />
            </div>
        </div>

        <!-- Footer Information -->
        <footer class="mt-8 space-y-1 text-center text-xs text-slate-500">
            <p>
                &copy; 2026 GPIB Jemaat Hosiana Jakarta. Hak Cipta Dilindungi.
            </p>
            <p class="text-slate-400">
                Jl. Rajawali Selatan V No. 7, Jakarta Pusat 10772
            </p>
        </footer>
    </div>
</template>
