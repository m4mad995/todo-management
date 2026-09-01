<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-10">
            <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6">
                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Profile</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola informasi akun dan pengaturan Anda.</p>
                </div>

                <!-- Profile Information -->
                <div class="card p-6">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Update Password -->
                <div class="card p-6">
                    <UpdatePasswordForm />
                </div>

                <!-- Delete Account -->
                <div class="card p-6">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
