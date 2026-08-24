<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage, useForm, router } from '@inertiajs/vue3';

const isQuickModalOpen = ref(false);
const topbarInput = ref('');
const page = usePage();
const showMobileMenu = ref(false);

const quickForm = useForm({
    type: 'focus',
    title: '',
    quadrant: 'none',
    repeat_type: 'daily',
    selected_days: [],
    event_date: '',
    event_time: '',
});

const getCurrentSection = () => {
    const currentUrl = page.url;
    if (currentUrl.startsWith('/routines')) return 'routine';
    if (currentUrl.startsWith('/agenda')) return 'agenda';
    return 'focus';
};

const topbarSection = computed(() => getCurrentSection());

const isDashboard = computed(() => page.url === '/dashboard' || page.url === '/');

const topbarPlaceholder = computed(() => {
    const placeholders = {
        focus: 'Ketik tugas baru, tekan Enter...',
        routine: 'Ketik rutinitas baru, tekan Enter...',
        agenda: 'Ketik agenda baru, tekan Enter...',
    };
    return placeholders[topbarSection.value] || placeholders.focus;
});

const topbarBadge = computed(() => {
    const badges = {
        focus: { label: 'Fokus', class: 'bg-red-50 text-red-600' },
        routine: { label: 'Rutinitas', class: 'bg-blue-50 text-blue-600' },
        agenda: { label: 'Agenda', class: 'bg-amber-50 text-amber-600' },
    };
    return badges[topbarSection.value] || badges.focus;
});

const topbarEndpoints = {
    focus: '/focus',
    routine: '/routines',
    agenda: '/agenda',
};

const openQuickModal = (type = null, initialTitle = '') => {
    quickForm.reset();
    quickForm.type = type || getCurrentSection();
    if (initialTitle) {
        quickForm.title = initialTitle;
    }
    isQuickModalOpen.value = true;
};

const handleTopbarSubmit = () => {
    if (!topbarInput.value.trim()) return;
    const section = getCurrentSection();
    const endpoint = topbarEndpoints[section] || '/focus';

    const payload = { title: topbarInput.value.trim() };
    if (section === 'focus') {
        payload.quadrant = 'none';
    }

    router.post(endpoint, payload, {
        preserveScroll: true,
        onSuccess: () => {
            topbarInput.value = '';
        }
    });
};

const submitQuickForm = () => {
    const endpoints = {
        focus: '/focus',
        routine: '/routines',
        agenda: '/agenda',
    };
    quickForm.post(endpoints[quickForm.type], {
        onSuccess: () => {
            isQuickModalOpen.value = false;
            quickForm.reset();
        },
    });
};

const isUrl = (...urls) => {
    return urls.some(url => page.url.startsWith(url));
};

const navItems = [
    { href: '/dashboard', label: 'Dashboard', icon: 'chart' },
    { href: '/focus', label: 'Fokus', icon: 'target' },
    { href: '/routines', label: 'Rutinitas', icon: 'refresh' },
    { href: '/agenda', label: 'Agenda', icon: 'calendar' },
];

const userName = () => {
    const name = page.props.auth?.user?.name || 'User';
    return name.split(' ')[0];
};
</script>

