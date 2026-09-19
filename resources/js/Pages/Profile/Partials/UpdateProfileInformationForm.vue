<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-bold text-slate-900">Informasi Profil</h2>
            <p class="mt-1 text-sm text-slate-600">
                Perbarui nama lengkap dan alamat email yang terdaftar pada akun
                portal Anda.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-5"
        >
            <div class="max-w-xl">
                <InputLabel for="name" value="Nama Lengkap" />
                <TextInput
                    id="name"
                    type="text"
                    class="w-full"
                    v-model="form.name"
                    required
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="max-w-xl">
                <InputLabel for="email" value="Alamat Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="max-w-xl"
            >
                <div
                    class="rounded-lg border border-amber-200 bg-amber-50 p-3.5 text-sm text-amber-800"
                >
                    <p>
                        Alamat email Anda belum diverifikasi.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="font-semibold underline hover:text-amber-900 focus:outline-none"
                        >
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </Link>
                    </p>

                    <div
                        v-show="status === 'verification-link-sent'"
                        class="mt-2 font-medium text-emerald-700"
                    >
                        Tautan verifikasi baru telah dikirimkan ke alamat email
                        Anda.
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-200"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-200"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="flex items-center gap-1.5 text-sm font-medium text-emerald-600"
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
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        <span>Perubahan berhasil disimpan.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
