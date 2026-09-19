<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(
    () =>
        page.props.auth?.user || {
            name: 'Pengguna',
            email: '',
            roles: [],
            permissions: [],
            is_super_admin: false,
        },
);

const searchQuery = ref('');
const selectedCategory = ref('Semua');
const showModal = ref(false);
const modalType = ref('coming_soon'); // 'coming_soon' | 'access_denied'
const activeModalApp = ref(null);
const userDropdownOpen = ref(false);

const apps = [
    {
        id: 'keuangan',
        name: 'Keuangan',
        subtitle: 'Jurnal, Buku Besar & Anggaran',
        category: 'Keuangan',
        route: '/keuangan',
        active: true,
        badge: 'Aktif',
        description:
            'Sistem pencatatan kas, persembahan, voucher masuk/keluar, jurnal umum, buku besar, dan laporan keuangan komprehensif GPIB Hosiana.',
        icon: 'accounting',
        color: 'from-amber-500 to-indigo-600',
    },
    {
        id: 'jemaat',
        name: 'Data Jemaat',
        subtitle: 'Sidi, Baptis & Sektor',
        category: 'Administrasi',
        route: '/jemaat',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Basis data terpadu jemaat, anggota sidi, baptisan, mutasi atestasi jemaat, dan sensus keluarga gerejawi.',
        icon: 'members',
        color: 'from-teal-400 to-indigo-600',
    },
    {
        id: 'aset',
        name: 'Aset Gereja',
        subtitle: 'Inventaris & Sarana Fisik',
        category: 'Sarana & Aset',
        route: '/aset',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Pencatatan barang inventaris gereja, peralatan multimedia, gedung ibadah, dan monitoring pemeliharaan berkala.',
        icon: 'inventory',
        color: 'from-amber-500 to-purple-600',
    },
    {
        id: 'absensi',
        name: 'Absensi & SDM',
        subtitle: 'Presensi Karyawan & Piket',
        category: 'Administrasi',
        route: '/absensi',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Pencatatan daftar hadir staf kantor gereja, koster, dan pengaturan jadwal tugas piket kantor.',
        icon: 'timesheet',
        color: 'from-rose-500 to-sky-600',
    },
    {
        id: 'ibadah',
        name: 'Jadwal & Ibadah',
        subtitle: 'Pelayan Firman & Liturgi',
        category: 'Pelayanan',
        route: '/ibadah',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Manajemen jadwal ibadah minggu, pengaturan pelayan firman, organis, kantoria, prokes, dan tata kebaktian.',
        icon: 'calendar',
        color: 'from-emerald-400 to-cyan-600',
    },
    {
        id: 'administrasi',
        name: 'Administrasi Surat',
        subtitle: 'Surat Masuk & Keluar',
        category: 'Administrasi',
        route: '/surat',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Penomoran surat resmi gereja, pencatatan surat masuk/keluar, surat keterangan baptis, sidi, dan kearsipan.',
        icon: 'documents',
        color: 'from-sky-500 to-amber-500',
    },
    {
        id: 'pelkat',
        name: 'Pelayanan Kategorial',
        subtitle: 'PA, PT, GP, PKP, PKB, PKLU',
        category: 'Pelayanan',
        route: '/pelkat',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Koordinasi program kerja dan agenda kegiatan kategorial anak, teruna, pemuda, wanita, pria, dan lansia.',
        icon: 'community',
        color: 'from-pink-500 to-teal-500',
    },
    {
        id: 'warta',
        name: 'Warta Jemaat',
        subtitle: 'Warta Digital & Pengumuman',
        category: 'Informasi',
        route: '/warta',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Penyusunan dan distribusi warta jemaat mingguan, pokok doa syafaat, serta jadwal agenda gereja.',
        icon: 'broadcast',
        color: 'from-blue-600 to-violet-600',
    },
    {
        id: 'multimedia',
        name: 'Multimedia & Studio',
        subtitle: 'Live Streaming & Dokumentasi',
        category: 'Sarana & Aset',
        route: '/multimedia',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Manajemen perlengkapan siaran ibadah online, sound system, arsip dokumentasi, dan materi slide kebaktian.',
        icon: 'studio',
        color: 'from-violet-500 to-cyan-500',
    },
    {
        id: 'konseling',
        name: 'Pastoral & Konseling',
        subtitle: 'Kunjungan & Pokok Doa',
        category: 'Pelayanan',
        route: '/pastoral',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Pengajuan permohonan konseling jemaat, jadwal kunjungan pendeta/majelis, dan rekap pokok doa.',
        icon: 'pastoral',
        color: 'from-rose-500 to-teal-500',
    },
    {
        id: 'helpdesk',
        name: 'Pusat Bantuan',
        subtitle: 'Panduan & Bantuan IT',
        category: 'Bantuan',
        route: '/helpdesk',
        active: false,
        badge: 'Segera Hadir',
        description:
            'Panduan lengkap penggunaan portal, pelaporan kendala teknis sistem, dan kontak tim pendukung TI.',
        icon: 'helpdesk',
        color: 'from-teal-500 to-emerald-600',
    },
    {
        id: 'settings',
        name: 'Pengaturan Portal',
        subtitle: 'Hak Akses & Konfigurasi',
        category: 'Sistem',
        route: '/settings',
        active: true,
        badge: 'Aktif',
        description:
            'Pengaturan profil institusi gereja, manajemen pengguna, penetapan peran & matriks hak akses, serta peninjau log aktivitas audit.',
        icon: 'settings',
        color: 'from-slate-600 to-blue-700',
    },
];