<template>
    <div class="min-h-screen bg-canvas flex text-gray-800">

        <!-- SIDEBAR -->
        <aside class="w-60 bg-surface border-r border-border flex flex-col justify-between shrink-0 fixed h-full z-20">
            <div class="p-5">
                <!-- Logo -->
                <Link href="/focus" class="flex items-center gap-2.5 mb-8 px-2">
                    <div class="w-8 h-8 rounded-btn bg-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900 text-[15px] tracking-tight">Second Brain</span>
                </Link>

                <!-- Navigation -->
                <nav class="space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-2.5 px-3 py-2 rounded-btn text-[15px] font-medium transition-all duration-150',
                            isUrl(item.href)
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'
                        ]"
                    >
                        <!-- Chart -->
                        <svg v-if="item.icon === 'chart'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <!-- Target -->
                        <svg v-if="item.icon === 'target'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <circle cx="12" cy="12" r="6" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>
                        <!-- Refresh -->
                        <svg v-if="item.icon === 'refresh'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <!-- Calendar -->
                        <svg v-if="item.icon === 'calendar'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ item.label }}
                    </Link>
                </nav>
            </div>

            <!-- New Entry Button -->
            <div class="p-5 pt-0">
                <button
                    @click="openQuickModal()"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-btn border border-dashed border-gray-300 text-gray-500 text-[15px] font-medium hover:border-red-400 hover:text-red-600 hover:bg-red-50/50 transition-all duration-150"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Entry
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="flex-1 ml-60 flex flex-col min-h-screen">

            <!-- TOPBAR -->
            <header class="h-14 bg-canvas/80 backdrop-blur-sm px-6 flex items-center justify-between sticky top-0 z-10 border-b border-transparent">
                <div v-if="!isDashboard" class="w-full max-w-lg">
                    <form @submit.prevent="handleTopbarSubmit" class="relative flex items-center">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="topbarInput"
                            type="text"
                            :placeholder="topbarPlaceholder"
                            class="w-full pl-9 pr-20 py-2 rounded-btn border border-gray-200 bg-surface text-[15px] text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 transition-all duration-150"
                        />
                        <span :class="[topbarBadge.class, 'absolute right-3 top-1/2 -translate-y-1/2 text-[12px] font-semibold px-2 py-0.5 rounded-full pointer-events-none']">
                            {{ topbarBadge.label }}
                        </span>
                    </form>
                </div>
                <div v-else></div>

                <div class="flex items-center gap-3">
                    <!-- User -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                            {{ $page.props.auth?.user?.name?.charAt(0) || 'U' }}
                        </div>
                        <span class="text-[15px] font-medium text-gray-600 hidden sm:block">{{ userName() }}</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-6 py-6">
                <slot />
            </main>
        </div>
    </div>

    <!-- QUICK ENTRY MODAL -->
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isQuickModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="isQuickModalOpen = false"></div>

                <!-- Modal -->
                <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-md animate-slide-up">
                    <div class="p-5">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-bold text-gray-900">New Entry</h3>
                            <button @click="isQuickModalOpen = false" class="text-gray-400 hover:text-gray-600 transition p-1 -m-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Type Tabs -->
                        <div class="flex gap-1 p-1 bg-gray-100 rounded-btn mb-5">
                            <button
                                v-for="tab in [
                                    { key: 'focus', label: 'Fokus' },
                                    { key: 'routine', label: 'Rutinitas' },
                                    { key: 'agenda', label: 'Agenda' }
                                ]"
                                :key="tab.key"
                                type="button"
                                @click="quickForm.type = tab.key"
                                :class="[
                                    'flex-1 py-2 text-sm font-semibold rounded-md transition-all duration-150',
                                    quickForm.type === tab.key
                                        ? 'bg-surface text-gray-900 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                {{ tab.label }}
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickForm" class="space-y-4">
                            <!-- Title -->
                            <div>
                                <label class="label">Judul / Kegiatan</label>
                                <input
                                    v-model="quickForm.title"
                                    type="text"
                                    required
                                    placeholder="Contoh: Selesaikan laporan"
                                    class="input"
                                />
                            </div>

                            <!-- Focus Options -->
                            <div v-if="quickForm.type === 'focus'">
                                <label class="label">Kuadran Prioritas</label>
                                <select v-model="quickForm.quadrant" class="input">
                                    <option value="none">Inbox / Belum Diproses</option>
                                    <option value="q1">Do First — Penting & Mendesak</option>
                                    <option value="q2">Do Next — Penting & Tidak Mendesak</option>
                                    <option value="q3">Hand Off — Tidak Penting & Mendesak</option>
                                    <option value="q4">Ignore — Tidak Penting & Tidak Mendesak</option>
                                </select>
                            </div>

                            <!-- Routine Options -->
                            <div v-if="quickForm.type === 'routine'" class="space-y-3">
                                <div>
                                    <label class="label">Frekuensi</label>
                                    <div class="flex gap-4 text-sm text-gray-700">
                                        <label class="flex items-center gap-2">
                                            <input type="radio" v-model="quickForm.repeat_type" value="daily" class="text-blue-600 focus:ring-blue-500" />
                                            Setiap Hari
                                        </label>
                                        <label class="flex items-center gap-2">
                                            <input type="radio" v-model="quickForm.repeat_type" value="specific_days" class="text-blue-600 focus:ring-blue-500" />
                                            Hari Tertentu
                                        </label>
                                    </div>
                                </div>
                                <div v-if="quickForm.repeat_type === 'specific_days'" class="flex gap-1 justify-between">
                                    <label v-for="d in [{ label: 'Sn', val: 1 }, { label: 'Sl', val: 2 }, { label: 'Rb', val: 3 }, { label: 'Km', val: 4 }, { label: 'Jm', val: 5 }, { label: 'Sb', val: 6 }, { label: 'Mn', val: 0 }]" :key="d.val" class="text-sm text-gray-600">
                                        <input type="checkbox" :value="d.val" v-model="quickForm.selected_days" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <span class="ml-1">{{ d.label }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Agenda Options -->
                            <div v-if="quickForm.type === 'agenda'" class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label">Tanggal</label>
                                    <input v-model="quickForm.event_date" type="date" required class="input" />
                                </div>
                                <div>
                                    <label class="label">Waktu</label>
                                    <input v-model="quickForm.event_time" type="time" class="input" />
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" @click="isQuickModalOpen = false" class="btn-ghost btn-sm">
                                    Batal
                                </button>
                                <button type="submit" :disabled="quickForm.processing" class="btn-primary btn-sm">
                                    {{ quickForm.processing ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
