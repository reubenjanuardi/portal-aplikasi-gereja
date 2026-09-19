<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Alamat Email" />

        <div class="mb-6">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                Verifikasi Alamat Email
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Sebelum memulai, silakan verifikasi alamat email Anda dengan
                mengklik tautan yang baru saja kami kirimkan ke email Anda. Jika
                Anda tidak menerima email tersebut, kami dapat mengirimkan
                tautan baru.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-5 flex items-start gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50 p-3.5 text-sm text-emerald-800"
        >
            <svg
                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <span class="leading-snug">
                Tautan verifikasi baru telah dikirimkan ke alamat email akun
                Anda.
            </span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <PrimaryButton
                    class="w-full py-2.5"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Mengirim Ulang...</span>
                    <span v-else>Kirim Ulang Email Verifikasi</span>
                </PrimaryButton>
            </div>

            <div class="pt-2 text-center">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs font-medium text-slate-600 transition-colors hover:text-red-600"
                >
                    Keluar (Log Out)
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
