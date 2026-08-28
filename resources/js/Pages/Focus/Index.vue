<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch, onBeforeUnmount, nextTick } from 'vue';

const props = defineProps({
    unprocessedTasks: { type: Array, default: () => [] },
    doFirst: { type: Array, default: () => [] },
    schedule: { type: Array, default: () => [] },
    delegate: { type: Array, default: () => [] },
    drop: { type: Array, default: () => [] },
    unprocessedCount: { type: Number, default: 0 },
    activeTargets: { type: Array, default: () => [] },
    completedTargets: { type: Array, default: () => [] },
});

const page = usePage();

// Carousel state
const currentSlide = ref(0);
const isAutoPlaying = ref(true);
let autoPlayInterval = null;

const nextSlide = () => {
    if (props.doFirst.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % props.doFirst.length;
    }
};

const prevSlide = () => {
    if (props.doFirst.length > 0) {
        currentSlide.value = (currentSlide.value - 1 + props.doFirst.length) % props.doFirst.length;
    }
};

const goToSlide = (index) => {
    currentSlide.value = index;
    restartAutoPlay();
};

const startAutoPlay = () => {
    stopAutoPlay();
    if (isAutoPlaying.value && props.doFirst.length > 1) {
        autoPlayInterval = setInterval(() => {
            currentSlide.value = (currentSlide.value + 1) % props.doFirst.length;
        }, 2500);
    }
};

const stopAutoPlay = () => {
    if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
        autoPlayInterval = null;
    }
};

const restartAutoPlay = () => {
    stopAutoPlay();
    startAutoPlay();
};

watch(() => props.doFirst.length, (newLength) => {
    if (currentSlide.value >= newLength) {
        currentSlide.value = Math.max(0, newLength - 1);
    }
    startAutoPlay();
}, { immediate: true });

onBeforeUnmount(() => {
    stopAutoPlay();
});

const currentTask = computed(() => {
    if (props.doFirst.length > 0) {
        return props.doFirst[currentSlide.value] || props.doFirst[0];
    }
    return null;
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat pagi';
    if (hour < 18) return 'Selamat siang';
    return 'Selamat malam';
});

const userName = computed(() => {
    const name = page.props.auth?.user?.name || 'User';
    return name.split(' ')[0];
});

// Inline add form per matrix
const showAddFor = ref(null);
const addTitle = ref('');
const addInput = ref(null);

const toggleAddForm = (matrix) => {
    if (showAddFor.value === matrix) {
        showAddFor.value = null;
        addTitle.value = '';
    } else {
        showAddFor.value = matrix;
        addTitle.value = '';
        nextTick(() => {
            const el = Array.isArray(addInput.value)
                ? addInput.value.find(Boolean)
                : addInput.value;
            el?.focus();
        });
    }
};

const submitDirectAdd = (matrixName) => {
    if (!addTitle.value.trim()) return;
    router.post('/tasks', { title: addTitle.value.trim(), matrix: matrixName });
    addTitle.value = '';
    showAddFor.value = null;
};

const assignMatrix = (taskId, matrixName) => {
    router.patch(`/tasks/${taskId}`, { matrix: matrixName });
};

const completeTask = (taskId) => {
    router.patch(`/tasks/${taskId}`, { completed: 1 });
};

const deleteTask = (taskId) => {
    router.delete(`/tasks/${taskId}`);
};

// Sub-task state
const expandedTask = ref(null);
const newSubTaskTitle = ref('');
const showSubTaskInput = ref(null);
const subTaskInput = ref(null);
const editingSubTask = ref(null);
const editSubTaskTitle = ref('');

const toggleExpand = (taskId) => {
    if (expandedTask.value === taskId) {
        expandedTask.value = null;
    } else {
        expandedTask.value = taskId;
    }
};

const toggleSubTaskInput = (itemId) => {
    if (showSubTaskInput.value === itemId) {
        showSubTaskInput.value = null;
        newSubTaskTitle.value = '';
    } else {
        showSubTaskInput.value = itemId;
        newSubTaskTitle.value = '';
        nextTick(() => {
            const el = Array.isArray(subTaskInput.value)
                ? subTaskInput.value.find(Boolean)
                : subTaskInput.value;
            el?.focus();
        });
    }
};

