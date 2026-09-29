<!DOCTYPE html>
<html lang="id" class="h-full bg-paper">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Portal Terintegrasi — GPIB Jemaat Hosiana Jakarta</title>
    <meta name="description" content="Portal Sistem Informasi GPIB Jemaat Hosiana Jakarta: pencatatan kas, mata anggaran, dan laporan keuangan gereja pada kode akun milik gereja ini sendiri.">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Faces are self-hosted from public/fonts and declared in resources/css/app.css,
         so the page makes no third-party font request and blocks on nothing. --}}

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        // Name, address and logo are all configurable in Pengaturan Gereja, so
        // the public page reads them from the same place. It must not fail if
        // the database is unreachable, so every lookup falls back to the
        // committed values and the committed seal asset.
        $logo = null;
        $churchName = 'GPIB Jemaat Hosiana Jakarta';
        $churchAddress1 = 'Jl. Rajawali Selatan V No. 7';
        $churchAddress2 = 'Jakarta Pusat 10772';
        try {
            $logo = \App\Models\AppSetting::getLogoUrl();
            $churchName = \App\Models\AppSetting::get('church_name', $churchName) ?: $churchName;
            $churchAddress1 = \App\Models\AppSetting::get('church_address1', $churchAddress1) ?: $churchAddress1;
            $churchAddress2 = \App\Models\AppSetting::get('church_address2', $churchAddress2) ?: $churchAddress2;
        } catch (\Throwable $e) {
            // Keep the committed defaults; the landing page still renders.
        }
        $logo = $logo ?: asset('favicon.png');
    @endphp