const categories = [
    'Semua',
    'Keuangan',
    'Administrasi',
    'Pelayanan',
    'Sarana & Aset',
    'Sistem',
];

function hasModuleAccess(app) {
    if (!app.active) return false;
    if (user.value.is_super_admin) return true;
    const perms = user.value.permissions || [];
    if (perms.includes('*')) return true;
    return perms.includes(`module.${app.id}`);
}

const filteredApps = computed(() => {
    return apps.filter((app) => {
        const matchesCategory =
            selectedCategory.value === 'Semua' ||
            app.category === selectedCategory.value;

        const query = searchQuery.value.toLowerCase().trim();
        const matchesSearch =
            !query ||
            app.name.toLowerCase().includes(query) ||
            app.subtitle.toLowerCase().includes(query) ||
            app.category.toLowerCase().includes(query) ||
            app.description.toLowerCase().includes(query);

        return matchesCategory && matchesSearch;
    });
});

function handleAppClick(app) {
    if (app.active && hasModuleAccess(app)) {
        window.location.href = app.route;
    } else if (app.active && !hasModuleAccess(app)) {
        activeModalApp.value = app;
        modalType.value = 'access_denied';
        showModal.value = true;
    } else {
        activeModalApp.value = app;
        modalType.value = 'coming_soon';
        showModal.value = true;
    }
}

function closeModal() {
    showModal.value = false;
    activeModalApp.value = null;
}

function handleLogout() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('logout');

    const match = document.cookie.match(/(^|;\s*)(XSRF-TOKEN)=([^;]+)/);
    const token = match ? decodeURIComponent(match[3]) : '';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = token;
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}
</script>

