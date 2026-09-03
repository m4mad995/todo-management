<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Doughnut, Bar, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Filler,
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, LineElement, PointElement, Filler);

const props = defineProps({
    stats: Object,
    urgentTasks: { type: Array, default: () => [] },
    todayRoutines: { type: Array, default: () => [] },
    completedRoutinesToday: { type: Array, default: () => [] },
    upcomingAgendas: { type: Array, default: () => [] },
    completedTasksToday: { type: Array, default: () => [] },
    weeklyHistory: { type: Array, default: () => [] },
    isFirstTime: { type: Boolean, default: false },
});

const page = usePage();
const userName = computed(() => {
    const name = page.props.auth?.user?.name || 'User';
    return name.split(' ')[0];
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat pagi';
    if (hour < 18) return 'Selamat siang';
    return 'Selamat malam';
});

// Dark mode support for charts
const isDark = ref(false);
const checkDark = () => { isDark.value = document.documentElement.classList.contains('dark'); };
onMounted(() => { checkDark(); window.addEventListener('classChange', checkDark); });
onUnmounted(() => { window.removeEventListener('classChange', checkDark); });

const chartTooltip = computed(() => ({
    backgroundColor: isDark.value ? '#334155' : '#1F2937',
    titleFont: { size: 13, weight: '600' },
    bodyFont: { size: 12 },
    padding: 10,
    cornerRadius: 8,
    displayColors: true,
    boxPadding: 4,
}));

const chartGridColor = computed(() => isDark.value ? '#334155' : '#F3F4F6');
const chartTickColor = computed(() => isDark.value ? '#94A3B8' : '#9CA3AF');

const dayLabels = [
    { label: 'Sen', val: 1 },
    { label: 'Sel', val: 2 },
    { label: 'Rab', val: 3 },
    { label: 'Kam', val: 4 },
    { label: 'Jum', val: 5 },
    { label: 'Sab', val: 6 },
    { label: 'Min', val: 0 },
];

const routineFreqLabel = (routine) => {
    if (routine.is_everyday) return 'Setiap Hari';
    if (routine.days_of_week && routine.days_of_week.length > 0) {
        const dayNames = { 0: 'Min', 1: 'Sen', 2: 'Sel', 3: 'Rab', 4: 'Kam', 5: 'Jum', 6: 'Sab' };
        return routine.days_of_week.map(d => dayNames[d]).join(', ');
    }
    return 'Sekali';
};

// === Doughnut: Matrix ===
const matrixColors = {
    do_first: '#EF4444',
    schedule: '#3B82F6',
    delegate: '#F59E0B',
    drop: '#9CA3AF',
};

const doughnutData = computed(() => {
    const m = props.stats.completedByMatrix;
    return {
        labels: ['Do First', 'Do Next', 'Hand Off', 'Ignore'],
        datasets: [{
            data: [m.do_first, m.schedule, m.delegate, m.drop],
            backgroundColor: [matrixColors.do_first, matrixColors.schedule, matrixColors.delegate, matrixColors.drop],
            borderWidth: 0,
            hoverOffset: 4,
        }],
    };
});

const doughnutOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: { display: false },
        tooltip: chartTooltip.value,
    },
}));

const matrixLabel = { do_first: 'Do First', schedule: 'Do Next', delegate: 'Hand Off', drop: 'Ignore' };

// Matrix badge class
const toggleRoutine = (routineId) => {
    router.patch(`/routines/${routineId}`, { is_completed_today: true }, { preserveScroll: true });
};

// Matrix badge class
const matrixBadgeClass = {
    do_first: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    schedule: 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    delegate: 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    drop: 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-400',
};

// === Bar: 6 Bulan ===
const barMonthData = computed(() => {
    const data = props.stats.completedByMonth;
    if (data.length === 0) {
        return { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'], datasets: [{ data: [0, 0, 0, 0, 0, 0], backgroundColor: '#3B82F6', borderRadius: 6, barThickness: 18 }] };
    }
    return { labels: data.map(d => d.month), datasets: [{ data: data.map(d => d.count), backgroundColor: '#3B82F6', borderRadius: 6, barThickness: 18 }] };
});

const barOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: chartTooltip.value },
    scales: {
        x: { grid: { display: false }, border: { display: false }, ticks: { color: chartTickColor.value, font: { size: 11 } } },
        y: { beginAtZero: true, grid: { color: chartGridColor.value }, border: { display: false }, ticks: { color: chartTickColor.value, font: { size: 11 }, stepSize: 1 } },
    },
}));
const matrixName = {
    do_first: 'Do First',
    schedule: 'Do Next',
    delegate: 'Hand Off',
    drop: 'Ignore',
};