</head>
<body class="min-h-full bg-paper font-figure text-ink antialiased">
    <a href="#utama" class="label-cap sr-only focus:not-sr-only focus:fixed focus:left-6 focus:top-6 focus:z-50 focus:bg-ink focus:px-4 focus:py-3 focus:text-paper">
        Lewati ke konten utama
    </a>

    <main id="utama" tabindex="-1">
    <!-- ============================ FIRST VIEWPORT ============================ -->
    <section class="relative" id="awal">
        <div class="bg-ink text-paper" data-surface="ink">
            <div class="mx-auto w-full max-w-rule px-5 sm:px-8 lg:px-10">

                <!-- Top rail: identity only. The page's one action lives in the field below. -->
                <div class="flex items-center justify-between gap-6 border-b border-white/10 py-6">
                    <div class="flex items-center gap-3.5">
                        <span class="plate grid h-11 w-11 place-items-center shrink-0 overflow-hidden">
                            <img src="{{ $logo }}" alt="Lambang GPIB" class="h-full w-full object-contain" onerror="this.onerror=null;this.src='{{ asset('favicon.png') }}'">
                        </span>
                        <span class="label-cap max-w-[14rem] leading-[1.5] text-paper/90">
                            {{ $churchName }}
                        </span>
                    </div>
                    <p class="hidden text-right text-[0.8125rem] leading-relaxed text-mist sm:block">
                        {{ $churchAddress1 }}<br>{{ $churchAddress2 }}
                    </p>
                </div>

                <!-- The field: the offer on the left, the mechanism on the right. -->
                <div class="grid grid-cols-1 items-center gap-x-10 gap-y-14 py-14 sm:py-20 lg:min-h-[74vh] lg:gap-x-6 lg:grid-cols-12 lg:content-center lg:gap-y-0">
                    <div class="lg:col-span-7">
                        <h1 class="font-display text-[2.6rem] font-medium leading-[1.02] tracking-[-0.02em] text-paper sm:text-[3.6rem] lg:text-[4.6rem] xl:text-[5.3rem]">
                            Setiap rupiah<br>punya <em class="italic text-brass">jejaknya</em>.
                        </h1>
                        <p class="mt-8 max-w-[65ch] text-[1.0625rem] leading-[1.7] text-mist">
                            Portal Sistem Informasi GPIB Jemaat Hosiana Jakarta. Setiap penerimaan
                            dan pengeluaran dicatat pada kode akun milik gereja ini sendiri,
                            lalu ditelusuri kembali oleh bendahara, operator kasir, dan majelis.
                        </p>

                        <!-- The slot: the one action on the page. -->
                        <a href="/login" class="slot mt-11 group">
                            <span class="font-display text-[1.6rem] tracking-[-0.01em] sm:text-[1.75rem]">Masuk Portal</span>
                            <span class="flex items-center gap-4">
                                <span class="label-cap hidden text-mist sm:inline">Akun gereja</span>
                                <svg class="slot-arrow h-5 w-5 text-brass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" d="M4 12h15M13 6l6 6-6 6"/>
                                </svg>
                            </span>
                        </a>
                        <p class="mt-4 text-[0.8125rem] text-mist">
                            Akses diberikan berdasarkan peran: bendahara, operator kasir, dan majelis peninjau.
                        </p>
                    </div>

                    <!-- The proof ledger. Demonstration data, labelled as such. -->
                    <div class="lg:col-span-5">
                        <div class="border border-white/10">
                            <div class="flex items-baseline justify-between gap-4 border-b border-white/10 px-5 py-4">
                                <p class="label-cap text-mist">Contoh pencatatan</p>
                                <p class="label-cap text-brass">Buku Besar</p>
                            </div>
                            @php
                                // Demonstration rows, drawn from this church's own
                                // Chart of Accounts. Labelled as illustration below.
                                $rows = [
                                    ['111.01', 'Kas Besar', null, null, '12.450.000'],
                                    ['21.01.01', 'Ibadah Minggu Pagi', '8.750.000', null, '21.200.000'],
                                    ['21.08', 'Persembahan Persepuluhan', '3.200.000', null, '24.400.000'],
                                    ['362.01.01.01', 'Gaji — KMJ', null, '4.900.000', '19.500.000'],
                                    ['361.02.01', 'SMJ Triwulan', null, '1.150.000', '18.350.000'],
                                ];
                            @endphp
                            <table class="num w-full text-[0.75rem]">
                                <caption class="sr-only">
                                    Contoh susunan pencatatan kas dengan kode akun, uraian, debit, kredit, dan saldo berjalan
                                </caption>
                                <thead>
                                    <tr class="border-b border-white/10 text-left text-mist">
                                        <th scope="col" class="label-cap px-2 py-3 font-normal">Kode</th>
                                        <th scope="col" class="label-cap px-1.5 py-3 text-left font-normal">Uraian</th>
                                        <th scope="col" class="label-cap hidden px-1.5 py-3 text-right font-normal sm:table-cell">Debit</th>
                                        <th scope="col" class="label-cap hidden px-1.5 py-3 text-right font-normal sm:table-cell">Kredit</th>
                                        <th scope="col" class="label-cap px-2 py-3 text-right font-normal">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="text-paper/90">
                                    @foreach ($rows as [$kode, $uraian, $debit, $kredit, $saldo])
                                        {{-- The last data row drops its own rule: the total row
                                             carries the brass one, and border-collapse would
                                             otherwise let the two fight at the shared edge. --}}
                                        <tr @class(['border-b border-white/5' => ! $loop->last])>
                                            <td class="num whitespace-nowrap px-2 py-3 text-mist">{{ $kode }}</td>
                                            <td class="whitespace-nowrap px-1.5 py-3">
                                                {{ $uraian }}
                                                @if ($debit || $kredit)
                                                    <span class="mt-1 block text-[0.6875rem] text-mist sm:hidden">
                                                        @if ($debit)<span class="num">D {{ $debit }}</span>@endif
                                                        @if ($debit && $kredit)<span class="px-1.5">&middot;</span>@endif
                                                        @if ($kredit)<span class="num">K {{ $kredit }}</span>@endif
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="hidden whitespace-nowrap px-1.5 py-3 text-right sm:table-cell">{{ $debit }}</td>
                                            <td class="hidden whitespace-nowrap px-1.5 py-3 text-right sm:table-cell">{{ $kredit }}</td>
                                            <td class="num whitespace-nowrap px-2 py-3 text-right">{{ $saldo }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="border-t-2 border-brass/70">
                                        <td class="num whitespace-nowrap px-2 py-3 text-mist">111.01</td>
                                        <td class="whitespace-nowrap px-1.5 py-3 font-medium text-brass">Saldo berjalan</td>
                                        <td class="hidden px-1.5 py-3 sm:table-cell"></td>
                                        <td class="hidden px-1.5 py-3 sm:table-cell"></td>
                                        <td class="num whitespace-nowrap px-2 py-3 text-right font-medium text-brass">18.350.000</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="border-t border-white/10 px-5 py-3.5 text-[0.75rem] leading-relaxed text-mist">
                                Ilustrasi susunan pencatatan. Angka diperagakan sebagai contoh.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- The crease. The only ornament on the page. -->
        <svg class="crease h-16 sm:h-24 lg:h-32" viewBox="0 0 1440 160" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0,44 C180,72 420,96 720,86 C1000,77 1240,44 1440,22 L1440,0 L0,0 Z" fill="#0A1D3D" />
            <path d="M0,44 C180,72 420,96 720,86 C1000,77 1240,44 1440,22" fill="none" stroke="currentColor" stroke-width="1.5" vector-effect="non-scaling-stroke" />
        </svg>
    </section>

        <!-- ============================ WHAT IT PRODUCES ============================ -->
        <section class="mx-auto w-full max-w-rule px-5 pb-24 pt-6 sm:px-8 lg:px-10 lg:pb-32">
            <div class="grid grid-cols-1 gap-x-10 gap-y-12 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <h2 class="font-display text-[2.1rem] leading-[1.1] tracking-[-0.015em] sm:text-[2.5rem] [text-wrap:balance]">
                        Yang dapat disusun dari catatan itu
                    </h2>
                    <p class="mt-6 max-w-[38ch] leading-[1.75] text-slate">
                        Empat laporan yang keluar dari akun yang sama. Semuanya dapat diekspor
                        untuk arsip dan ditandatangani basah.
                    </p>
                </div>

                <div class="lg:col-span-8">
                    <ul class="border-t hairline">
                        <li class="grid grid-cols-1 items-baseline gap-x-6 gap-y-1 border-b hairline py-6 sm:grid-cols-[1fr_auto]">
                            <div>
                                <h3 class="text-[1.0625rem] font-semibold">Buku Besar</h3>
                                <p class="mt-1.5 max-w-[65ch] leading-relaxed text-slate">Rekapitulasi seluruh mutasi per kode akun, dengan saldo berjalan di setiap baris.</p>
                            </div>
                            <p class="label-cap shrink-0 text-slate">PDF · Excel</p>
                        </li>
                        <li class="grid grid-cols-1 items-baseline gap-x-6 gap-y-1 border-b hairline py-6 sm:grid-cols-[1fr_auto]">
                            <div>
                                <h3 class="text-[1.0625rem] font-semibold">Jurnal Umum</h3>
                                <p class="mt-1.5 max-w-[65ch] leading-relaxed text-slate">Semua jurnal dalam satu periode, dikelompokkan menurut tanggal dan akun.</p>
                            </div>
                            <p class="label-cap shrink-0 text-slate">PDF · Excel</p>
                        </li>
                        <li class="grid grid-cols-1 items-baseline gap-x-6 gap-y-1 border-b hairline py-6 sm:grid-cols-[1fr_auto]">
                            <div>
                                <h3 class="text-[1.0625rem] font-semibold">Jurnal</h3>
                                <p class="mt-1.5 max-w-[65ch] leading-relaxed text-slate">Rincian pembukuan per voucher, sebagai bukti dasar setiap transaksi kas.</p>
                            </div>
                            <p class="label-cap shrink-0 text-slate">PDF · Excel</p>
                        </li>
                        <li class="grid grid-cols-1 items-baseline gap-x-6 gap-y-1 border-b hairline py-6 sm:grid-cols-[1fr_auto]">
                            <div>
                                <h3 class="text-[1.0625rem] font-semibold">Realisasi Mingguan</h3>
                                <p class="mt-1.5 max-w-[65ch] leading-relaxed text-slate">Perbandingan realisasi terhadap anggaran, mingguan, untuk pengawas pos.</p>
                            </div>
                            <p class="label-cap shrink-0 text-slate">PDF · Excel</p>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ============================ THE PLATFORM ============================ -->
        <section class="mx-auto w-full max-w-rule px-5 pb-24 sm:px-8 lg:px-10 lg:pb-32">
            <div class="grid grid-cols-1 gap-x-10 gap-y-12 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <h2 class="font-display text-[2.1rem] leading-[1.1] tracking-[-0.015em] sm:text-[2.5rem] [text-wrap:balance]">
                        Saat ini, baru modul keuangan
                    </h2>
                    <p class="mt-6 max-w-[38ch] leading-[1.75] text-slate">
                        Yang sudah berjalan hari ini adalah modul keuangan: pencatatan kas,
                        buku besar, dan laporan. Modul lainnya masih dalam tahap
                        pengembangan dan akan menyusul satu per satu.
                    </p>
                    <p class="mt-6 text-[0.8125rem] leading-relaxed text-slate">
                        Daftar di bawah ini menunjukkan apa yang sudah ada dan apa yang
                        masih akan datang.
                    </p>
                </div>

                <div class="lg:col-span-8">
                    <div class="border-t hairline">
                        @php
                            // Only the finance module is live today. Pengaturan
                            // Portal is an administrative back-office screen, not a
                            // module of the portal, so it is not listed here.
                            $modules = [
                                ['Keuangan', 'Pencatatan kas, buku besar, dan laporan keuangan', true],
                                ['Data Jemaat', 'Sidi, baptis, mutasi, dan atestasi', false],
                                ['Aset Gereja', 'Inventaris dan sarana fisik', false],
                                ['Absensi & SDM', 'Presensi staf kantor dan piket', false],
                                ['Jadwal & Ibadah', 'Pelayan firman, organ, dan liturgi', false],
                                ['Administrasi Surat', 'Nomor dan arsip surat masuk keluar', false],
                                ['Pelayanan Kategorial', 'PA, PT, GP, PKP, PKB, PKLU', false],
                                ['Warta Jemaat', 'Warta digital dan pengumuman', false],
                                ['Multimedia & Studio', 'Siaran daring dan dokumentasi', false],
                                ['Pastoral & Konseling', 'Kunjungan dan pokok doa', false],
                                ['Pusat Bantuan', 'Panduan dan pelaporan kendala', false],
                            ];
                        @endphp
                        <ul>
                            @foreach ($modules as [$name, $scope, $live])
                                <li class="flex flex-wrap items-baseline gap-x-5 border-b hairline py-4">
                                    <span class="order-1 sm:order-1 text-[0.9375rem] font-medium leading-snug">{{ $name }}</span>
                                    <span @class([
                                        'label-cap order-2 ml-auto sm:order-4',
                                        'text-brass-deep' => $live,
                                        'text-slate' => ! $live,
                                    ])>{{ $live ? 'Aktif' : 'Akan Datang' }}</span>
                                    <span class="order-3 mt-1.5 w-full text-[0.8125rem] leading-snug text-slate sm:order-2 sm:mt-0 sm:w-auto sm:max-w-[19rem]">
                                        {{ $scope }}
                                    </span>
                                    <span class="leader order-4 hidden h-px min-w-[1rem] flex-1 translate-y-[-0.3em] sm:order-3 sm:block"></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================ CLOSE ============================ -->
        <section class="relative">
            {{-- The navy begins ON the crease, not under a straight edge: the
                 curve is the boundary between the two planes. --}}
            <svg class="crease h-12 sm:h-16" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,26 C300,58 640,76 980,58 C1180,48 1330,28 1440,32 L1440,80 L0,80 Z" fill="#0A1D3D" />
                <path d="M0,26 C300,58 640,76 980,58 C1180,48 1330,28 1440,32" fill="none" stroke="currentColor" stroke-width="1.5" vector-effect="non-scaling-stroke" />
            </svg>

            <div class="bg-ink text-paper" data-surface="ink">
                <div class="mx-auto w-full max-w-rule px-5 pb-14 pt-6 sm:px-8 lg:px-10 lg:pb-16">
                    <div class="grid grid-cols-1 gap-x-10 gap-y-12 lg:grid-cols-12">
                        <div class="lg:col-span-7">
                            <h2 class="font-display text-[2.1rem] leading-[1.1] tracking-[-0.015em] sm:text-[2.7rem] lg:text-[3.1rem] [text-wrap:balance]">
                                Catatan hari ini bisa Anda masuki dalam hitungan menit.
                            </h2>
                            <p class="mt-7 max-w-[62ch] leading-[1.75] text-mist">
                                Portal ini dipakai oleh bendahara, operator kasir, dan majelis peninjau
                                {{ $churchName }} untuk menjaga setiap rupiah tetap dapat
                                ditelusuri oleh yang berhak.
                            </p>
                            <a href="/login" class="slot mt-10 group">
                                <span class="font-display text-[1.6rem] tracking-[-0.01em] sm:text-[1.75rem]">Masuk Portal</span>
                                <span class="flex items-center gap-4">
                                    <span class="label-cap hidden text-mist sm:inline">Akun gereja</span>
                                    <svg class="slot-arrow h-5 w-5 text-brass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" d="M4 12h15M13 6l6 6-6 6"/>
                                    </svg>
                                </span>
                            </a>
                        </div>

                        <div class="lg:col-span-5">
                            <div class="flex items-start gap-4">
                                <span class="plate grid h-14 w-14 shrink-0 place-items-center overflow-hidden">
                                    <img src="{{ $logo }}" alt="" class="h-full w-full object-contain" onerror="this.onerror=null;this.src='{{ asset('favicon.png') }}'">
                                </span>
                                <div>
                                    <p class="label-cap leading-[1.6] text-paper/90">{{ $churchName }}</p>
                                    <p class="mt-2 text-[0.8125rem] leading-relaxed text-mist">
                                        {{ $churchAddress1 }}, {{ $churchAddress2 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Copyright, under the church's name. --}}
                    <div class="mt-12 flex flex-col gap-1.5 border-t border-white/10 pt-7 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                        <p class="label-cap text-mist">Komisi Inforkom {{ $churchName }}</p>
                        <p class="text-[0.75rem] leading-relaxed text-mist">
                            &copy; {{ date('Y') }} {{ $churchName }}. Seluruh hak cipta dilindungi.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Back to top. Revealed once the first viewport has been scrolled past. -->
    <button type="button" id="ke-atas" class="to-top" aria-label="Kembali ke atas">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M6 11l6-6 6 6"/>
        </svg>
    </button>

    <script>
        (function () {
            var button = document.getElementById('ke-atas');
            var first = document.getElementById('awal');
            if (!button || !first || !('IntersectionObserver' in window)) return;

            new IntersectionObserver(
                function (entries) {
                    button.classList.toggle('is-shown', !entries[0].isIntersecting);
                },
                { threshold: 0 },
            ).observe(first);

            button.addEventListener('click', function () {
                var reduce = window.matchMedia(
                    '(prefers-reduced-motion: reduce)',
                ).matches;
                window.scrollTo({
                    top: 0,
                    behavior: reduce ? 'auto' : 'smooth',
                });
                button.blur();
            });
        })();
    </script>
</body>
</html>
