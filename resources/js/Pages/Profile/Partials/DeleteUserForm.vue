<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const showPassword = ref(false);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
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
    <section>
        <header>
            <h2 class="text-lg font-bold text-gray-900">Hapus Akun</h2>
            <p class="text-sm text-gray-500 mt-1">Setelah akun dihapus, semua data akan dihapus secara permanen. Pastikan Anda sudah menyimpan data yang diperlukan.</p>
        </header>

        <button @click="confirmUserDeletion" class="btn btn-sm bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm mt-4">
            Hapus Akun
        </button>

        <!-- Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="confirmingUserDeletion" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-md p-6 animate-slide-up">
                        <h3 class="text-lg font-bold text-gray-900">Yakin ingin menghapus akun?</h3>
                        <p class="text-sm text-gray-500 mt-2">Semua data akun akan dihapus permanen. Masukkan password Anda untuk konfirmasi.</p>

                        <div class="mt-5">
                            <label class="label" for="delete-password">Password</label>
                            <div class="relative">
                                <input
                                    id="delete-password"
                                    ref="passwordInput"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="input pr-10"
                                    placeholder="Masukkan password"
                                    @keyup.enter="deleteUser"
                                />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition" tabindex="-1">
                                    <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
                            <button @click="closeModal" class="btn btn-ghost btn-sm">Batal</button>
                            <button
                                @click="deleteUser"
                                :disabled="form.processing"
                                class="btn btn-sm bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm"
                            >
                                {{ form.processing ? 'Menghapus...' : 'Hapus Akun' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>