// === Chart: Tren 7 Hari (Line/Area) ===
const trendData = computed(() => {
    const data = props.weeklyHistory;
    if (!data || data.length === 0) {
        return {
            labels: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            datasets: [{
                data: [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }],
        };
    }
    const todayStr = new Date().toISOString().split('T')[0];
    return {
        labels: data.map(d => {
            const dayNames = { Sun: 'Min', Mon: 'Sen', Tue: 'Sel', Wed: 'Rab', Thu: 'Kam', Fri: 'Jum', Sat: 'Sab' };
            return dayNames[d.day_name] || d.day_name;
        }),
        datasets: [{
            data: data.map(d => d.total_completed),
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: data.map(d => d.date === todayStr ? '#8B5CF6' : '#3B82F6'),
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
        }],
    };
});

const trendOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            ...chartTooltip.value,
            callbacks: {
                afterLabel: function(context) {
                    const day = props.weeklyHistory[context.dataIndex];
                    if (!day) return '';
                    return `${day.tasks_completed} task + ${day.routines_completed} routine`;
                },
            },
        },
    },
    scales: {
        x: { grid: { display: false }, border: { display: false }, ticks: { color: chartTickColor.value, font: { size: 11 } } },
        y: { beginAtZero: true, grid: { color: chartGridColor.value }, border: { display: false }, ticks: { color: chartTickColor.value, font: { size: 11 }, stepSize: 1 } },
    },
}));

// === Chart: Task Selesai vs Belum per Matrix (Stacked Horizontal Bar) ===
const matrixBarData = computed(() => {
    const data = props.stats.matrixStatus;
    if (!data || data.length === 0) {
        return { labels: ['Do First', 'Do Next', 'Hand Off'], datasets: [
            { label: 'Selesai', data: [0, 0, 0], backgroundColor: '#10B981', borderRadius: 4, barThickness: 20 },
            { label: 'Belum', data: [0, 0, 0], backgroundColor: '#E5E7EB', borderRadius: 4, barThickness: 20 },
        ] };
    }
    return {
        labels: data.map(d => d.label),
        datasets: [
            { label: 'Selesai', data: data.map(d => d.completed), backgroundColor: '#10B981', borderRadius: 4, barThickness: 20 },
            { label: 'Belum', data: data.map(d => d.pending), backgroundColor: '#E5E7EB', borderRadius: 4, barThickness: 20 },
        ],
    };
});

