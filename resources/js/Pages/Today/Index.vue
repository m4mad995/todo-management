<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    dailyTargets: { type: Array, default: () => [] },
    availableTasks: { type: Array, default: () => [] },
});

const showCompleted = ref(false);
const showAddTask = ref(false);

const activeTargets = computed(() => props.dailyTargets.filter(t => !t.is_completed));
const completedTargets = computed(() => props.dailyTargets.filter(t => t.is_completed));

const totalCount = computed(() => props.dailyTargets.length);
const completedCount = computed(() => completedTargets.value.length);
const progressPercent = computed(() => {
    if (totalCount.value === 0) return 0;
    return Math.round((completedCount.value / totalCount.value) * 100);
});

const doFirstTargets = computed(() =>
    activeTargets.value.filter(t => t.targetable?.matrix === 'do_first')
);
const otherTaskTargets = computed(() =>
    activeTargets.value.filter(t =>
        t.targetable_type?.includes('Task') &&
        !t.targetable_type?.includes('SubTask') &&
        t.targetable?.matrix !== 'do_first'
    )
);
const subTaskTargets = computed(() =>
    activeTargets.value.filter(t => t.targetable_type?.includes('SubTask'))
);
const routineTargets = computed(() =>
    activeTargets.value.filter(t => t.targetable_type?.includes('Routine'))
);

// Sub-tasks grouped by parent task ID
const subTasksByParent = computed(() => {
    const map = {};
    subTaskTargets.value.forEach(t => {
        const parentId = t.targetable?.task_id;
        if (parentId) {
            if (!map[parentId]) map[parentId] = [];
            map[parentId].push(t);
        }
    });
    return map;
});

// Orphan sub-tasks (parent task not in Hari Ini)
const orphanSubTasks = computed(() =>
    subTaskTargets.value.filter(t =>
        !t.targetable?.task_id ||
        !activeTargets.value.some(p =>
            p.targetable_type?.includes('Task') &&
            !p.targetable_type?.includes('SubTask') &&
            p.targetable_id === t.targetable.task_id
        )
    )
);

const todayDate = computed(() => {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
});

const toggleComplete = (targetId) => {
    router.patch(`/daily-targets/${targetId}`, {}, { preserveScroll: true });
};

const removeFromToday = (targetId) => {
    router.delete(`/daily-targets/${targetId}`, { preserveScroll: true });
};

const addToToday = (type, id) => {
    router.post('/daily-targets', {
        targetable_type: type,
        targetable_id: id,
    }, { preserveScroll: true });
};

const matrixConfig = {
    do_first: { label: 'Do First', badge: 'badge-red' },
    schedule: { label: 'Do Next', badge: 'badge-blue' },
    delegate: { label: 'Hand Off', badge: 'badge-amber' },
    drop: { label: 'Ignore', badge: 'badge-gray' },
};

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat pagi';
    if (hour < 15) return 'Selamat siang';
    if (hour < 18) return 'Selamat sore';
    return 'Selamat malam';
});
</script>

