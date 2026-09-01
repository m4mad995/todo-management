<script setup>
import { useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-bold text-gray-900">Informasi Profile</h2>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi profil dan alamat email akun Anda.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-5">
            <!-- Name -->
            <div>
                <label class="label" for="name">Nama</label>
                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    class="input"
                    required
                    autofocus
                    autocomplete="name"
                />
                <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
                <label class="label" for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    class="input"
                    required
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Email verification notice -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-3 rounded-btn bg-amber-50 text-amber-600 text-sm">
                Alamat email Anda belum diverifikasi.
                <button type="button" class="font-semibold underline hover:text-amber-700 ml-1">
                    Kirim ulang email verifikasi.
                </button>
                <div v-if="status === 'verification-link-sent'" class="mt-2 text-emerald-600 font-medium">
                    Link verifikasi baru telah dikirim ke email Anda.
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-btn bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 shadow-sm hover:shadow-md active:scale-[0.98] transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-medium">Tersimpan.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
