<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Password" />

        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Lupa Password?</h2>
            <p class="text-gray-500 text-sm mt-1">Masukkan email Anda dan kami akan mengirimkan link untuk mengatur ulang password.</p>
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
                <span v-if="!form.processing">Kirim Link Reset</span>
                <span v-else>Mengirim...</span>
            </button>
        </form>

        <!-- Back to login -->
        <p class="mt-8 text-center text-sm text-gray-500">
            Ingat password Anda?
            <Link :href="route('login')" class="text-blue-600 hover:text-blue-700 font-semibold transition">
                Masuk
            </Link>
        </p>
    </GuestLayout>
</template>