<template>
    <Head title="App Launcher - Portal GPIB Hosiana" />

    <div
        class="flex min-h-screen flex-col bg-slate-100 font-sans selection:bg-blue-900 selection:text-white"
    >
        <!-- Top Navbar -->
        <header
            class="sticky top-0 z-40 border-b border-slate-200 bg-white bg-white/95 shadow-sm backdrop-blur-md"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <!-- Left: Brand -->
                    <div class="flex shrink-0 items-center gap-3">
                        <img
                            src="/favicon.png"
                            alt="GPIB Hosiana"
                            class="h-9 w-9 object-contain"
                        />
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-base font-bold tracking-tight text-slate-900 sm:text-lg"
                                >
                                    Portal GPIB Hosiana
                                </span>
                                <span
                                    class="hidden items-center rounded border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-900 sm:inline-flex"
                                >
                                    App Launcher
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Center: Search Bar -->
                    <div class="hidden max-w-md flex-1 md:block">
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari modul aplikasi..."
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-9 pr-8 text-sm transition-all placeholder:text-slate-400 focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-900"
                            />
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Right: User Menu -->
                    <div class="flex items-center gap-3">
                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button
                                @click="userDropdownOpen = !userDropdownOpen"
                                class="flex items-center gap-2.5 rounded-lg p-1.5 transition-colors hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-900"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-900 text-xs font-bold text-white shadow-sm"
                                >
                                    {{
                                        user.name
                                            ? user.name.charAt(0).toUpperCase()
                                            : 'U'
                                    }}
                                </div>
                                <div class="hidden flex-col text-left lg:flex">
                                    <span
                                        class="max-w-[140px] truncate text-sm font-medium leading-tight text-slate-800"
                                    >
                                        {{ user.name }}
                                    </span>
                                    <span
                                        class="text-[10px] font-normal text-slate-500"
                                    >
                                        {{
                                            user.roles && user.roles.length
                                                ? user.roles[0]
                                                : user.is_super_admin
                                                  ? 'Super Admin'
                                                  : 'Pengguna'
                                        }}
                                    </span>
                                </div>
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
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>

                            <!-- Click away backdrop -->
                            <div
                                v-if="userDropdownOpen"
                                @click="userDropdownOpen = false"
                                class="fixed inset-0 z-40"
                            />

                            <!-- Dropdown Menu -->
                            <div
                                v-if="userDropdownOpen"
                                class="absolute right-0 z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white py-1.5 text-sm shadow-lg"
                            >
                                <div
                                    class="border-b border-slate-100 px-4 py-2"
                                >
                                    <p
                                        class="truncate font-semibold text-slate-900"
                                    >
                                        {{ user.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ user.email }}
                                    </p>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <span
                                            v-for="r in user.roles || []"
                                            :key="r"
                                            class="inline-block rounded border border-blue-100 bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-800"
                                        >
                                            {{ r }}
                                        </span>
                                    </div>
                                </div>
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center gap-2 px-4 py-2 text-slate-700 transition-colors hover:bg-slate-50"
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
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    Profil Akun
                                </Link>
                                <a
                                    v-if="
                                        user.is_super_admin ||
                                        (user.permissions &&
                                            user.permissions.includes(
                                                'module.settings',
                                            ))
                                    "
                                    href="/settings"
                                    class="flex items-center gap-2 px-4 py-2 text-slate-700 transition-colors hover:bg-slate-50"
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
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    Pengaturan Portal
                                </a>
                                <button
                                    @click="handleLogout"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-red-600 transition-colors hover:bg-red-50"
                                >
                                    <svg
                                        class="h-4 w-4 text-red-500"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                        />
                                    </svg>
                                    Keluar (Logout)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Launcher Content -->
        <main
            class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 sm:py-12 lg:px-8"
        >
            <!-- Header Greeting & Search on Mobile -->
            <div
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Aplikasi &amp; Layanan Portal
                    </h1>
                    <p class="mt-1 text-sm text-slate-600 sm:text-base">
                        Pilih modul di bawah untuk membuka sistem pengelolaan
                        GPIB Jemaat Hosiana sesuai hak akses Anda.
                    </p>
                </div>

                <!-- Mobile search -->
                <div class="w-full md:hidden">
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari modul aplikasi..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-900"
                        />
                    </div>
                </div>
            </div>

            <!-- Category Filter Pills -->
            <div
                class="no-scrollbar mb-8 flex items-center gap-2 overflow-x-auto px-1 py-2"
            >
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="[
                        'whitespace-nowrap rounded-full px-4 py-1.5 text-xs font-semibold transition-all duration-200 focus:outline-none',
                        selectedCategory === cat
                            ? 'border border-blue-900 bg-blue-900 text-white shadow-sm'
                            : 'shadow-2xs border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                    ]"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Apps Grid (Odoo / Launcher Inspired 6 Columns) -->
            <div
                v-if="filteredApps.length > 0"
                class="grid grid-cols-2 justify-items-center gap-6 sm:grid-cols-3 sm:gap-8 md:grid-cols-4 lg:grid-cols-6"
            >
                <div
                    v-for="app in filteredApps"
                    :key="app.id"
                    @click="handleAppClick(app)"
                    class="group flex w-full max-w-[150px] cursor-pointer flex-col items-center transition-transform duration-200"
                >
                    <!-- App Card Box -->
                    <div
                        class="relative flex aspect-square w-full items-center justify-center rounded-3xl border bg-white p-4 shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-all duration-300 group-hover:-translate-y-1.5 group-hover:shadow-xl sm:p-5"
                        :class="[
                            app.active && hasModuleAccess(app)
                                ? 'border-slate-100 shadow-blue-900/5 ring-2 ring-blue-900/10 group-hover:border-blue-300'
                                : app.active && !hasModuleAccess(app)
                                  ? 'border-amber-200/80 bg-slate-50/70 opacity-90 group-hover:border-amber-300'
                                  : 'border-slate-100 opacity-80 group-hover:border-slate-300',
                        ]"
                    >
                        <!-- Lock badge for active module WITHOUT user permission -->
                        <span
                            v-if="app.active && !hasModuleAccess(app)"
                            class="shadow-2xs absolute right-2.5 top-2.5 inline-flex items-center gap-0.5 rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800"
                            title="Akses Terkunci (Hubungi Admin)"
                        >
                            <svg
                                class="h-3 w-3"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2.5"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                            Terkunci
                        </span>

                        <!-- Coming Soon icon for unreleased modules -->
                        <span
                            v-else-if="!app.active"
                            class="absolute right-2.5 top-2.5 h-4 w-4 text-slate-300 transition-colors group-hover:text-slate-400"
                            title="Segera Hadir"
                        >
                            <svg
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </span>

                        <!-- App Vector Icon -->
                        <div
                            class="flex h-full w-full items-center justify-center transition-transform duration-300 group-hover:scale-110"
                        >
                            <!-- 1. Accounting / Keuangan Icon -->
                            <svg
                                v-if="app.icon === 'accounting'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <circle cx="24" cy="24" r="9" fill="#F59E0B" />
                                <circle cx="40" cy="40" r="9" fill="#0D9488" />
                                <path
                                    d="M46 18L18 46"
                                    stroke="#4F46E5"
                                    stroke-width="7"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 2. Data Jemaat Icon -->
                            <svg
                                v-else-if="app.icon === 'members'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <circle cx="32" cy="22" r="10" fill="#0284C7" />
                                <circle cx="18" cy="26" r="7" fill="#F59E0B" />
                                <circle cx="46" cy="26" r="7" fill="#0D9488" />
                                <path
                                    d="M14 50C14 42 22 38 32 38C42 38 50 42 50 50"
                                    stroke="#4F46E5"
                                    stroke-width="6"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 3. Aset Gereja Icon (Isometric 3D Cube) -->
                            <svg
                                v-else-if="app.icon === 'inventory'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <path
                                    d="M32 10L50 20L32 30L14 20L32 10Z"
                                    fill="#F59E0B"
                                />
                                <path
                                    d="M14 20L32 30V52L14 42V20Z"
                                    fill="#EA580C"
                                />
                                <path
                                    d="M50 20L32 30V52L50 42V20Z"
                                    fill="#7C3AED"
                                />
                            </svg>

                            <!-- 4. Absensi & SDM Icon (Timesheet / Clock) -->
                            <svg
                                v-else-if="app.icon === 'timesheet'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <circle cx="32" cy="32" r="22" fill="#0284C7" />
                                <path
                                    d="M32 10C44.15 10 54 19.85 54 32C54 44.15 44.15 54 32 54"
                                    stroke="#F43F5E"
                                    stroke-width="5"
                                    stroke-linecap="round"
                                />
                                <circle cx="32" cy="32" r="4" fill="white" />
                                <path
                                    d="M32 32L44 20"
                                    stroke="white"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 5. Jadwal & Ibadah Icon (Calendar / Cross) -->
                            <svg
                                v-else-if="app.icon === 'calendar'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <rect
                                    x="12"
                                    y="14"
                                    width="40"
                                    height="38"
                                    rx="8"
                                    fill="#10B981"
                                />
                                <rect
                                    x="12"
                                    y="14"
                                    width="40"
                                    height="12"
                                    rx="4"
                                    fill="#047857"
                                />
                                <circle cx="22" cy="11" r="3" fill="#F59E0B" />
                                <circle cx="42" cy="11" r="3" fill="#F59E0B" />
                                <path
                                    d="M32 32V44M26 38H38"
                                    stroke="white"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 6. Administrasi Surat Icon (Documents) -->
                            <svg
                                v-else-if="app.icon === 'documents'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <rect
                                    x="14"
                                    y="18"
                                    width="28"
                                    height="36"
                                    rx="4"
                                    fill="#0284C7"
                                    transform="rotate(-6 14 18)"
                                />
                                <rect
                                    x="22"
                                    y="12"
                                    width="28"
                                    height="36"
                                    rx="4"
                                    fill="#F59E0B"
                                />
                                <path
                                    d="M28 22H42M28 28H38M28 34H42"
                                    stroke="white"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 7. Pelayanan Kategorial Icon (Partnership / Handshake) -->
                            <svg
                                v-else-if="app.icon === 'community'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <path
                                    d="M12 28L26 42L40 28L26 14L12 28Z"
                                    fill="#0D9488"
                                />
                                <path
                                    d="M24 40L38 54L52 40L38 26L24 40Z"
                                    fill="#7C3AED"
                                />
                            </svg>

                            <!-- 8. Warta Jemaat Icon (Paper plane / Broadcast) -->
                            <svg
                                v-else-if="app.icon === 'broadcast'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <path
                                    d="M10 32L54 14L36 54L28 36L10 32Z"
                                    fill="#2563EB"
                                />
                                <path
                                    d="M28 36L54 14"
                                    stroke="white"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                                <path d="M28 36V48L34 42" fill="#1D4ED8" />
                            </svg>

                            <!-- 9. Multimedia & Studio Icon -->
                            <svg
                                v-else-if="app.icon === 'studio'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <path
                                    d="M18 16L46 44M46 16L18 44"
                                    stroke="#7C3AED"
                                    stroke-width="7"
                                    stroke-linecap="round"
                                />
                                <path
                                    d="M24 10L10 24M54 40L40 54"
                                    stroke="#0284C7"
                                    stroke-width="6"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 10. Pastoral & Konseling Icon -->
                            <svg
                                v-else-if="app.icon === 'pastoral'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <path
                                    d="M32 50C32 50 14 38 14 26C14 18 20 14 26 14C29 14 32 17 32 17C32 17 35 14 38 14C44 14 50 18 50 26C50 38 32 50 32 50Z"
                                    fill="#F43F5E"
                                />
                                <path
                                    d="M32 22V36M25 29H39"
                                    stroke="white"
                                    stroke-width="3.5"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 11. Helpdesk Icon -->
                            <svg
                                v-else-if="app.icon === 'helpdesk'"
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <rect
                                    x="12"
                                    y="12"
                                    width="40"
                                    height="40"
                                    rx="10"
                                    fill="#0D9488"
                                />
                                <path
                                    d="M32 20V44M20 32H44"
                                    stroke="white"
                                    stroke-width="7"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- 12. Settings Icon -->
                            <svg
                                v-else
                                class="h-12 w-12 sm:h-14 sm:w-14"
                                viewBox="0 0 64 64"
                                fill="none"
                            >
                                <rect
                                    x="14"
                                    y="14"
                                    width="16"
                                    height="16"
                                    rx="4"
                                    fill="#7C3AED"
                                />
                                <rect
                                    x="34"
                                    y="14"
                                    width="16"
                                    height="16"
                                    rx="4"
                                    fill="#EF4444"
                                />
                                <rect
                                    x="14"
                                    y="34"
                                    width="16"
                                    height="16"
                                    rx="4"
                                    fill="#0284C7"
                                />
                                <rect
                                    x="34"
                                    y="34"
                                    width="16"
                                    height="16"
                                    rx="4"
                                    fill="#10B981"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- App Title Label -->
                    <span
                        class="mt-3 w-full truncate text-center text-sm font-semibold tracking-tight text-slate-800 transition-colors group-hover:text-blue-900 sm:text-base"
                    >
                        {{ app.name }}
                    </span>
                    <span
                        class="hidden w-full truncate text-center text-[11px] text-slate-500 sm:block"
                    >
                        {{ app.subtitle }}
                    </span>
                </div>
            </div>

            <!-- Empty State for Search -->
            <div
                v-else
                class="rounded-3xl border border-slate-200 bg-white p-8 py-16 text-center shadow-sm"
            >
                <div
                    class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900">
                    Aplikasi tidak ditemukan
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Tidak ada modul yang cocok dengan kata kunci "{{
                        searchQuery
                    }}".
                </p>
                <button
                    @click="
                        searchQuery = '';
                        selectedCategory = 'Semua';
                    "
                    class="mt-4 inline-flex items-center rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-900 transition-colors hover:bg-blue-100"
                >
                    Reset Pencarian
                </button>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="mt-auto border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500"
        >
            <div class="mx-auto max-w-7xl px-4">
                &copy; 2026 GPIB Jemaat Hosiana Jakarta. Portal Terintegrasi
                Administrasi, Keuangan &amp; Pelayanan.
            </div>
        </footer>

        <!-- Modal: Coming Soon or Access Denied Notification -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm"
            @click.self="closeModal"
        >
            <div
                class="animate-in fade-in zoom-in-95 relative w-full max-w-md rounded-3xl border border-slate-100 bg-white p-6 text-center shadow-2xl duration-200 sm:p-8"
            >
                <button
                    @click="closeModal"
                    class="absolute right-4 top-4 rounded-full p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- Case 1: ACCESS DENIED MODAL -->
                <div v-if="modalType === 'access_denied'">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 text-amber-600 shadow-sm"
                    >
                        <svg
                            class="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            />
                        </svg>
                    </div>

                    <div
                        class="mb-2 inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-900"
                    >
                        Akses Terkunci
                    </div>

                    <h3 class="text-xl font-bold tracking-tight text-slate-900">
                        Modul {{ activeModalApp?.name }}
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Akun Anda (<strong>{{ user.name }}</strong
                        >) belum diberikan izin untuk mengakses modul ini.
                    </p>

                    <div
                        class="mt-4 flex items-start gap-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-left text-xs text-slate-600"
                    >
                        <svg
                            class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>
                            Sistem menerapkan
                            <strong>Role-Based Access Control (RBAC)</strong>.
                            Silakan hubungi
                            <strong>Super Administrator</strong> jika Anda
                            memerlukan akses ke modul ini.
                        </span>
                    </div>

                    <div class="mt-6">
                        <button
                            @click="closeModal"
                            class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800"
                        >
                            Saya Mengerti
                        </button>
                    </div>
                </div>

                <!-- Case 2: COMING SOON MODAL -->
                <div v-else>
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-900 shadow-sm"
                    >
                        <svg
                            class="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.75"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>
                    </div>

                    <div
                        class="mb-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800"
                    >
                        Tahap Pengembangan
                    </div>

                    <h3 class="text-xl font-bold tracking-tight text-slate-900">
                        Modul {{ activeModalApp?.name }}
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        {{ activeModalApp?.description }}
                    </p>

                    <div
                        class="mt-4 flex items-start gap-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3 text-left text-xs text-slate-500"
                    >
                        <svg
                            class="mt-0.5 h-4 w-4 shrink-0 text-blue-900"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>
                            Modul ini sedang dalam proses pengembangan terpadu
                            dan akan diaktifkan secara bertahap.
                        </span>
                    </div>

                    <div class="mt-6">
                        <button
                            @click="closeModal"
                            class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-200"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
