<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Atur Ulang Kata Sandi" />

        <div class="mb-6">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                Atur Ulang Kata Sandi
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Masukkan kata sandi baru untuk akun portal Anda.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Alamat Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="w-full cursor-not-allowed bg-slate-100 text-slate-500"
                    v-model="form.email"
                    disabled
                    readonly
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi Baru" />
                <TextInput
                    id="password"
                    type="password"
                    class="w-full"
                    v-model="form.password"
                    required
                    autofocus
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Kata Sandi Baru"
                />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Ulangi kata sandi baru"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3 text-base"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Menyimpan Kata Sandi...</span>
                    <span v-else>Simpan Kata Sandi Baru</span>
                </PrimaryButton>
            </div>

            <div class="pt-2 text-center">
                <Link
                    :href="route('login')"
                    class="text-xs font-medium text-slate-600 transition-colors hover:text-blue-900"
                >
                    &larr; Kembali ke Halaman Masuk
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
