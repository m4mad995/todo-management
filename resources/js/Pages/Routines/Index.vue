<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    routines: { type: Array, default: () => [] }
});

const dayLabels = [
    { label: 'Sen', val: 1 },
    { label: 'Sel', val: 2 },
    { label: 'Rab', val: 3 },
    { label: 'Kam', val: 4 },
    { label: 'Jum', val: 5 },
    { label: 'Sab', val: 6 },
    { label: 'Min', val: 0 },
];

const dayLabelsFull = {
    0: 'Minggu', 1: 'Senin', 2: 'Selasa', 3: 'Rabu',
    4: 'Kamis', 5: 'Jumat', 6: 'Sabtu'
};

// Check if routine is completed today based on last_completed_date
const isCompletedToday = (routine) => {
    if (!routine.last_completed_date) return false;
    const today = new Date().toISOString().split('T')[0];
    // Handle both string and object formats
    const completedDate = typeof routine.last_completed_date === 'string'
        ? routine.last_completed_date.split('T')[0]
        : new Date(routine.last_completed_date).toISOString().split('T')[0];
    return completedDate === today;
};

// Today's day number (0=Sun, 1=Mon, ..., 6=Sat)
const todayDayNum = new Date().getDay();

// Check if routine is active today
const isRoutineActiveToday = (routine) => {
    if (routine.is_everyday) return true;
    if (routine.days_of_week && routine.days_of_week.includes(todayDayNum)) return true;
    return false;
};

// Get routines for today
const todayRoutines = computed(() => {
    return props.routines.filter(r => isRoutineActiveToday(r));
});

const todayCompletedCount = computed(() => {
    return todayRoutines.value.filter(r => isCompletedToday(r)).length;
});

// Get other routines (not active today)
const otherRoutines = computed(() => {
    return props.routines.filter(r => !isRoutineActiveToday(r));
});

const isModalOpen = ref(false);
const editingRoutine = ref(null);
const showDeleteConfirm = ref(false);
const deletingRoutineId = ref(null);

const form = useForm({
    title: '',
    notes: '',
    is_everyday: false,
    days_of_week: [],
});

const toggleEveryday = () => {
    if (form.is_everyday) {
        form.days_of_week = [1, 2, 3, 4, 5, 6, 0];
    } else {
        form.days_of_week = [];
    }
};

const toggleDay = (dayValue) => {
    const idx = form.days_of_week.indexOf(dayValue);
    if (idx > -1) {
        form.days_of_week.splice(idx, 1);
    } else {
        form.days_of_week.push(dayValue);
    }
    form.is_everyday = form.days_of_week.length === 7;
};