const addSubTask = (taskId) => {
    if (!newSubTaskTitle.value.trim()) return;
    router.post(`/tasks/${taskId}/subtasks`, { title: newSubTaskTitle.value.trim() });
    newSubTaskTitle.value = '';
    showSubTaskInput.value = null;
    expandedTask.value = taskId;
};

const toggleSubTask = (subTaskId, currentStatus) => {
    router.patch(`/subtasks/${subTaskId}`, { is_completed: !currentStatus });
};

const deleteSubTask = (subTaskId) => {
    router.delete(`/subtasks/${subTaskId}`);
};

const startEditSubTask = (sub) => {
    editingSubTask.value = sub.id;
    editSubTaskTitle.value = sub.title;
};

const saveEditSubTask = (subTaskId) => {
    if (!editSubTaskTitle.value.trim()) return;
    router.patch(`/subtasks/${subTaskId}`, { title: editSubTaskTitle.value.trim() });
    editingSubTask.value = null;
    editSubTaskTitle.value = '';
};

const cancelEditSubTask = () => {
    editingSubTask.value = null;
    editSubTaskTitle.value = '';
};

const canHaveSubTasks = (matrix) => matrix !== 'drop';

// Daily Targets
const addToToday = (type, id) => {
    router.post('/daily-targets', { targetable_type: type, targetable_id: id });
};

const matrixConfig = {
    do_first: {
        label: 'Do First',
        bgClass: 'bg-red-50',
        borderClass: 'border-red-200',
        textClass: 'text-red-600',
        badgeClass: 'badge-red',
        hoverClass: 'hover:bg-red-100',
        iconBg: 'bg-red-100',
    },
    schedule: {
        label: 'Do Next',
        bgClass: 'bg-blue-50',
        borderClass: 'border-blue-200',
        textClass: 'text-blue-600',
        badgeClass: 'badge-blue',
        hoverClass: 'hover:bg-blue-100',
        iconBg: 'bg-blue-100',
    },
    delegate: {
        label: 'Hand Off',
        bgClass: 'bg-amber-50',
        borderClass: 'border-amber-200',
        textClass: 'text-amber-600',
        badgeClass: 'badge-amber',
        hoverClass: 'hover:bg-amber-100',
        iconBg: 'bg-amber-100',
    },
    drop: {
        label: 'Ignore',
        bgClass: 'bg-gray-50',
        borderClass: 'border-gray-200',
        textClass: 'text-gray-500',
        badgeClass: 'badge-gray',
        hoverClass: 'hover:bg-gray-100',
        iconBg: 'bg-gray-100',
    },
};
</script>

<template>
    <Head title="Fokus" />

    <AuthenticatedLayout>
        <!-- Greeting -->
        <div class="mb-4 sm:mb-6">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">
                {{ greeting }}, {{ userName }}.
            </h1>
            <p class="text-gray-500 mt-0.5 text-[13px] sm:text-[15px]">
                Fokus hari ini. Tetap tenang dan tujuan.
            </p>
        </div>

        <!-- Hari Ini (Compact Link) -->
        <Link
            v-if="activeTargets.length > 0 || completedTargets.length > 0"
            href="/today"
            class="flex items-center gap-2.5 px-4 py-3 mb-4 sm:mb-6 rounded-card bg-emerald-50/50 border border-emerald-200 hover:bg-emerald-50 transition"
        >
            <span class="badge-emerald">Hari Ini</span>
            <span class="text-[13px] text-gray-600">
                {{ activeTargets.length }} target aktif
                <span v-if="completedTargets.length > 0" class="text-emerald-600">· {{ completedTargets.length }} selesai</span>
            </span>
            <span class="text-[12px] font-medium text-emerald-600 ml-auto">Lihat →</span>
        </Link>

        <!-- Inbox -->
        <div v-if="unprocessedTasks.length > 0" class="card p-4 mb-4 sm:mb-6">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="flex items-center gap-2">
                    <span class="badge-blue">Inbox</span>
                    <span class="text-[13px] text-gray-400 hidden sm:inline">{{ unprocessedTasks.length }} tugas belum diproses</span>
                    <span class="text-[13px] text-gray-400 sm:hidden">{{ unprocessedTasks.length }}</span>
                </div>
                <span class="text-xs text-gray-400 hidden sm:inline">Klik kategori untuk memindahkan</span>
            </div>

            <div class="space-y-1.5 max-h-[200px] sm:max-h-[320px] overflow-y-auto overflow-x-hidden scroll-hidden relative">
                <div
                    v-for="task in unprocessedTasks"
                    :key="task.id"
                    class="flex items-center justify-between p-2.5 rounded-btn hover:bg-gray-50 transition group"
                >
                    <span class="text-[15px] text-gray-700 truncate pr-4" :title="task.title">{{ task.title }}</span>

                    <div class="flex items-center gap-1">
                        <button
                            @click="completeTask(task.id)"
                            class="w-7 h-7 flex items-center justify-center rounded-md text-gray-300 hover:text-emerald-500 hover:bg-emerald-50 transition"
                            title="Tandai selesai"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <div class="hidden lg:flex items-center gap-1">
                            <button
                                v-for="matrix in ['do_first', 'schedule', 'delegate', 'drop']"
                                :key="matrix"
                                @click="assignMatrix(task.id, matrix)"
                                :class="[
                                    'px-2.5 py-1 text-[13px] font-semibold rounded-md transition',
                                    matrixConfig[matrix].badgeClass,
                                    matrixConfig[matrix].hoverClass,
                                ]"
                            >
                                {{ matrixConfig[matrix].label }}
                            </button>
                        </div>
                        <button @click="deleteTask(task.id)" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-300 hover:text-red-500 hover:bg-red-50 transition text-xs font-bold">
                            x
                        </button>
                    </div>
                </div>
                <div v-if="unprocessedTasks.length > 5"
                     class="sticky bottom-0 h-8 bg-gradient-to-t from-white via-white/80 to-transparent pointer-events-none" />
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4 lg:gap-6 items-start">

            <!-- Left: Carousel -->
            <div
                class="lg:col-span-6"
                @mouseenter="stopAutoPlay"
                @mouseleave="startAutoPlay"
            >
