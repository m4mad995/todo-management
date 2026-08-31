<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    isQuickModalOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['openQuickModal']);

const page = usePage();

const isUrl = (...urls) => {
    return urls.some(url => page.url.startsWith(url));
};

const navItems = [
    { href: '/dashboard', label: 'Dashboard', icon: 'chart' },
    { href: '/focus', label: 'Fokus', icon: 'target' },
    { href: '/today', label: 'Hari Ini', icon: 'checklist' },
    { href: '/routines', label: 'Rutinitas', icon: 'refresh' },
];
</script>

<template>
    <nav class="fixed bottom-0 inset-x-0 z-20 lg:hidden px-4" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="max-w-lg mx-auto mb-3">
            <div class="bg-surface rounded-3xl shadow-lg shadow-black/5 border border-border relative overflow-visible">

                <!-- Nav items -->
                <div class="flex items-center h-16 relative z-10">

                    <!-- Dashboard -->
                    <Link href="/dashboard" class="flex-1 flex flex-col items-center justify-center h-16 relative z-10 transition-[color] duration-300"
                        :class="isUrl('/dashboard') ? 'text-blue-600' : 'text-gray-400'">
                        <div class="flex items-center justify-center rounded-full transition-[width,height,margin,box-shadow,border-color] duration-300 ease-[cubic-bezier(0.25,0.1,0.25,1)]"
                            :class="isUrl('/dashboard') ? 'w-12 h-12 -mt-6 bg-surface border-2 border-blue-600 shadow-lg' : 'w-8 h-8'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] transition-[color,font-weight] duration-300 mt-1"
                            :class="isUrl('/dashboard') ? 'font-bold' : 'font-semibold'">Dashboard</span>
                    </Link>

                    <!-- Fokus -->
                    <Link href="/focus" class="flex-1 flex flex-col items-center justify-center h-16 relative z-10 transition-[color] duration-300"
                        :class="isUrl('/focus') ? 'text-blue-600' : 'text-gray-400'">
                        <div class="flex items-center justify-center rounded-full transition-[width,height,margin,box-shadow,border-color] duration-300 ease-[cubic-bezier(0.25,0.1,0.25,1)]"
                            :class="isUrl('/focus') ? 'w-12 h-12 -mt-6 bg-surface border-2 border-blue-600 shadow-lg' : 'w-8 h-8'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="6" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>
                        </div>
                        <span class="text-[10px] transition-[color,font-weight] duration-300 mt-1"
                            :class="isUrl('/focus') ? 'font-bold' : 'font-semibold'">Fokus</span>
                    </Link>

                    <!-- FAB (+) -->
                    <button @click="emit('openQuickModal')" class="flex-1 flex flex-col items-center justify-center h-16 relative z-10 transition-[color] duration-300"
                        :class="isQuickModalOpen ? 'text-blue-600' : 'text-gray-400'">
                        <div class="flex items-center justify-center rounded-full transition-[width,height,margin,box-shadow,border-color] duration-300 ease-[cubic-bezier(0.25,0.1,0.25,1)]"
                            :class="isQuickModalOpen ? 'w-12 h-12 -mt-6 bg-blue-600 border-2 border-blue-600 shadow-lg' : 'w-8 h-8 bg-blue-600 text-white'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-[10px] transition-[color,font-weight] duration-300 mt-1"
                            :class="isQuickModalOpen ? 'font-bold text-blue-600' : 'font-semibold'">Baru</span>
                    </button>

                    <!-- Hari Ini -->
                    <Link href="/today" class="flex-1 flex flex-col items-center justify-center h-16 relative z-10 transition-[color] duration-300"
                        :class="isUrl('/today') ? 'text-blue-600' : 'text-gray-400'">
                        <div class="flex items-center justify-center rounded-full transition-[width,height,margin,box-shadow,border-color] duration-300 ease-[cubic-bezier(0.25,0.1,0.25,1)]"
                            :class="isUrl('/today') ? 'w-12 h-12 -mt-6 bg-surface border-2 border-blue-600 shadow-lg' : 'w-8 h-8'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <span class="text-[10px] transition-[color,font-weight] duration-300 mt-1"
                            :class="isUrl('/today') ? 'font-bold' : 'font-semibold'">Hari Ini</span>
                    </Link>

                    <!-- Rutinitas -->
                    <Link href="/routines" class="flex-1 flex flex-col items-center justify-center h-16 relative z-10 transition-[color] duration-300"
                        :class="isUrl('/routines') ? 'text-blue-600' : 'text-gray-400'">
                        <div class="flex items-center justify-center rounded-full transition-[width,height,margin,box-shadow,border-color] duration-300 ease-[cubic-bezier(0.25,0.1,0.25,1)]"
                            :class="isUrl('/routines') ? 'w-12 h-12 -mt-6 bg-surface border-2 border-blue-600 shadow-lg' : 'w-8 h-8'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <span class="text-[10px] transition-[color,font-weight] duration-300 mt-1"
                            :class="isUrl('/routines') ? 'font-bold' : 'font-semibold'">Rutinitas</span>
                    </Link>

                </div>
            </div>
        </div>
    </nav>
</template>