const openModal = (routine = null) => {
    if (routine) {
        editingRoutine.value = routine;
        form.title = routine.title;
        form.notes = routine.notes || '';
        form.is_everyday = routine.is_everyday;
        form.days_of_week = routine.days_of_week || [];
    } else {
        editingRoutine.value = null;
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (editingRoutine.value) {
        form.patch(`/routines/${editingRoutine.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/routines', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleCheck = (routine) => {
    router.patch(`/routines/${routine.id}`, {
        is_completed_today: !isCompletedToday(routine),
    }, {
        preserveScroll: true,
        onFinish: () => {
            router.reload({ only: ['routines'] });
        },
    });
};

const confirmDelete = (id) => {
    deletingRoutineId.value = id;
    showDeleteConfirm.value = true;
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
    deletingRoutineId.value = null;
};

const deleteRoutine = () => {
    if (deletingRoutineId.value) {
        router.delete(`/routines/${deletingRoutineId.value}`);
        showDeleteConfirm.value = false;
        deletingRoutineId.value = null;
    }
};

const freqLabel = (routine) => {
    if (routine.is_everyday) return 'Setiap Hari';
    if (routine.days_of_week && routine.days_of_week.length > 0) {
        return routine.days_of_week.map(d => dayLabelsFull[d]).join(', ');
    }
    return 'Sekali saja';
};
</script>

<template>
    <Head title="Rutinitas" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Rutinitas & Kebiasaan</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-0.5 text-[15px]">
                    Jaga konsistensi dengan kebiasaan harianmu.
                </p>
            </div>

            <button @click="openModal()" class="btn-primary btn-md self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Rutinitas Baru
            </button>
        </div>

        <!-- Empty State -->
        <div v-if="routines.length === 0" class="card p-10 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <p class="text-[15px] text-gray-500 dark:text-gray-400 mb-1">Belum ada rutinitas.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500">Klik <strong>Rutinitas Baru</strong> untuk memulai.</p>
        </div>

        <template v-else>
            <!-- Hari Ini Section -->
            <div class="mb-6">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-6 h-6 rounded-md bg-violet-50 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h2 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Hari Ini</h2>
                    <span v-if="todayRoutines.length > 0" class="text-[11px] text-violet-500 font-semibold">
                        {{ todayCompletedCount }}/{{ todayRoutines.length }} selesai
                    </span>
                </div>

                <div v-if="todayRoutines.length > 0" class="space-y-1.5">
                    <div
                        v-for="item in todayRoutines"
                        :key="item.id"
                        :class="[
                            'card p-3 flex items-center gap-3 transition',
                            isCompletedToday(item) ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white dark:bg-slate-800'
                        ]"
                    >
                        <button
                            @click="toggleCheck(item)"
                            :class="[
                                'w-5 h-5 rounded-md border-2 flex items-center justify-center transition shrink-0',
                                isCompletedToday(item)
                                    ? 'bg-emerald-500 border-emerald-500 text-white'
                                    : 'border-gray-300 dark:border-slate-600 text-transparent hover:border-emerald-400'
                            ]"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <div class="min-w-0 flex-1">
                            <p
                                :class="[
                                    'text-[14px] font-medium truncate',
                                    isCompletedToday(item) ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-800 dark:text-gray-200'
                                ]"
                            >
                                {{ item.title }}
                            </p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ freqLabel(item) }}</p>
                        </div>
                        <div class="flex items-center gap-0.5 shrink-0">
                            <button @click="openModal(item)" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 text-xs font-medium px-1.5 py-0.5 transition">
                                Edit
                            </button>
                            <button @click="confirmDelete(item.id)" class="text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 text-xs font-medium px-1.5 py-0.5 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="card p-6 text-center">
                    <p class="text-[13px] text-gray-400 dark:text-gray-500">Tidak ada rutinitas hari ini.</p>
                </div>
            </div>

            <!-- Semua Rutinitas -->
            <div>
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-6 h-6 rounded-md bg-gray-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                    <h2 class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Semua Rutinitas</h2>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold">{{ routines.length }} total</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div
                        v-for="item in routines"
                        :key="item.id"
                        :class="[
                            'card p-4 flex flex-col justify-between transition',
                            isCompletedToday(item) ? 'bg-emerald-50/50 border-emerald-200' : '',
                            !isRoutineActiveToday(item) ? 'opacity-50' : ''
                        ]"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <button
                                        @click="toggleCheck(item)"
                                        :class="[
                                            'w-5 h-5 rounded-md border-2 flex items-center justify-center transition shrink-0',
                                            isCompletedToday(item)
                                                ? 'bg-emerald-500 border-emerald-500 text-white'
                                                : 'border-gray-300 dark:border-slate-600 text-transparent hover:border-emerald-400'
                                        ]"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <h3
                                        :class="[
                                            'font-semibold text-[15px] truncate',
                                            isCompletedToday(item) ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-800 dark:text-gray-200'
                                        ]"
                                    >
                                        {{ item.title }}
                                    </h3>
                                </div>

                                <div class="flex items-center gap-0.5 shrink-0">
                                    <button @click="openModal(item)" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 text-xs font-medium px-1.5 py-0.5 transition">
                                        Edit
                                    </button>
                                    <button @click="confirmDelete(item.id)" class="text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 text-xs font-medium px-1.5 py-0.5 transition">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <p v-if="item.notes" class="text-[13px] text-gray-500 dark:text-gray-400 mt-2 bg-gray-50 dark:bg-slate-800 p-2.5 rounded-btn whitespace-pre-line border border-gray-100 dark:border-slate-700">
                                {{ item.notes }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-slate-700 flex items-center justify-between text-xs">
                            <span class="text-gray-400 dark:text-gray-500 font-medium">Ulangi:</span>
                            <span v-if="item.is_everyday" class="font-semibold text-emerald-600">
                                Setiap Hari
                            </span>
                            <div v-else-if="item.days_of_week && item.days_of_week.length > 0" class="flex gap-0.5">
                                <span
                                    v-for="d in dayLabels"
                                    :key="d.val"
                                    :class="[
                                        'w-5 h-5 rounded-md flex items-center justify-center font-bold text-[10px]',
                                        item.days_of_week.includes(d.val) ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-300 dark:text-gray-600'
                                    ]"
                                >
                                    {{ d.label[0] }}
                                </span>
                            </div>
                            <span v-else class="text-gray-400 dark:text-gray-500">Sekali saja</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="cancelDelete"></div>
                    <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-sm p-5">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Hapus Rutinitas?</h3>
                        <p class="text-[14px] text-gray-500 dark:text-gray-400 mb-5">Rutinitas yang dihapus tidak dapat dikembalikan.</p>
                        <div class="flex justify-end gap-2">
                            <button @click="cancelDelete" class="btn-ghost btn-sm">Batal</button>
                            <button @click="deleteRoutine" class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="closeModal"></div>

                    <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-md animate-slide-up">
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-5">
                                {{ editingRoutine ? 'Edit Rutinitas' : 'Rutinitas Baru' }}
                            </h3>

                            <form @submit.prevent="submitForm" class="space-y-4">
                                <div>
                                    <label class="label">Nama Rutinitas</label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        placeholder="Contoh: Olahraga Pagi, Baca Buku"
                                        required
                                        class="input"
                                    />
                                </div>

                                <div>
                                    <label class="label">Catatan (Opsional)</label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        placeholder="Catatan tambahan..."
                                        class="input resize-none"
                                    ></textarea>
                                </div>

                                <div class="bg-gray-50 dark:bg-slate-800 p-4 rounded-card space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Setiap Hari</span>
                                        <input
                                            type="checkbox"
                                            v-model="form.is_everyday"
                                            @change="toggleEveryday"
                                            class="rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Atur Hari Spesifik:</label>
                                        <div class="flex justify-between gap-1">
                                            <button
                                                v-for="d in dayLabels"
                                                :key="d.val"
                                                type="button"
                                                @click="toggleDay(d.val)"
                                                :class="[
                                                    'flex-1 py-1.5 rounded-md text-xs font-semibold transition-all duration-150',
                                                    form.days_of_week.includes(d.val)
                                                        ? 'bg-blue-600 text-white shadow-sm'
                                                        : 'bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700'
                                                ]"
                                            >
                                                {{ d.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="closeModal" class="btn-ghost btn-sm">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="form.processing" class="btn-primary btn-sm">
                                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