<div class="flex items-center justify-between mb-2 sm:mb-3">
                    <span class="section-title">Daily Review</span>
                    <span class="badge-gray">{{ unprocessedCount }} di Inbox</span>
                </div>

                <div class="card p-3 sm:p-6 flex flex-col items-center text-center min-h-[200px] sm:min-h-[340px] relative overflow-hidden">
                    <!-- Top -->
                    <div class="w-full flex items-center justify-between mb-4">
                        <span class="badge-red text-[13px] uppercase tracking-wider">Deep Work</span>
                        <span v-if="doFirst.length > 1" class="text-[13px] font-medium text-gray-400">
                            {{ currentSlide + 1 }} / {{ doFirst.length }}
                        </span>
                    </div>

                    <!-- Prev -->
                    <button
                        v-if="doFirst.length > 1"
                        @click="prevSlide(); restartAutoPlay()"
                        class="absolute left-1 sm:left-3 top-1/2 -translate-y-1/2 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Content -->
                    <div class="my-auto py-4 w-full">
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 leading-snug mb-2 break-words">
                            {{ currentTask ? currentTask.title : 'Tidak ada task' }}
                        </h2>
                        <p class="text-gray-500 text-[15px] leading-relaxed">
                            {{ currentTask ? `Prioritas #${currentSlide + 1} dari daftar Do First.` : 'Belum ada tugas di Do First.' }}
                        </p>
                    </div>

                    <!-- Next -->
                    <button
                        v-if="doFirst.length > 1"
                        @click="nextSlide(); restartAutoPlay()"
                        class="absolute right-1 sm:right-3 top-1/2 -translate-y-1/2 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Action -->
                    <div class="w-full mt-4">
                        <Link
                            :href="currentTask ? `/focus/session?task_id=${currentTask.id}` : '/focus/session'"
                            class="btn-primary btn-sm sm:btn-md w-full justify-center"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z" />
                            </svg>
                            Do Now (Pomodoro)
                        </Link>
                    </div>

                    <!-- Dots -->
                    <div v-if="doFirst.length > 1" class="flex items-center gap-1.5 mt-4">
                        <button
                            v-for="(_, index) in doFirst"
                            :key="index"
                            @click="goToSlide(index)"
                            :class="[
                                'h-1.5 rounded-full transition-all duration-300',
                                index === currentSlide ? 'w-5 bg-red-500' : 'w-1.5 bg-gray-200 hover:bg-gray-300'
                            ]"
                        ></button>
                    </div>
                </div>
            </div>

            <!-- Right: Matrix -->
            <div class="lg:col-span-6">
                <div class="flex items-center gap-2 mb-2 sm:mb-3">
                    <span class="section-title">Action Matrix</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div
                        v-for="(tasks, key) in { do_first: doFirst, schedule: schedule, delegate: delegate, drop: drop }"
                        :key="key"
                        :class="[
                            'card p-2.5 sm:p-4 min-h-[120px] sm:min-h-[180px] flex flex-col overflow-hidden',
                            matrixConfig[key].bgClass,
                            matrixConfig[key].borderClass,
                        ]"
                    >
                        <!-- Header -->
