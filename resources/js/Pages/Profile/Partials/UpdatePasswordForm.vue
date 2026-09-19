<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-bold text-slate-900">Keamanan Akun</h2>
            <p class="mt-1 text-sm text-slate-600">
                Pastikan akun Anda menggunakan kata sandi yang kuat dan unik
                untuk menjaga keamanan akses portal.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-5">
            <div class="max-w-xl">
                <InputLabel
                    for="current_password"
                    value="Kata Sandi Saat Ini"
                />
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="w-full"
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.current_password" />
            </div>

            <div class="max-w-xl">
                <InputLabel for="password" value="Kata Sandi Baru" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full"
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="max-w-xl">
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Kata Sandi Baru"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full"
                    autocomplete="new-password"
                    placeholder="Ulangi kata sandi baru"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">Memperbarui...</span>
                    <span v-else>Perbarui Kata Sandi</span>
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
                        <span>Kata sandi berhasil diperbarui.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
