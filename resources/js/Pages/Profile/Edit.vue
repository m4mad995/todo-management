<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const currentTheme = ref(usePage().props.theme || 'system');

const themes = [
    { value: 'light', label: 'Light', icon: 'sun' },
    { value: 'dark', label: 'Dark', icon: 'moon' },
    { value: 'system', label: 'System', icon: 'monitor' },
];

const applyTheme = (theme) => {
    const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
    localStorage.setItem('theme', theme);
};

const switchTheme = (theme) => {
    currentTheme.value = theme;
    applyTheme(theme);
    router.patch(route('profile.theme'), { theme }, { preserveScroll: true });
};

// Apply theme on mount
applyTheme(currentTheme.value);

// Listen for system theme changes
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (currentTheme.value === 'system') {
            applyTheme('system');
        }
    });
}
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-10">
            <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6">
                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Profile</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola informasi akun dan pengaturan Anda.</p>
                </div>

                <!-- Theme -->
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Tema</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih tampilan yang Anda sukai.</p>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <button
                            v-for="t in themes"
                            :key="t.value"
                            @click="switchTheme(t.value)"
                            :class="[
                                'flex flex-col items-center gap-2 p-4 rounded-card border-2 transition-all duration-150',
                                currentTheme === t.value
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-gray-200 dark:border-slate-600 hover:border-gray-300 dark:hover:border-slate-500'
                            ]"
                        >
                            <!-- Sun icon -->
                            <svg v-if="t.icon === 'sun'" :class="['w-6 h-6', currentTheme === t.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <!-- Moon icon -->
                            <svg v-if="t.icon === 'moon'" :class="['w-6 h-6', currentTheme === t.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <!-- Monitor icon -->
                            <svg v-if="t.icon === 'monitor'" :class="['w-6 h-6', currentTheme === t.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span :class="['text-sm font-medium', currentTheme === t.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400']">{{ t.label }}</span>
                        </button>
                    </div>
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
