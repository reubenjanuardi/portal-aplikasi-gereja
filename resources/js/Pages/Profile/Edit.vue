<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

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
        return user.value.roles.join(', ');
    }
    return user.value.is_super_admin ? 'Super Admin' : 'Pengguna Portal';
});
</script>

<template>
    <Head title="Pengaturan Profil" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900"
                    >
                        Profil Pengguna
                    </h1>
                    <p class="text-sm text-slate-500">
                        Kelola informasi akun, preferensi keamanan, dan
                        identitas pengguna portal.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700"
                >
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Akun Aktif</span>
                </div>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- User Overview Summary Card -->
                <div
                    class="shadow-xs rounded-2xl border border-slate-200 bg-white p-6 sm:p-8"
                >
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center"
                    >
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-blue-900 text-xl font-bold text-white shadow-sm"
                        >
                            {{ userInitials }}
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h2 class="text-xl font-bold text-slate-900">
                                    {{ user.name }}
                                </h2>
                                <span
                                    class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-900"
                                >
                                    {{ userRoleBadge }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-600">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 1: Update Profile Information -->
                <div
                    class="shadow-xs rounded-2xl border border-slate-200 bg-white p-6 sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Section 2: Update Password -->
                <div
                    class="shadow-xs rounded-2xl border border-slate-200 bg-white p-6 sm:p-8"
                >
                    <UpdatePasswordForm />
                </div>

                <!-- Section 3: Danger Zone -->
                <div
                    class="shadow-xs rounded-2xl border border-red-200 bg-red-50/30 p-6 sm:p-8"
                >
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
