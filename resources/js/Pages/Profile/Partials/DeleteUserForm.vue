<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-4">
        <header class="border-b border-red-100 pb-4">
            <div class="flex items-center gap-2">
                <svg
                    class="h-5 w-5 text-red-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
                <h2 class="text-lg font-bold text-red-700">
                    Zona Berbahaya: Hapus Akun
                </h2>
            </div>

            <p class="mt-1 text-sm text-slate-600">
                Setelah akun Anda dihapus, semua data dan hak akses yang
                terhubung akan dihapus secara permanen. Tindakan ini tidak dapat
                dibatalkan.
            </p>
        </header>

        <div class="pt-2">
            <DangerButton @click="confirmUserDeletion">
                Hapus Akun Pengguna
            </DangerButton>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600"
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
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900">
                            Konfirmasi Penghapusan Akun
                        </h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                            Apakah Anda yakin ingin menghapus akun Anda secara
                            permanen? Seluruh sesi dan hak akses portal akan
                            dihentikan seketika. Silakan masukkan kata sandi
                            Anda untuk melanjutkan.
                        </p>

                        <div class="mt-5">
                            <InputLabel
                                for="delete_password"
                                value="Kata Sandi Akun"
                            />

                            <TextInput
                                id="delete_password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="w-full"
                                placeholder="Masukkan kata sandi Anda"
                                @keyup.enter="deleteUser"
                            />

                            <InputError :message="form.errors.password" />
                        </div>

                        <div
                            class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                        >
                            <SecondaryButton @click="closeModal">
                                Batal
                            </SecondaryButton>

                            <DangerButton
                                :disabled="form.processing"
                                @click="deleteUser"
                            >
                                <span v-if="form.processing">Menghapus...</span>
                                <span v-else>Hapus Akun Permanen</span>
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </section>
</template>