<div class="flex items-center justify-between mb-2 sm:mb-3">
                            <span :class="['text-[13px] font-bold', matrixConfig[key].textClass]">
                                {{ matrixConfig[key].label }}
                            </span>
                            <button
                                @click="toggleAddForm(key)"
                                :class="[
                                    'text-[13px] font-semibold px-2 py-0.5 rounded-md transition',
                                    matrixConfig[key].textClass,
                                    matrixConfig[key].hoverClass,
                                ]"
                            >
                                + Add
                            </button>
                        </div>

                        <!-- Inline Add Form -->
                        <div v-if="showAddFor === key" class="mb-2 animate-fade-in">
                            <form @submit.prevent="submitDirectAdd(key)" class="flex gap-1.5">
                                <input
                                    v-model="addTitle"
                                    ref="addInput"
                                    type="text"
                                    placeholder="Judul tugas..."
                                    class="input text-xs flex-1"
                                />
                                <button type="submit" class="btn-primary btn-sm !px-2.5 !py-1.5 text-[13px]">
                                    OK
                                </button>
                            </form>
                        </div>

                        <!-- Tasks -->
                        <ul v-if="tasks.length > 0" class="space-y-1.5 flex-1 max-h-[240px] overflow-y-auto overflow-x-hidden scroll-hidden relative">
                            <li
                                v-for="item in tasks"
                                :key="item.id"
                                :class="[
                                    'rounded-md px-2 py-1.5 -mx-2 transition',
                                    key === 'do_first' ? 'hover:bg-red-100/50' :
                                    key === 'schedule' ? 'hover:bg-blue-100/50' :
                                    key === 'delegate' ? 'hover:bg-amber-100/50' :
                                    'hover:bg-gray-100/50'
                                ]"
                            >
                                <!-- Task row -->
                                <div class="flex items-center justify-between gap-2 group text-[13px] sm:text-[15px]">
                                    <!-- Left: expand icon + title -->
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <button
                                            v-if="canHaveSubTasks(key) && item.sub_tasks && item.sub_tasks.length > 0"
                                            @click="toggleExpand(item.id)"
                                            class="w-5 h-5 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 transition shrink-0"
                                        >
                                            <svg
                                                :class="['w-3 h-3 transition-transform', expandedTask === item.id ? 'rotate-90' : '']"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <span
                                            class="truncate flex-1"
                                            :class="key === 'drop' ? 'text-gray-400 line-through' : 'text-gray-700'"
                                            :title="item.title"
                                        >
                                            {{ item.title }}
                                        </span>
                                    </div>

                                    <!-- Right: progress + actions -->
                                    <div class="flex items-center gap-1">
                                        <div class="hidden lg:flex items-center gap-1">
                                            <!-- Add to Hari Ini -->
                                            <button
                                                @click.stop="addToToday('Task', item.id)"
                                                class="w-7 h-7 flex items-center justify-center rounded-md text-gray-300 hover:text-emerald-500 hover:bg-emerald-50 transition"
                                                title="Tambah ke Hari Ini"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                            <!-- Progress badge -->
                                            <span
                                                v-if="canHaveSubTasks(key) && item.sub_tasks && item.sub_tasks.length > 0"
                                                :class="[
                                                    'text-[11px] font-semibold px-1.5 py-0.5 rounded-full',
                                                    item.progress === 100 ? 'bg-emerald-100 text-emerald-600' :
                                                    item.progress > 0 ? 'bg-blue-100 text-blue-600' :
                                                    'bg-gray-100 text-gray-500'
                                                ]"
                                            >
                                                {{ item.sub_tasks.filter(s => s.is_completed).length }}/{{ item.sub_tasks.length }}
                                            </span>
                                        </div>
                                        <!-- Sub-task button -->
                                        <button
                                            v-if="canHaveSubTasks(key)"
                                            @click.stop="toggleSubTaskInput(item.id)"
                                            class="w-7 h-7 flex items-center justify-center rounded-md text-gray-300 hover:text-violet-500 hover:bg-violet-50 transition"
                                            title="Tambah sub-task"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="completeTask(item.id)"
                                            class="w-7 h-7 flex items-center justify-center rounded-md text-gray-300 hover:text-emerald-500 hover:bg-emerald-50 transition"
                                            title="Tandai selesai"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <Link
                                            v-if="key !== 'drop'"
                                            :href="`/focus/session?task_id=${item.id}`"
                                            class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z" />
                                            </svg>
                                        </Link>
                                        <button @click="deleteTask(item.id)" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-300 hover:text-red-500 hover:bg-red-50 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Sub-task input -->
                                <div
                                    v-if="showSubTaskInput === item.id"
                                    class="mt-2 ml-7 animate-fade-in"
                                >
                                    <form @submit.prevent="addSubTask(item.id)" class="flex gap-1.5">
                                        <input
                                            v-model="newSubTaskTitle"
                                            type="text"
                                            placeholder="Judul sub-task..."
                                            class="input text-xs flex-1"
                                            ref="subTaskInput"
                                        />
                                        <button type="submit" class="btn-primary btn-sm !px-2.5 !py-1.5 text-[13px]">
                                            OK
                                        </button>
                                    </form>
                                </div>

                                <!-- Expanded sub-task list -->
                                <div
                                    v-if="expandedTask === item.id && canHaveSubTasks(key) && item.sub_tasks && item.sub_tasks.length > 0"
                                    class="mt-2 ml-5 space-y-1 animate-fade-in"
                                >
                                    <div
                                        v-for="sub in item.sub_tasks"
                                        :key="sub.id"
                                        class="flex items-center gap-2 px-2 py-1 rounded-md hover:bg-white/60 transition group/sub"
                                    >
                                        <button
                                            @click="toggleSubTask(sub.id, sub.is_completed)"
                                            :class="[
                                                'w-4 h-4 rounded border flex items-center justify-center transition shrink-0',
                                                sub.is_completed
                                                    ? 'bg-emerald-400 border-emerald-400 text-white'
                                                    : 'border-gray-300 hover:border-gray-400'
                                            ]"
                                        >
                                            <svg v-if="sub.is_completed" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <!-- Normal view -->
                                        <template v-if="editingSubTask !== sub.id">
                                            <span
                                                @dblclick="startEditSubTask(sub)"
                                                :class="[
                                                    'text-[13px] flex-1 cursor-default',
                                                    sub.is_completed ? 'text-gray-400 line-through' : 'text-gray-600'
                                                ]"
                                            >
                                                {{ sub.title }}
                                            </span>
                                            <button
                                                @click="addToToday('SubTask', sub.id)"
                                                class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-emerald-400 transition opacity-0 group-hover/sub:opacity-100"
                                                title="Tambah ke Hari Ini"
                                            >
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="startEditSubTask(sub)"
                                                class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-blue-400 transition opacity-0 group-hover/sub:opacity-100"
                                                title="Edit sub-task"
                                            >
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        </template>
                                        <!-- Edit view -->
                                        <template v-else>
                                            <input
                                                v-model="editSubTaskTitle"
                                                @keyup.enter="saveEditSubTask(sub.id)"
                                                @keyup.escape="cancelEditSubTask"
                                                @blur="saveEditSubTask(sub.id)"
                                                type="text"
                                                class="input text-xs flex-1 !py-1"
                                                autofocus
                                            />
                                        </template>
                                        <button
                                            @click="deleteSubTask(sub.id)"
                                            class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition opacity-0 group-hover/sub:opacity-100"
                                        >
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </li>
                            <li v-if="tasks.length > 5"
                                class="sticky bottom-0 h-6 pointer-events-none"
                                :class="{
                                    'bg-gradient-to-t from-red-50 to-transparent': key === 'do_first',
                                    'bg-gradient-to-t from-blue-50 to-transparent': key === 'schedule',
                                    'bg-gradient-to-t from-amber-50 to-transparent': key === 'delegate',
                                    'bg-gradient-to-t from-gray-50 to-transparent': key === 'drop'
                                }" />
                        </ul>
                        <p v-else class="text-[13px] text-gray-400 italic mt-1">Belum ada task</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
