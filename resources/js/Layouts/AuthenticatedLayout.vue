<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const page = usePage();
const user = computed(
    () =>
        page.props.auth?.user || {
            name: 'Pengguna',
            email: '',
            roles: [],
        },
);

const userInitials = computed(() => {
    const name = user.value.name || 'Pengguna';
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const userRoleBadge = computed(() => {
    if (user.value.roles && user.value.roles.length > 0) {
        return user.value.roles[0];
    }
    return user.value.is_super_admin ? 'Super Admin' : 'Pengguna Portal';
});

const showingNavigationDropdown = ref(false);

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
    <div
        class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-blue-900 selection:text-white"
    >
        <!-- Top Navbar -->
        <nav
            class="shadow-xs sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-md"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <!-- Left: Brand & Navigation Links -->
                    <div class="flex items-center gap-6">
                        <Link
                            :href="route('dashboard')"
                            class="flex shrink-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            <img
                                src="/favicon.png"
                                alt="Logo GPIB Hosiana"
                                class="h-9 w-9 object-contain"
                            />
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-base font-bold tracking-tight text-slate-900 sm:text-lg"
                                >
                                    Portal GPIB Hosiana
                                </span>
                            </div>
                        </Link>

                        <!-- Desktop Navigation -->
                        <div class="hidden sm:flex sm:items-center sm:gap-4">
                            <Link
                                :href="route('dashboard')"
                                :class="[
                                    route().current('dashboard')
                                        ? 'bg-blue-50 font-semibold text-blue-900'
                                        : 'font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                                    'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm transition-colors',
                                ]"
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
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                                    />
                                </svg>
                                <span>App Launcher</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Right: User Menu -->
                    <div class="hidden sm:flex sm:items-center sm:gap-3">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-1.5 pr-3 text-left transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                                >
                                    <div
                                        class="shadow-xs flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-900 text-xs font-bold text-white"
                                    >
                                        {{ userInitials }}
                                    </div>
                                    <div class="flex flex-col text-start">
                                        <span
                                            class="max-w-[140px] truncate text-xs font-semibold text-slate-900"
                                        >
                                            {{ user.name }}
                                        </span>
                                        <span
                                            class="max-w-[140px] truncate text-[11px] font-medium text-slate-500"
                                        >
                                            {{ userRoleBadge }}
                                        </span>
                                    </div>
                                    <svg
                                        class="h-4 w-4 text-slate-400"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div
                                    class="border-b border-slate-100 px-4 py-2.5"
                                >
                                    <p
                                        class="truncate text-xs font-bold text-slate-900"
                                    >
                                        {{ user.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ user.email }}
                                    </p>
                                </div>

                                <div class="py-1">
                                    <DropdownLink :href="route('dashboard')">
                                        App Launcher
                                    </DropdownLink>
                                    <DropdownLink :href="route('profile.edit')">
                                        Pengaturan Profil
                                    </DropdownLink>
                                </div>

                                <div class="border-t border-slate-100 py-1">
                                    <button
                                        type="button"
                                        @click="handleLogout"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm font-medium text-red-600 transition-colors hover:bg-red-50 focus:bg-red-50 focus:outline-none"
                                    >
                                        <svg
                                            class="h-4 w-4 shrink-0"
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
                                        <span>Keluar (Log Out)</span>
                                    </button>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <div class="flex items-center sm:hidden">
                        <button
                            @click="
                                showingNavigationDropdown =
                                    !showingNavigationDropdown
                            "
                            class="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-900"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex':
                                            !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex':
                                            showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="border-b border-slate-200 bg-white sm:hidden"
            >
                <div class="space-y-1 px-4 pb-3 pt-2">
                    <ResponsiveNavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                    >
                        App Launcher
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('profile.edit')"
                        :active="route().current('profile.edit')"
                    >
                        Pengaturan Profil
                    </ResponsiveNavLink>
                </div>

                <div class="border-t border-slate-200 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-900 text-xs font-bold text-white"
                        >
                            {{ userInitials }}
                        </div>
                        <div class="truncate">
                            <div class="text-sm font-semibold text-slate-900">
                                {{ user.name }}
                            </div>
                            <div class="truncate text-xs text-slate-500">
                                {{ user.email }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button
                            type="button"
                            @click="handleLogout"
                            class="flex w-full items-center gap-2 rounded-lg py-2 text-start text-sm font-semibold text-red-600 transition-colors hover:bg-red-50 focus:outline-none"
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
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                />
                            </svg>
                            <span>Keluar (Log Out)</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header Slot -->
        <header
            v-if="$slots.header"
            class="shadow-xs border-b border-slate-200 bg-white"
        >
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
