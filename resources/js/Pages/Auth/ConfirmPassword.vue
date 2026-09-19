<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Konfirmasi Kata Sandi" />

        <div class="mb-6">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                Konfirmasi Kata Sandi
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Ini adalah area aman aplikasi. Harap konfirmasi kata sandi Anda
                sebelum melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="password" value="Kata Sandi" />
                <TextInput
                    id="password"
                    type="password"
                    class="w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3 text-base"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Mengonfirmasi...</span>
                    <span v-else>Konfirmasi Kata Sandi</span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