<template>
    <Head title="Hari Ini" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Hari Ini</h1>
            <p class="text-gray-500 mt-0.5 text-[15px]">{{ greeting }} — {{ todayDate }}</p>
        </div>

        <!-- Progress Card -->
        <div v-if="totalCount > 0" class="card p-4 mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[13px] font-semibold text-gray-700">
                    {{ completedCount }}/{{ totalCount }} selesai
                </span>
                <span class="text-[13px] font-bold text-emerald-600">{{ progressPercent }}%</span>
            </div>
            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                    :style="{ width: progressPercent + '%' }"
                ></div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="totalCount === 0" class="card p-8 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <p class="text-[15px] font-semibold text-gray-600 mb-1">Belum ada target hari ini</p>
            <p class="text-[13px] text-gray-400 mb-4">Tambah task atau rutinitas yang ingin kamu kerjakan hari ini.</p>
            <div class="flex items-center justify-center gap-2">
                <button @click="showAddTask = true" class="btn-primary btn-sm">
                    + Task
                </button>
            </div>
        </div>

        <!-- Sections -->
        <div v-else class="space-y-4">
            <!-- Do First -->
            <div v-if="doFirstTargets.length > 0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="text-[13px] font-bold text-gray-700 uppercase tracking-wider">Do First</span>
                    <span class="badge-red text-[11px]">{{ doFirstTargets.length }}</span>
                </div>
                <div class="space-y-1">
                    <div
                        v-for="target in doFirstTargets"
                        :key="target.id"
                        class="card p-3 group"
                    >
                        <div class="flex items-center gap-3">
                            <button
                                @click="toggleComplete(target.id)"
                                class="w-5 h-5 rounded border-2 border-red-300 flex items-center justify-center shrink-0 hover:border-red-500 transition"
                            >
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-[14px] text-gray-800 truncate">{{ target.targetable?.title }}</p>
                            </div>
                            <span :class="[matrixConfig[target.targetable?.matrix]?.badge || 'badge-gray', 'text-[10px] shrink-0']">
                                {{ matrixConfig[target.targetable?.matrix]?.label }}
                            </span>
                            <Link href="/focus" class="text-[11px] font-semibold text-gray-400 hover:text-blue-500 transition shrink-0">
                                Fokus →
                            </Link>
                            <button
                                @click="removeFromToday(target.id)"
                                class="w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <!-- Nested sub-tasks -->
                        <div v-if="subTasksByParent[target.targetable_id] && subTasksByParent[target.targetable_id].length > 0" class="mt-2 ml-8 space-y-0.5">
                            <div
                                v-for="sub in subTasksByParent[target.targetable_id]"
                                :key="sub.id"
                                class="flex items-center gap-2.5 py-1"
                            >
                                <button
                                    @click="toggleComplete(sub.id)"
                                    class="w-4 h-4 rounded border-[1.5px] border-red-200 flex items-center justify-center shrink-0 hover:border-red-400 transition"
                                >
                                </button>
                                <p class="text-[13px] text-gray-600 truncate flex-1">{{ sub.targetable?.title }}</p>
                                <button
                                    @click="removeFromToday(sub.id)"
                                    class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0"
                                >
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Lainnya -->
            <div v-if="otherTaskTargets.length > 0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="text-[13px] font-bold text-gray-700 uppercase tracking-wider">Task Lainnya</span>
                    <span class="badge-blue text-[11px]">{{ otherTaskTargets.length }}</span>
                </div>
                <div class="space-y-1">
                    <div
                        v-for="target in otherTaskTargets"
                        :key="target.id"
                        class="card p-3 group"
                    >
                        <div class="flex items-center gap-3">
                            <button
                                @click="toggleComplete(target.id)"
                                class="w-5 h-5 rounded border-2 border-blue-300 flex items-center justify-center shrink-0 hover:border-blue-500 transition"
                            >
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-[14px] text-gray-800 truncate">{{ target.targetable?.title }}</p>
                            </div>
                            <span :class="[matrixConfig[target.targetable?.matrix]?.badge || 'badge-gray', 'text-[10px] shrink-0']">
                                {{ matrixConfig[target.targetable?.matrix]?.label }}
                            </span>
                            <Link href="/focus" class="text-[11px] font-semibold text-gray-400 hover:text-blue-500 transition shrink-0">
                                Fokus →
                            </Link>
                            <button
                                @click="removeFromToday(target.id)"
                                class="w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <!-- Nested sub-tasks -->
                        <div v-if="subTasksByParent[target.targetable_id] && subTasksByParent[target.targetable_id].length > 0" class="mt-2 ml-8 space-y-0.5">
                            <div
                                v-for="sub in subTasksByParent[target.targetable_id]"
                                :key="sub.id"
                                class="flex items-center gap-2.5 py-1"
                            >
                                <button
                                    @click="toggleComplete(sub.id)"
                                    class="w-4 h-4 rounded border-[1.5px] border-blue-200 flex items-center justify-center shrink-0 hover:border-blue-400 transition"
                                >
                                </button>
                                <p class="text-[13px] text-gray-600 truncate flex-1">{{ sub.targetable?.title }}</p>
                                <button
                                    @click="removeFromToday(sub.id)"
                                    class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0"
                                >
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub-task Pilihan (added individually, not nested under parent) -->
            <div v-if="orphanSubTasks.length > 0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <span class="text-[13px] font-bold text-gray-700 uppercase tracking-wider">Sub-task Pilihan</span>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-purple-50 text-purple-600">{{ orphanSubTasks.length }}</span>
                </div>
                <div class="space-y-1">
                    <div
                        v-for="target in orphanSubTasks"
                        :key="target.id"
                        class="card p-3 flex items-center gap-3 group"
                    >
                        <button
                            @click="toggleComplete(target.id)"
                            class="w-5 h-5 rounded border-2 border-purple-300 flex items-center justify-center shrink-0 hover:border-purple-500 transition"
                        >
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="text-[14px] text-gray-800 truncate">{{ target.targetable?.title }}</p>
                            <p v-if="target.targetable?.task" class="text-[11px] text-gray-400 truncate mt-0.5">
                                {{ target.targetable.task.title }}
                            </p>
                        </div>
                        <Link href="/focus" class="text-[11px] font-semibold text-gray-400 hover:text-blue-500 transition shrink-0">
                            Fokus →
                        </Link>
                        <button
                            @click="removeFromToday(target.id)"
                            class="w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rutinitas -->
            <div v-if="routineTargets.length > 0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-[13px] font-bold text-gray-700 uppercase tracking-wider">Rutinitas</span>
                    <span class="badge-emerald text-[11px]">{{ routineTargets.length }}</span>
                </div>
                <div class="space-y-1">
                    <div
                        v-for="target in routineTargets"
                        :key="target.id"
                        class="card p-3 flex items-center gap-3 group"
                    >
                        <button
                            @click="toggleComplete(target.id)"
                            class="w-5 h-5 rounded border-2 border-emerald-300 flex items-center justify-center shrink-0 hover:border-emerald-500 transition"
                        >
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="text-[14px] text-gray-800 truncate">{{ target.targetable?.title }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span v-if="target.targetable?.is_everyday" class="text-[11px] font-semibold text-emerald-600">Setiap Hari</span>
                                <div v-else-if="target.targetable?.days_of_week && target.targetable.days_of_week.length > 0" class="flex gap-0.5">
                                    <span
                                        v-for="d in [{l:'S',v:1},{l:'S',v:2},{l:'R',v:3},{l:'K',v:4},{l:'J',v:5},{l:'S',v:6},{l:'M',v:0}]"
                                        :key="d.v"
                                        :class="[
                                            'w-4 h-4 rounded flex items-center justify-center font-bold text-[8px]',
                                            target.targetable.days_of_week.includes(d.v) ? 'bg-emerald-100 text-emerald-700' : 'text-gray-300'
                                        ]"
                                    >{{ d.l }}</span>
                                </div>
                            </div>
                        </div>
                        <Link href="/routines" class="text-[11px] font-semibold text-gray-400 hover:text-blue-500 transition shrink-0">
                            Rutinitas →
                        </Link>
                        <button
                            @click="removeFromToday(target.id)"
                            class="w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Completed (Collapsible) -->
            <div v-if="completedTargets.length > 0">
                <button
                    @click="showCompleted = !showCompleted"
                    class="flex items-center gap-2 text-[13px] font-medium text-emerald-600 hover:text-emerald-700 transition mb-2"
                >
                    <svg
                        :class="['w-3.5 h-3.5 transition-transform', showCompleted ? 'rotate-90' : '']"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    {{ completedTargets.length }} selesai hari ini
                </button>
                <div v-if="showCompleted" class="space-y-1 animate-fade-in">
                    <div
                        v-for="target in completedTargets"
                        :key="target.id"
                        class="card p-3 flex items-center gap-3 bg-emerald-50/50 group"
                    >
                        <button
                            @click="toggleComplete(target.id)"
                            class="w-5 h-5 rounded bg-emerald-400 border-2 border-emerald-400 flex items-center justify-center shrink-0"
                        >
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <span class="text-[14px] text-gray-500 line-through truncate flex-1">{{ target.targetable?.title }}</span>
                        <button
                            @click="removeFromToday(target.id)"
                            class="w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Target Buttons -->
        <div v-if="totalCount > 0" class="mt-6 flex items-center gap-2">
            <button @click="showAddTask = true" class="btn-outline btn-sm">
                + Task
            </button>
        </div>

        <!-- Add Task Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddTask" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="showAddTask = false"></div>
                    <div class="relative bg-surface rounded-card shadow-elevated border border-border w-full max-w-md max-h-[70vh] flex flex-col animate-slide-up">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[15px] font-bold text-gray-900">Tambah Task ke Hari Ini</h3>
                                <button @click="showAddTask = false" class="text-gray-400 hover:text-gray-600 transition p-1 -m-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="overflow-y-auto flex-1 p-4">
                            <div v-if="availableTasks.length === 0" class="text-center py-6">
                                <p class="text-[13px] text-gray-400">Semua task aktif sudah ada di hari ini.</p>
                            </div>
                            <div v-else class="space-y-1">
                                <div v-for="task in availableTasks" :key="task.id">
                                    <button
                                        @click="addToToday('Task', task.id); showAddTask = false"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-btn hover:bg-blue-50/50 transition text-left"
                                    >
                                        <span :class="[matrixConfig[task.matrix]?.badge || 'badge-gray', 'text-[10px] shrink-0']">
                                            {{ matrixConfig[task.matrix]?.label || 'Inbox' }}
                                        </span>
                                        <span class="text-[14px] text-gray-800 truncate flex-1">{{ task.title }}</span>
                                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    <!-- Sub-tasks -->
                                    <div v-if="task.sub_tasks && task.sub_tasks.length > 0" class="pl-6 space-y-0.5">
                                        <button
                                            v-for="sub in task.sub_tasks"
                                            :key="sub.id"
                                            @click="addToToday('SubTask', sub.id); showAddTask = false"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 rounded-btn hover:bg-purple-50/50 transition text-left"
                                        >
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-purple-50 text-purple-600 shrink-0">Sub</span>
                                            <span class="text-[13px] text-gray-600 truncate flex-1">{{ sub.title }}</span>
                                            <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AuthenticatedLayout>
</template>
