<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const whatsappUrl =
    'https://wa.me/6281519787818?text=' +
    encodeURIComponent(
        'Halo Admin, saya lupa password Portal GPIB Hosiana dan membutuhkan link untuk reset password. Nama: Email:',
    );
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Portal" />

        <div class="mb-6">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                Masuk ke Portal
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Gunakan akun portal Anda untuk melanjutkan.
            </p>
        </div>

        <!-- Status Notification Feedback -->
        <div
            v-if="status"
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
            <span class="leading-snug">{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Alamat Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@gpibhosiana.org"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi" />
                <TextInput
                    id="password"
                    type="password"
                    class="w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label for="remember" class="flex cursor-pointer items-center">
                    <Checkbox
                        id="remember"
                        name="remember"
                        v-model:checked="form.remember"
                    />
                    <span class="ms-2 select-none text-sm text-slate-600">
                        Ingat Saya
                    </span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3 text-base"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Memproses Masuk...</span>
                    <span v-else>Masuk ke Portal</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- WhatsApp Password Recovery UX -->
        <div class="mt-6 border-t border-slate-200 pt-6">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"
                            />
                            <path
                                d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.98-1.309C8.423 21.533 10.153 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.63 0-3.147-.48-4.437-1.312l-.317-.205-2.943.772.785-2.87-.225-.333C3.992 14.73 3.5 13.418 3.5 12 3.5 7.313 7.313 3.5 12 3.5 16.687 3.5 20.5 7.313 20.5 12 20.5 16.687 16.687 20.5 12 20.5z"
                            />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xs font-semibold text-slate-900">
                            Lupa Kata Sandi?
                        </h2>
                        <p
                            class="mt-0.5 text-xs leading-relaxed text-slate-600"
                        >
                            Hubungi administrator melalui WhatsApp untuk
                            mendapatkan link reset password.
                        </p>
                    </div>
                </div>

                <a
                    :href="whatsappUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="shadow-xs mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                >
                    <span>Hubungi Administrator</span>
                    <svg
                        class="h-3.5 w-3.5 text-slate-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                        />
                    </svg>
                </a>
            </div>
        </div>
    </GuestLayout>
</template>
