<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Verifikasi Email</h2>
            <p class="text-gray-500 text-sm mt-1">Terima kasih sudah mendaftar! Sebelum memulai, silakan verifikasi email Anda dengan mengklik link yang kami kirimkan.</p>
        </div>

        <!-- Info -->
        <div v-if="verificationLinkSent" class="mb-6 p-3 rounded-btn bg-emerald-50 text-emerald-600 text-sm font-medium">
            Link verifikasi baru telah dikirim ke email Anda.
        </div>

        <div class="space-y-5">
            <p class="text-sm text-gray-500">
                Jika Anda tidak menerima email, kami dapat mengirim ulang link verifikasi.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-3">
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="btn btn-primary btn-lg w-full sm:w-auto"
                >
                    <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="!form.processing">Kirim Ulang Email</span>
                    <span v-else>Mengirim...</span>
                </button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="btn btn-ghost btn-lg w-full sm:w-auto"
                >
                    Keluar
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
