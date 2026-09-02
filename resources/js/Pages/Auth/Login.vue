<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Masuk ke Akun Anda</h2>
            <p class="text-gray-500 text-sm mt-1">Selamat datang kembali! Silakan masukkan data Anda.</p>
        </div>

        <!-- Status message -->
        <div v-if="status" class="mb-6 p-3 rounded-btn bg-emerald-50 text-emerald-600 text-sm font-medium">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div>
                <label class="label" for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    class="input"
                    placeholder="nama@email.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
                <label class="label" for="password">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        class="input pr-10"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                        tabindex="-1"
                    >
                        <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Remember me + Forgot password -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm text-gray-500">Ingat saya</span>
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium transition"
                >
                    Lupa password?
                </Link>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="btn btn-primary btn-lg w-full"
            >
                <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="!form.processing">Masuk</span>
                <span v-else>Masuk...</span>
            </button>
        </form>

        <!-- Register link -->
        <p class="mt-8 text-center text-sm text-gray-500">
            Belum punya akun?
            <Link :href="route('register')" class="text-blue-600 hover:text-blue-700 font-semibold transition">
                Daftar
            </Link>
        </p>
    </GuestLayout>
</template>