const matrixBarOptions = computed(() => ({
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
            labels: { usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11 }, color: chartTickColor.value },
        },
        tooltip: {
            ...chartTooltip.value,
            callbacks: {
                label: function(context) {
                    const item = props.stats.matrixStatus[context.dataIndex];
                    if (!item) return context.dataset.label + ': ' + context.raw;
                    const pct = item.total > 0 ? Math.round((context.raw / item.total) * 100) : 0;
                    return context.dataset.label + ': ' + context.raw + ' (' + pct + '%)';
                },
            },
        },
    },
    scales: {
        x: { stacked: true, beginAtZero: true, grid: { color: chartGridColor.value }, border: { display: false }, ticks: { color: chartTickColor.value, font: { size: 11 }, stepSize: 1 } },
        y: { stacked: true, grid: { display: false }, border: { display: false }, ticks: { color: chartTickColor.value, font: { size: 12, weight: '600' } } },
    },
}));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- Greeting -->
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                {{ greeting }}, {{ userName }}.
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-0.5 text-[15px]">
                Ini ringkasan produktivitas kamu.
            </p>
        </div>

        <!-- Onboarding: Welcome Card (first-time user) -->
        <div v-if="isFirstTime" class="card p-6 mb-4 text-center">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Selamat datang!</h2>
            <p class="text-[14px] text-gray-500 dark:text-gray-400 mb-4">Mulai kelola produktivitasmu dengan langkah pertama ini.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                <Link href="/focus" class="btn-primary btn-sm">
                    + Tambah Task
                </Link>
                <Link href="/routines" class="btn-primary btn-sm !bg-blue-50 dark:bg-blue-900/200 hover:!bg-blue-600">
                    + Buat Rutinitas
                </Link>
                <Link href="/agenda" class="btn-primary btn-sm !bg-amber-50 dark:bg-amber-900/200 hover:!bg-amber-600">
                    + Tambah Agenda
                </Link>
            </div>
        </div>

        <!-- Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">

            <!-- Stats Row -->
            <div class="md:col-span-2 lg:col-span-3 grid grid-cols-3 gap-2">
                <!-- Stats: Selesai Hari Ini -->
                <div class="rounded-btn border border-emerald-200 bg-emerald-50/50 dark:bg-emerald-900/20 p-2 sm:p-2.5 flex flex-col items-center text-center gap-0.5">
                    <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Selesai</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight">{{ stats.completedToday }}</p>
                    <p class="text-[9px] sm:text-[10px] text-emerald-600/60 truncate max-w-full">{{ stats.totalToday }} total</p>
                </div>

                <!-- Stats: Rate -->
                <div class="rounded-btn border border-indigo-200 bg-indigo-50/50 dark:bg-indigo-900/20 p-2 sm:p-2.5 flex flex-col items-center text-center gap-0.5">
                    <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Rate</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight">{{ stats.completionRate }}%</p>
                    <p class="text-[9px] sm:text-[10px] text-indigo-600/60 truncate max-w-full">semua task</p>
                </div>

                <!-- Stats: Streak -->
                <div class="rounded-btn border border-amber-200 bg-amber-50 dark:bg-amber-900/20/50 dark:bg-amber-900/20 p-2 sm:p-2.5 flex flex-col items-center text-center gap-0.5">
                    <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Streak</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight">{{ stats.currentStreak }} <span class="text-[9px] sm:text-[10px] font-medium text-amber-700 dark:text-amber-400/60">hari</span></p>
                    <p class="text-[9px] sm:text-[10px] text-amber-600/60 truncate max-w-full">task + rutin</p>
                </div>
            </div>

            <!-- Perlu Dikerjakan (full width) -->
            <div class="card border-l-[3px] border-l-red-500 p-4 md:col-span-2 lg:col-span-3">
                <div class="flex items-center gap-2.5 mb-2.5">
                    <div class="w-6 h-6 rounded-md bg-red-50 dark:bg-red-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Perlu Dikerjakan</h3>
                    <span class="badge-red text-[11px] ml-auto">{{ urgentTasks.length }} Do First</span>
                </div>
                <div v-if="urgentTasks.length > 0" class="space-y-1">
                    <Link
                        v-for="task in urgentTasks"
                        :key="task.id"
                        href="/focus"
                        class="flex items-center justify-between px-3 py-2 rounded-btn hover:bg-red-50 dark:hover:bg-red-900/20 transition group"
                    >
                        <span class="text-[14px] text-gray-800 dark:text-gray-200 truncate">{{ task.title }}</span>
                        <span class="shrink-0 text-[12px] font-semibold text-red-400 group-hover:text-red-500 transition">
                            Fokus →
                        </span>
                    </Link>
                </div>
                <div v-else class="text-center py-4">
                    <p class="text-[13px] text-gray-400 dark:text-gray-500">Tidak ada task mendesak. Bagus!</p>
                </div>
            </div>

            <!-- Rutinitas Hari Ini -->
            <div class="card border-l-[3px] border-l-blue-500 p-4">
                <div class="flex items-center gap-2.5 mb-2.5">
                    <div class="w-6 h-6 rounded-md bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Rutinitas Hari Ini</h3>
                    <span class="text-[11px] text-blue-500 font-semibold ml-auto">{{ todayRoutines.length }} aktif</span>
                </div>
                <div v-if="todayRoutines.length > 0" class="space-y-1">
                    <div
                        v-for="routine in todayRoutines"
                        :key="routine.id"
                        class="flex items-center gap-2.5 px-2.5 py-2 rounded-btn hover:bg-blue-50 dark:hover:bg-blue-900/20 transition group"
                    >
                        <button
                            @click="toggleRoutine(routine.id)"
                            class="w-5 h-5 rounded-md border-2 border-gray-300 flex items-center justify-center shrink-0 hover:border-blue-400 transition text-transparent hover:text-blue-400"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] text-gray-800 dark:text-gray-200 truncate">{{ routine.title }}</p>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span v-if="routine.is_everyday" class="text-[11px] font-semibold text-emerald-600">Setiap Hari</span>
                                <div v-else-if="routine.days_of_week && routine.days_of_week.length > 0" class="flex gap-0.5">
                                    <span
                                        v-for="d in dayLabels"
                                        :key="d.val"
                                        :class="[
                                            'w-5 h-5 rounded-md flex items-center justify-center font-bold text-[10px]',
                                            routine.days_of_week.includes(d.val) ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 dark:text-blue-400' : 'text-gray-300 dark:text-gray-600'
                                        ]"
                                    >
                                        {{ d.label[0] }}
                                    </span>
                                </div>
                                <span v-else class="text-[11px] text-gray-400 dark:text-gray-500">Sekali</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-4">
                    <p class="text-[13px] text-gray-400 dark:text-gray-500">Tidak ada rutinitas hari ini.</p>
                </div>
                <Link href="/routines" class="block text-center mt-2 text-[12px] font-medium text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 dark:text-gray-400 transition">
                    Lihat di Rutinitas →
                </Link>
            </div>

            <!-- Agenda Mendatang -->
            <div class="card border-l-[3px] border-l-amber-500 p-4 lg:col-span-2">
                <Link href="/agenda" class="flex items-center gap-2.5 mb-2.5 group">
                    <div class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200 group-hover:text-amber-600 transition">Agenda Mendatang</h3>
                </Link>
                <div v-if="upcomingAgendas.length > 0" class="space-y-1">
                    <div
                        v-for="agenda in upcomingAgendas"
                        :key="agenda.id"
                        class="flex items-center gap-3 px-3 py-2 rounded-btn hover:bg-amber-50 dark:hover:bg-amber-900/20 transition"
                    >
                        <span
                            :class="[
                                'text-[11px] font-semibold px-2 py-0.5 rounded-full shrink-0',
                                agenda.is_today ? 'bg-red-50 dark:bg-red-900/20 text-red-600' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-600'
                            ]"
                        >
                            {{ agenda.date_label }}
                        </span>
                        <span v-if="agenda.event_time" class="text-[12px] text-amber-600 font-semibold shrink-0">
                            {{ agenda.event_time.substring(0, 5) }}
                        </span>
                        <span class="text-[13px] text-gray-800 dark:text-gray-200 truncate">{{ agenda.title }}</span>
                    </div>
                </div>
                <div v-else class="text-center py-4">
                    <p class="text-[13px] text-gray-400 dark:text-gray-500">Tidak ada agenda mendatang.</p>
                </div>
                <Link href="/agenda" class="flex items-center justify-center gap-1.5 pt-3 border-t border-gray-100 dark:border-slate-700 mt-3 text-[13px] text-amber-600 hover:text-amber-700 dark:text-amber-400 font-medium transition">
                    Lihat Semua Agenda →
                </Link>
            </div>

            <!-- Chart: Tren 7 Hari (Line/Area) -->
            <div class="card border-l-[3px] border-l-blue-500 p-4 md:col-span-2 lg:col-span-2">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-6 h-6 rounded-md bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Tren 7 Hari</h3>
                </div>
                <div class="h-44">
                    <Line :data="trendData" :options="trendOptions" />
                </div>
            </div>

            <!-- Chart: Selesai vs Belum per Matrix (Stacked Horizontal Bar) -->
            <div class="card border-l-[3px] border-l-emerald-500 p-4 md:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-6 h-6 rounded-md bg-emerald-50 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Selesai vs Belum</h3>
                </div>
                <div class="h-36">
                    <Bar :data="matrixBarData" :options="matrixBarOptions" />
                </div>
            </div>

            <!-- Donut Chart: Kuadran -->
            <div class="card border-l-[3px] border-l-gray-300 p-4">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-6 h-6 rounded-md bg-gray-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Berdasarkan Kuadran</h3>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-5">
                    <div class="w-32 h-32 sm:w-40 sm:h-40 shrink-0 relative">
                        <Doughnut :data="doughnutData" :options="doughnutOptions" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalCompleted }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div
                            v-for="(count, key) in stats.completedByMatrix"
                            :key="key"
                            class="flex items-center gap-2.5 text-[13px]"
                        >
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: matrixColors[key] }"></span>
                            <span class="text-gray-600 dark:text-gray-400 w-16">{{ matrixLabel[key] }}</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart: 6 Bulan -->
            <div class="card border-l-[3px] border-l-gray-300 p-4 lg:col-span-2">
                <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200 mb-2.5">6 Bulan Terakhir</h3>
                <div class="h-36">
                    <Bar :data="barMonthData" :options="barOptions" />
                </div>
            </div>

            <!-- Recent Completed (full width) -->
            <div class="card border-l-[3px] border-l-emerald-500 p-4 md:col-span-2 lg:col-span-3">
                <div class="flex items-center gap-2.5 mb-2.5">
                    <div class="w-6 h-6 rounded-md bg-emerald-50 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Task Terakhir Selesai</h3>
                </div>
                <div v-if="stats.recentCompleted.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5">
                    <div
                        v-for="task in stats.recentCompleted"
                        :key="task.id"
                        class="flex items-center gap-2.5 px-2.5 py-2 rounded-btn hover:bg-emerald-50/40 dark:hover:bg-emerald-900/20 transition"
                    >
                        <div class="w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-[13px] text-gray-700 dark:text-gray-300 truncate flex-1">{{ task.title }}</span>
                        <span :class="['text-[10px] font-semibold px-1.5 py-0.5 rounded-full shrink-0', matrixBadgeClass[task.matrix] || 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-gray-400']">
                            {{ matrixName[task.matrix] || task.matrix }}
                        </span>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500 shrink-0">{{ task.completed_diff }}</span>
                    </div>
                </div>
                <div v-else class="text-center py-4">
                    <p class="text-[13px] text-gray-400 dark:text-gray-500 italic">Belum ada task yang selesai.</p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
